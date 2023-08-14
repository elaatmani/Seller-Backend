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
            'title' => 'Orders',
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

}
