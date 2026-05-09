# 07 — Multi-Item kNN Suggestions

## Context

The kNN prediction API returns 5 ranked item predictions, 3 brands, and 3 content tags per photo, but we only store and show the top-1 of each. Offline evaluation shows massive accuracy gains at Top-3:

| Category | Top-1 | Top-3 | Gain |
|----------|-------|-------|------|
| Item     | 68.7% | 86.2% | +17.5% |
| Brand    | 40.7% | 54.5% | +13.8% |
| Content  | 60.7% | 81.3% | +20.6% |

**Goal**: Show 3 item choices as mini cards + 3 brands and 3 content tags as checkboxes, so the user can quickly pick the correct ones. Track `accepted_item_rank` for post-deployment validation.

**No backwards compat needed**: Old suggestions were from a classifier model being scrapped. Old `photo_suggestions` rows will be ignored/deleted.

## Design Decisions

1. **One row per photo with JSON `predictions`** — stores the full ranked lists from the API. No separate rows per item.
2. **3 mini cards side-by-side** for items (radio select). No "similar photos" count displayed.
3. **Top-3 brands as checkboxes** (multi-select) — a photo can have multiple brands.
4. **Top-3 content as checkboxes** (multi-select) — a photo can have multiple content tags.
5. **Pre-check** brand/content if score >= 50%. User unchecks if wrong.
6. **Display threshold lowered to `item_score >= 30`** (from 50).
7. **Simple keyboard shortcuts**: `1`/`2`/`3` to select item card, `Ctrl+Enter` to accept.

## Data Model

### Migration: `add_predictions_to_photo_suggestions_table`

Add to `photo_suggestions`:

```php
$table->json('predictions')->nullable();
$table->unsignedTinyInteger('accepted_item_rank')->nullable(); // 1-based rank user picked
$table->boolean('brand_accepted')->nullable();    // did user keep any brand?
$table->boolean('content_accepted')->nullable();   // did user keep any content?
```

The existing flat columns (`item_id`, `item_score`, `item_count`, `brand_tag_id`, etc.) stay — they hold the top-1 prediction and are used by `SuggestionMetrics` and the Photo model's `has_item_suggestions` scope. We just stop relying on them in the frontend.

### `predictions` JSON shape

```json
{
  "items": [
    {"id": 903, "confidence": 0.449, "count": 9},
    {"id": 1031, "confidence": 0.100, "count": 2},
    {"id": 1029, "confidence": 0.099, "count": 2}
  ],
  "brands": [
    {"id": 6753, "confidence": 0.257},
    {"id": 4360, "confidence": 0.251},
    {"id": 4085, "confidence": 0.246}
  ],
  "content": [
    {"id": 6613, "confidence": 0.398},
    {"id": 4794, "confidence": 0.202},
    {"id": 6799, "confidence": 0.201}
  ]
}
```

Names are NOT stored (waste of space) — resolved via `Item::whereIn` + `Tag::whereIn` on read.

## Implementation Steps

### Step 1: Migration

**Create**: `database/migrations/..._add_predictions_to_photo_suggestions_table.php`

Add `predictions` (json nullable), `accepted_item_rank` (unsigned tinyint nullable), `brand_accepted` (boolean nullable), `content_accepted` (boolean nullable).

### Step 2: Model — `PhotoSuggestion`

**Edit**: `app/Models/PhotoSuggestion.php`

- Add `predictions`, `accepted_item_rank`, `brand_accepted`, `content_accepted` to `@property` PHPDoc
- Add casts: `predictions` → `'array'`, `accepted_item_rank` → `'integer'`, `brand_accepted` → `'boolean'`, `content_accepted` → `'boolean'`
- Add accessor `getPredictionItemsAttribute(): array` — resolves IDs from predictions JSON:
  ```php
  // Returns: ['items' => [['id' => 903, 'name' => 'Cigarette Butt', 'confidence' => 44], ...], 'brands' => [...], 'content' => [...]]
  // Confidence converted to 0-100 int for display
  ```
  Uses `Item::whereIn(ids)->pluck('name', 'id')` + `Tag::whereIn(ids)->pluck('name', 'id')` — two queries, cached per request.

### Step 3: DTO — `PhotoSuggestionResult`

**Edit**: `app/DTO/PhotoSuggestionResult.php`

