<?php

namespace App\Policies;

use App\Models\EducationalGrade;
use App\Models\User;

class EducationalGradePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_admin;
    }

    public function view(User $user, EducationalGrade $educationalGrade): bool
    {
        return $user->is_admin;
    }

    public function create(User $user): bool
    {
        return $user->is_admin;
    }

    public function update(User $user, EducationalGrade $educationalGrade): bool
    {
        return $user->is_admin;
    }

    public function delete(User $user, EducationalGrade $educationalGrade): bool
    {
        return $user->is_admin && ! $educationalGrade->studentApplications()->exists();
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }
}
