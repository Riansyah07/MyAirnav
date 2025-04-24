@extends('layouts.main')

@section('title', 'Daftar Sertifikat')

@section('content')
<div class="container bg-white p-4 rounded shadow-sm">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>Daftar Sertifikat</h4>
        <a href="{{ route('superadmin.sertifikat.create') }}" class="btn btn-primary">+ Tambah Sertifikat</a>
    </div>

    {{-- Filter dan Pencarian --}}
    <form method="GET" class="row g-3 mb-4">
        <div class="col-md-4">
            <input type="text" name="search" class="form-control" placeholder="Cari judul..." value="{{ request('search') }}">
        </div>
        <div class="col-md-3">
            <input type="date" name="date" class="form-control" value="{{ request('date') }}">
        </div>
        <div class="col-md-2">
            <select id="sort" name="sort" class="form-control">
                <option value="">-- Urutkan --</option>
                <option value="asc" {{ request('sort') == 'asc' ? 'selected' : '' }}>A-Z</option>
                <option value="desc" {{ request('sort') == 'desc' ? 'selected' : '' }}>Z-A</option>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-secondary w-100">Filter</button>
        </div>
    </form>

    {{-- Tabel Sertifikat --}}
    <form id="bulk-action-form">
        @csrf
        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th><input type="checkbox" id="select-all"></th>
                        <th>Judul</th>
                        <th>Tipe</th>
                        <th>Tanggal Upload</th>
                        <th>Diunggah Oleh</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sertifikat as $item)
                        <tr id="row-{{ $item->id }}">
                            <td><input type="checkbox" name="certificate_ids[]" value="{{ $item->id }}"></td>
                            <td>{{ $item->title }}</td>
                            <td>{{ strtoupper($item->file_type) }}</td>
                            <td>{{ $item->created_at->format('d M Y') }}</td>
                            <td>{{ $item->user ? $item->user->name : 'Unknown' }}</td>
                            <td>
                                <a href="{{ route('superadmin.sertifikat.show', $item->id) }}" class="btn btn-sm btn-info">Lihat</a>
                                <a href="{{ route('superadmin.sertifikat.edit', $item->id) }}" class="btn btn-sm btn-warning">Edit</a>
                                <button type="button" class="btn btn-sm btn-danger" onclick="deleteCertificate({{ $item->id }})">Hapus</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">Tidak ada sertifikat ditemukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Bulk Action Buttons --}}
        <div class="mt-3 d-flex justify-content-between align-items-center">
            <div>
                <button type="button" class="btn btn-success" onclick="bulkDownload()">Download Terpilih</button>
                <button type="button" class="btn btn-danger" onclick="bulkDelete()">Hapus Terpilih</button>
            </div>
            <div>
                {{ $sertifikat->withQueryString()->links() }}
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    // Centang semua
    document.getElementById('select-all').addEventListener('change', function () {
        const checkboxes = document.querySelectorAll('input[name="certificate_ids[]"]');
        checkboxes.forEach(checkbox => checkbox.checked = this.checked);
    });

    function bulkDownload() {
        const formData = new FormData(document.getElementById('bulk-action-form'));
        fetch("{{ route('superadmin.sertifikat.bulkDownload') }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                window.location.href = data.zip_url;
            } else {
                alert(data.error || 'Terjadi kesalahan saat mengunduh file.');
            }
        });
    }

    function bulkDelete() {
        if (!confirm("Yakin ingin menghapus sertifikat terpilih?")) return;

        const formData = new FormData(document.getElementById('bulk-action-form'));
        fetch("{{ route('superadmin.sertifikat.bulkDelete') }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            alert(data.success || 'Data berhasil dihapus.');
            location.reload();
        });
    }

    function deleteCertificate(id) {
        if (!confirm("Yakin ingin menghapus sertifikat ini?")) return;

        fetch(`/superadmin/sertifikat/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                document.getElementById('row-' + id).remove();
                alert('Sertifikat berhasil dihapus.');
            } else {
                alert(data.error || 'Gagal menghapus sertifikat.');
            }
        });
    }
</script>
@endpush
