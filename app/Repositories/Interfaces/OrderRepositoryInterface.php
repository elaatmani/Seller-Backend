<?php

namespace App\Repositories\Interfaces;

interface OrderRepositoryInterface {

    public function all($options = []);

    public function update($id, $data);

    public function create($data);

    public function whereCount($where, $callback = null);

    public function paginate(int $perPage, string $sortBy, string $sortOrder, array $options = []);

    public function followUpStatistics($userId);

    public function sellerStatistics($userId);

    public function agentStatistics($userId);

    // public function agentOrdersPaginate(array $where, int $perPage, string $sortBy, string $sortOrder);
}
