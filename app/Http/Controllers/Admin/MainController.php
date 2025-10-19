<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MainController extends Controller
{
    //
    public function dashboard(Request $request)
    {
        return Inertia::render('admin/Dashboard', []);
    }
}
