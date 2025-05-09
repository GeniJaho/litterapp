<?php

namespace App\Models;

use App\DTO\UserSettings;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Http\UploadedFile;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Lab404\Impersonate\Models\Impersonate;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Jetstream\HasTeams;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property Collection<int, Photo> $photos
 * @property UserSettings $settings
 * @property Collection<int, Team> $ownedTeams
 * @property Collection<int, TagShortcut> $tagShortcuts
 * @property-read string $profile_photo_url
 * @property-read bool $is_admin
 * @property-read bool $is_being_impersonated
 */
class User extends Authenticatable implements FilamentUser, MustVerifyEmail
{
    use HasApiTokens;

    /** @use HasFactory<UserFactory> */
    use HasFactory;

    use HasProfilePhoto;
    use HasTeams;
    use Impersonate;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var list<string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'settings' => UserSettings::class.':default',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_admin;
    }

    public function canImpersonate(): bool
    {
        return $this->is_admin;
    }

    public function canBeImpersonated(): bool
    {
        return ! $this->is_admin;
    }

    /**
     * @return Attribute<bool, never>
     */
    protected function isBeingImpersonated(): Attribute
    {
        return Attribute::get(fn () => $this->isImpersonated());
    }

    /**
     * @return Attribute<bool, never>
     */
    protected function isAdmin(): Attribute
    {
        return Attribute::get(fn (): bool => in_array($this->email, [
            'admin@litterhero.com',
            'admin@litterapp.com',
            'suzefred@gmail.com',
            'pjhummelen@gmail.com',
        ]));
    }

    /**
     * Update the user's profile photo.
     */
    public function updateProfilePhoto(UploadedFile $photo, string $storagePath = 'profile-photos'): void
    {
        tap($this->profile_photo_path, function (?string $previous) use ($photo, $storagePath): void {
            $this->forceFill([
                'profile_photo_path' => $photo->store(
                    $storagePath, ['disk' => $this->profilePhotoDisk()]
                ),
            ])->save();

            if ($previous === null || $previous === '') {
                return;
            }

            Storage::disk($this->profilePhotoDisk())->delete($previous);
        });
    }

    /**
     * @return Attribute<string|null, never>
     */
    protected function profilePhotoUrl(): Attribute
    {
        return Attribute::get(function () {
            // path can sometimes be a full URL from Google or Facebook
            if (Str::isUrl($this->profile_photo_path)) {
                return $this->profile_photo_path;
            }

            return $this->profile_photo_path
                ? Storage::disk($this->profilePhotoDisk())->url($this->profile_photo_path)
                : $this->defaultProfilePhotoUrl();
        });
    }

    /**
     * Get the default profile photo URL if no profile photo has been uploaded.
     */
    protected function defaultProfilePhotoUrl(): string
    {
        $name = trim(collect(explode(' ', (string) $this->name))
            ->map(fn (string $segment): string => mb_substr($segment, 0, 1))
            ->join(' ')
        );

        return 'https://ui-avatars.com/api/?name='.urlencode($name).'&color=1F2937&background=e3faf8';
    }

    /**
     * @return HasMany<Photo, $this>
     */
    public function photos(): HasMany
    {
        return $this->hasMany(Photo::class);
    }

    /**
     * @return HasMany<TagShortcut, $this>
     */
    public function tagShortcuts(): HasMany
    {
        return $this->hasMany(TagShortcut::class);
    }
}