- Replace `toSuggestionAttributes()` with a version that returns BOTH flat top-1 columns AND `predictions` key:
  ```php
  return [
      'item_id' => $topItem['id'],
      'item_score' => (int) round($topItem['confidence'] * 100),
      'item_count' => $topItem['count'],
      // ... brand/content flat columns (same as before) ...
      'predictions' => [
          'items' => array_map(fn ($i) => ['id' => $i['id'], 'confidence' => $i['confidence'], 'count' => $i['count']], array_slice($this->items, 0, 3)),
          'brands' => array_map(fn ($b) => ['id' => $b['id'], 'confidence' => $b['confidence']], array_slice($this->brands, 0, 3)),
          'content' => array_map(fn ($c) => ['id' => $c['id'], 'confidence' => $c['confidence']], array_slice($this->content, 0, 3)),
      ],
  ];
  ```
- Validate ALL item/tag IDs in predictions exist (batch `whereIn` queries). Filter out invalid ones.
- Limit to top 3 items, top 3 brands, top 3 content.

### Step 4: Job — `SuggestPhotoItem`

**Edit**: `app/Jobs/SuggestPhotoItem.php`

- No structural changes needed — `toSuggestionAttributes()` now returns predictions key automatically
- Keep the existing duplicate-item check (top-1 `item_id` already on photo → skip)

### Step 5: Factory — `PhotoSuggestionFactory`

**Edit**: `database/factories/PhotoSuggestionFactory.php`

- Add `withPredictions()` state:
  ```php
  public function withPredictions(array $itemIds = [], array $brandTagIds = [], array $contentTagIds = []): static
  ```
  Generates realistic predictions JSON with decreasing confidence scores.

### Step 6: JSON Response — `PhotosController::show()`

**Edit**: `app/Http/Controllers/Photos/PhotosController.php` (~line 97)

- After eager loading, append `prediction_items` to each suggestion:
  ```php
  $photo->photoSuggestions->each->append('prediction_items');
  ```
- This adds the resolved names to the JSON response.

### Step 7: Request Validation — `StorePhotoItemRequest`

**Edit**: `app/Http/Requests/Photos/StorePhotoItemRequest.php`

Add rules:
```php
'accepted_item_rank' => ['nullable', 'integer', 'min:1', 'max:5'],
'brand_tag_ids' => ['nullable', 'array'],
'brand_tag_ids.*' => ['integer', 'exists:tags,id'],
'content_tag_ids' => ['nullable', 'array'],
'content_tag_ids.*' => ['integer', 'exists:tags,id'],
```

### Step 8: Controller — `PhotoItemsController::store()`

**Edit**: `app/Http/Controllers/Photos/PhotoItemsController.php`

When `suggestion_id` is provided:
1. Mark `is_accepted = true`
2. Store `accepted_item_rank` from request
3. Collect tag IDs from `brand_tag_ids` + `content_tag_ids` arrays (instead of auto-applying >= 50)
4. Store `brand_accepted = !empty($request->brand_tag_ids)`, `content_accepted = !empty($request->content_tag_ids)`
5. Sync all tag IDs to the photo item
6. The item being added (`item_ids[0]`) may differ from the suggestion's `item_id` (user picked rank 2 or 3) — sync tags to the correct photo_item

### Step 9: Vue Component — `SuggestionPanel.vue`

**New file**: `resources/js/Pages/Photos/Partials/SuggestionPanel.vue`

(Replaces `SuggestedItem.vue` — old file can be deleted)

```
┌──────────────┐ ┌──────────────┐ ┌──────────────┐
│ [1]          │ │ [2]          │ │ [3]          │
│ Cigarette    │ │ Packaging    │ │ Pack/Box     │
│ Butt         │ │              │ │ (Cigarette)  │
│ 44%          │ │ 10%          │ │  9%          │
│ ▓▓▓▓░░░░░░  │ │ ▓░░░░░░░░░  │ │ ▓░░░░░░░░░  │
└──────────────┘ └──────────────┘ └──────────────┘

Brands:  ☑ Brand:Unknown  ☑ Red Bull  ☐ Marlboro
Content: ☑ Cigarettes     ☐ Energy drink  ☐ Cannabis

[Accept ⌘⏎]                         [Reject]

Picked Up ○    Recycled ○    Deposit ○
```

**Props**: `suggestion` (object with `prediction_items`), `photoItems` (array, to filter already-tagged items)

**State**:
- `selectedRank` — 1-indexed, defaults to 1
- `selectedBrandIds` — array, pre-populated with brands where `confidence * 100 >= 50`
- `selectedContentIds` — array, pre-populated with content where `confidence * 100 >= 50`

**Emits**:
- `accept-suggestion` → `{ itemId, rank, brandTagIds, contentTagIds }`
- `reject-suggestion`

