<?php

namespace App\Http\Controllers\Api;

use App\Actions\Photos\ClassifyPhotoAction;
use App\Http\Requests\Api\UpdateLitterbotUrlRequest;
use App\Models\AppSetting;
use Illuminate\Container\Attributes\Config;
use Illuminate\Http\JsonResponse;

class LitterbotUrlController
{
    public function __invoke(
        UpdateLitterbotUrlRequest $request,
        #[Config('services.litterbot.update_key')] string $updateKey,
    ): JsonResponse {
        if (! hash_equals($updateKey, $request->string('key')->value())) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        AppSetting::query()
            ->where('key', 'litterbot_url')
            ->update(['value' => $request->string('url')]);

        cache()->forget(ClassifyPhotoAction::LITTERBOT_URL_CACHE_KEY);

        return response()->json(['message' => 'URL updated']);
    }
}
