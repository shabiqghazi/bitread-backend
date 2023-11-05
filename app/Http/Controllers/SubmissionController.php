<?php

namespace App\Http\Controllers;

use App\Models\Submission;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class SubmissionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Book $book)
    {
        try {
            $data = Book::with('user')->with('submission')->where('id', $book->id)->get();
            return response()->json([
                'status' => 200,
                'message' => "Berhasil mengambil data submission",
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
    // public function store(StoreSubmissionRequest $request)
    // {

    // }

    /**
     * Display the specified resource.
     */
    public function show(Book $book, Submission $submission)
    {
        try {
            $data = Book::with('user')->with(['submission' => function ($query) use ($submission) {
                $query->where('submissions.id', $submission->id);
            }])->where('id', $book->id)->first();
            return response()->json([
                'status' => 200,
                'message' => "Berhasil mengambil data submission",
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
    public function update(Request $request, Book $book, Submission $submission)
    {
        $data = [];
        try {
            $data = $request->validate([
                'status' => ['nullable'],
                'draft' => ['nullable'],
                'last_message' => ['nullable'],
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'status' => 400,
                'message' => $e->getMessage(),
            ], 400);
        }
        try {
            if ($request->file('draft')) {
                if ($submission->draft) {
                    Storage::delete($submission->draft);
                }
                $data['draft'] = Storage::putFile('submission/draft', $request->file('draft'));
            }
        } catch (Throwable $e) {
            return response()->json([
                'status' => 500,
                'message' => $e->getMessage(),
            ], 500);
        }
        try {
            $row = DB::table('submissions')->where('book_id', $book->id)->where('id', $submission->id)->count();
            if ($row < 1) {
                return response()->json([
                    'status' => 404,
                    'message' => "Data tidak ditemukan",
                ], 404);
            }
            if (DB::table('submissions')->where('book_id', $book->id)->where('id', $submission->id)->update($data) == 1) {
                return response()->json([
                    'status' => 200,
                    'message' => "Pengajuan berhasil disimpan",
                    'data' => DB::table('submissions')->where('book_id', $book->id)->where('id', $submission->id)->first()
                ], 200);
            } else {
                return response()->json([
                    'status' => 422,
                    'message' => "Gagal mengubah data",
                ], 422);
            }
        } catch (Throwable $e) {
            if ($e->getCode() == 42000) {
                return response()->json([
                    'status' => 402,
                    'message' => "Tidak ada data yang diubah",
                ], 500);
            }
            return response()->json([
                'status' => 500,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Book $book, Submission $submission)
    {
        try {
            $row = DB::table('submissions')->where('book_id', $book->id)->where('id', $submission->id)->count();
            if ($row < 1) {
                if ($submission->draft) {
                    Storage::delete($submission->draft);
                }
                return response()->json([
                    'status' => 404,
                    'message' => "Data tidak ditemukan",
                ], 404);
            }
            if (DB::table('submissions')->where('book_id', $book->id)->where('id', $submission->id)->delete() == 1) {
                return response()->json([
                    'status' => 200,
                    'message' => "Pengajuan berhasil dihapus",
                ], 200);
            } else {
                return response()->json([
                    'status' => 422,
                    'message' => "Gagal menghapus data",
                ], 422);
            }
        } catch (Throwable $e) {
            if ($e->getCode() == 42000) {
                return response()->json([
                    'status' => 402,
                    'message' => "Tidak ada data yang diubah",
                ], 500);
            }
            return response()->json([
                'status' => 500,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
