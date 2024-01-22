<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Exception;
class StatisticsService
{

    public static $confirmations = [
        null => 'New',
        'day-one-call-one'=> 'No reply 1 / day1',
        'day-one-call-two' =>'No reply 2 / day1',
        'day-one-call-three' =>'No reply 3 / day1',
        'day-two-call-one' =>'No reply 1 / day2',
        'day-two-call-two' =>'No reply 2 / day2',
        'day-two-call-three' =>'No reply 3 / day2',
        'day-three-call-one' =>'No reply 1 / day3',
        'day-three-call-two' =>'No reply 2 / day3',
        'day-three-call-three' =>'No reply 3 / day3',
        'reporter' =>'Reported',
        'annuler' =>'Canceled',
        'wrong-number' =>'Wrong number',
        'confirmer' =>'Confirmed',
        'double' =>'Double',
        'reconfirmer' => 'Reconfirmed',
        'change' => 'Change',
        'refund' => 'Refund'
    ];

    public static function orders($request) {
        $orders = Order::query();
        
        $created_from = $request->created_from;
        $created_to = $request->created_to;
        $product_id = $request->product_id;
        $user_id = auth()->id();
        
        $orders = $orders->when(!!$created_from, fn($q) => $q->whereDate('created_at', '>=', $created_from))
        ->when(!!$created_to, fn($q) => $q->whereDate('created_at', '<=', $created_to))
        ->when($product_id != 'all', fn($q) => $q->whereHas('items', fn($oq) => $oq->where('product_id', $product_id)))
        ->when($user_id != 'all' , fn($q) => $q->where('user_id',$user_id));
        
        return $orders;
    }

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


