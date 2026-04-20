<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use App\Models\Customer\Customer;
use App\Models\Master\TransportDirectory\Airport;
use App\Models\Master\TransportDirectory\CarrierLines;
use App\Models\Master\TransportDirectory\Port;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DropdownListSearchController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('query');
        $db = $request->get('db');

        // Check if the function exists in this class
        if (method_exists($this, $db)) {
            return $this->$db($search);
        }

        // Fallback to default if function not found
        return $this->defaultSearch($search);
    }

    public function sea($search)
    {
        $port = Port::when($search, function ($q) use ($search) {
            $q->where('code', 'LIKE', "%{$search}%")->orWhere('name', 'LIKE', "%{$search}%");
        })->select('id', 'name', 'code')->orderBy('code')->limit(100)->get();
        if ($port->count() == 0) {
            return ['No Data Found'];
        }
        return $port;
    }

    public function air($search)
    {
        $port = Airport::when($search, function ($q) use ($search) {
            $q->where('code', 'LIKE', "%{$search}%")->orWhere('name', 'LIKE', "%{$search}%");
        })->select('id', 'name', 'code')->orderBy('code')->limit(100)->get();
        if ($port->count() == 0) {
            return ['No Data Found'];
        }
        return $port;
    }

    public function seaLines($search)
    {
        $carrier = CarrierLines::where('name', 'LIKE', "%{$search}%")->select('id', 'name', 'mode')->where('mode', 'Sea')->orderBy('name')->limit(50)->get();
        if ($carrier->count() == 0) {
            return ['No Data Found'];
        }
        return $carrier;
    }

    public function airLines($search)
    {
        $carrier = CarrierLines::where('name', 'LIKE', "%{$search}%")->select('id', 'name', 'mode')->where('mode', 'Air')->orderBy('name')->limit(50)->get();
        if ($carrier->count() == 0) {
            return ['No Data Found'];
        }
        return $carrier;
    }

    public function customerList()
    {
        $customers = Customer::confirmedCustomers();
        if ($customers->count() == 0) {
            return ['No Data Found'];
        }
        return $customers;
    }

    public function defaultSearch(): array
    {
        return ['No Data Found'];
    }

}
