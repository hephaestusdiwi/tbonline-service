<?php

namespace App\Policies;

use App\Models\Faq;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class FaqPolicy
{
    use HandlesAuthorization;
 
    /** Semua role terautentikasi bisa lihat daftar FAQ di admin */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'manager', 'staff']);
    }
 
    public function view(User $user, Faq $faq): bool
    {
        return $user->hasAnyRole(['admin', 'manager', 'staff']);
    }
 
    /** Hanya admin & manager yang bisa membuat FAQ */
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'manager']);
    }
 
    /** Hanya admin & manager yang bisa mengubah FAQ */
    public function update(User $user, Faq $faq): bool
    {
        return $user->hasAnyRole(['admin', 'manager']);
    }
 
    /** Hanya admin & manager yang bisa menghapus FAQ */
    public function delete(User $user, Faq $faq): bool
    {
        return $user->hasAnyRole(['admin', 'manager']);
    }
 
    public function restore(User $user, Faq $faq): bool
    {
        return $user->hasRole('admin');
    }
 
    public function forceDelete(User $user, Faq $faq): bool
    {
        return $user->hasRole('admin');
    }
}
 