    public static function agent($request) {

        $orders = Order::query();

        $orders->where('agente_id', auth()->id());

        $created_from = $request->created_from;
        $created_to = $request->created_to;

        $orders
        ->when(!!$created_from, fn($q) => $q->whereDate('created_at', '>=', $created_from))
        ->when(!!$created_to, fn($q) => $q->whereDate('created_at', '<=', $created_to));

        $orders = $orders->get();

      
        $reported = $orders->where('confirmation', 'reporter')->count();
        $new = $orders->where('confirmation', null)->count();
        $confirmed = $orders->where('confirmation', 'confirmer')->count();
        $cancelled = $orders->where('confirmation', 'annuler')->count();
        $doubles = $orders->where('confirmation', 'double')->count();
        $upsells = $orders->where('confirmation', 'confirmer')->where('upsell', 'oui')->count();
        $earnings = $orders->where('confirmation', 'confirmer')->where('upsell', 'oui')->where('delivery', 'livrer')->count();
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

        $earnings = [
            'id' => 5,
            'title' => 'Earnings',
            'value' => round($earnings * 0.7, 2),
            'icon' => 'mdi-currency-usd',
            'symbol' => '$',
            'color' => '#34d399'
        ];


        $reported = [
            'id' => 2,
            'title' => 'Reported',
            'value' => $reported,
            'percentage' => $totalCount > 0  ? ($reported * 100) / $totalCount : 0,
            'icon' => 'mdi-clock-outline',
            'color' => '#14b8a6'
        ];

        $confirmed = [
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


        $new = [
            'id' => 9,
            'title' => 'Need Confirmation',
            'value' => $new,
            'icon' => 'mdi-new-box',
            'color' => '#a78bfa'
        ];


        $statistics = [$earnings, $all, $confirmed, $upsell, $reported,  $noAnswer, $cancelled, $doubles];

        if($new['value'] > 0) {
            $statistics[] = $new;
        }

        return $statistics;
    }

    public static function seller($request) {
        $orders = Order::query();
        $orders->where('user_id', auth()->id());
        $created_from = $request->created_from;
        $created_to = $request->created_to;
        $dropped_from = $request->dropped_from;
        $dropped_to = $request->dropped_to;
        $delivered_from = $request->delivered_from;
        $delivered_to = $request->delivered_to;
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
        ->when(!!$delivered_from, fn($q) => $q->whereDate('delivery_date', '>=', $delivered_from))
        ->when(!!$delivered_to, fn($q) => $q->whereDate('delivery_date', '<=', $delivered_to))
        ->when($affectation != 'all', fn($q) => $q->where('affectation', '=', $affectation))
        ->when($confirmation != 'all', fn($q) => $q->where('confirmation', '=', $confirmation))
        ->when($delivery != 'all', fn($q) => $q->where('delivery', '=', $delivery))
        ->when($agente_id != 'all', fn($q) => $q->where('agente_id', '=', $agente_id))
        ->when($upsell != 'all', fn($q) => $q->where('upsell', '=', $upsell))
        ->when($product_id != 'all', fn($q) => $q->whereHas('items', fn($oq) => $oq->where('product_id', $product_id)));

        $orders = $orders->get();

        $confirmations = [];
        $delivery = [];
        $totalCount = $orders->where('confirmation', '!=', null)->where('confirmation', '!=', 'double')->count();

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


        $deliveredCount = $deliveryOrders->whereIn('delivery', ['livrer', 'paid'])->count();
        $delivered = [
            'id' => 2,
            'title' => 'Delivered',
            'value' => $deliveryOrders->where('delivery', 'livrer')->count(),
            'percentage' => $deliveryOrders->count() > 0  ? ($deliveredCount * 100) / $deliveryOrders->count() : 0,
            'icon' => 'mdi-truck-check',
            'color' => '#10b981'
        ];
        $delivery[] = $delivered;

         $paidCount = $deliveryOrders->where('delivery', 'paid')->count();
                $paid = [
                    'id' => 6,
                    'title' => 'Paid',
                    'value' => $paidCount,
                    'icon' => 'mdi-currency-usd',
                    'color' => '#10b981'
                ];
                $delivery[] = $paid;
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


        
        
        $orderIds = self::orders($request)->where('confirmation', 'confirmer')->whereIn('delivery', ['livrer', 'paid'])->get()->pluck('id')->values()->toArray();
        $totalRevenue = self::orders($request)->where('confirmation', 'confirmer')->whereIn('delivery', ['livrer', 'paid'])->sum('price');
        $orderItemsTotalRevenue = OrderItem::whereIn('order_id', $orderIds)->sum('price');
        $totalRevenueValue = round($totalRevenue + $orderItemsTotalRevenue, 2);
        
        $totalRevenue = [
            'id' => 1,
            'title' => 'Revenue',
            'value' => $totalRevenueValue,
            'icon' => 'mdi-currency-usd',
            'color' => '#22c55e'
        ];


        $orderIds = self::orders($request)->where('confirmation', 'confirmer')->where('delivery', 'paid')->get()->pluck('id')->values()->toArray();
        $ordersTotalPaid = self::orders($request)->where('confirmation', 'confirmer')->where('delivery', 'paid')->sum('price');
        $orderItemsTotalPaid = OrderItem::whereIn('order_id', $orderIds)->sum('price');
        $paidRevenueValue = round($ordersTotalPaid + $orderItemsTotalPaid, 2);
        
        $totalPaid = [
            'id' => 2,
            'title' => 'Paid',
            'value' => $paidRevenueValue,
            'icon' => 'mdi-check',
            'color' => '#22c55e'
        ];


        $orderIds = self::orders($request)->where('confirmation', 'confirmer')->where('delivery', 'livrer')->get()->pluck('id')->values()->toArray();
        $ordersTotalDelivered = self::orders($request)->where('confirmation', 'confirmer')->where('delivery', 'livrer')->sum('price');
        $orderItemsTotalDelivered = OrderItem::whereIn('order_id', $orderIds)->sum('price');
        $deliveredRevenueValue = round($ordersTotalDelivered + $orderItemsTotalDelivered, 2);
        
        $totalDelivered = [
            'id' => 3,
            'title' => 'Delivered',
            'value' => $deliveredRevenueValue,
            'icon' => 'mdi-truck-check',
            'color' => '#22c55e'
        ];


        $orderIds = self::orders($request)->where('confirmation', 'confirmer')->whereIn('delivery', ['expidier', 'transfer'])->get()->pluck('id')->values()->toArray();
        $ordersTotalShipped = self::orders($request)->where('confirmation', 'confirmer')->whereIn('delivery', ['expidier', 'transfer'])->sum('price');
        $orderItemsTotalShipped = OrderItem::whereIn('order_id', $orderIds)->sum('price');
        $shippedRevenueValue = round($ordersTotalShipped + $orderItemsTotalShipped, 2);
        
        $totalShipped = [
            'id' => 3,
            'title' => 'Shipped & Tranferred',
            'value' => $shippedRevenueValue,
            'icon' => 'mdi-dolly',
            'color' => '#22c55e'
        ];


        $statistics = [
            'confirmations' => $confirmations,
            'delivery' => $delivery,
            'revenue' => [
                $totalRevenue,
                $totalPaid,
                $totalDelivered,
                $totalShipped
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
        $delivered_from = $request->delivered_from;
        $delivered_to = $request->delivered_to;
        $affectation = $request->affectation;
        $confirmation = $request->confirmation;
        $delivery = $request->delivery;
        $product_id = $request->product_id;
        $agente_id = $request->agente_id;
        $user_id = $request->user_id;
        $upsell = $request->upsell;

        $orders
        ->when(!!$created_from, fn($q) => $q->whereDate('created_at', '>=', $created_from))
        ->when(!!$created_to, fn($q) => $q->whereDate('created_at', '<=', $created_to))
        ->when(!!$dropped_from, fn($q) => $q->whereDate('dropped_at', '>=', $dropped_from))
        ->when(!!$dropped_to, fn($q) => $q->whereDate('dropped_at', '<=', $dropped_to))
        ->when(!!$delivered_from, fn($q) => $q->whereDate('delivery_date', '>=', $delivered_from))
        ->when(!!$delivered_to, fn($q) => $q->whereDate('delivery_date', '<=', $delivered_to))
        ->when($affectation != 'all', fn($q) => $q->where('affectation', '=', $affectation))
        ->when($confirmation != 'all', fn($q) => $q->where('confirmation', '=', $confirmation))
        ->when($delivery != 'all', fn($q) => $q->where('delivery', '=', $delivery))
        ->when($agente_id != 'all', fn($q) => $q->where('agente_id', '=', $agente_id))
        ->when($user_id != 'all', fn($q) => $q->where('user_id', '=', $user_id))
        ->when($upsell != 'all', fn($q) => $q->where('upsell', '=', $upsell))
        ->when($product_id != 'all', fn($q) => $q->whereHas('items', fn($oq) => $oq->where('product_id', $product_id)));

        $orders = $orders->get();

        $confirmations = [];
        $delivery = [];
        $totalCount = $orders->where('confirmation', '!=', null)->where('confirmation', '!=', 'double')->count();

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
            'percentage' => $totalCount > 0  ? (($confirmedCount * 100) / $totalCount) : 0,
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


        $deliveredCount = $deliveryOrders->whereIn('delivery', ['livrer', 'paid'])->count();
        $delivered = [
            'id' => 2,
            'title' => 'Delivered',
            'value' => $deliveryOrders->where('delivery', 'livrer')->count(),
            'percentage' => $deliveryOrders->count() > 0  ? ($deliveredCount * 100) / $deliveryOrders->count() : 0,
            'icon' => 'mdi-truck-check',
            'color' => '#10b981'
        ];
        $delivery[] = $delivered;
        
        $paidCount = $deliveryOrders->where('delivery', 'paid')->count();
        $paid = [
            'id' => 6,
            'title' => 'Paid',
            'value' => $paidCount,
            'icon' => 'mdi-currency-usd',
            'color' => '#10b981'
        ];
        $delivery[] = $paid;


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


        


        $statistics = [
            'confirmations' => $confirmations,
            'delivery' => $delivery,
        ];

        return $statistics;
    }



    public static function adminSalesStatistics()
    {
        $orders = DB::table('orders')->groupBy('confirmation')->selectRaw("confirmation, count('confirmation') as total")->get();

        $deliveryOrders = DB::table('orders')->groupBy('delivery')->selectRaw("delivery, count('delivery') as total")->get();

        $total = $orders->where('confirmation', '!=', 'double')->sum('total');

        $noAnswers = [
            'day-one-call-one',
            'day-one-call-two',
            'day-one-call-three',
            'day-two-call-one',
            'day-two-call-two',
            'day-two-call-three',
            'day-three-call-one',
            'day-three-call-two',
            'day-three-call-three',
        ];

        $noAnswer = $orders->whereIn('confirmation', $noAnswers);

        $statistics = $orders->map(function($c) use($total, $orders) {
            return [
                'name' => self::$confirmations[$c->confirmation],
                'confirmation' => $c->confirmation,
                'total' => $c->total,
                'percent' => $c->confirmation != 'double' ? round(($c->total * 100) / $total, 2) : round(($c->total * 100) / $orders->sum('total'), 2),
            ];
        })->whereNotIn('confirmation', $noAnswers);

        $statistics[] = [
                'name' => 'No Answer',
                'confirmation' => 'day-one-call-one',
                'total' => $noAnswer->sum('total'),
                'percent' =>$total == 0 ? 0 : round(($noAnswer->sum('total') * 100) / $total, 2),
        ];

        $confirmedTotal = $orders->where('confirmation', 'confirmer')->first()?->total;

        $deliveredCount = $deliveryOrders->where('delivery', 'livrer')->first()?->total;
        $statistics[] = [
            'name' => 'Delivered',
            'confirmation' => 'confirmer',
            'total' => $deliveredCount,
            'percent' => $confirmedTotal == 0 ? 0 : round(($deliveredCount * 100) / $confirmedTotal, 2),
        ];
        
        $paidCount = $deliveryOrders->where('delivery', 'paid')->first()?->total;
        $statistics[] = [
            'name' => 'Paid',
            'confirmation' => 'confirmer',
            'total' => $paidCount,
            'percent' => $confirmedTotal == 0 ? 0 : round(($paidCount * 100) / $confirmedTotal, 2),
        ];

        // $statistics[] = [
        //     'name' => 'Test',
        //     'confirmation' => 'confirmer',
        //     'total' => $deliveredCount,
        //     'percent' => $deliveredCount,
        // ];

        $shippedCount = $deliveryOrders->where('delivery', 'expidier')->first()?->total;
        $statistics[] = [
            'name' => 'Shipped',
            'confirmation' => 'wrong-number',
            'total' => $shippedCount,
            'percent' => $confirmedTotal == 0 ? 0 : round(($shippedCount * 100) / $confirmedTotal, 2),
        ];

        $show = [ '*' ];
        $response = [
            'data' => $statistics,
            'show' => $show
        ];

        return $response;
    }


    public static function getPrice($order) {
        if (!$order) return 0;
        $total = array_reduce($order['items']->values()->toArray(), function($sum, $item) {
            return $sum + (!$item['price'] ? 0 : $item['price']);
        }, 0);
            return floatval(!$order['price'] ? 0 : $order['price']) + floatval($total);
    }

}
