@extends('dashboard.layouts.app')

@section('content')
    <div class="app-title">
        <div>
            <h1><i class="fa fa-shopping-cart"></i> Purchase Requests</h1>
            <p>Review and manage purchase requests from all departments</p>
        </div>
        <ul class="app-breadcrumb breadcrumb">
            <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
            <li class="breadcrumb-item"><a href="{{ route('storekeeper.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item">Purchase Requests</li>
        </ul>
    </div>

    <!-- Tab Navigation -->
    <div class="row">
        <div class="col-md-12">
            <div class="tile">
                <ul class="nav nav-tabs mb-4">
                    <li class="nav-item">
                        <a class="nav-link {{ $tab === 'pending' ? 'active' : '' }}"
                            href="{{ route('storekeeper.purchase-requests', ['tab' => 'pending']) }}">
                            <i class="fa fa-clock-o"></i> Pending
                            @if($tab === 'pending') <span class="badge badge-danger">{{ $requests->total() }}</span> @endif
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $tab === 'approved' ? 'active' : '' }}"
                            href="{{ route('storekeeper.purchase-requests', ['tab' => 'approved']) }}">
                            <i class="fa fa-check-circle"></i> In Progress
                        </a>
                    </li>
                </ul>

                <div class="tile-title-w-btn mb-3">
                    <h3 class="title">
                        @if($tab === 'pending') Pending Requests @else In-Progress Requests @endif
                    </h3>
                    @if($tab === 'pending' && $requests->count() > 0)
                        <a href="{{ route('storekeeper.shopping-list.create') }}" class="btn btn-sm btn-primary">
                            <i class="fa fa-list-alt"></i> Create Shopping List
                        </a>
                    @endif
                </div>

                @if($requests->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered">
                            <thead class="thead-light">
                                <tr>
                                    <th>#</th>
                                    <th>Item</th>
                                    <th>Quantity</th>
                                    <th>Department</th>
                                    <th>Requested By</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($requests as $i => $req)
                                    <tr>
                                        <td>{{ $requests->firstItem() + $i }}</td>
                                        <td><strong>{{ $req->item_name }}</strong><br><small
                                                class="text-muted">{{ $req->description }}</small></td>
                                        <td>{{ $req->quantity }} {{ $req->unit }}</td>
                                        <td><span
                                                class="badge badge-info">{{ $req->requestedBy?->getDepartmentName() ?? 'N/A' }}</span>
                                        </td>
                                        <td>{{ $req->requestedBy?->name ?? 'N/A' }}</td>
                                        <td>
                                            @php
                                                $statusColors = [
                                                    'pending' => 'warning',
                                                    'approved' => 'info',
                                                    'on_list' => 'primary',
                                                    'purchased' => 'success',
                                                    'rejected' => 'danger',
                                                ];
                                            @endphp
                                            <span class="badge badge-{{ $statusColors[$req->status] ?? 'secondary' }}">
                                                {{ ucfirst(str_replace('_', ' ', $req->status)) }}
                                            </span>
                                        </td>
                                        <td><small>{{ $req->created_at->format('M d, Y') }}</small></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-center mt-3">
                        {{ $requests->appends(request()->query())->links() }}
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fa fa-check-circle fa-4x text-success mb-3"></i>
                        <h3>No Requests Found</h3>
                        <p class="text-muted">There are no {{ $tab === 'pending' ? 'pending' : 'in-progress' }} purchase
                            requests.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

@endsection