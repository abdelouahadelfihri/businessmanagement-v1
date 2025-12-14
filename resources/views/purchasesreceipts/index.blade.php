@extends('layouts.app')

@section('content')
<div class="container mt-4">

    <h1 class="mb-4">Purchase Requests</h1>

    @if($requests->isEmpty())
        <div class="alert alert-info">No purchase requests found.</div>

        @if($selectFor && $returnUrl)
            <a class="btn btn-primary"
               href="{{ route('purchase-requests.create', ['select_for' => $selectFor, 'return_url' => $returnUrl]) }}">
               Add Request and Return
            </a>
        @else
            <a class="btn btn-primary" href="{{ route('purchase-requests.create') }}">Add Request</a>
        @endif
    @else

    <div class="card shadow-sm">
        <div class="card-body p-0">

            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Title</th>
                        <th width="150">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($requests as $r)
                    <tr>
                        <td>{{ $r->title }}</td>
                        <td>
                            @if($selectFor && $returnUrl)
                                <a class="btn btn-success btn-sm"
                                   href="{{ $returnUrl }}?selected_request_id={{ $r->id }}">
                                   Select
                                </a>
                            @else
                                <a class="btn btn-warning btn-sm"
                                   href="{{ route('purchase-requests.edit', $r) }}">
                                   Edit
                                </a>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>

            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $requests->withQueryString()->links() }}
    </div>

    @endif

    @if(!$selectFor)
        <a class="btn btn-primary mt-3" href="{{ route('purchase-requests.create') }}">Add Request</a>
    @endif

</div>
@endsection