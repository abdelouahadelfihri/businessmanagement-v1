@extends('layouts.app')

@section('title', 'New Contact')

@section('content')
    <div class="container mt-4">
        <h1 class="mb-4">New Contact</h1>

        <form method="POST" action="{{ route('crm.contacts.store') }}">
            @csrf
            @include('crm.contacts._form')
        </form>
    </div>
@endsection
