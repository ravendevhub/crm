<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Modules\CRM\Models\Lead;

class LeadController extends Controller
{
    /**
     * GET /api/v1/leads
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::in(['new', 'contacted', 'qualified', 'proposal_sent', 'won', 'lost'])],
            'source' => ['nullable', 'string', 'max:255'],
            'search' => ['nullable', 'string', 'max:255'],
            'assigned_user_id' => ['nullable', 'integer', Rule::exists('users', 'id')->where('company_id', current_company_id())],
            'sort_by' => ['nullable', Rule::in(['title', 'created_at', 'estimated_value', 'expected_close_date'])],
            'sort_dir' => ['nullable', Rule::in(['asc', 'desc'])],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $query = Lead::query()
            ->with(['assignedUser:id,name', 'customer:id,name', 'pipelineStage:id,name']);

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (! empty($filters['source'])) {
            $query->where('source', $filters['source']);
        }
        if (! empty($filters['assigned_user_id'])) {
            $query->where('assigned_user_id', $filters['assigned_user_id']);
        }
        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('contact_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $sortBy  = $filters['sort_by'] ?? 'created_at';
        $sortDir = $filters['sort_dir'] ?? 'desc';
        $query->orderBy($sortBy, $sortDir);

        return response()->json($query->paginate($filters['per_page'] ?? 15));
    }

    /**
     * POST /api/v1/leads
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'title'               => ['required', 'string', 'max:255'],
            'contact_name'        => ['nullable', 'string', 'max:255'],
            'phone'               => ['nullable', 'string', 'max:50'],
            'email'               => ['nullable', 'email', 'max:255'],
            'source'              => ['nullable', 'string', 'max:255'],
            'estimated_value'     => ['nullable', 'numeric', 'min:0'],
            'expected_close_date' => ['nullable', 'date'],
            'notes'               => ['nullable', 'string'],
            'status'              => ['required', 'in:new,contacted,qualified,proposal_sent,won,lost'],
            'assigned_user_id'    => ['nullable', 'integer', Rule::exists('users', 'id')->where('company_id', current_company_id())],
            'pipeline_stage_id'   => ['nullable', 'integer', Rule::exists('pipeline_stages', 'id')->where('company_id', current_company_id())],
            'customer_id'         => ['nullable', 'integer', Rule::exists('customers', 'id')->where('company_id', current_company_id())],
        ]);

        $lead = Lead::create($data);

        return response()->json($lead->load(['assignedUser:id,name', 'pipelineStage:id,name']), 201);
    }

    /**
     * GET /api/v1/leads/{id}
     */
    public function show(Lead $lead): JsonResponse
    {
        return response()->json(
            $lead->load(['assignedUser:id,name', 'customer:id,name', 'pipelineStage:id,name', 'followUpTasks', 'quotations', 'auditLogs.user:id,name'])
        );
    }

    /**
     * PUT /api/v1/leads/{id}
     */
    public function update(Request $request, Lead $lead): JsonResponse
    {
        $data = $request->validate([
            'title'               => ['sometimes', 'string', 'max:255'],
            'contact_name'        => ['nullable', 'string', 'max:255'],
            'phone'               => ['nullable', 'string', 'max:50'],
            'email'               => ['nullable', 'email', 'max:255'],
            'source'              => ['nullable', 'string', 'max:255'],
            'estimated_value'     => ['nullable', 'numeric', 'min:0'],
            'expected_close_date' => ['nullable', 'date'],
            'notes'               => ['nullable', 'string'],
            'status'              => ['sometimes', 'in:new,contacted,qualified,proposal_sent,won,lost'],
            'assigned_user_id'    => ['nullable', 'integer', Rule::exists('users', 'id')->where('company_id', current_company_id())],
            'pipeline_stage_id'   => ['nullable', 'integer', Rule::exists('pipeline_stages', 'id')->where('company_id', current_company_id())],
        ]);

        $lead->update($data);

        return response()->json($lead->fresh()->load(['assignedUser:id,name', 'pipelineStage:id,name']));
    }

    /**
     * DELETE /api/v1/leads/{id}
     */
    public function destroy(Lead $lead): JsonResponse
    {
        $lead->delete();

        return response()->json(['message' => 'Lead deleted successfully.']);
    }
}
