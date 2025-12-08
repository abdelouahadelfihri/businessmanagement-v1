@extends('layouts.app')

@section('content')

<h2>Add Supplier</h2>

<form method="POST" action="/api/suppliers"> 
    @csrf

    <input type="hidden" name="redirect" value="{{ $redirect }}">
    <input type="hidden" name="id" value="{{ $id }}">

    <div class="mb-3">
        <label>Name</label>
        <input name="name" class="form-control">
    </div>

    <div class="mb-3">
        <label>Email</label>
        <input name="email" class="form-control">
    </div>

    <div class="mb-3">
        <label>Phone</label>
        <input name="phone" class="form-control">
    </div>

    <div class="mb-3">
        <label>Address</label>
        <input name="address" class="form-control">
    </div>

    <button class="btn btn-success">Save</button>
</form>

@endsection