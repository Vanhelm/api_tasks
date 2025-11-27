<?php

namespace App\Http\Controllers;

use App\Events\TaskCreated;
use App\Http\Resources\TaskResource;
use App\Http\Responses\ApiResponse;
use App\Models\Task;
use App\Service\TaskService;
use App\Support\Enum\StatusTask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Translation\Exception\NotFoundResourceException;

class TaskController extends Controller
{
    public function list(Request $request, TaskService $taskService): \Illuminate\Http\JsonResponse
    {
        return ApiResponse::make(
            TaskResource::collection($taskService->list($request)),
        )
            ->setPagination($taskService->getLimit(), $taskService->getCurrentPage(), $taskService->getTotal())
            ->setCursor($taskService->getCursor())
            ->response();
    }

    public function show(Request $request, int $id, TaskService $taskService): \Illuminate\Http\JsonResponse
    {
        try {
            return ApiResponse::make(
                $taskService->show(Task::find($id))
            )->response();
        } catch (NotFoundResourceException $e) {
            return ApiResponse::make(statusCode: 404)
                ->setError('tasks', 'resource_not_found', $e->getMessage())
                ->response();
        } catch (AccessDeniedHttpException $e) {
            return ApiResponse::make(statusCode: 403)
                ->setError('tasks', 'access_denied', $e->getMessage())
                ->response();
        }
    }

    public function create(Request $request, TaskService $taskService): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:4000'],
            'file'        => ['nullable', 'file', 'mimes:jpeg,png,gif,webp,jpg,pdf,doc,docx,xlx,xls', 'max:4096'],
            'status'      => ['required', Rule::enum(StatusTask::class)],
            'finished_at' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return ApiResponse::make([], collect($validator->errors()->messages()), [], 422)->response();
        }

        $task = $taskService->create($request);
        event(new TaskCreated($task));

        return ApiResponse::make(
            data: new TaskResource($task),
            statusCode: 201
        )->response();
    }

    public function update(Request $request, int $id, TaskService $taskService): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:4000'],
            'file'        => ['nullable', 'file', 'mimes:jpeg,png,gif,webp,jpg,pdf,doc,docx,xlx,xls', 'max:4096'],
            'status'      => ['required', Rule::enum(StatusTask::class)],
            'finished_at' => ['nullable', 'date'],
        ]);

        if ($validator->fails()) {
            return ApiResponse::make([], collect($validator->errors()->messages()), [], 422)->response();
        }

        return ApiResponse::make(
            new TaskResource($taskService->update(Task::find($id), $request)),
        )->response();
    }

    public function delete(int $id, TaskService $taskService): \Illuminate\Http\JsonResponse
    {
        $task = Task::find($id);

        try {
            if ($taskService->delete($task)) {
                return ApiResponse::make([
                    'task' => [
                        'message' => 'delete success'
                    ]
                ])->response();
            }
            return ApiResponse::make()->setError('delete', 'delete error', "delete task with id {$task->id} failed")->response();
        } catch (NotFoundResourceException $e) {
            return ApiResponse::make(statusCode: 404)
                ->setError('tasks', 'resource_not_found', $e->getMessage())
                ->response();
        } catch (AccessDeniedHttpException $e) {
            return ApiResponse::make(statusCode: 403)
                ->setError('tasks', 'access_denied', $e->getMessage())
                ->response();
        }
    }
}
