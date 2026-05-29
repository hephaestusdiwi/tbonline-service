<?php

namespace App\Policies;

use App\Models\Content;
use App\Models\User;

class ContentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'manager', 'staff']);
    }

    public function view(User $user, Content $content): bool
    {
        return $user->hasAnyRole(['admin', 'manager', 'staff']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'manager', 'staff']);
    }

    public function update(User $user, Content $content): bool
    {
        if ($user->hasRole('staff')) {
            return $content->status === Content::STATUS_DRAFT;
        }

        return $user->hasAnyRole(['admin', 'manager']);
    }

    public function publish(User $user, Content $content): bool
    {
        return $user->hasAnyRole(['admin', 'manager']);
    }

    public function delete(User $user,  Content $content): bool
    {
        return $user->hasRole('admin');
    }

    public function restore(User $user, Content $content): bool
    {
        return $user->hasRole('admin');
    }

    public function forceDelete(User $user, Content $content): bool
    {
        return $user->hasRole('admin');
    }
}