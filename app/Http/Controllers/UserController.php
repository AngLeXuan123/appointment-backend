<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function userList()
    {
        try {
            $userAuth = JWTAuth::user();
            if ($userAuth && $userAuth->role === 'admin') {
                $user = User::all();
                return response()->json([
                    'message' => 'User Listed',
                    'data' => $user,
                ]);
            } else {
                return response()->json([
                    'message' => 'Unauthorized. Only admin can view user records',
                ], 403);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error displaying user record',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function statusAccept($id)
    {
        try {
            $userAuth = JWTAuth::user();
            if ($userAuth && $userAuth->role === 'admin') {
                $user = User::findOrFail($id);
                $user->update([
                    'doctorStatus' => 'Accepted',
                ]);

                return response()->json([
                    'message' => 'Accepted',
                ]);
            } else {
                return response()->json([
                    'message' => 'Unauthorized. Only admin can use this action',
                ], 403);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error accepting doctor as user',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function statusReject($id)
    {
        try {
            $userAuth = JWTAuth::user();
            if ($userAuth && $userAuth->role === 'admin') {
                $user = User::findOrFail($id);
                $user->update([
                    'doctorStatus' => 'Rejected',
                ]);

                return response()->json([
                    'message' => 'Rejected',
                ]);
            } else {
                return response()->json([
                    'message' => 'Unauthorized. Only admin can use this action',
                ], 403);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error rejecting doctor as user',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function userListEdit(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|min:4|max:50',
        ]);

        if ($validator->fails()) {
            // Return a JSON response with validation errors and status code 422
            return response()->json([
                'errors' => $validator->errors()->toJson(),
                'message' => 'Validation failed',
            ], 422);
        }

        $users = User::findOrFail($id);

        if ($users->update($validator->validated())) {
            return response()->json([
                'success' => 'User successfully updated',
            ]);
        } else {
            return response()->json([
                'fail' => 'User failed to update',
            ]);
        }
    }
}
