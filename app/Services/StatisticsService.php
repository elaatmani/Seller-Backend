<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderHistory;
use Illuminate\Support\Facades\Http;

class StatisticsService
{

    public static function followup($orders) {
        // $orders = ;
        $newCount = $orders->where('followup_confirmation', null)->where('delivery', 'annuler')->count();
        $reconfirmedCount = $orders->where('followup_confirmation', 'reconfirmer')->where('delivery', 'annuler')->count();
        $deliveredCount = $orders->where('followup_confirmation', 'reconfirmer')->where('delivery', 'livrer')->count();

        $all = [
            'id' => 1,
            'title' => 'All Orders',
            'value' => $orders->where('confirmation', 'confirmer')
            ->where('followup_confirmation', null)
            ->where('delivery', 'annuler')
            ->count(),
            'icon' => 'mdi-package-variant-closed',
            'color' => '#6b7280'
        ];

        $new = [
            'id' => 2,
            'title' => 'New',
            'value' => $newCount,
            'percentage' => ($newCount * 100) / $orders->count(),
            'icon' => 'mdi-bell',
            'color' => '#ef4444'
        ];

        $reconfirmed = [
            'id' => 3,
            'title' => 'Reconfirmed',
            'value' => $reconfirmedCount,
            'percentage' => ($reconfirmedCount * 100) / $orders->count(),
            'icon' => 'mdi-check-all',
            'color' => '#06b6d4'
        ];

        $delivered = [
            'id' => 4,
            'title' => 'Delivered',
            'value' => $deliveredCount,
            'percentage' => ($deliveredCount * 100) / $orders->count(),
            'icon' => 'mdi-truck-delivery-outline',
            'color' => '#34d399'
        ];

        return [$all, $new, $reconfirmed, $delivered];
    }


    public static function agent($orders) {
        // $orders = ;
        // $orders ;
        $reported = $orders->where('confirmation', 'reporter')->count();
        $confirmed = $orders->where('confirmation', 'confirmer')->count();
        $cancelled = $orders->where('confirmation', 'annuler')->count();
        $doubles = $orders->where('confirmation', 'double')->count();
        $upsells = $orders->where('confirmation', 'confirmer')->where('upsell', 'oui')->count();
        $noAnswer = $orders->whereIn('confirmation',
        [
            'day-one-call-one',
            'day-one-call-two',
            'day-one-call-three',
            'day-two-call-one',
            'day-two-call-two',
            'day-two-call-three',
            'day-three-call-one',
            'day-three-call-two',
            'day-three-call-three',
        ])->count();

        $totalCount = $orders->where('confirmation', '!=', 'double')->count();

        $all = [
            'id' => 1,
            'title' => 'All Orders',
            'value' => $orders->count(),
            'icon' => 'mdi-package-variant-closed',
            'color' => '#6b7280'
        ];

        $new = [
            'id' => 2,
            'title' => 'Reported',
            'value' => $reported,
            'percentage' => $totalCount > 0  ? ($reported * 100) / $totalCount : 0,
            'icon' => 'mdi-clock-outline',
            'color' => '#14b8a6'
        ];

        $reconfirmed = [
            'id' => 3,
            'title' => 'Confirmed',
            'value' => $confirmed,
            'percentage' => $totalCount > 0  ? ($confirmed * 100) / $totalCount : 0,
            'icon' => 'mdi-check-all',
            'color' => '#06b6d4'
        ];

        $noAnswer = [
            'id' => 4,
            'title' => 'No Answer',
            'value' => $noAnswer,
            'percentage' => $totalCount > 0  ? ($noAnswer * 100) / $totalCount : 0,
            'icon' => 'mdi-phone-alert',
            'color' => '#facc15'
        ];

        $earnings = [
            'id' => 5,
            'title' => 'Earnings',
            'value' => '0',
            'percentage' => $totalCount > 0  ? (0 * 100) / $totalCount : 0,
            'icon' => 'mdi-currency-usd',
            'symbol' => '$',
            'color' => '#34d399'
        ];

        $cancelled = [
            'id' => 6,
            'title' => 'Cancelled',
            'value' => $cancelled,
            'percentage' => $totalCount > 0  ? ($cancelled * 100) / $totalCount : 0,
            'icon' => 'mdi-cancel',
            'color' => '#f43f5e'
        ];

        $upsell = [
            'id' => 7,
            'title' => 'Upsell',
            'value' => $upsells,
            'percentage' => $totalCount > 0  ? ($upsells * 100) / $totalCount : 0,
            'icon' => 'mdi-transfer-up',
            'color' => '#fb923c'
        ];

        $doubles = [
            'id' => 8,
            'title' => 'Double',
            'value' => $doubles,
            'percentage' => $totalCount > 0  ? ($doubles * 100) / $totalCount : 0,
            'icon' => 'mdi-selection-multiple',
            'color' => '#a78bfa'
        ];


        return [$all, $new, $reconfirmed, $earnings, $noAnswer, $cancelled, $doubles, $upsell];
    }

