<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CustomerDashboardController extends Controller
{

    public function __construct()
    {
        $this->middleware('role:customer');
    }
    
    public function index()
    {
        return response()->json(['message' => 'Customer Dashboard']);
    }
}
