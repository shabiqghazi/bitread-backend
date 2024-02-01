<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\ChapterCommentController;
use App\Http\Controllers\ChapterController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectLikeController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SubmissionController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('/search', [SearchController::class, 'index']);
Route::get('/books/search', [SearchController::class, 'books']);
Route::get('/projects/search', [SearchController::class, 'projects']);

Route::get('/books', [BookController::class, 'index']);
Route::get('/books/{book}', [BookController::class, 'show']);
Route::post('/books', [BookController::class, 'store'])->middleware(['auth', 'verified']);
Route::put('/books/{book}', [BookController::class, 'update'])->middleware(['auth', 'verified']);
Route::delete('/books/{book}', [BookController::class, 'destroy'])->middleware(['auth', 'verified']);

Route::get('/projects', [ProjectController::class, 'index']);
Route::get('/projects/{project}', [ProjectController::class, 'show']);
Route::post('/projects', [ProjectController::class, 'store'])->middleware(['auth', 'verified']);
Route::put('/projects/{project}', [ProjectController::class, 'update'])->middleware(['auth', 'verified']);
Route::delete('/projects/{project}', [ProjectController::class, 'destroy'])->middleware(['auth', 'verified']);

Route::get('/users', [UserController::class, 'index']);
Route::get('/users/{user}', [UserController::class, 'show']);
Route::put('/users/{user}', [UserController::class, 'update'])->middleware(['auth', 'verified']);
Route::delete('/users/{user}', [UserController::class, 'destroy'])->middleware(['auth', 'verified']);

Route::get('/projects/{project}/chapters', [ChapterController::class, 'index']);
Route::get('/projects/{project}/chapters/{chapter}', [ChapterController::class, 'show']);
Route::post('/projects/{project}/chapters', [ChapterController::class, 'store'])->middleware(['auth', 'verified']);
Route::put('/projects/{project}/chapters/{chapter}', [ChapterController::class, 'update'])->middleware(['auth', 'verified']);
Route::delete('/projects/{project}/chapters/{chapter}', [ChapterController::class, 'destroy'])->middleware(['auth', 'verified']);

Route::post('/projects/{project}/chapters/{chapter}/comments', [ChapterCommentController::class, 'store'])->middleware(['auth', 'verified']);
Route::delete('/projects/{project}/chapters/{chapter}/comments/{chapterComment}', [ChapterCommentController::class, 'destroy'])->middleware(['auth', 'verified']);

Route::post('/projects/{project}/likes', [ProjectLikeController::class, 'store'])->middleware(['auth', 'verified']);
Route::delete('/projects/{project}/likes', [ProjectLikeController::class, 'destroy'])->middleware(['auth', 'verified']);

Route::get('/books/{book}/submissions', [SubmissionController::class, 'index'])->middleware(['auth', 'verified']);
Route::put('/books/{book}/submissions', [SubmissionController::class, 'update'])->middleware(['auth', 'verified']);


Route::group(['middleware' => ['api']], function () {
    Route::post('/register', [RegisteredUserController::class, 'store'])
        ->middleware('guest')
        ->name('register');

    Route::post('/login', [AuthenticatedSessionController::class, 'store'])
        ->middleware('guest')
        ->name('login');

    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
        ->middleware('guest')
        ->name('password.email');

    Route::post('/reset-password', [NewPasswordController::class, 'store'])
        ->middleware('guest')
        ->name('password.store');

    Route::get('/verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware(['auth', 'throttle:6,1'])
        ->name('verification.send');

    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
        ->middleware(['auth', 'verified'])
        ->name('logout');
    Route::get('/get-authenticated-user', [AuthenticatedSessionController::class, 'show'])
        ->middleware(['auth', 'verified']);
    Route::put('/change-password', [AuthenticatedSessionController::class, 'update'])
        ->middleware(['auth', 'verified'])
        ->name('password.update');
});
