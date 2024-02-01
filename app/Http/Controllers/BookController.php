<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Submission;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Throwable;

class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query_params = [];
        $query_param_status = "";
        if (isset($request->all()['writers'])) {
            $query_params['writers'] = $request->all()['writers'];
        }
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
            $data = Book::with('user')->with('submission')->whereRelation('submission', 'status', 'like', '%' . $query_param_status . '%')->where($query_params)->paginate(12);
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
            if ($request->file('cover')) {
                $data['cover'] = Storage::putFile('book/cover', $request->file('cover'));
            }
            if ($request->file('file')) {
                $data['file'] = Storage::putFile('book/file', $request->file('file'));
            }
            $validator = Validator::make($request->all(), [
                'title' => ['required'],
                'writers' => ['required'],
                'category' => ['required'],
                'description' => ['nullable'],
                'price' => ['nullable'],
                'shop_links' => ['nullable', 'json']
            ]);
            $data = array_merge($data, $validator->validated());
            if ($validator->fails()) {
                throw new Exception(implode(", ", $validator->messages()->all()), 400);
            }
            $data['user_id'] = $request->user()->id;

            $book_created = Book::create($data);
            $submission = new Submission([
                'status' => json_encode(['status_id' => 0, 'label' => "Belum ada naskah"]),
                'last_message' => "Data buku berhasil dibuat.",
            ]);
            $book = Book::find($book_created->id);
            $book->submission()->save($submission);
        } catch (Throwable $e) {
            if (isset($data['file'])) {
                Storage::delete($data['file']);
            }
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
            'message' => "Buku berhasil diajukan!",
            'data' => $book->with('submission')->find($book->id),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Book $book)
    {
        try {
            $data = $book->with('user')->with('submission')->where('id', $book->id)->first();
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
        $this->authorize('update', $book);
        $data = [];
        try {
            if ($request->file('cover')) {
                $data['cover'] = Storage::putFile('book/cover', $request->file('cover'));
            }
            if ($request->file('file')) {
                $data['file'] = Storage::putFile('book/file', $request->file('file'));
            }
            $validator = Validator::make($request->all(), [
                'title' => ['nullable'],
                'writers' => ['nullable'],
                'status' => ['json', 'nullable'],
                'category' => ['nullable'],
                'description' => ['nullable'],
                'price' => ['nullable'],
                'shop_links' => ['nullable', 'json']
            ]);
            $data = array_merge($data, $validator->validated());
            if ($validator->fails()) {
                throw new Exception(implode(", ", $validator->messages()->all()), 400);
            }
            if (DB::table('books')->where('id', $book->id)->update($data) != 1) {
                throw new Exception("Data tidak berubah", 422);
            }
        } catch (Throwable $e) {
            if (isset($data['file'])) {
                Storage::delete($data['file']);
            }
            if (isset($data['cover'])) {
                Storage::delete($data['cover']);
            }
            return response()->json([
                'status' => $e->getCode(),
                'message' => $e->getMessage(),
            ], $e->getCode());
        }
        if ($request->file('cover')) {
            if ($book->cover) {
                Storage::delete($book->cover);
            }
        }
        if ($request->file('file')) {
            if ($book->file) {
                Storage::delete($book->file);
            }
        }
        return response()->json([
            'status' => 200,
            'message' => "Buku berhasil diubah",
            'data' => Book::find($book->id),
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Book $book)
    {
        $this->authorize('delete', $book);
        try {
            if (DB::table('books')->delete($book->id) != 1) {
                throw new Exception("Gagal menghapus data", 422);
            }
        } catch (Throwable $e) {
            return response()->json([
                'status' => $e->getCode(),
                'message' => $e->getMessage(),
            ], $e->getCode());
        }
        if ($book->cover) {
            Storage::delete($book->cover);
        }
        if ($book->file) {
            Storage::delete($book->file);
        }
        if ($book->submission?->draft) {
            Storage::delete($book->submission->draft);
        }
        return response()->json([
            'status' => 200,
            'message' => "Buku berhasil dihapus",
        ], 200);
    }
}
