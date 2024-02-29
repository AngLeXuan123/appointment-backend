<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DoctorDashboardController extends Controller
{

    public function __construct()
    {
        $this->middleware('role:doctor');
    }
    
    public function index()
    {
        return response()->json(['message' => 'Doctor Dashboard']);
    }
}
