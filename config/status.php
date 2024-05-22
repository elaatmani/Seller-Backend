<?php

// ****
// Define All application statuses
// ****

return [
    'supply_requests' => [
        'default' => [ 'name' => 'Pending', 'value' => 'pending' ],
        'accepted' => [ 'name' => 'Accepted', 'value' => 'accepted' ],
        'values' => [
            [ 'name' => 'Pending', 'value' => 'pending' ],
            [ 'name' => 'Processing', 'value' => 'processing' ],
            [ 'name' => 'Accepted', 'value' => 'accepted' ],
            [ 'name' => 'Refused', 'value' => 'refused' ],
        ]
    ],

    'sourcings' => [
        'quotation_status' => [
            'default' => [ 'name' => 'Pending', 'value' => 'pending' ],
            'values' => [
                [ 'name' => 'Pending', 'value' => 'pending' ],
                [ 'name' => 'Processing', 'value' => 'processing' ],
                [ 'name' => 'Quoting', 'value' => 'quoting' ],
                [ 'name' => 'Cancelled', 'value' => 'cancelled' ],
                [ 'name' => 'Confirmed', 'value' => 'confirmed' ],
            ]
        ],
        'sourcing_status' => [
            'default' => [ 'name' => 'Pending', 'value' => 'pending' ],
            'values' => [
                [ 'name' => 'Pending', 'value' => 'pending' ],
                [ 'name' => 'Processing', 'value' => 'processing' ],
                [ 'name' => 'Packing', 'value' => 'packing' ],
                [ 'name' => 'Shipped', 'value' => 'shipped' ],
                [ 'name' => 'Delivered', 'value' => 'delivered' ],
                [ 'name' => 'Cancelled', 'value' => 'cancelled' ],
            ]
        ]
    ]
];
