<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:admin');
    }

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
}
