<?php

namespace App\Repositories\Interfaces;

interface SupplyRequestRepositoryInterface {

    public function all($options = []);

    // public function update($id, $data);

    public function create($data);

    public function delete($id);

    public function paginate(int $perPage = 10, string $sortBy = 'created_at', string $sortOrder = 'desc', array $options = []);

    // public function agentOrdersPaginate(array $where, int $perPage, string $sortBy, string $sortOrder);
}
