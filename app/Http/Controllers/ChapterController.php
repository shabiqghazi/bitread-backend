<?php

namespace App\Http\Controllers;

use App\Models\Chapter;
use App\Models\Project;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ChapterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project)
    {
        try {
            $data = $project::with('chapters')->with('user')->where('id', $project->id)->get();
            return response()->json([
                'status' => 200,
                'message' => "Berhasil mengambil data chapters",
                'data' => $data
            ], 200);
        } catch (Throwable $e) {
            return response()->json([
                'status' => 500,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Project $project)
    {
        $data = [];
        try {
            $data = $request->validate([
                'title' => ['nullable'],
                'text' => ['nullable'],
                'image' => ['nullable'],
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'status' => 400,
                'message' => $e->getMessage(),
            ], 400);
        }
        try {
            if ($request->file('image')) {
                $data['image'] = Storage::putFile('chapter/image', $request->file('image'));
            }
        } catch (Throwable $e) {
            return response()->json([
                'status' => 500,
                'message' => $e->getMessage(),
            ], 500);
        }
        try {
            $chapter = new Chapter([
                'title' => isset($data['title']) ? $data['title'] : "",
                'text' => isset($data['text']) ? $data['text'] : "",
                'status' => json_encode(['status_id' => 1, 'label' => "draft"]),
                'image' => isset($data['image']) ? $data['image'] : null,
            ]);
            $project = Project::find($project->id);
            $chapter_created = $project->chapters()->save($chapter);
            return response()->json([
                'status' => 201,
                'message' => "Berhasil menambah chapter!",
                'data' => Chapter::with('project')->find($chapter_created->id),
            ], 201);
        } catch (Throwable $e) {
            return response()->json([
                'status' => 500,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project, Chapter $chapter)
    {
        try {
            $data = Project::with('user')->with(['chapters' => function ($query) use ($chapter) {
                $query->where('chapters.id', $chapter->id);
            }])->where('id', $project->id)->first();
            return response()->json([
                'status' => 200,
                'message' => "Berhasil mengambil data chapters",
                'data' => $data
            ], 200);
        } catch (Throwable $e) {
            return response()->json([
                'status' => 500,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project, Chapter $chapter)
    {
        $data = [];
        try {
            $data = $request->validate([
                'title' => ['nullable'],
                'status' => ['nullable'],
                'text' => ['nullable'],
                'image' => ['nullable'],
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'status' => 400,
                'message' => $e->getMessage(),
            ], 400);
        }
        try {
            if ($request->file('image')) {
                if ($chapter->image) {
                    Storage::delete($chapter->image);
                }
                $data['image'] = Storage::putFile('chapter/image', $request->file('image'));
            }
        } catch (Throwable $e) {
            return response()->json([
                'status' => 500,
                'message' => $e->getMessage(),
            ], 500);
        }
        try {
            $row = DB::table('chapters')->where('id', $chapter->id)->where('project_id', $project->id)->count();
            if ($row < 1) {
                Storage::delete($data['image']);
                return response()->json([
                    'status' => 404,
                    'message' => "Data tidak ditemukan",
                ], 404);
            }
            if (DB::table('chapters')->where('id', $chapter->id)->where('project_id', $project->id)->update($data) == 1) {
                return response()->json([
                    'status' => 200,
                    'message' => "Chapter berhasil disimpan",
                    'data' => DB::table('chapters')->where('id', $chapter->id)->where('project_id', $project->id)->first()
                ], 200);
            } else {
                return response()->json([
                    'status' => 422,
                    'message' => "Data tidak terubah",
                ], 422);
            }
        } catch (Throwable $e) {
            return response()->json([
                'status' => 500,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, Chapter $chapter)
    {

        try {
            $row = DB::table('chapters')->where('id', $chapter->id)->where('project_id', $project->id)->count();
            if ($row < 1) {
                return response()->json([
                    'status' => 404,
                    'message' => "Data tidak ditemukan",
                ], 404);
            }
            if (DB::table('chapters')->where('id', $chapter->id)->where('project_id', $project->id)->delete() == 1) {
                if ($chapter->image) {
                    Storage::delete($chapter->image);
                }
                return response()->json([
                    'status' => 200,
                    'message' => "Chapter berhasil dihapus",
                ], 200);
            } else {
                return response()->json([
                    'status' => 422,
                    'message' => "Gagal menghapus data",
                ], 422);
            }
        } catch (Throwable $e) {
            return response()->json([
                'status' => 500,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    public function saveDraft()
    {
        //
    }
    public function publish()
    {
        //
    }
}