    public static function seller($request) {
        $orders = Order::query();
        $orders->where('user_id', auth()->id());
        $created_from = $request->created_from;
        $created_to = $request->created_to;
        $dropped_from = $request->dropped_from;
        $dropped_to = $request->dropped_to;
        $affectation = $request->affectation;
        $confirmation = $request->confirmation;
        $delivery = $request->delivery;
        $product_id = $request->product_id;
        $agente_id = $request->agente_id;
        $upsell = $request->upsell;

        $orders
        ->when(!!$created_from, fn($q) => $q->whereDate('created_at', '>=', $created_from))
        ->when(!!$created_to, fn($q) => $q->whereDate('created_at', '<=', $created_to))
        ->when(!!$dropped_from, fn($q) => $q->whereDate('dropped_at', '>=', $dropped_from))
        ->when(!!$dropped_to, fn($q) => $q->whereDate('dropped_at', '<=', $dropped_to))
        ->when($affectation != 'all', fn($q) => $q->where('affectation', '=', $affectation))
        ->when($confirmation != 'all', fn($q) => $q->where('confirmation', '=', $confirmation))
        ->when($delivery != 'all', fn($q) => $q->where('delivery', '=', $delivery))
        ->when($agente_id != 'all', fn($q) => $q->where('agente_id', '=', $agente_id))
        ->when($upsell != 'all', fn($q) => $q->where('upsell', '=', $upsell))
        ->when($product_id != 'all', fn($q) => $q->whereHas('items', fn($oq) => $oq->where('product_id', $product_id)));

        $orders = $orders->get();

        $confirmations = [];
        $delivery = [];
        $totalCount = $orders->where('confirmation', '!=', 'double')->count();

        $allCount = $orders->count();
        $all = [
            'id' => 1,
            'title' => 'Orders',
            'value' => $allCount,
            'icon' => 'mdi-package-variant-closed',
            'color' => '#6b7280'
        ];
        $confirmations[] = $all;


        $confirmedCount = $orders->where('confirmation', 'confirmer')->count();
        $confirmed = [
            'id' => 2,
            'title' => 'Confirmed',
            'value' => $confirmedCount,
            'percentage' => $totalCount > 0  ? ($confirmedCount * 100) / $totalCount : 0,
            // 'icon' => 'mdi-check-all',
            'icon' => 'mdi-phone-check',
            'color' => '#10b981'
        ];
        $confirmations[] = $confirmed;


        $newCount = $orders->where('confirmation', null)->count();
        $new = [
            'id' => 3,
            'title' => 'New',
            'value' => $newCount,
            'percentage' => $totalCount > 0  ? ($newCount * 100) / $totalCount : 0,
            'icon' => 'mdi-new-box',
            'color' => '#475569'
        ];
        $confirmations[] = $new;


        $reportedCount = $orders->where('confirmation', 'reporter')->count();
        $reported = [
            'id' => 4,
            'title' => 'Reported',
            'value' => $reportedCount,
            'percentage' => $totalCount > 0  ? ($reportedCount * 100) / $totalCount : 0,
            'icon' => 'mdi-clock-outline',
            'color' => '#a855f7'
        ];
        $confirmations[] = $reported;


        $noAnswerConfirmations = ['day-one-call-one', 'day-one-call-two', 'day-one-call-three', 'day-two-call-one', 'day-two-call-two', 'day-two-call-three', 'day-three-call-one', 'day-three-call-two', 'day-three-call-three'];
        $noAnswerCount = $orders->whereIn('confirmation', $noAnswerConfirmations)->count();
        $noAnswer = [
            'id' => 5,
            'title' => 'No Answer',
            'value' => $noAnswerCount,
            'percentage' => $totalCount > 0  ? ($noAnswerCount * 100) / $totalCount : 0,
            'icon' => 'mdi-phone-missed',
            'color' => '#fbbf24'
        ];
        $confirmations[] = $noAnswer;


        $cancelledCount = $orders->where('confirmation', 'annuler')->count();
        $cancelled = [
            'id' => 6,
            'title' => 'Cancelled',
            'value' => $cancelledCount,
            'percentage' => $totalCount > 0  ? ($cancelledCount * 100) / $totalCount : 0,
            'icon' => 'mdi-cancel',
            'color' => '#f43f5e'
        ];
        $confirmations[] = $cancelled;


        $doubledCount = $orders->where('confirmation', 'double')->count();
        $double = [
            'id' => 7,
            'title' => 'Double',
            'value' => $doubledCount,
            'percentage' => $totalCount > 0  ? ($doubledCount * 100) / $totalCount : 0,
            'icon' => 'mdi-selection-multiple',
            'color' => '#8b5cf6'
        ];
        $confirmations[] = $double;


        $wrondNumberCount = $orders->where('confirmation', 'wrong-number')->count();
        $wrongNumber = [
            'id' => 8,
            'title' => 'Wrong Number',
            'value' => $wrondNumberCount,
            'percentage' => $totalCount > 0  ? ($wrondNumberCount * 100) / $totalCount : 0,
            'icon' => 'mdi-phone-remove',
            'color' => '#db2777'
        ];
        $confirmations[] = $wrongNumber;


        $deliveryOrders = $orders->where('confirmation', 'confirmer');

        $all = [
            'id' => 1,
            'title' => 'Confirmed',
            'value' => $deliveryOrders->count(),
            'icon' => 'mdi-check-circle-outline',
            'color' => '#6b7280'
        ];
        $delivery[] = $all;


        $deliveredCount = $deliveryOrders->where('delivery', 'livrer')->count();
        $delivered = [
            'id' => 2,
            'title' => 'Delivered',
            'value' => $deliveredCount,
            'percentage' => $deliveryOrders->count() > 0  ? ($deliveredCount * 100) / $deliveryOrders->count() : 0,
            'icon' => 'mdi-truck-check',
            'color' => '#10b981'
        ];
        $delivery[] = $delivered;


        $shippedCount = $deliveryOrders->where('delivery', 'expidier')->count();
        $shipped = [
            'id' => 3,
            'title' => 'Shipped',
            'value' => $shippedCount,
            'percentage' => $deliveryOrders->count() > 0  ? ($shippedCount * 100) / $deliveryOrders->count() : 0,
            'icon' => 'mdi-truck-fast-outline',
            'color' => '#f97316'
        ];
        $delivery[] = $shipped;

        $transferCount = $deliveryOrders->where('delivery', 'transfer')->count();
        $transferred = [
            'id' => 7,
            'title' => 'Transferred',
            'value' => $transferCount,
            'percentage' => $deliveryOrders->count() > 0  ? ($transferCount * 100) / $deliveryOrders->count() : 0,
            'icon' => 'mdi-dolly',
            'color' => '#a855f7'
        ];
        $delivery[] = $transferred;


        $reportedCount = $deliveryOrders->where('delivery', 'reporter')->count();
        $reported = [
            'id' => 4,
            'title' => 'Reported',
            'value' => $reportedCount,
            'percentage' => $deliveryOrders->count() > 0  ? ($reportedCount * 100) / $deliveryOrders->count() : 0,
            'icon' => 'mdi-calendar-clock',
            'color' => '#38bdf8'
        ];
        $delivery[] = $reported;


        $noanswerCount = $deliveryOrders->where('delivery', 'pas-de-reponse')->count();
        $noanswer = [
            'id' => 5,
            'title' => 'No Answer',
            'value' => $noanswerCount,
            'percentage' => $deliveryOrders->count() > 0  ? ($noanswerCount * 100) / $deliveryOrders->count() : 0,
            'icon' => 'mdi-account-cancel',
            'color' => '#eab308'
        ];
        $delivery[] = $noanswer;


        $cancelledCount = $deliveryOrders->where('delivery', 'annuler')->count();
        $cancelled = [
            'id' => 6,
            'title' => 'Cancelled',
            'value' => $cancelledCount,
            'percentage' => $deliveryOrders->count() > 0  ? ($cancelledCount * 100) / $deliveryOrders->count() : 0,
            'icon' => 'mdi-close-circle',
            'color' => '#f43f5e'
        ];
        $delivery[] = $cancelled;


        $deliveredRevenue = [
            'id' => 2,
            'title' => 'Revenue',
            'value' => 0,
            'icon' => 'mdi-currency-usd',
            'color' => '#22c55e'
        ];

        $orders
        ->where('confirmation', 'confirmer')
        ->where('delivery', 'livrer')
        ->map(function($o) use(&$deliveredRevenue) {
            $deliveredRevenue['value'] += self::getPrice($o);
        });


        // $deliveredRevenue = 0;
        // $deliveredOrders = $orders
        // ->where('confirmation', 'confirmer')
        // ->where('delivery', 'livrer')
        // ->map(function($o) use(&$total) {
        //     $total += self::getPrice($o);
        // });


        $statistics = [
            'confirmations' => $confirmations,
            'delivery' => $delivery,
            'revenue' => [
               
                // $deliveredRevenue
            ]
        ];

        return $statistics;
    }

