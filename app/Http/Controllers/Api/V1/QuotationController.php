<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Modules\CRM\Models\Quotation;

class QuotationController extends Controller
{
    /**
     * GET /api/v1/quotations
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::in(['draft', 'sent', 'accepted', 'declined'])],
            'customer_id' => ['nullable', 'integer', Rule::exists('customers', 'id')->where('company_id', current_company_id())],
            'lead_id' => ['nullable', 'integer', Rule::exists('leads', 'id')->where('company_id', current_company_id())],
            'assigned_user_id' => ['nullable', 'integer', Rule::exists('users', 'id')->where('company_id', current_company_id())],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $query = Quotation::query()
            ->with(['customer:id,name', 'lead:id,title', 'assignedUser:id,name']);

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (! empty($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
        }
        if (! empty($filters['lead_id'])) {
            $query->where('lead_id', $filters['lead_id']);
        }
        if (! empty($filters['assigned_user_id'])) {
            $query->where('assigned_user_id', $filters['assigned_user_id']);
        }

        $query->orderBy('created_at', 'desc');

        return response()->json($query->paginate($filters['per_page'] ?? 15));
    }

    /**
     * POST /api/v1/quotations
     */
    public function store(Request $request): JsonResponse
    {
        $data = $this->validateQuotation($request);

        $quotation = DB::transaction(function () use ($data) {
            $items = $data['items'] ?? [];
            unset($data['items']);

            $quotation = Quotation::create($data);
            $this->syncItems($quotation, $items);

            return $quotation->fresh();
        });

        return response()->json($quotation->load(['customer:id,name,email', 'lead:id,title', 'items', 'assignedUser:id,name']), 201);
    }

    /**
     * GET /api/v1/quotations/{id}
     */
    public function show(Quotation $quotation): JsonResponse
    {
        return response()->json(
            $quotation->load(['customer:id,name,email', 'lead:id,title', 'items', 'assignedUser:id,name', 'creator:id,name', 'auditLogs.user:id,name'])
        );
    }

    /**
     * PUT /api/v1/quotations/{id}
     */
    public function update(Request $request, Quotation $quotation): JsonResponse
    {
        $data = $this->validateQuotation($request, true);

        DB::transaction(function () use ($quotation, $data) {
            $items = $data['items'] ?? null;
            unset($data['items']);

            $quotation->update($data);

            if (is_array($items)) {
                $quotation->items()->get()->each->delete();
                $this->syncItems($quotation, $items);
                $quotation->recalculateTotal();
            }
        });

        return response()->json($quotation->fresh()->load(['customer:id,name,email', 'lead:id,title', 'items', 'assignedUser:id,name']));
    }

    /**
     * DELETE /api/v1/quotations/{id}
     */
    public function destroy(Quotation $quotation): JsonResponse
    {
        $quotation->delete();

        return response()->json(['message' => 'Quotation deleted successfully.']);
    }

    private function validateQuotation(Request $request, bool $isUpdate = false): array
    {
        return $request->validate([
            'lead_id' => ['nullable', 'integer', Rule::exists('leads', 'id')->where('company_id', current_company_id())],
            'customer_id' => ['nullable', 'integer', Rule::exists('customers', 'id')->where('company_id', current_company_id())],
            'quotation_number' => [$isUpdate ? 'sometimes' : 'required', 'string', 'max:255'],
            'total_amount' => ['nullable', 'numeric', 'min:0'],
            'status' => [$isUpdate ? 'sometimes' : 'required', Rule::in(['draft', 'sent', 'accepted', 'declined'])],
            'assigned_user_id' => ['nullable', 'integer', Rule::exists('users', 'id')->where('company_id', current_company_id())],
            'items' => ['nullable', 'array'],
            'items.*.description' => ['required_with:items', 'string', 'max:255'],
            'items.*.quantity' => ['required_with:items', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required_with:items', 'numeric', 'min:0'],
            'items.*.discount' => ['nullable', 'numeric', 'min:0'],
            'items.*.tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);
    }

    private function syncItems(Quotation $quotation, array $items): void
    {
        foreach ($items as $item) {
            $quantity = (float) $item['quantity'];
            $unitPrice = (float) $item['unit_price'];
            $discount = (float) ($item['discount'] ?? 0);
            $taxRate = (float) ($item['tax_rate'] ?? 0);
            $subtotal = max(($quantity * $unitPrice) - $discount, 0);

            $quotation->items()->create([
                'description' => $item['description'],
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'discount' => $discount,
                'tax_rate' => $taxRate,
                'total' => $subtotal + ($subtotal * $taxRate / 100),
            ]);
        }

        if ($items !== []) {
            $quotation->recalculateTotal();
        }
    }
}
