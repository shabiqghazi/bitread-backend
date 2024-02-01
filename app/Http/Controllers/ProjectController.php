<?php

namespace App\Http\Controllers;

use App\Models\Chapter;
use App\Models\Project;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Throwable;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query_params = [];
        $query_param_status = "";

        if (isset($request->all()['user_id'])) {
            $query_params['user_id'] = $request->all()['user_id'];
        }
        if (isset($request->all()['category'])) {
            $query_params['category'] = $request->all()['category'];
        }
        if (isset($request->all()['status'])) {
            $query_param_status = $request->all()['status'];
        }
        try {
            $data = Project::with('user')->withCount('chapters')->withCount('projectLikes')->where($query_params)->where('status', 'like', '%' . $query_param_status . '%')->paginate(12);
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
            if ($request->file('cover')) {
                $data['cover'] = Storage::putFile('project/cover', $request->file('cover'));
            }
            $validator = Validator::make($request->all(), [
                'title' => ['required'],
                'description' => ['nullable'],
                'category' => ['required'],
            ]);
            $data = array_merge($data, $validator->validated());
            if ($validator->fails()) {
                throw new Exception(implode(", ", $validator->messages()->all()), 400);
            }
            $data['user_id'] = $request->user()->id;
            $data['status'] = json_encode(['status_id' => 1, 'label' => 'draft']);

            $project_created = Project::create($data);
            $chapter = new Chapter([
                'title' => '',
                'text' => '',
                'status' => json_encode(['status_id' => 1, 'label' => "draft"]),
            ]);
            $project = Project::find($project_created->id);
            $project->chapters()->save($chapter);
        } catch (Throwable $e) {
            if (isset($data['cover'])) {
                Storage::delete($data['cover']);
            }
            return response()->json([
                'status' => $e->getCode(),
                'message' => $e->getMessage(),
            ], $e->getCode());
        }
        return response()->json([
            'status' => 201,
            'message' => "Tulisan berhasil disimpan!",
            'data' => $project->with('chapters')->find($project->id),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project)
    {
        try {
            $data = Project::with('user')->withCount('projectLikes')->find($project->id);
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
        $this->authorize('update', $project);
        $data = [];
        try {
            if ($request->file('cover')) {
                $data['cover'] = Storage::putFile('project/cover', $request->file('cover'));
            }
            $validator = Validator::make($request->all(), [
                'title' => ['nullable'],
                'description' => ['nullable'],
                'category' => ['nullable'],
                'status' => ['json', 'nullable']
            ]);
            $data = array_merge($data, $validator->validated());
            if ($validator->fails()) {
                throw new Exception(implode(", ", $validator->messages()->all()), 400);
            }
            if (DB::table('projects')->where('id', $project->id)->update($data) != 1) {
                throw new Exception("Data tidak berubah", 422);
            }
        } catch (Throwable $e) {
            if (isset($data['cover'])) {
                Storage::delete($data['cover']);
            }
            return response()->json([
                'status' => $e->getCode(),
                'message' => $e->getMessage(),
            ], $e->getCode());
        }
        if ($request->file('cover')) {
            if ($project->cover) {
                Storage::delete($project->cover);
            }
        }
        return response()->json([
            'status' => 200,
            'message' => "Tulisan berhasil diubah",
            'data' => Project::find($project->id),
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project)
    {
        $this->authorize('delete', $project);
        try {
            if (DB::table('projects')->delete($project->id) != 1) {
                throw new Exception("Gagal menghapus data", 422);
            };
        } catch (Throwable $e) {
            return response()->json([
                'status' => $e->getCode(),
                'message' => $e->getMessage(),
            ], $e->getCode());
        }
        if ($project->cover) {
            Storage::delete($project->cover);
        }
        $chapter_images = $project->chapters->map(function ($chapter) {
            return $chapter->image;
        });
        $chapter_images = array_filter($chapter_images->toArray());
        if ($chapter_images) {
            Storage::delete($chapter_images);
        }
        return response()->json([
            'status' => 200,
            'message' => "Tulisan berhasil dihapus",
        ], 200);
    }
}
