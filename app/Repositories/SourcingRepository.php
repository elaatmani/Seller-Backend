<?php

namespace App\Repositories;

use App\Models\Sourcing;
use Carbon\Carbon;
use App\Repositories\Interfaces\SourcingRepositoryInterface;
use Exception;

class SourcingRepository  implements SourcingRepositoryInterface {


    public function all($options = []) {
        $query = Sourcing::query();


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
        $sourcings = $query->paginate($perPage);

        return $sourcings;
    }


    public function create($data) {
        $sourcing = Sourcing::create([
            'user_id' => auth()->id(),
            'product_name' => $data['product_name'],
            'product_url' => $data['product_url'],
            'estimated_quantity' => $data['estimated_quantity'],
            'destination_country' => $data['destination_country'],
            'note_by_seller' => $data['note_by_seller'],
            'shipping_method' => $data['shipping_method'],
            'quotation_status' => config('status.sourcings.quotation_status.default')['value'],
            'sourcing_status' => config('status.sourcings.sourcing_status.default')['value'],
            'cost_per_unit' => 0,
            'total_cost' => 0,
            'additional_fees' => 0
        ]);

        $sourcing->fresh();

        return $sourcing;
    }

    public function delete($id) {

    }

    public function update($id, $data) {

    }


}
