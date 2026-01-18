<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Jobs\CopyDefaultTagShortcutsAndPhotosJob;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['password'] = Hash::make('password');
        $data['email_verified_at'] = now();

        return $data;
    }

    protected function afterCreate(): void
    {
        /** @phpstan-ignore-next-line argument.type */
        CopyDefaultTagShortcutsAndPhotosJob::dispatch($this->record);
    }
}