    public static function admin($request) {
        $orders = Order::query();

        $created_from = $request->created_from;
        $created_to = $request->created_to;
        $dropped_from = $request->dropped_from;
        $dropped_to = $request->dropped_to;
        $affectation = $request->affectation;
        $confirmation = $request->confirmation;
        $delivery = $request->delivery;
        $product_id = $request->product_id;
        $agente_id = $request->agente_id;
        $upsell = $request->upsell;

        $orders
        ->when(!!$created_from, fn($q) => $q->whereDate('created_at', '>=', $created_from))
        ->when(!!$created_to, fn($q) => $q->whereDate('created_at', '<=', $created_to))
        ->when(!!$dropped_from, fn($q) => $q->whereDate('dropped_at', '>=', $dropped_from))
        ->when(!!$dropped_to, fn($q) => $q->whereDate('dropped_at', '<=', $dropped_to))
        ->when($affectation != 'all', fn($q) => $q->where('affectation', '=', $affectation))
        ->when($confirmation != 'all', fn($q) => $q->where('confirmation', '=', $confirmation))
        ->when($delivery != 'all', fn($q) => $q->where('delivery', '=', $delivery))
        ->when($agente_id != 'all', fn($q) => $q->where('agente_id', '=', $agente_id))
        ->when($upsell != 'all', fn($q) => $q->where('upsell', '=', $upsell))
        ->when($product_id != 'all', fn($q) => $q->whereHas('items', fn($oq) => $oq->where('product_id', $product_id)));

        $orders = $orders->get();

        $confirmations = [];
        $delivery = [];
        $totalCount = $orders->where('confirmation', '!=', 'double')->count();

        $allCount = $orders->count();
        $all = [
            'id' => 1,
            'title' => 'Orders',
            'value' => $allCount,
            'icon' => 'mdi-package-variant-closed',
            'color' => '#6b7280'
        ];
        $confirmations[] = $all;


        $confirmedCount = $orders->where('confirmation', 'confirmer')->count();
        $confirmed = [
            'id' => 2,
            'title' => 'Confirmed',
            'value' => $confirmedCount,
            'percentage' => $totalCount > 0  ? ($confirmedCount * 100) / $totalCount : 0,
            // 'icon' => 'mdi-check-all',
            'icon' => 'mdi-phone-check',
            'color' => '#10b981'
        ];
        $confirmations[] = $confirmed;


        $newCount = $orders->where('confirmation', null)->count();
        $new = [
            'id' => 3,
            'title' => 'New',
            'value' => $newCount,
            'percentage' => $totalCount > 0  ? ($newCount * 100) / $totalCount : 0,
            'icon' => 'mdi-new-box',
            'color' => '#475569'
        ];
        $confirmations[] = $new;


        $reportedCount = $orders->where('confirmation', 'reporter')->count();
        $reported = [
            'id' => 4,
            'title' => 'Reported',
            'value' => $reportedCount,
            'percentage' => $totalCount > 0  ? ($reportedCount * 100) / $totalCount : 0,
            'icon' => 'mdi-clock-outline',
            'color' => '#a855f7'
        ];
        $confirmations[] = $reported;


        $noAnswerConfirmations = ['day-one-call-one', 'day-one-call-two', 'day-one-call-three', 'day-two-call-one', 'day-two-call-two', 'day-two-call-three', 'day-three-call-one', 'day-three-call-two', 'day-three-call-three'];
        $noAnswerCount = $orders->whereIn('confirmation', $noAnswerConfirmations)->count();
        $noAnswer = [
            'id' => 5,
            'title' => 'No Answer',
            'value' => $noAnswerCount,
            'percentage' => $totalCount > 0  ? ($noAnswerCount * 100) / $totalCount : 0,
            'icon' => 'mdi-phone-missed',
            'color' => '#fbbf24'
        ];
        $confirmations[] = $noAnswer;


        $cancelledCount = $orders->where('confirmation', 'annuler')->count();
        $cancelled = [
            'id' => 6,
            'title' => 'Cancelled',
            'value' => $cancelledCount,
            'percentage' => $totalCount > 0  ? ($cancelledCount * 100) / $totalCount : 0,
            'icon' => 'mdi-cancel',
            'color' => '#f43f5e'
        ];
        $confirmations[] = $cancelled;


        $doubledCount = $orders->where('confirmation', 'double')->count();
        $double = [
            'id' => 7,
            'title' => 'Double',
            'value' => $doubledCount,
            'percentage' => $totalCount > 0  ? ($doubledCount * 100) / $totalCount : 0,
            'icon' => 'mdi-selection-multiple',
            'color' => '#8b5cf6'
        ];
        $confirmations[] = $double;


        $wrondNumberCount = $orders->where('confirmation', 'wrong-number')->count();
        $wrongNumber = [
            'id' => 8,
            'title' => 'Wrong Number',
            'value' => $wrondNumberCount,
            'percentage' => $totalCount > 0  ? ($wrondNumberCount * 100) / $totalCount : 0,
            'icon' => 'mdi-phone-remove',
            'color' => '#db2777'
        ];
        $confirmations[] = $wrongNumber;


        $deliveryOrders = $orders->where('confirmation', 'confirmer');

        $all = [
            'id' => 1,
            'title' => 'Confirmed',
            'value' => $deliveryOrders->count(),
            'icon' => 'mdi-check-circle-outline',
            'color' => '#6b7280'
        ];
        $delivery[] = $all;


        $deliveredCount = $deliveryOrders->where('delivery', 'livrer')->count();
        $delivered = [
            'id' => 2,
            'title' => 'Delivered',
            'value' => $deliveredCount,
            'percentage' => $deliveryOrders->count() > 0  ? ($deliveredCount * 100) / $deliveryOrders->count() : 0,
            'icon' => 'mdi-truck-check',
            'color' => '#10b981'
        ];
        $delivery[] = $delivered;


        $shippedCount = $deliveryOrders->where('delivery', 'expidier')->count();
        $shipped = [
            'id' => 3,
            'title' => 'Shipped',
            'value' => $shippedCount,
            'percentage' => $deliveryOrders->count() > 0  ? ($shippedCount * 100) / $deliveryOrders->count() : 0,
            'icon' => 'mdi-truck-fast-outline',
            'color' => '#f97316'
        ];
        $delivery[] = $shipped;

        $transferCount = $deliveryOrders->where('delivery', 'transfer')->count();
        $transferred = [
            'id' => 7,
            'title' => 'Transferred',
            'value' => $transferCount,
            'percentage' => $deliveryOrders->count() > 0  ? ($transferCount * 100) / $deliveryOrders->count() : 0,
            'icon' => 'mdi-dolly',
            'color' => '#a855f7'
        ];
        $delivery[] = $transferred;


        $reportedCount = $deliveryOrders->where('delivery', 'reporter')->count();
        $reported = [
            'id' => 4,
            'title' => 'Reported',
            'value' => $reportedCount,
            'percentage' => $deliveryOrders->count() > 0  ? ($reportedCount * 100) / $deliveryOrders->count() : 0,
            'icon' => 'mdi-calendar-clock',
            'color' => '#38bdf8'
        ];
        $delivery[] = $reported;


        $noanswerCount = $deliveryOrders->where('delivery', 'pas-de-reponse')->count();
        $noanswer = [
            'id' => 5,
            'title' => 'No Answer',
            'value' => $noanswerCount,
            'percentage' => $deliveryOrders->count() > 0  ? ($noanswerCount * 100) / $deliveryOrders->count() : 0,
            'icon' => 'mdi-account-cancel',
            'color' => '#eab308'
        ];
        $delivery[] = $noanswer;


        $cancelledCount = $deliveryOrders->where('delivery', 'annuler')->count();
        $cancelled = [
            'id' => 6,
            'title' => 'Cancelled',
            'value' => $cancelledCount,
            'percentage' => $deliveryOrders->count() > 0  ? ($cancelledCount * 100) / $deliveryOrders->count() : 0,
            'icon' => 'mdi-close-circle',
            'color' => '#f43f5e'
        ];
        $delivery[] = $cancelled;


        $deliveredRevenue = [
            'id' => 2,
            'title' => 'Revenue',
            'value' => 0,
            'icon' => 'mdi-currency-usd',
            'color' => '#22c55e'
        ];

        $orders
        ->where('confirmation', 'confirmer')
        ->where('delivery', 'livrer')
        ->map(function($o) use(&$deliveredRevenue) {
            $deliveredRevenue['value'] += self::getPrice($o);
        });


        // $deliveredRevenue = 0;
        // $deliveredOrders = $orders
        // ->where('confirmation', 'confirmer')
        // ->where('delivery', 'livrer')
        // ->map(function($o) use(&$total) {
        //     $total += self::getPrice($o);
        // });


        $statistics = [
            'confirmations' => $confirmations,
            'delivery' => $delivery,
            'revenue' => [
                // $deliveredRevenue
            ]
        ];

        return $statistics;
    }


    public static function getPrice($order) {
        if (!$order) return 0;
        $total = array_reduce($order['items']->values()->toArray(), function($sum, $item) {
            return $sum + (!$item['price'] ? 0 : $item['price']);
        }, 0);
            return floatval(!$order['price'] ? 0 : $order['price']) + floatval($total);
    }

}
