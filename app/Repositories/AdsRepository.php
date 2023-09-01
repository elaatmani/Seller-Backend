<?php
namespace App\Repositories;

use Carbon\Carbon;
use App\Models\Ads;
use Illuminate\Support\Facades\DB;
use App\Repositories\Interfaces\AdsRepositoryInterface;

class AdsRepository implements AdsRepositoryInterface {

public function all($options = []) {
        $query = Ads::query();

        foreach(data_get($options, 'orWhere', []) as $w) {
            $query->orWhere($w[0], $w[1], $w[2]);
        }

        foreach(data_get($options, 'whereDate', []) as $wd) {
            if(!$wd[2]) continue;
            $query->whereDate($wd[0], $wd[1], Carbon::make($wd[2])->toDate());
        }

        foreach(data_get($options, 'AdsBy', []) as $wd) {
            $query->AdsBy($wd[0], $wd[1]);
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
        string $sortAds = 'desc',
        array $options = []
    ) {
         // Number of records per page
        $options['get'] = false;

        // Get the query builder instance for the 'users' table
        $query = $this->all($options);

        $validSortAds = ['asc', 'desc'];

        if (!in_array($sortAds, $validSortAds)) {
            $sortAds = 'desc'; // Set default if the provided sort Ads is invalid
        }

        // Apply the sorting to the query
        $query->orderBy($sortBy, $sortAds);

        // Retrieve the paginated results
        $Ads = $query->paginate($perPage);

        return $Ads;
    }

    public function create($data){
        $order = Ads::create([
            ...$data,     
        ]);

        $order = $order->fresh();
        return $order;
    }

    public function update($id,$data){
        try {
            DB::beginTransaction();
            $order = Ads::where('id', $id)->first();

            $order->update($data);

            $order = $order->fresh();
            DB::commit();
            return $order;

        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}