<?php

use App\Http\Controllers\BookController;
use App\Http\Controllers\ChapterController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\SubmissionController;
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

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});


Route::get('/books', [BookController::class, 'index']);
Route::get('/books/{book}', [BookController::class, 'show']);
Route::post('/books', [BookController::class, 'store']);
Route::put('/books/{book}', [BookController::class, 'update']);
Route::delete('/books/{book}', [BookController::class, 'destroy']);

Route::get('/projects', [ProjectController::class, 'index']);
Route::get('/projects/{project}', [ProjectController::class, 'show']);
Route::post('/projects', [ProjectController::class, 'store']);
Route::put('/projects/{project}', [ProjectController::class, 'update']);
Route::delete('/projects/{project}', [ProjectController::class, 'destroy']);

Route::get('/projects/{project}/chapters', [ChapterController::class, 'index']);
Route::get('/projects/{project}/chapters/{chapter}', [ChapterController::class, 'show']);
Route::post('/projects/{project}/chapters', [ChapterController::class, 'store']);
Route::put('/projects/{project}/chapters/{chapter}', [ChapterController::class, 'update']);
Route::delete('/projects/{project}/chapters/{chapter}', [ChapterController::class, 'destroy']);

Route::get('/books/{book}/submissions', [SubmissionController::class, 'index']);
Route::get('/books/{book}/submissions/{submission}', [SubmissionController::class, 'show']);
Route::post('/books/{book}/submissions', [SubmissionController::class, 'store']);
Route::put('/books/{book}/submissions/{submission}', [SubmissionController::class, 'update']);
Route::delete('/books/{book}/submissions/{submission}', [SubmissionController::class, 'destroy']);
