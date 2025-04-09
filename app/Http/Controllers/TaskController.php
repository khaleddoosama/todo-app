<?php

namespace App\Http\Controllers;

use App\Http\Requests\TaskRequest;
use App\Http\Resources\TaskResource;
use App\Traits\ApiResponseTrait;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class TaskController extends Controller
{
    use AuthorizesRequests, ApiResponseTrait;

    public function index(Request $request)
    {
        // $this->authorize('viewAny', Task::class);
        $perPage = $request->get('per_page', 10);

        $tasks = Task::withTrashed()->filter($request->only(['status', 'search']))
            ->latest()->with(['user', 'category'])
            ->paginate($perPage);

        return $this->apiResponse(TaskResource::collection($tasks), 'Tasks fetched successfully', 200);
    }

    public function store(TaskRequest $request)
    {
        $validated = $request->validated();

        $validated['user_id'] = auth()->id();
        return $this->apiResponse(new TaskResource(Task::create($validated)), 'Task created successfully', 201);
    }

    public function show(Task $task)
    {
        $this->authorize('view', $task);
        return $this->apiResponse(new TaskResource($task), 'Task fetched successfully', 200);
    }

    public function update(TaskRequest $request, Task $task)
    {
        $this->authorize('update', $task);

        $validated = $request->validated();

        $task->update($validated);
        return $this->apiResponse(new TaskResource($task), 'Task updated successfully', 200);
    }

    public function destroy(Task $task)
    {
        $this->authorize('delete', $task);
        $task->delete();
        return $this->apiResponse(null, 'Task deleted successfully', 200);
    }

    public function restore($id)
    {
        $task = Task::onlyTrashed()->where('id', $id)->firstOrFail();
        $this->authorize('restore', $task);
        $task->restore();
        return $this->apiResponse(new TaskResource($task), 'Task restored successfully', 200);
    }

    public function toggleStatus(Task $task)
    {
        $this->authorize('update', $task);
        $task->status = $task->status === 'completed' ? 'pending' : 'completed';
        $task->save();
        return $this->apiResponse(new TaskResource($task), 'Task status updated successfully', 200);
    }
}
