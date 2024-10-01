<?php

namespace App\Http\Controllers\Api\Analytics\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class AgentsRankingController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {

        $user_ids = Role::where('name', 'agente')->first()->users()->where('status', 1)->select('id')->get()->pluck('id')->toArray();

        $query = DB::table('orders')
            ->select(
                'agente_id',
                DB::raw('COUNT(*) as total_orders'),
                DB::raw('CONCAT(users.firstname, " ", users.lastname) as name'),
                DB::raw('SUM(CASE WHEN confirmation = "confirmer" THEN 1 ELSE 0 END) as confirmed_orders')
            )
            ->whereIn('agente_id', $user_ids)
            ->join('users', 'users.id', '=', 'orders.agente_id')
            ->where('confirmation', '!=', 'double')
            ->groupBy('agente_id', 'users.firstname', 'users.lastname') // Include these fields in GROUP BY
            ->get();

        $result = $query->map(function ($item) {
            $item->confirmation_percentage = ($item->total_orders > 0)
                ? round(($item->confirmed_orders / $item->total_orders) * 100, 2)
                : 0;
            return $item;
        });

        return response()->json([
            'code' => 'SUCCESS',
            'data' => $result
        ]);
    }
}
