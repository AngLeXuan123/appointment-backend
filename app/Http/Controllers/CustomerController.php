<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CustomerController extends Controller
{

    public function __construct()
    {
        $this->middleware('role:customer');
    }
    
    public function dashboard()
    {
        return response()->json(['message' => 'Customer Dashboard']);
    }
}
