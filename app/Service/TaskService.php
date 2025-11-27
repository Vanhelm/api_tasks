<?php

namespace App\Service;

use App\Http\Resources\TaskResource;
use App\Http\Responses\ApiResponse;
use App\Models\Task;
use App\Service\Contract\CrudInterface;
use App\Service\Contract\PaginatorInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Pagination\CursorPaginator;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Translation\Exception\NotFoundResourceException;

class TaskService implements CrudInterface, PaginatorInterface
{
    private int $total;
    private ?string $cursor;
    private int $limit;
    private int $page;

    public function list(Request $request): JsonResource
    {
        $query = Task::with('media')->where('user_id', auth()->id());

        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        $type         = $request->input('type', 'page');
        $this->limit  = min((int)$request->input('limit', 200), 1000);
        $this->page   = max((int)$request->input('page', 1), 1);
        $this->cursor = $request->input('cursor', '');

        $tasks = $type === 'cursor'
            ? $this->cursor($query)
            : $this->paginate($query);

        return TaskResource::collection($tasks);
    }

    public function show(?Model $model): JsonResource
    {
        $this->checkModel($model);
        return new TaskResource($model);
    }

    public function create(Request $request): JsonResource
    {
        $task = Task::create([
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'status' => $request->input('status'),
            'finished_at' => $request->input('finished_at'),
            'user_id' => auth()->id(),
        ]);

        if ($request->hasFile('file') && $request->file('file')->isValid()) {
            $task->addMediaFromRequest('file')
                ->toMediaCollection('files');
        }

        return new TaskResource($task->load('media'));
    }

    public function update(?Model $model, Request $request): JsonResource
    {
        $this->checkModel($model);
        $model->update([
            'title'       => $request->input('title'),
            'description' => $request->input('description'),
            'status'      => $request->input('status'),
            'finished_at' => $request->input('finished_at'),
        ]);

        if ($request->hasFile('file') && $request->file('file')->isValid()) {
            $model->clearMediaCollection('files');
            $model->addMediaFromRequest('file')
                ->toMediaCollection('files');
        }

        return new TaskResource($model->load('media'));
    }

    public function delete(?Model $model): bool
    {
        $this->checkModel($model);
        return $model->delete();
    }

    public function paginate(Builder $builder): AbstractPaginator
    {
        $tasks       = $builder->paginate($this->limit, ['*'], 'page', $this->page);
        $this->total = $tasks->total();
        return $tasks;
    }

    public function cursor(Builder $builder): array
    {
        $tasks = $builder
            ->orderBy('id', 'asc')
            ->cursorPaginate($this->limit, ['*'], 'cursor', $this->cursor);
        $this->cursor = $tasks->nextCursor()?->encode();
        return $tasks->items();
    }

    public function getTotal(): int
    {
        return $this->total ?? 0;
    }

    public function getCurrentPage(): int
    {
        return $this->page ?? 1;
    }

    public function getCursor(): ?string
    {
        return $this->cursor;
    }

    public function getLimit(): int
    {
        return $this->limit ?? 10;
    }

    private function checkModel(?Model $model)
    {
        if ($model === null) {
            throw new NotFoundResourceException('Task not founded');
        }

        if ($model->user_id !== auth()->id()) {
            throw new AccessDeniedHttpException("Access denied to task with id {$model->id}");
        }
    }
}
