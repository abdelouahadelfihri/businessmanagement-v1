<h2>Create Purchase Request</h2>

<form action="{{ route('purchase_requests.store') }}" method="POST">
    @csrf

    <label>Supplier</label>
    @if ($suppliers->count() > 0)
        <select name="supplier_id" required>
            <option value="">-- choose supplier --</option>
            @foreach ($suppliers as $s)
                <option value="{{ $s->id }}">{{ $s->name }}</option>
            @endforeach
        </select>
    @else
        <p>No suppliers found.</p>
    @endif

    <!-- Add supplier button -->
    <a href="{{ route('suppliers.create', ['return_to' => url()->current()]) }}">
        Add new supplier
    </a>

    <br><br>

    <label>Description</label>
    <textarea name="description" required></textarea>

    <label>Date</label>
    <input type="date" name="date" required>

    <label>Status</label>
    <select name="status" required>
        <option value="pending">Pending</option>
        <option value="approved">Approved</option>
    </select>

    <button type="submit">Save</button>
</form>