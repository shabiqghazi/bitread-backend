<?php

namespace App\Policies;

use App\Models\Chapter;
use App\Models\Project;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ChapterPolicy
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
    public function view(User $user, Chapter $chapter): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, Project $project): Response
    {
        if ($user->id == $project->user_id) {
            return Response::allow();
        }
        return Response::deny('You do not have access to this action.');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Project $project): Response
    {
        if ($user->id == $project->user_id) {
            return Response::allow();
        }
        return Response::deny('You do not have access to this action.');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Project $project): Response
    {
        if (json_decode($user->role)->role_id == 1) {
            return Response::allow();
        }
        if ($user->id == $project->user_id) {
            return Response::allow();
        }
        return Response::deny('You do not have access to this action.');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Project $project): Response
    {
        if ($user->id == $project->user_id) {
            return Response::allow();
        }
        return Response::deny('You do not have access to this action.');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Project $project): Response
    {
        if ($user->id == $project->user_id) {
            return Response::allow();
        }
        return Response::deny('You do not have access to this action.');
    }
}
