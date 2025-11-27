<?php

namespace App\Service\Contract;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

interface CrudInterface
{
    public function list(Request $request): JsonResource;
    public function show(?Model $model): JsonResource;
    public function create(Request $request): JsonResource;
    public function update(?Model $model, Request $request): JsonResource;
    public function delete(?Model $model): bool;
}
