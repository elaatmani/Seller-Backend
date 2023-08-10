<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateOrderRequest;
use App\Models\Order;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use Illuminate\Http\Request;

class FollowUpController extends Controller
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

        $orders = $this->orderRepository->paginate($perPage, $sortBy, $sortOrder);
        $statistics = $this->orderRepository->followUpStatistics(1);

        return response()->json([
            'code' => 'SUCCESS',
            'data' => [
                'statistics' => $statistics,
                'orders' => $orders
            ]
            ]);
    }


    public function update(UpdateOrderRequest $request, $id) {
        return $request->validated();
    }
}
