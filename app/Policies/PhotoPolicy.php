<?php

namespace App\Policies;

use App\Models\Photo;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PhotoPolicy
{
    public function manage(User $user, Photo $photo): Response
    {
        return $user->is_admin || $user->id === $photo->user_id
            ? Response::allow()
            : Response::denyAsNotFound();
    }
}
