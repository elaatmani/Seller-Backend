<?php
namespace App\Repositories;

use Carbon\Carbon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderHistory;
use Illuminate\Support\Facades\DB;
use App\Services\RoadRunnerService;
use App\Repositories\Interfaces\AdsRepositoryInterface;

class AdsRepository implements AdsRepositoryInterface {

public function all($options = []) {
        $query = Order::query();

        foreach(data_get($options, 'orWhere', []) as $w) {
            $query->orWhere($w[0], $w[1], $w[2]);
        }

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

        // Retrieve the paginated results
        $orders = $query->paginate($perPage);

        return $orders;
    }

    public function create($data){
        
    }

    public function update($id,$data){

    }
}