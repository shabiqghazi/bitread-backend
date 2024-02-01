<?php

namespace App\Policies;

use App\Models\Book;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SubmissionPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Submission $submission): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Book $book): Response
    {
        if (json_decode($user->role)->role_id == 1) {
            return Response::allow();
        }
        if ($user->id == $book->user_id) {
            return Response::allow();
        }
        return Response::deny('You do not have access to this action.');
    }
}
