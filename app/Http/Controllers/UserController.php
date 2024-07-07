<?php

namespace App\Http\Controllers;

use App\Models\Organisation;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $request = $request->validate([
            'email' => 'required|string|email|max:255',
            'password' => 'required|string'
        ]);
        if (!auth('web')->attempt(['email' => $request['email'], 'password' => $request['password']])) {
            return response()->json([
                'status' => 'Bad request',
                'message' => 'Authentication failed',
                'statusCode' => Response::HTTP_UNAUTHORIZED
            ], Response::HTTP_UNAUTHORIZED);
        }
        $user = auth('web')->user();
        return response()->json([
            'status' => 'success',
            'message' => 'Login successful',
            'data' => [
                'accessToken' => $user->createToken('authToken')->accessToken,
                'user' => $user,
            ]
        ]);
    }

    public function register(Request $request): JsonResponse
    {
        $request = $request->validate([
           'email' => 'required|string|email|max:255|unique:users,email',
           'password' => 'required|string|min:8',
           'firstName' => 'required|string|max:255',
           'lastName' => 'required|string|max:255',
           'phone' => 'sometimes|string|max:255',
        ]);

        try {
            DB::beginTransaction();
            $user = User::create($request);
            $org = Organisation::create([
                'owner_id' => $user->userId,
                'name' => ucfirst($user->firstName) . '\'s Organisation'
            ]);
            $user->organisations()->attach($org);
            $accessToken = $user->createToken('authToken')->accessToken;
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Registration successful',
                'data' => [
                    'accessToken' => $accessToken,
                    'user' => $user
                ]
            ], Response::HTTP_CREATED);
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error($exception->getMessage());
            return response()->json([
                'status' => 'Bad request',
                'message' => 'Registration unsuccessful',
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function get_users(User $user): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Get users',
            'data' => [
                $user
            ]
        ]);
    }
}