**Behavior**:
- Filter out prediction items already present on the photo
- Re-number displayed items 1/2/3 (even if some filtered out)
- Selected card: highlighted border (accent color)
- `1`/`2`/`3` keys select card
- Brand/content checkboxes: click to toggle
- Hide brand section if no brands in predictions; same for content
- Dashed border styling (matches existing suggestion card aesthetic)
- Show confidence as percentage (multiply by 100 from JSON)
- Show a small confidence bar under the percentage

### Step 10: Vue Integration — `Show.vue`

**Edit**: `resources/js/Pages/Photos/Show.vue`

- Replace `suggestedItem` ref → `suggestion` ref
- `getPhoto()`: find first suggestion where `is_accepted === null` AND has `predictions` AND `item_score >= 30` AND has displayable items (not all already on photo)
- `addSuggestedItem(payload)` sends:
  ```js
  axios.post(`/photos/${photo.value.id}/items`, {
      item_ids: [payload.itemId],
      suggestion_id: suggestion.value.id,
      accepted_item_rank: payload.rank,
      brand_tag_ids: payload.brandTagIds,
      content_tag_ids: payload.contentTagIds,
  })
  ```
- `rejectSuggestedItem()` — unchanged (POST to reject endpoint)
- `Ctrl+Enter` — triggers accept on the SuggestionPanel
- When user manually selects an item from the search box that matches a prediction item: pass `suggestion_id` + `accepted_item_rank` in the POST
- `MagicWandIcon` still shown when suggestion exists
- Delete `SuggestedItem` import, replace with `SuggestionPanel`

### Step 11: Metrics — `SuggestionMetrics` command

**Edit**: `app/Console/Commands/SuggestionMetrics.php`

Add section **Acceptance by Rank** (after item acceptance):
```
  Rank Distribution (accepted with rank data)
  ┌──────────┬───────┬─────────┐
  │ Rank     │ Count │ % Share │
  ├──────────┼───────┼─────────┤
  │ Rank 1   │ 450   │ 72.6%   │
  │ Rank 2   │ 120   │ 19.4%   │
  │ Rank 3   │ 50    │ 8.1%    │
  └──────────┴───────┴─────────┘
  Multi-suggestion uplift: 27.4% of accepted were NOT top-1
```

Update brand/content sections to also check `brand_accepted`/`content_accepted` columns.

### Step 12: Filament — `PhotoSuggestionResource`

**Edit**: `app/Filament/Resources/PhotoSuggestionResource.php`

- Add `accepted_item_rank` text column (sortable)
- Add `brand_accepted` / `content_accepted` icon columns
- Add filter for accepted rank

## Edge Cases

| Case | Handling |
|---|---|
| Old rows without `predictions` JSON | Not displayed (old classifier model, being scrapped) |
| API returns < 3 items | Show however many cards are available |
| API returns 0 items | No suggestion row created |
| Photo already has a predicted item | Filter from displayed cards; if all filtered → hide panel |
| User manually picks item matching a prediction | Link suggestion with correct `accepted_item_rank` |
| Tag shortcut applied with suggestion | Marks `is_accepted = true`, `accepted_item_rank = null` |
| Brand/content score < 50 | Checkbox shown but unchecked by default |
| Only 1 displayable item after filtering | Show single mini card |
| Prediction has invalid item/tag IDs | Filtered out during `toSuggestionAttributes()` validation |

## Test Plan

### Unit Tests — `PhotoSuggestionResult`
- `toSuggestionAttributes()` returns flat columns + predictions JSON
- Predictions limited to top 3 per category
- Invalid item/tag IDs filtered out
- Empty items/brands/content handled

### Unit Tests — `SuggestPhotoItem` job
- Stores `predictions` JSON alongside flat columns
- Existing duplicate-item check still works

### Feature Tests — `PhotoItemsController`
- Accept at rank 2: correct item attached, `accepted_item_rank = 2` stored
- Accept with `brand_tag_ids`: tags applied, `brand_accepted = true`
- Accept with empty `brand_tag_ids`: no brand tags, `brand_accepted = false`
- Accept with `content_tag_ids`: tags applied, `content_accepted = true`
- Accept with `accepted_item_rank` + different item than top-1: works correctly

### Feature Tests — `PhotoSuggestionsController`
- Reject unchanged

### Feature Tests — `SuggestionMetrics`
- Rank distribution output

### Verification
1. `php artisan test --compact` on affected test files
2. `composer qa` (Pint + type-coverage + PHPStan)
3. Manual: dispatch job for a photo → verify predictions JSON stored → verify 3 cards + brand/content checkboxes rendered → test keyboard shortcuts → accept at rank 2 → check DB
