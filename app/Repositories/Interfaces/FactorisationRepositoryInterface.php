<?php

namespace App\Repositories\Interfaces;

interface FactorisationRepositoryInterface {

    public function all($options = []);

    public function update($id, $data);

    public function create($data);

    public function paginate(int $perPage, string $sortBy, string $sortAds, array $options = []);

}
