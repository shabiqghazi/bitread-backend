<?php

namespace App\Http\Controllers;

use App\Models\Chapter;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $data = Project::with('user')->get();
            return response()->json([
                'status' => 200,
                'message' => "Berhasil mengambil data tulisan",
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
    public function store(Request $request)
    {
        $data = [];
        try {
            $data = $request->validate([
                'title' => ['required'],
                'cover' => ['nullable'],
                'description' => ['nullable'],
                'category' => ['required'],
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'status' => 400,
                'message' => $e->getMessage(),
            ], 400);
        }
        $data['user_id'] = 1;
        $data['status'] = json_encode(['status_id' => 1, 'label' => 'draft']);

        try {
            if ($request->file('cover')) {
                $data['cover'] = Storage::putFile('project/cover', $request->file('cover'));
            }
        } catch (Throwable $e) {
            return response()->json([
                'status' => 500,
                'message' => $e->getMessage(),
            ], 500);
        }
        try {
            $project_created = Project::create($data);
            $chapter = new Chapter([
                'title' => '',
                'text' => '',
                'status' => json_encode(['status_id' => 1, 'label' => "draft"]),
            ]);
            $project = Project::find($project_created->id);
            $project->chapters()->save($chapter);
            return response()->json([
                'status' => 201,
                'message' => "Tulisan berhasil disimpan!",
                'data' => $project->with('chapters')->find($project->id),
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
    public function show(Project $project)
    {
        try {
            $data = Project::with('user')->find($project->id);
            return response()->json([
                'status' => 200,
                'message' => "Berhasil mengambil data tulisan",
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
    public function update(Request $request, Project $project)
    {
        $data = [];
        try {
            $data = $request->validate([
                'title' => ['nullable'],
                'cover' => ['nullable'],
                'description' => ['nullable'],
                'category' => ['nullable'],
                'status' => ['json', 'nullable']
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'status' => 400,
                'message' => $e->getMessage(),
            ], 400);
        }
        try {
            if ($request->file('cover')) {
                if ($project->cover) {
                    Storage::delete($project->cover);
                }
                $data['cover'] = Storage::putFile('project/cover', $request->file('cover'));
            }
        } catch (Throwable $e) {
            return response()->json([
                'status' => 500,
                'message' => $e->getMessage(),
            ], 500);
        }
        try {
            DB::table('projects')->where('id', $project->id)->update($data);
            return response()->json([
                'status' => 200,
                'message' => "Tulisan berhasil diubah",
                'data' => Project::find($project->id),
            ], 200);
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
    public function destroy(Project $project)
    {
        try {
            $chapter_images = $project->chapters->map(function ($chapter) {
                return $chapter->image;
            });
            $chapter_images = array_filter($chapter_images->toArray());
            DB::table('projects')->delete($project->id);
            if ($project->cover) {
                Storage::delete($project->cover);
            }
            if ($chapter_images) {
                Storage::delete($chapter_images);
            }
            return response()->json([
                'status' => 200,
                'message' => "Tulisan berhasil dihapus",
            ], 200);
        } catch (Throwable $e) {
            return response()->json([
                'status' => 500,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
