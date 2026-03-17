# Feature: Bulk Add Tags to Photos

## Summary
Added a new "Add Tags" feature to MyPhotos that allows users to bulk add tags to photos that have exactly 1 item.

## Branch
`feature/bulk-add-tags`

## Requirements from User

1. **Add Tags Button**: Create a new "Add Tags" button (similar to existing "Remove items & tags")
   - Located next to the existing bulk action buttons when photos are selected
   
2. **Tag Selection Modal**: 
   - Same kind of input screen as the remove option, but without "Items"
   - Button shows "Add" instead of "Remove"
   
3. **Tag Application Logic**:
   - Tags are added to ALL items that were already tagged on those photos
   - If a tag already exists on an item, it won't be added again (no duplicates)
   
4. **Safety Rules**:
   - **Photos with NO items**: Tags are NOT added (skipped with warning)
   - **Photos with MORE than 1 item**: Tags are NOT added (skipped with warning)
   - **Photos with EXACTLY 1 item**: Tags ARE added
   
5. **User Feedback**:
   - Confirmation dialog explains that tags go to "the item" (singular), not "all items"
   - Warning in confirmation: "Be aware, if a photo has more than 1 item, no tags will be added to that photo."

## Implementation Details

### Files Created

1. **DTO**: `app/DTO/BulkAddPhotoTags.php`
   - Validates `photo_ids` (required) and `tag_ids` (required)
   - Uses `PhotosBelongToUser` rule for authorization

2. **Vue Component**: `resources/js/Pages/Photos/Partials/BulkAddTags.vue`
   - Modal for selecting tags to add
   - Uses TagSelector component with `buttonText="Add"`
   - Shows confirmation modal before submitting

### Files Modified

1. **Controller**: `app/Http/Controllers/Photos/BulkPhotoItemsController.php`
   - Added `addTags()` method
   - Returns flash data with results (photos_with_no_items, photos_with_multiple_items, tags_added)

2. **Route**: `routes/web.php`
   - Added: `POST /photos/tags` -> `BulkPhotoItemsController@addTags`
   - Named: `bulk-photo-tags.add`

3. **TagSelector Component**: `resources/js/Pages/Photos/Partials/TagSelector.vue`
   - Added `buttonText` prop (default: "Remove")
   - Used by both BulkAddTags (buttonText="Add") and BulkRemoveItemsAndTags (default)

4. **Photos Index**: `resources/js/Pages/Photos/Index.vue`
   - Added import for BulkAddTags component
   - Added BulkAddTags component with v-if conditions

## Example Behavior

Given:
- Photo 1: has 1 item (blikje) with tags: [merk=redbull]
- Photo 2: has 2 items (blikje, flesje)
- Photo 3: has no items

When user selects all 3 photos and adds tag `content=energydrink`:

Result:
- Photo 1: blikje now has tags [merk=redbull, content=energydrink] ✓
- Photo 2: No changes (skipped - has 2 items)
- Photo 3: No changes (skipped - has no items)

## Text Updates

- Header text: "Add tags to the item on the selected photos. If a photo has more than 1 item, no tags will be added."
- Confirmation dialog: "Are you sure you want to add the tags to the item on the selected photos? Be aware, if a photo has more than 1 item, no tags will be added to that photo."
