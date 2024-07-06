<?php

namespace App\Http\Controllers;

use App\Models\Organisation;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public function login(): JsonResponse
    {
        return response()->json([]);
    }

    public function register(Request $request): JsonResponse
    {
        $request = $request->validate([
           'email' => 'required|string|email|max:255|unique:users,email',
           'password' => 'required|string|confirmed|min:8',
           'firstName' => 'required|string|max:255',
           'lastName' => 'required|string|max:255',
           'phone' => 'sometimes|string|max:255',
        ]);

        try {
            DB::beginTransaction();
            $user = User::create($request);
            Organisation::create([
                'owner_id' => $user->userId,
                'member_id' => $user->userId,
                'name' => $user->firstName . "'s Organisation"
            ]);
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Registration successful',
                'accessToken' => $user->createToken('authToken')->accessToken,
                'user' => $user,
            ], Response::HTTP_CREATED);
        } catch (\Exception $exception) {
            Log::error($exception->getMessage());
            DB::rollBack();
            return response()->json([
                'status' => 'Bad request',
                'message' => 'Registration unsuccessful',
            ], Response::HTTP_BAD_REQUEST);
        }



    }
}
