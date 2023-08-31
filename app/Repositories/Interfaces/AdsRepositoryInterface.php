<?php

namespace App\Repositories\Interfaces;

interface AdsRepositoryInterface {

    public function all($options = []);

    public function update($id, $data);

    public function create($data);

    public function paginate(int $perPage, string $sortBy, string $sortOrder, array $options = []);

}
