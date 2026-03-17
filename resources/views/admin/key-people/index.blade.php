@extends('admin.layouts.admin')

@section('title', 'Key People')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item" aria-current="page">Key People</li>
@endsection
@section('page_title', 'Team Members Management')

@push('styles')
    <!-- data tables css -->
    <link rel="stylesheet" href="{{ asset('admin_assets/css/plugins/dataTables.bootstrap5.min.css') }}">
@endpush

@section('content')
<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5>Our Team</h5>
                <a href="{{ route('admin.key-people.create') }}" class="btn btn-primary">
                    <i class="ti ti-plus"></i> Add Team Member
                </a>
            </div>
            <div class="card-body">
                
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                
                <div class="table-responsive dt-responsive">
                    <table id="people-table" class="table table-striped table-bordered nowrap">
                        <thead>
                            <tr>
                                <th>Order</th>
                                <th>Photo</th>
                                <th>Name</th>
                                <th>Role</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($people as $person)
                            <tr>
                                <td>{{ $person->order }}</td>
                                <td>
                                    @if($person->image_url)
                                        <img src="{{ asset('storage/' . $person->image_url) }}" alt="Photo" class="img-radius align-top m-r-15" style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px;">
                                    @else
                                        <div class="avtar avtar-s bg-light-primary">
                                            <i class="ti ti-user"></i>
                                        </div>
                                    @endif
                                </td>
                                <td>{{ $person->name }}</td>
                                <td>{{ $person->role }}</td>
                                <td class="text-end">
                                    <div class="d-inline-flex">
                                        <a href="{{ route('admin.key-people.edit', $person->id) }}" class="btn btn-icon btn-link-success" title="Edit">
                                            <i class="ti ti-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.key-people.destroy', $person->id) }}" method="POST" class="d-inline delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-icon btn-link-danger delete-btn" title="Delete">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center">No team members found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="{{ asset('admin_assets/js/plugins/dataTables.min.js') }}"></script>
<script src="{{ asset('admin_assets/js/plugins/dataTables.bootstrap5.min.js') }}"></script>
<script>
    $(document).ready(function() {
        $('#people-table').DataTable({
            "pageLength": 10,
            "order": [[ 0, "asc" ]], // Order by the 'Order' column
            "columnDefs": [
                { "orderable": false, "targets": [1, 6] } // Disable sorting on photo and actions
            ]
        });
    });

    document.querySelectorAll('.delete-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const form = this.closest('form');
            
            Swal.fire({
                title: 'Are you sure?',
                text: "Remove this person from the About Us page?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, remove them!'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            })
        });
    });
</script>
@endpush
