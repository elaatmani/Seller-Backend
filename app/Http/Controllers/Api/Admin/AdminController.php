<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\StatisticsService;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateOrderRequest;
use App\Http\Requests\CreateOrderRequest;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Arr;

class AdminController extends Controller
{

    private $orderRepository;

    public function __construct(OrderRepositoryInterface $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    public function statistics(Request $request) {


        $statistics = StatisticsService::admin($request);

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
        $statistics = StatisticsService::adminSalesStatistics();

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
            $order = $this->orderRepository->update($id, $request->all());

            return [
                'code' => 'SUCCESS',
                'data' => [
                    'order' => $order
                ]
            ];

        } catch (\Throwable $th) {


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


    public function create(CreateOrderRequest $request) {

        try {
            DB::beginTransaction();

            $order = $this->orderRepository->create($request->all());

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
        $reportedFirst = data_get($filters, 'reported_first', false);

        $orWhere = !$search ? [] : [
            ['id', 'LIKE', "%$search%"],
            ['fullname', 'LIKE', "%$search%"],
            ['phone', 'LIKE', "%$search%"],
            ['adresse', 'LIKE', "%$search%"],
            ['city', 'LIKE', "%$search%"],
            ['note', 'LIKE', "%$search%"],
        ];


        $filtersDate = Arr::only($filters, ['created_from', 'created_to', 'dropped_from', 'dropped_to']);
        $validatedFilters = Arr::only($filters, ['confirmation', 'delivery', 'affectation', 'agente_id', 'upsell']);

        $toFilter = [];
        if(is_array($validatedFilters)){
            foreach($validatedFilters as $f => $v) {
                if($v == 'all') continue;
                $toFilter[] = [$f, '=', $v];
            }
        }

        $whereDate = [
            ['created_at', '>=', data_get($filtersDate, 'created_from', null)],
            ['created_at', '<=', data_get($filtersDate, 'created_to', null)],
            ['dropped_at', '>=', data_get($filtersDate, 'dropped_from', null)],
            ['dropped_at', '<=', data_get($filtersDate, 'dropped_to', null)],

        ];

        $whereHas = [
            [ 'product_id', '=', data_get($filters, 'product_id', 'all'), 'items' ]
        ];

        $options = [
            'whereDate' => $whereDate,
            'where' => $toFilter,
            'orWhere' => $orWhere,
            'reported_first' => $reportedFirst,
            'whereHas' => $whereHas
        ];

        return $options;
    }
}
