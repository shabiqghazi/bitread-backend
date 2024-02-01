<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Throwable;
use Tymon\JWTAuth\Contracts\Providers\JWT;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            dd($request->all());
            $data = User::where($request->all())->paginate(12);
            return response()->json([
                'status' => 200,
                'message' => "Berhasil mengambil data user",
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
     * Display the specified resource.
     */
    public function show(User $user)
    {
        try {
            return response()->json([
                'status' => 200,
                'message' => "Berhasil mengambil data user",
                'data' => $user
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
    public function update(Request $request, User $user)
    {
        $data = [];
        try {
            if ($request->file('display_picture')) {
                $data['display_picture'] = Storage::putFile('user/display_picture', $request->file('display_picture'));
            }
            $validator = Validator::make($request->all(), [
                'name' => ['nullable'],
                'role' => ['nullable', 'json'],
                'gender' => ['nullable'],
                'bio' => ['nullable'],
                'social' => ['nullable', 'json'],
            ]);
            $data = array_merge($data, $validator->validated());
            if ($validator->fails()) {
                throw new Exception(implode(", ", $validator->messages()->all()), 400);
            }
            if (DB::table('users')->where('id', $user->id)->update($data) != 1) {
                throw new Exception("Data tidak berubah", 422);
            }
        } catch (Throwable $e) {
            if (isset($data['display_picture'])) {
                Storage::delete($data['display_picture']);
            }
            return response()->json([
                'status' => $e->getCode(),
                'message' => $e->getMessage(),
            ], $e->getCode());
        }
        if ($request->file('display_picture')) {
            if ($user->display_picture) {
                Storage::delete($user->display_picture);
            }
        }
        return response()->json([
            'status' => 200,
            'message' => "Profil berhasil diubah",
            'data' => User::find($user->id),
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        try {
            if (DB::table('users')->delete($user->id) != 1) {
                throw new Exception("Gagal menghapus data", 422);
            }
        } catch (Throwable $e) {
            return response()->json([
                'status' => $e->getCode(),
                'message' => $e->getMessage(),
            ], $e->getCode());
        }
        if ($user->display_picture) {
            Storage::delete($user->display_picture);
        }
        return response()->json([
            'status' => 200,
            'message' => "User berhasil dihapus",
        ], 200);
    }
}
