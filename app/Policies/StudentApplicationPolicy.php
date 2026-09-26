<?php

namespace App\Policies;

use App\Models\StudentApplication;
use App\Models\User;

class StudentApplicationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_admin;
    }

    public function view(User $user, StudentApplication $studentApplication): bool
    {
        return $user->is_admin;
    }

    public function update(User $user, StudentApplication $studentApplication): bool
    {
        return $user->is_admin;
    }

    public function downloadDocument(User $user, StudentApplication $studentApplication): bool
    {
        return $user->is_admin;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function delete(User $user, StudentApplication $studentApplication): bool
    {
        return false;
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }
}
