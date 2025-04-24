<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use ZipArchive;

class CertificateController extends Controller
{
    public function index(Request $request)
    {
        $query = Certificate::query();

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        if ($request->sort === 'asc') {
            $query->orderBy('title', 'asc');
        } elseif ($request->sort === 'desc') {
            $query->orderBy('title', 'desc');
        } else {
            $query->latest();
        }

        $sertifikat = $query->paginate(10);

        return view('superadmin.sertifikat.index', compact('sertifikat'));
    }

    public function create()
    {
        return view('superadmin.sertifikat.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'file' => 'required|file|mimes:pdf,docx',
        ]);

        $file = $request->file('file')->store('sertifikat', 'public');

        $certificate = Certificate::create([
            'title' => $validated['title'],
            'file_path' => $file,
            'file_type' => $request->file('file')->getClientOriginalExtension(),
            'user_id' => Auth::id(),
        ]);

        Notification::create([
            'user_id' => Auth::id(),
            'role' => Auth::user()->role,
            'message' => 'Menambahkan sertifikat "' . $validated['title'] . '"',
            'type' => 'create',
            'created_at' => now(),
        ]);

        return redirect()->route('superadmin.sertifikat.index')->with('success', 'Sertifikat berhasil ditambahkan.');
    }

    public function show($id)
    {
        $sertifikat = Certificate::findOrFail($id);
        return view('superadmin.sertifikat.show', compact('sertifikat'));
    }

    public function edit($id)
    {
        $sertifikat = Certificate::findOrFail($id);
        return view('superadmin.sertifikat.edit', compact('sertifikat'));
    }

    public function update(Request $request, $id)
    {
        $sertifikat = Certificate::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'file' => 'nullable|file|mimes:pdf,docx',
        ]);

        if ($request->hasFile('file')) {
            if ($sertifikat->file_path && Storage::disk('public')->exists($sertifikat->file_path)) {
                Storage::disk('public')->delete($sertifikat->file_path);
            }

            $newFile = $request->file('file')->store('sertifikat', 'public');

            $sertifikat->update([
                'file_path' => $newFile,
                'file_type' => $request->file('file')->getClientOriginalExtension(),
            ]);
        }

        $sertifikat->update([
            'title' => $validated['title'],
        ]);

        Notification::create([
            'user_id' => Auth::id(),
            'role' => Auth::user()->role,
            'message' => 'Mengedit sertifikat "' . $validated['title'] . '"',
            'type' => 'edit',
            'created_at' => now(),
        ]);

        return redirect()->route('superadmin.sertifikat.index')->with('success', 'Sertifikat berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $sertifikat = Certificate::findOrFail($id);

        if ($sertifikat->file_path && Storage::disk('public')->exists($sertifikat->file_path)) {
            Storage::disk('public')->delete($sertifikat->file_path);
        }

        $sertifikat->delete();

        Notification::create([
            'user_id' => Auth::id(),
            'role' => Auth::user()->role,
            'message' => 'Menghapus sertifikat "' . $sertifikat->title . '"',
            'type' => 'delete',
            'created_at' => now(),
        ]);

        return response()->json(['success' => 'Sertifikat berhasil dihapus.']);
    }

    public function bulkDownload(Request $request)
    {
        $request->validate([
            'certificate_ids' => 'required|array',
            'certificate_ids.*' => 'integer|exists:certificates,id',
        ]);

        $sertifikat = Certificate::whereIn('id', $request->certificate_ids)->get();

        if ($sertifikat->isEmpty()) {
            return response()->json(['error' => 'Tidak ada sertifikat yang dipilih.'], 400);
        }

        $zip = new ZipArchive;
        $zipFileName = 'sertifikat-terpilih-' . now()->format('YmdHis') . '.zip';
        $zipPath = storage_path('app/public/' . $zipFileName);

        if ($zip->open($zipPath, ZipArchive::CREATE) === TRUE) {
            foreach ($sertifikat as $item) {
                $filePath = storage_path('app/public/' . $item->file_path);
                if (file_exists($filePath)) {
                    $zip->addFile($filePath, basename($filePath));
                }
            }
            $zip->close();
        }

        return response()->json([
            'success' => true,
            'zip_url' => asset('storage/' . $zipFileName)
        ]);
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'certificate_ids' => 'required|array',
            'certificate_ids.*' => 'integer|exists:certificates,id',
        ]);

        $sertifikat = Certificate::whereIn('id', $request->certificate_ids)->get();

        foreach ($sertifikat as $item) {
            if ($item->file_path && Storage::disk('public')->exists($item->file_path)) {
                Storage::disk('public')->delete($item->file_path);
            }
            Notification::create([
                'user_id' => Auth::id(),
                'role' => Auth::user()->role,
                'message' => 'Menghapus sertifikat "' . $item->title . '"',
                'type' => 'delete',
                'created_at' => now(),
            ]);
            $item->delete();
        }

        return response()->json(['success' => 'Sertifikat terpilih berhasil dihapus.']);
    }
}
