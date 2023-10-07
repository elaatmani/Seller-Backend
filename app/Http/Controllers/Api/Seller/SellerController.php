<?php

namespace App\Http\Controllers\Api\Seller;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\StatisticsService;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateOrderRequest;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Exception;

class SellerController extends Controller
{
    private $orderRepository;

    public function __construct(OrderRepositoryInterface $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    public function statistics(Request $request) {


        $statistics = StatisticsService::seller($request);

        return response()->json([
            'code' => 'SUCCESS',
            'data' => [
                'statistics' => $statistics
            ]
        ]);
    }

    public function index(Request $request) {

        $sortBy = $request->input('sort_by');
        $sortOrder = $request->input('sort_order');
        $perPage = $request->input('per_page');
        
        $options = $this->get_options($request);
        
        $orders = $this->orderRepository->paginate($perPage, $sortBy, $sortOrder, $options);
        $statistics = $this->orderRepository->sellerStatistics(auth()->id());

        
        return response()->json([
            'code' => 'SUCCESS',
            'data' => [
                'orders' => $orders,
                'statistics' => $statistics
            ]
        ]);
    }


    public function update(UpdateOrderRequest $request, $id) {

        try {
            DB::beginTransaction();

            $order = $this->orderRepository->update($id, $request->all());

            DB::commit();
            return [
                'code' => 'SUCCESS',
                'data' => [
                    'order' => $order
                ]
            ];

        } catch (\Throwable $th) {

            // rollback transaction on error
            DB::rollBack();

            return response()->json(
                [
                    'status' => false,
                    'code' => 'SERVER_ERROR',
                    'message' => $th->getMessage(),
                ],
                500
            );
        }
    }

    public function export(Request $request) {

        try {
            DB::beginTransaction();

            $options = $this->get_options($request);

            $orders = $this->orderRepository->all($options);

            DB::commit();
            return [
                'code' => 'SUCCESS',
                'data' => [
                    'orders' => $orders
                ]
            ];

        } catch (\Throwable $th) {

            // rollback transaction on error
            DB::rollBack();

            return response()->json(
                [
                    'status' => false,
                    'code' => 'SERVER_ERROR',
                    'message' => $th->getMessage(),
                ],
                500
            );
        }
    }

    public function get_options($request) {
        $filters = $request->input('filters', []);
        $search = $request->input('search', '');
        $searchByField = $request->input('searchByField', 'all');
        
        switch ($searchByField) {
            case 'id':
                $orWhere = [
                    ['id', '=', $search]
                ];
            break;
            
            default:
                $orWhere = !$search ? [] : [
                    ['id', 'LIKE', "%$search%"],
                    ['fullname', 'LIKE', "%$search%"],
                    ['phone', 'LIKE', "%$search%"],
                    ['adresse', 'LIKE', "%$search%"],
                    ['city', 'LIKE', "%$search%"],
                    ['note', 'LIKE', "%$search%"],
                ];
            break;
        }


        $filtersDate = Arr::only($filters, ['created_from', 'created_to', 'dropped_from', 'dropped_to']);
        $validatedFilters = Arr::only($filters, ['confirmation', 'delivery', 'upsell']);

        $toFilter = [];
        if(is_array($validatedFilters)){
            foreach($validatedFilters as $f => $v) {
                if($v == 'all') continue;
                $toFilter[] = [$f, '=', $v];
            }
        }

        $toFilter[] = ['user_id', '=' , auth()->id()];

        $whereDate = [
            ['created_at', '>=', $filtersDate['created_from']],
            ['created_at', '<=', $filtersDate['created_to']],
            ['dropped_at', '>=', $filtersDate['dropped_from']],
            ['dropped_at', '<=', $filtersDate['dropped_to']],

        ];

        $whereHas = [
            [ 'product_id', '=', data_get($filters, 'product_id', 'all'), 'items' ]
        ];

        $options = [
            'whereDate' => $whereDate,
            'where' => $toFilter,
            'orWhere' => $orWhere,
            'whereHas' => $whereHas
        ];

        
        return $options;
    }
}
