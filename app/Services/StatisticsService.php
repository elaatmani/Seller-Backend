<?php

namespace App\Services;


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
            'percentage' => $orders->count() > 0  ? ($reported * 100) / $orders->count() : 0,
            'icon' => 'mdi-clock-outline',
            'color' => '#14b8a6'
        ];

        $reconfirmed = [
            'id' => 3,
            'title' => 'Confirmed',
            'value' => $confirmed,
            'percentage' => $orders->count() > 0  ? ($confirmed * 100) / $orders->count() : 0,
            'icon' => 'mdi-check-all',
            'color' => '#06b6d4'
        ];

        $noAnswer = [
            'id' => 4,
            'title' => 'No Answer',
            'value' => $noAnswer,
            'percentage' => $orders->count() > 0  ? ($noAnswer * 100) / $orders->count() : 0,
            'icon' => 'mdi-phone-alert',
            'color' => '#facc15'
        ];

        $earnings = [
            'id' => 5,
            'title' => 'Earnings',
            'value' => '0',
            'percentage' => $orders->count() > 0  ? (0 * 100) / $orders->count() : 0,
            'icon' => 'mdi-currency-usd',
            'symbol' => '$',
            'color' => '#34d399'
        ];

        $cancelled = [
            'id' => 6,
            'title' => 'Cancelled',
            'value' => $cancelled,
            'percentage' => $orders->count() > 0  ? ($cancelled * 100) / $orders->count() : 0,
            'icon' => 'mdi-cancel',
            'color' => '#f43f5e'
        ];

        $upsell = [
            'id' => 7,
            'title' => 'Upsell',
            'value' => $upsells,
            'percentage' => $orders->count() > 0  ? ($upsells * 100) / $orders->count() : 0,
            'icon' => 'mdi-transfer-up',
            'color' => '#fb923c'
        ];

        $doubles = [
            'id' => 8,
            'title' => 'Double',
            'value' => $doubles,
            'percentage' => $orders->count() > 0  ? ($doubles * 100) / $orders->count() : 0,
            'icon' => 'mdi-selection-multiple',
            'color' => '#a78bfa'
        ];


        return [$all, $new, $reconfirmed, $earnings, $noAnswer, $cancelled, $doubles, $upsell];
    }

}
