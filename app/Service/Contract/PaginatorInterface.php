<?php

namespace App\Service\Contract;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Pagination\CursorPaginator;

interface PaginatorInterface
{
    public function paginate(Builder $builder): AbstractPaginator;
    public function cursor(Builder $builder): array;
    public function getTotal(): int;
    public function getCurrentPage(): int;
    public function getCursor(): ?string;
    public function getLimit(): int;
}
