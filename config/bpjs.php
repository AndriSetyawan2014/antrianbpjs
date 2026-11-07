<?php

return [
    'vclaim' => [
        'base_url' => env('BPJS_VCLAIM_BASE_URL', 'https://apijkn.bpjs-kesehatan.go.id/vclaim-rest'),
        'cons_id' => [
            'QLJ'   => env('BPJS_VCLAIM_CONS_ID_QLJ'),
            'QLKP'  => env('BPJS_VCLAIM_CONS_ID_QLKP'),
            'QLTMG' => env('BPJS_VCLAIM_CONS_ID_QLTMG'),
        ],
        'secret' => [
            'QLJ'   => env('BPJS_VCLAIM_SECRET_QLJ'),
            'QLKP'  => env('BPJS_VCLAIM_SECRET_QLKP'),
            'QLTMG' => env('BPJS_VCLAIM_SECRET_QLTMG'),
        ],
        'user_key' => [
            'QLJ'   => env('BPJS_VCLAIM_USERKEY_QLJ'),
            'QLKP'  => env('BPJS_VCLAIM_USERKEY_QLKP'),
            'QLTMG' => env('BPJS_VCLAIM_USERKEY_QLTMG'),
        ],
    ],
    'antrol' => [
        'base_url' => env('BPJS_BASE_URL', 'https://apijkn.bpjs-kesehatan.go.id/antreanrs'),
        'cons_id' => [
            'QLJ'   => env('BPJS_ANTROL_CONS_ID_QLJ'),
            'QLKP'  => env('BPJS_ANTROL_CONS_ID_QLKP'),
            'QLTMG' => env('BPJS_ANTROL_CONS_ID_QLTMG'),
        ],
        'secret' => [
            'QLJ'   => env('BPJS_ANTROL_SECRET_QLJ'),
            'QLKP'  => env('BPJS_ANTROL_SECRET_QLKP'),
            'QLTMG' => env('BPJS_ANTROL_SECRET_QLTMG'),
        ],
        'user_key' => [
            'QLJ'   => env('BPJS_ANTROL_USERKEY_QLJ'),
            'QLKP'  => env('BPJS_ANTROL_USERKEY_QLKP'),
            'QLTMG' => env('BPJS_ANTROL_USERKEY_QLTMG'),
        ],
    ],
];
