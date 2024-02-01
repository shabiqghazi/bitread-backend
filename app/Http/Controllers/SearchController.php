<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Project;
use Illuminate\Http\Request;
use Throwable;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $keyword = $request->all()['keyword'];
        try {
            $dataBooks = Book::with('user')
                ->where('title', 'like', '%' . $keyword . '%')
                ->orWhere('writers', 'like', '%' . $keyword . '%')
                ->orWhere('category', 'like', '%' . $keyword . '%')
                ->paginate(12);
            $dataProjects = Project::with('user')->withCount('chapters')->withCount('projectLikes')
                ->where('title', 'like', '%' . $keyword . '%')
                ->orWhere('category', 'like', '%' . $keyword . '%')
                ->paginate(12);
            $data = [
                'books' => $dataBooks,
                'projects' => $dataProjects
            ];
            return response()->json([
                'status' => 200,
                'message' => "Hasil pencarian untuk $keyword",
                'data' => $data
            ], 200);
        } catch (Throwable $e) {
            return response()->json([
                'status' => 500,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    public function books(Request $request)
    {
        $keyword = $request->all()['keyword'];
        try {
            $dataBooks = Book::with('user')
                ->where('title', 'like', '%' . $keyword . '%')
                ->orWhere('writers', 'like', '%' . $keyword . '%')
                ->orWhere('category', 'like', '%' . $keyword . '%')
                ->paginate(12);
            return response()->json([
                'status' => 200,
                'message' => "Hasil pencarian untuk $keyword",
                'data' => $dataBooks
            ], 200);
        } catch (Throwable $e) {
            return response()->json([
                'status' => 500,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    public function projects(Request $request)
    {
        $keyword = $request->all()['keyword'];
        try {
            $dataProjects = Project::with('user')->withCount('chapters')->withCount('projectLikes')
                ->where('title', 'like', '%' . $keyword . '%')
                ->orWhere('category', 'like', '%' . $keyword . '%')
                ->paginate(12);

            return response()->json([
                'status' => 200,
                'message' => "Hasil pencarian untuk $keyword",
                'data' => $dataProjects
            ], 200);
        } catch (Throwable $e) {
            return response()->json([
                'status' => 500,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
