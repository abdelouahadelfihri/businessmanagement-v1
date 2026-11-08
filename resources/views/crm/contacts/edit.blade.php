@extends('layouts.app')

@section('title', 'Edit ' . $contact->full_name)

@section('content')
    <div class="container mt-4">
        <h1 class="mb-4">Edit Contact {{ $contact->full_name }}</h1>

        <form method="POST" action="{{ route('crm.contacts.update', $contact) }}">
            @csrf
            @method('PUT')
            @include('crm.contacts._form')
        </form>
    </div>
@endsection
