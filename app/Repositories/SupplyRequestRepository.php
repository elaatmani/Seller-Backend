<?php

namespace App\Repositories;

use App\Models\SupplyRequest;
use Carbon\Carbon;
use App\Repositories\Interfaces\SupplyRequestRepositoryInterface;
use Exception;

class SupplyRequestRepository  implements SupplyRequestRepositoryInterface {


    public function all($options = []) {
        $query = SupplyRequest::query();


        $query->where(function($q) use($options){

            foreach(data_get($options, 'orWhere', []) as $w) {
                    $q->orWhere($w[0], $w[1], $w[2]);
            }
        });

        foreach(data_get($options, 'whereDate', []) as $wd) {
            if(!$wd[2]) continue;
            $query->whereDate($wd[0], $wd[1], Carbon::make($wd[2])->toDate());
        }

        foreach(data_get($options, 'orderBy', []) as $wd) {
            $query->orderBy($wd[0], $wd[1]);
        }

        foreach(data_get($options, 'where', []) as $w ) {
            $query->where($w[0], $w[1], $w[2]);
        }

        if($with = data_get($options, 'with', [])) {
            $query->with($with);
        }

        foreach(data_get($options, 'whereHas', []) as $w) {

            $query->when($w[2] != 'all', fn($q) => $q->whereHas($w[3], fn($oq) => $oq->where($w[0], $w[1], $w[2])));
        }


        if(data_get($options, 'get', true)) {
            return $query->get();
        }
        return $query;

    }


    public function paginate(
        int $perPage = 10,
        string $sortBy = 'created_at',
        string $sortOrder = 'desc',
        array $options = []
    ) {
         // Number of records per page
        $options['get'] = false;

        // Get the query builder instance for the 'users' table
        $query = $this->all($options);

        $validSortOrders = ['asc', 'desc'];

        if (!in_array($sortOrder, $validSortOrders)) {
            $sortOrder = 'desc'; // Set default if the provided sort order is invalid
        }

        // Apply the sorting to the query
        $query->orderBy($sortBy, $sortOrder);

        // $query->where('agente_id', 18);

        // Retrieve the paginated results
        $products = $query->paginate($perPage);

        return $products;
    }


    public function create($data) {
        $supply = SupplyRequest::create([
            'seller_id' => auth()->id(),
            'product_id'=> $data['product_id'],
            'product_variation_id' => $data['product_variation_id'],
            'quantity' => $data['quantity'],
            'note' => $data['note']
        ])->fresh();

        return $supply;
    }

    public function delete($id) {
        $supply = SupplyRequest::findOrFail($id);
        if($supply->status != config('status.supply_requests.default.value')) {
            throw new Exception('This request has already been treated');
        }

        return $supply->delete();
    }

    public function update($id, $data) {
        $supply = SupplyRequest::where('id', $id)->first();

        if(!$supply) return false;

        $supply->update([
            'product_id'=> $data['product_id'],
            'product_variation_id' => $data['product_variation_id'],
            'quantity' => $data['quantity'],
            'note' => $data['note'],
            'status' => $data['status'],
            'admin_note' => $data['admin_note']
        ]);

        return $supply->fresh();
    }


}
