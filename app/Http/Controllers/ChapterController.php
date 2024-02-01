<?php

namespace App\Http\Controllers;

use App\Models\Chapter;
use App\Models\Project;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Throwable;

class ChapterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project)
    {
        try {
            $data = $project::with([
                'chapters' => function ($q) {
                    $q->withCount('chapterComments');
                }
            ])->with('user')->where('id', $project->id)->first();
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
        $this->authorize('create', $project);
        $data = [];
        try {
            if ($request->file('image')) {
                $data['image'] = Storage::putFile('chapter/image', $request->file('image'));
            }
            $validator = Validator::make($request->all(), [
                'title' => ['nullable'],
                'text' => ['nullable'],
            ]);
            if ($validator->fails()) {
                throw new Exception(implode(", ", $validator->messages()->all()), 400);
            }
            $data = array_merge($data, $validator->validated());
            $chapter = new Chapter([
                'title' => isset($data['title']) ? $data['title'] : "",
                'text' => isset($data['text']) ? $data['text'] : "",
                'status' => json_encode(['status_id' => 1, 'label' => "draft"]),
                'image' => isset($data['image']) ? $data['image'] : null,
            ]);
            $project = Project::find($project->id);
            $chapter_created = $project->chapters()->save($chapter);
        } catch (Throwable $e) {
            if (isset($data['image'])) {
                Storage::delete($data['image']);
            }
            return response()->json([
                'status' => $e->getCode(),
                'message' => $e->getMessage(),
            ], $e->getCode());
        }
        return response()->json([
            'status' => 201,
            'message' => "Berhasil menambah chapter!",
            'data' => Chapter::with('project')->find($chapter_created->id),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project, Chapter $chapter)
    {
        try {
            $data = Project::with('user')->with(['chapters' => function ($query) use ($chapter) {
                $query->where('chapters.id', $chapter->id)->with('chapterComments');
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
        $this->authorize('update', $project);
        $data = [];
        try {
            if ($request->file('image')) {
                $data['image'] = Storage::putFile('chapter/image', $request->file('image'));
            }
            $row = DB::table('chapters')->where('id', $chapter->id)->where('project_id', $project->id)->count();
            if ($row < 1) {
                throw new Exception("Data tidak ditemukan", 404);
            }
            $validator = Validator::make($request->all(), [
                'title' => ['nullable'],
                'status' => ['nullable'],
                'text' => ['nullable'],
            ]);
            $data = array_merge($data, $validator->validated());
            if ($validator->fails()) {
                throw new Exception(implode(", ", $validator->messages()->all()), 400);
            }
            if (DB::table('chapters')->where('id', $chapter->id)->where('project_id', $project->id)->update($data) != 1) {
                throw new Exception("Data tidak berubah", 422);
            }
        } catch (Throwable $e) {
            if (isset($data['image'])) {
                Storage::delete($data['image']);
            }
            return response()->json([
                'status' => $e->getCode(),
                'message' => $e->getMessage(),
            ], $e->getCode());
        }
        if ($request->file('image')) {
            if ($chapter->image) {
                Storage::delete($chapter->image);
            }
        }
        return response()->json([
            'status' => 200,
            'message' => "Chapter berhasil disimpan",
            'data' => DB::table('chapters')->where('id', $chapter->id)->where('project_id', $project->id)->first()
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, Chapter $chapter)
    {
        $this->authorize('delete', $project);
        try {
            $row = DB::table('chapters')->where('id', $chapter->id)->where('project_id', $project->id)->count();
            if ($row < 1) {
                throw new Exception("Data tidak ditemukan", 404);
            }
            if (DB::table('chapters')->where('id', $chapter->id)->where('project_id', $project->id)->delete() != 1) {
                throw new Exception("Gagal menghapus data", 422);
            }
        } catch (Throwable $e) {
            return response()->json([
                'status' => $e->getCode(),
                'message' => $e->getMessage(),
            ], $e->getCode());
        }
        if ($chapter->image) {
            Storage::delete($chapter->image);
        }
        return response()->json([
            'status' => 200,
            'message' => "Chapter berhasil dihapus",
        ], 200);
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
