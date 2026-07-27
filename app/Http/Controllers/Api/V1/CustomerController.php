<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Modules\CRM\Models\Customer;

class CustomerController extends Controller
{
    /**
     * GET /api/v1/customers
     * List company-scoped customers with filters and pagination.
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::in(['lead', 'active', 'inactive'])],
            'customer_type' => ['nullable', Rule::in(['individual', 'corporate'])],
            'search' => ['nullable', 'string', 'max:255'],
            'assigned_user_id' => ['nullable', 'integer', Rule::exists('users', 'id')->where('company_id', current_company_id())],
            'sort_by' => ['nullable', Rule::in(['name', 'created_at', 'status'])],
            'sort_dir' => ['nullable', Rule::in(['asc', 'desc'])],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $query = Customer::query()
            ->with(['assignedUser:id,name', 'creator:id,name']);

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (! empty($filters['customer_type'])) {
            $query->where('customer_type', $filters['customer_type']);
        }
        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('company_name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }
        if (! empty($filters['assigned_user_id'])) {
            $query->where('assigned_user_id', $filters['assigned_user_id']);
        }

        $sortBy  = $filters['sort_by'] ?? 'created_at';
        $sortDir = $filters['sort_dir'] ?? 'desc';
        $query->orderBy($sortBy, $sortDir);

        $customers = $query->paginate($filters['per_page'] ?? 15);

        return response()->json($customers);
    }

    /**
     * POST /api/v1/customers
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'             => ['required', 'string', 'max:255'],
            'company_name'     => ['nullable', 'string', 'max:255'],
            'email'            => ['nullable', 'email', 'max:255'],
            'phone'            => ['nullable', 'string', 'max:50'],
            'website'          => ['nullable', 'url', 'max:255'],
            'address'          => ['nullable', 'string', 'max:500'],
            'customer_type'    => ['required', 'in:individual,corporate'],
            'status'           => ['required', 'in:lead,active,inactive'],
            'source'           => ['nullable', 'string', 'max:255'],
            'assigned_user_id' => ['nullable', 'integer', Rule::exists('users', 'id')->where('company_id', current_company_id())],
        ]);

        $customer = Customer::create($data);

        return response()->json($customer->load('assignedUser:id,name'), 201);
    }

    /**
     * GET /api/v1/customers/{id}
     */
    public function show(Customer $customer): JsonResponse
    {
        return response()->json(
            $customer->load(['assignedUser:id,name', 'creator:id,name', 'leads', 'quotations', 'auditLogs.user:id,name'])
        );
    }

    /**
     * PUT /api/v1/customers/{id}
     */
    public function update(Request $request, Customer $customer): JsonResponse
    {
        $data = $request->validate([
            'name'             => ['sometimes', 'string', 'max:255'],
            'company_name'     => ['nullable', 'string', 'max:255'],
            'email'            => ['nullable', 'email', 'max:255'],
            'phone'            => ['nullable', 'string', 'max:50'],
            'website'          => ['nullable', 'url', 'max:255'],
            'address'          => ['nullable', 'string', 'max:500'],
            'customer_type'    => ['sometimes', 'in:individual,corporate'],
            'status'           => ['sometimes', 'in:lead,active,inactive'],
            'source'           => ['nullable', 'string', 'max:255'],
            'assigned_user_id' => ['nullable', 'integer', Rule::exists('users', 'id')->where('company_id', current_company_id())],
        ]);

        $customer->update($data);

        return response()->json($customer->fresh()->load('assignedUser:id,name'));
    }

    /**
     * DELETE /api/v1/customers/{id}
     */
    public function destroy(Customer $customer): JsonResponse
    {
        $customer->delete();

        return response()->json(['message' => 'Customer deleted successfully.']);
    }
}
