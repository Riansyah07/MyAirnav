<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Document;
use App\Models\Certificate;
use App\Models\Loca;

class DashboardController extends Controller
{
    public function index()
{
    $totalUsers = User::count();
    $totalDocuments = Document::count();
    $totalCertificates = Certificate::count();
    $totalLoca = Loca::count();

    return view('superadmin.dashboard', compact('totalUsers', 'totalDocuments', 'totalCertificates', 'totalLoca'));
}
}

