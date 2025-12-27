<?php
namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\MasterData\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    // Normal CRUD

    public function index(Request $request)
    {
        $suppliers = Supplier::query();

        // Search
        if ($request->has('search')) {
            $suppliers->where('name', 'like', "%{$request->search}%")
                      ->orWhere('email', 'like', "%{$request->search}%")
                      ->orWhere('phone', 'like', "%{$request->search}%");
        }

        $suppliers = $suppliers->paginate(12)->withQueryString();

        // Picker mode
        $selectFor = $request->query('select_for');
        $returnUrl = $request->query('return_url');

        return view('suppliers.index', compact('suppliers', 'selectFor', 'returnUrl'));
    }

    public function picker(Request $request)
    {
        // simply redirect to index with select mode
        return redirect()->route('suppliers.index', [
            'select_for' => $request->query('select_for'),
            'return_url' => $request->query('return_url')
        ]);
    }

    public function create() { return view('suppliers.create'); }
    public function store(Request $request) {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
        ]);
        Supplier::create($data);
        return redirect()->route('suppliers.index')->with('success', 'Supplier created.');
    }

    public function edit(Supplier $supplier) { return view('suppliers.edit', compact('supplier')); }
    public function update(Request $request, Supplier $supplier)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
        ]);
        $supplier->update($data);
        return redirect()->route('suppliers.index')->with('success', 'Supplier updated.');
    }

    public function destroy(Supplier $supplier) { $supplier->delete(); return back()->with('success','Deleted'); }
}



// ---------------------------
// app/Models/Supplier.php
// ---------------------------
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model {
    use HasFactory;
    protected $fillable = ['name'];
}

// ---------------------------
// app/Models/PurchaseRequest.php
// ---------------------------
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseRequest extends Model {
    use HasFactory;
    protected $fillable = ['supplier_id','request_number'];

    public function supplier() {
        return $this->belongsTo(Supplier::class);
    }
}

// ---------------------------
// app/Models/PurchaseOrder.php
// ---------------------------
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model {
    use HasFactory;
    protected $fillable = ['supplier_id','purchase_request_id','order_number'];

    public function supplier() {
        return $this->belongsTo(Supplier::class);
    }

    public function purchaseRequest() {
        return $this->belongsTo(PurchaseRequest::class);
    }
}

// ---------------------------
// app/Http/Controllers/SupplierController.php
// ---------------------------
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Supplier;

class SupplierController extends Controller {
    public function index() { return view('suppliers.index', ['suppliers'=>Supplier::all()]); }
    public function create() { return view('suppliers.create'); }
    public function store(Request $req) { Supplier::create($req->all()); return redirect()->route('suppliers.index'); }
    public function edit(Supplier $supplier) { return view('suppliers.edit', compact('supplier')); }
    public function update(Request $req, Supplier $supplier) { $supplier->update($req->all()); return redirect()->route('suppliers.index'); }

    // AJAX store for modal
    public function ajaxStore(Request $req) {
        $supplier = Supplier::create(['name'=>$req->name]);
        return response()->json($supplier);
    }
}

// ---------------------------
// app/Http/Controllers/PurchaseRequestController.php
// ---------------------------
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\PurchaseRequest;
use App\Models\Supplier;

class PurchaseRequestController extends Controller {
    public function index() { $requests=PurchaseRequest::with('supplier')->get(); return view('purchase_requests.index', compact('requests')); }
    public function create() { return view('purchase_requests.create'); }
    public function store(Request $req) { PurchaseRequest::create($req->all()); return redirect()->route('purchase-requests.index'); }
    public function edit(PurchaseRequest $purchaseRequest) { return view('purchase_requests.edit', compact('purchaseRequest')); }
    public function update(Request $req, PurchaseRequest $purchaseRequest) { $purchaseRequest->update($req->all()); return redirect()->route('purchase-requests.index'); }

    // AJAX store for modal
    public function ajaxStore(Request $req) {
        $request = PurchaseRequest::create(['request_number'=>$req->request_number,'supplier_id'=>$req->supplier_id]);
        return response()->json($request);
    }
}

// ---------------------------
// app/Http/Controllers/PurchaseOrderController.php
// ---------------------------
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\PurchaseOrder;

class PurchaseOrderController extends Controller {
    public function index() { $orders = PurchaseOrder::with('supplier','purchaseRequest')->get(); return view('purchase_orders.index', compact('orders')); }
    public function create() { return view('purchase_orders.create'); }
    public function store(Request $req) { PurchaseOrder::create($req->all()); return redirect()->route('purchase-orders.index'); }
    public function edit(PurchaseOrder $purchaseOrder) { return view('purchase_orders.edit', compact('purchaseOrder')); }
    public function update(Request $req, PurchaseOrder $purchaseOrder) { $purchaseOrder->update($req->all()); return redirect()->route('purchase-orders.index'); }
}
