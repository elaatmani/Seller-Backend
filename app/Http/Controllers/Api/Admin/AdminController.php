<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use App\Services\StatisticsService;
use Illuminate\Http\Request;

class AdminController extends Controller
{

    private $orderRepository;

    public function __construct(OrderRepositoryInterface $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    public function index(Request $request) {

        $sortBy = $request->input('sort_by');
        $sortOrder = $request->input('sort_order');
        $perPage = $request->input('per_page');
        $filters = $request->input('filters');
        $search = $request->input('search');
        // $confirmation = $request->input('confirmation');

        $orWhere = !$search ? [] : [
            ['id', 'LIKE', "%$search%"],
            ['fullname', 'LIKE', "%$search%"],
            ['phone', 'LIKE', "%$search%"],
            ['adresse', 'LIKE', "%$search%"],
            ['city', 'LIKE', "%$search%"],
            ['note', 'LIKE', "%$search%"],
        ];

        $toFilter = [];

        if(is_array($filters)){
            foreach($filters as $f => $v) {
                if($v == 'all') continue;
                if($f == 'created_at') {

                    $toFilter[] = [$f, 'like', "%$v%"];
                } else {
                    $toFilter[] = [$f, $v];
                }
            }
        }

        $where = [
            ...$toFilter
        ];



        $orders = $this->orderRepository->paginate($where, $orWhere, $perPage, $sortBy, $sortOrder);
        // $statistics = $this->orderRepository->agentStatistics(auth()->id());

        return response()->json([
            'code' => 'SUCCESS',
            'data' => [
                'orders' => $orders,
                // 'statistics' => $statistics
            ]
        ]);

    }
}
