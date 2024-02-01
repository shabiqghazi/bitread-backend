<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
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
     * Update the specified resource in storage.
     */
    public function update(Request $request, Book $book)
    {
        $this->authorize('update', $book);
        $data = [];
        try {
            if ($request->file('draft')) {
                $data['draft'] = Storage::putFile('submission/draft', $request->file('draft'));
            }
            $validator = Validator::make($request->all(), [
                'status' => ['nullable'],
                'last_message' => ['nullable'],
            ]);
            $data = array_merge($data, $validator->validated());
            if ($validator->fails()) {
                throw new Exception(implode(", ", $validator->messages()->all()), 400);
            }
            if (DB::table('submissions')->where('id', $book->id)->update($data) != 1) {
                throw new Exception("Data tidak berubah", 422);
            }
        } catch (Throwable $e) {
            return response()->json([
                'status' => $e->getCode(),
                'message' => $e->getMessage(),
            ], $e->getCode());
        }
        if ($request->file('draft')) {
            if ($book->submission?->draft) {
                Storage::delete($book->submission->draft);
            }
        }
        return response()->json([
            'status' => 200,
            'message' => "Pengajuan berhasil disimpan",
            'data' => DB::table('submissions')->where('id', $book->id)->first()
        ], 200);
    }
}
