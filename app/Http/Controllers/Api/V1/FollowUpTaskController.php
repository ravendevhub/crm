<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Modules\CRM\Models\Customer;
use Modules\CRM\Models\FollowUpTask;
use Modules\CRM\Models\Lead;

class FollowUpTaskController extends Controller
{
    /**
     * GET /api/v1/follow-up-tasks
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::in(['pending', 'in_progress', 'completed', 'cancelled'])],
            'priority' => ['nullable', Rule::in(['low', 'medium', 'high'])],
            'assigned_user_id' => ['nullable', 'integer', Rule::exists('users', 'id')->where('company_id', current_company_id())],
            'related_type' => ['nullable', Rule::in(['customer', 'lead'])],
            'related_id' => ['nullable', 'integer'],
            'overdue' => ['nullable', 'boolean'],
            'today' => ['nullable', 'boolean'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $query = FollowUpTask::query()
            ->with(['assignedUser:id,name', 'related']);

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (! empty($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }
        if (! empty($filters['assigned_user_id'])) {
            $query->where('assigned_user_id', $filters['assigned_user_id']);
        }
        if (! empty($filters['related_type'])) {
            $query->where('related_type', $this->relatedClass($filters['related_type']));
        }
        if (! empty($filters['related_id'])) {
            $query->where('related_id', $filters['related_id']);
        }
        if ($request->boolean('overdue')) {
            $query->where('due_date', '<', now())
                  ->whereIn('status', ['pending', 'in_progress']);
        }
        if ($request->boolean('today')) {
            $query->whereDate('due_date', today())
                  ->whereIn('status', ['pending', 'in_progress']);
        }

        $query->orderBy('due_date', 'asc');

        return response()->json($query->paginate($filters['per_page'] ?? 15));
    }

    /**
     * POST /api/v1/follow-up-tasks
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'title'            => ['required', 'string', 'max:255'],
            'related_type'     => ['required', 'in:customer,lead'],
            'related_id'       => ['required', 'integer'],
            'due_date'         => ['required', 'date'],
            'reminder_at'      => ['nullable', 'date'],
            'priority'         => ['required', 'in:low,medium,high'],
            'status'           => ['required', 'in:pending,in_progress,completed,cancelled'],
            'notes'            => ['nullable', 'string'],
            'assigned_user_id' => ['nullable', 'integer', Rule::exists('users', 'id')->where('company_id', current_company_id())],
        ]);

        $data['related_type'] = $this->relatedClass($data['related_type']);
        $this->assertRelatedRecordExists($data['related_type'], (int) $data['related_id']);

        $task = FollowUpTask::create($data);

        return response()->json($task->load(['assignedUser:id,name', 'related']), 201);
    }

    /**
     * GET /api/v1/follow-up-tasks/{id}
     */
    public function show(FollowUpTask $followUpTask): JsonResponse
    {
        return response()->json($followUpTask->load(['assignedUser:id,name', 'creator:id,name', 'related']));
    }

    /**
     * PUT /api/v1/follow-up-tasks/{id}
     */
    public function update(Request $request, FollowUpTask $followUpTask): JsonResponse
    {
        $data = $request->validate([
            'title'            => ['sometimes', 'string', 'max:255'],
            'due_date'         => ['sometimes', 'date'],
            'reminder_at'      => ['nullable', 'date'],
            'priority'         => ['sometimes', 'in:low,medium,high'],
            'status'           => ['sometimes', 'in:pending,in_progress,completed,cancelled'],
            'notes'            => ['nullable', 'string'],
            'assigned_user_id' => ['nullable', 'integer', Rule::exists('users', 'id')->where('company_id', current_company_id())],
        ]);

        $followUpTask->update($data);

        return response()->json($followUpTask->fresh()->load('assignedUser:id,name'));
    }

    /**
     * DELETE /api/v1/follow-up-tasks/{id}
     */
    public function destroy(FollowUpTask $followUpTask): JsonResponse
    {
        $followUpTask->delete();

        return response()->json(['message' => 'Follow-up task deleted successfully.']);
    }

    private function relatedClass(string $type): string
    {
        return match ($type) {
            'customer' => Customer::class,
            'lead' => Lead::class,
            default => $type,
        };
    }

    private function assertRelatedRecordExists(string $class, int $id): void
    {
        if (! $class::query()->whereKey($id)->exists()) {
            throw ValidationException::withMessages([
                'related_id' => ['The selected related record is invalid for this company.'],
            ]);
        }
    }
}
