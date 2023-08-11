<?php

namespace App\Repositories\Interfaces;

interface OrderRepositoryInterface {

    public function all();

    public function update($id, $data);

    public function whereCount($where, $callback = null);

    public function paginate(int $perPage, string $sortBy, string $sortOrder);

    public function followUpStatistics($userId);

    public function agentOrdersPaginate(array $where, int $perPage, string $sortBy, string $sortOrder);
}
