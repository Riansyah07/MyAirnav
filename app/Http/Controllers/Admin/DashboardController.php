<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Document;

class DashboardController extends Controller
{
    public function index()
    {
        $totalUsers = User::count();
        $totalDocuments = Document::count();

        return view('admin.dashboard', compact('totalUsers', 'totalDocuments'));
    }
}
