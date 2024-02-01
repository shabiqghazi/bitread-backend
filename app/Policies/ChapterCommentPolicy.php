<?php

namespace App\Policies;

use App\Models\ChapterComment;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ChapterCommentPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        //
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ChapterComment $chapterComment): bool
    {
        //
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        //
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ChapterComment $chapterComment): bool
    {
        //
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ChapterComment $chapterComment): Response
    {
        if (json_decode($user->role)->role_id == 1) {
            return Response::allow();
        }
        if ($user->id == $chapterComment->user_id) {
            return Response::allow();
        }
        return Response::deny('You do not have access to this action.');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ChapterComment $chapterComment): bool
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ChapterComment $chapterComment): bool
    {
        //
    }
}
