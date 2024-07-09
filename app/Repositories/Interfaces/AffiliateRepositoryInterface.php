<?php

namespace App\Repositories\Interfaces;

interface AffiliateRepositoryInterface {

    public function all($options = []);

    public function update($id, $data);

    public function get($id);

    public function store($data);

    public function paginate(int $perPage, string $sortBy, string $sortOrder, array $options = []);

    // public function agentOrdersPaginate(array $where, int $perPage, string $sortBy, string $sortOrder);
}
