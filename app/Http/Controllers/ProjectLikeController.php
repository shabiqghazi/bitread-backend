<?php

namespace App\Http\Controllers;

use App\Models\ProjectLike;
use App\Http\Requests\StoreProjectLikeRequest;
use App\Http\Requests\UpdateProjectLikeRequest;
use App\Models\Project;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Throwable;

class ProjectLikeController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Project $project)
    {
        $data = [];
        $data['user_id'] = $request->user()->id;
        $data['project_id'] = $project->id;

        try {
            $row = ProjectLike::where('user_id', $data['user_id'])->where('project_id', $project->id)->count();
            if ($row > 0) {
                throw new Exception('Tulisan sudah disukai sebelumnya', 409);
            }
            $project_like_created = ProjectLike::create($data);
        } catch (Throwable $e) {
            return response()->json([
                'status' => $e->getCode(),
                'message' => $e->getMessage(),
            ], $e->getCode());
        }
        return response()->json([
            'status' => 201,
            'message' => "Menyukai tulisan",
            'data' => ProjectLike::find($project_like_created->id)
        ], 201);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project)
    {
        try {
            if (DB::table('project_likes')->where('user_id', Auth::id())->where('project_id', $project->id)->delete() < 1) {
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
            'message' => "Batal menyukai tulisan.",
        ], 200);
    }
}
