<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $data = Book::with('user')->get();
            return response()->json([
                'status' => 200,
                'message' => "Berhasil mengambil data buku",
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
                'writers' => ['required'],
                'category' => ['required'],
                'description' => ['nullable'],
                'price' => ['nullable'],
                'shop_links' => ['nullable', 'json']
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'status' => 400,
                'message' => $e->getMessage(),
            ], 400);
        }

        // ganti user_id
        $data['user_id'] = 1;

        try {
            if ($request->file('cover')) {
                $data['cover'] = Storage::putFile('book/cover', $request->file('cover'));
            }
        } catch (Throwable $e) {
            return response()->json([
                'status' => 500,
                'message' => $e->getMessage(),
            ], 500);
        }
        try {
            if ($request->file('file')) {
                $data['file'] = Storage::putFile('book/file', $request->file('file'));
            }
        } catch (Throwable $e) {
            return response()->json([
                'status' => 500,
                'message' => $e->getMessage(),
            ], 500);
        }
        try {
            $book_created = Book::create($data);
            $submission = new Submission([
                'status' => json_encode(['status_id' => 0, 'label' => "Belum ada naskah"]),
                'last_message' => "Data buku berhasil dibuat.",
            ]);
            $book = Book::find($book_created->id);
            $book->submission()->save($submission);
            return response()->json([
                'status' => 201,
                'message' => "Buku berhasil diajukan!",
                'data' => $book->with('submission')->find($book->id),
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
    public function show(Book $book)
    {
        try {
            $data = $book->with('user')->where('id', $book->id)->first();
            return response()->json([
                'status' => 200,
                'message' => "Berhasil mengambil data buku",
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
    public function update(Request $request, Book $book)
    {
        $data = [];
        try {
            $data = $request->validate([
                'title' => ['nullable'],
                'writers' => ['nullable'],
                'status' => ['json', 'nullable'],
                'category' => ['nullable'],
                'description' => ['nullable'],
                'price' => ['nullable'],
                'shop_links' => ['nullable', 'json']
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'status' => 400,
                'message' => $e->getMessage(),
            ], 400);
        }
        try {
            if ($request->file('cover')) {
                if ($book->cover) {
                    Storage::delete($book->cover);
                }
                $data['cover'] = Storage::putFile('book/cover', $request->file('cover'));
            }
        } catch (Throwable $e) {
            return response()->json([
                'status' => 500,
                'message' => $e->getMessage(),
            ], 500);
        }
        try {
            if ($request->file('file')) {
                if ($book->file) {
                    Storage::delete($book->file);
                }
                $data['file'] = Storage::putFile('book/file', $request->file('file'));
            }
        } catch (Throwable $e) {
            return response()->json([
                'status' => 500,
                'message' => $e->getMessage(),
            ], 500);
        }
        try {
            DB::table('books')->where('id', $book->id)->update($data);
            return response()->json([
                'status' => 200,
                'message' => "Buku berhasil diubah",
                'data' => Book::find($book->id),
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
    public function destroy(Book $book)
    {
        try {
            $submission_draft = $book->submission->draft;
            DB::table('books')->delete($book->id);
            if ($book->cover) {
                Storage::delete($book->cover);
            }
            if ($book->file) {
                Storage::delete($book->file);
            }
            if ($submission_draft) {
                Storage::delete($submission_draft);
            }
            return response()->json([
                'status' => 200,
                'message' => "Buku berhasil dihapus",
            ], 200);
        } catch (Throwable $e) {
            return response()->json([
                'status' => 500,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
