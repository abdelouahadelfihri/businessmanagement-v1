<h2>Add Supplier</h2>

<form action="{{ route('suppliers.store') }}" method="POST">
    @csrf

    @if(isset($returnTo))
        <input type="hidden" name="return_to" value="{{ $returnTo }}">
    @endif

    <label>Name</label>
    <input type="text" name="name" required>

    <label>Email</label>
    <input type="email" name="email">

    <label>Phone</label>
    <input type="text" name="phone">

    <label>Address</label>
    <textarea name="address"></textarea>

    <button type="submit">Save</button>
</form>