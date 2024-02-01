<?php

namespace App\Providers;

use App\Models\Book;
use App\Models\Chapter;
use App\Models\ChapterComment;
use App\Models\Project;
use App\Models\ProjectLike;
use App\Models\Submission;
use App\Policies\BookPolicy;
use App\Policies\ChapterCommentPolicy;
use App\Policies\ChapterPolicy;
use App\Policies\ProjectLikePolicy;
use App\Policies\ProjectPolicy;
use App\Policies\SubmissionPolicy;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Book::class => BookPolicy::class,
        Chapter::class => ChapterPolicy::class,
        Project::class => ProjectPolicy::class,
        Submission::class => SubmissionPolicy::class,
        ChapterComment::class => ChapterCommentPolicy::class,
        ProjectLike::class => ProjectLikePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        ResetPassword::createUrlUsing(function (object $notifiable, string $token) {
            return config('app.frontend_url') . "/password-reset/$token?email={$notifiable->getEmailForPasswordReset()}";
        });

        //
    }
}
