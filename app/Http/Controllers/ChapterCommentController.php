<?php

namespace App\Http\Controllers;

use App\Models\ChapterComment;
use App\Http\Requests\StoreChapterCommentRequest;
use App\Http\Requests\UpdateChapterCommentRequest;
use App\Models\Chapter;
use App\Models\Project;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Throwable;

class ChapterCommentController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Project $project, Chapter $chapter)
    {
        $data = [];
        try {
            $row = DB::table('chapters')->where('id', $chapter->id)->where('project_id', $project->id)->count();
            if ($row < 1) {
                throw new Exception("Data tidak ditemukan", 404);
            }
            $validator = Validator::make($request->all(), [
                'text' => 'required'
            ]);
            $data = array_merge($data, $validator->validated());
            if ($validator->fails()) {
                throw new Exception(implode(", ", $validator->messages()->all()), 400);
            }
            $data['user_id'] = $request->user()->id;
            $data['chapter_id'] = $chapter->id;
            $chapter_comment_created = ChapterComment::create($data);
        } catch (Throwable $e) {
            return response()->json([
                'status' => $e->getCode(),
                'message' => $e->getMessage(),
            ], $e->getCode());
        }
        return response()->json([
            'status' => 201,
            'message' => "Komentar berhasil ditambahkan",
            'data' => ChapterComment::find($chapter_comment_created->id)
        ], 201);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, Chapter $chapter, ChapterComment $chapterComment)
    {
        $this->authorize('delete', $chapterComment);
        try {
            $row = Chapter::with(['chapterComments' => function ($query) use ($chapterComment) {
                $query->where('chapter_comments.id', $chapterComment->id);
            }])->where('id', $chapter->id)->where('project_id', $project->id)->count();
            if ($row < 1) {
                throw new Exception("Data tidak ditemukan", 404);
            }
            if (DB::table('chapter_comments')->where('id', $chapterComment->id)->where('chapter_id', $chapter->id)->delete() != 1) {
                throw new Exception("Gagal menghapus data", 422);
            }
        } catch (Throwable $e) {
            return response()->json([
                'status' => $e->getCode(),
                'message' => $e->getMessage(),
            ], $e->getCode());
        }
        return response()->json([
            'status' => 200,
            'message' => "Komentar berhasil dihapus",
        ], 200);
    }
}
