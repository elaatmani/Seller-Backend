<?php

namespace App\Http\Controllers\Api\Analytics\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class AgentsPerformanceController extends Controller
{
    public function __invoke(Request $request)
    {
        // Retrieve all active agent IDs
        $user_ids = Role::where('name', 'agente')->first()
            ->users()->where('status', 1)
            ->pluck('id');

        // Extract filters from request
        $startDate = $request->input('dateRange.startDate'); // Corrected key
        $endDate = $request->input('dateRange.endDate');     // Corrected key
        $selectedAgentIds = $request->input('filter.selectedAgenteId', []); // Handles array input

        // Build the query starting from users to include all agents
        $query = DB::table('users')
            ->select(
                'users.id as agente_id',
                DB::raw('CONCAT(users.firstname, " ", users.lastname) as name'),
                DB::raw('COUNT(orders.id) as total_orders'),
                DB::raw('SUM(CASE WHEN orders.confirmation = "confirmer" THEN 1 ELSE 0 END) as confirmed_orders'),
                DB::raw('SUM(CASE WHEN orders.delivery = "livrer" THEN 1 ELSE 0 END) as delivered_orders')
            )
            ->leftJoin('orders', function ($join) use ($startDate, $endDate, $request) {
                $join->on('users.id', '=', 'orders.agente_id')
                    ->where('orders.confirmation', '!=', 'double');

                // Apply date filter if provided
                if (!empty($startDate) && !empty($endDate)) {
                    $join->whereBetween('orders.created_at', [$startDate, $endDate]);
                }

               // Apply product filter using a subquery
               if ($request->has('filter.selectedProductId')) {
                $productIds = $request->input('filter.selectedProductId');  // Now it should match the request key
            
                $join->whereExists(function ($subQuery) use ($productIds) {
                    $subQuery->select(DB::raw(1))
                        ->from('order_items')
                        ->whereColumn('order_items.order_id', 'orders.id')
                        ->whereIn('order_items.product_id', $productIds);
                });
            }
            

            })
            ->whereIn('users.id', $user_ids)
            ->groupBy('users.id', 'users.firstname', 'users.lastname');

        // Apply agent filter if provided
        if (!empty($selectedAgentIds)) {
            $query->whereIn('users.id', (array) $selectedAgentIds);
        }

        // Execute the query and calculate percentages
        $result = $query->get()
            ->map(function ($item) {
                $item->confirmation_percentage = $item->total_orders > 0
                    ? round(($item->confirmed_orders / $item->total_orders) * 100, 2)
                    : 0;
                $item->delivery_percentage = $item->total_orders > 0
                    ? round(($item->delivered_orders / $item->total_orders) * 100, 2)
                    : 0;
                return $item;
            })
            ->sortByDesc('confirmation_percentage') // Default sorting by confirmation rate
            ->values();

        return response()->json([
            'code' => 'SUCCESS',
            'data' => $result
        ]);
    }
}
