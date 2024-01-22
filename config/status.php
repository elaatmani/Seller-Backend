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
                [ 'name' => 'Quoting', 'value' => 'quoting' ],
                [ 'name' => 'Processing', 'value' => 'processing' ],
                [ 'name' => 'Accepted', 'value' => 'accepted' ],
                [ 'name' => 'Refused', 'value' => 'refused' ],
            ]
        ],
        'sourcing_status' => [
            'default' => [ 'name' => 'Pending', 'value' => 'pending' ],
            'values' => [
                [ 'name' => 'Pending', 'value' => 'pending' ],
                [ 'name' => 'Processing', 'value' => 'processing' ],
                [ 'name' => 'Accepted', 'value' => 'accepted' ],
                [ 'name' => 'Refused', 'value' => 'refused' ],
            ]
        ]
    ]
];
