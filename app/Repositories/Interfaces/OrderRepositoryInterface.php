<?php

namespace App\Repositories\Interfaces;

interface OrderRepositoryInterface {

    public function all();

    public function update($id, $data);

    public function whereCount($where, $callback = null);

    public function paginate(array $where, array $orWhere, int $perPage, string $sortBy, string $sortOrder, $whereDate);

    public function adminStatistics();

    public function followUpStatistics($userId);

    public function agentStatistics($userId);

    // public function agentOrdersPaginate(array $where, int $perPage, string $sortBy, string $sortOrder);
}
