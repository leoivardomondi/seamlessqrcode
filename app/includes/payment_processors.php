<?php
/*
 * Copyright (c) 2025 leoivard
 */

defined('ALTUMCODE') || die();

return [
    'paypal' => [
        'payment_type' => ['one_time', 'recurring'],
        'icon' => 'fab fa-paypal',
        'color' => '#3b7bbf',
    ],
    'stripe' => [
        'payment_type' => ['one_time', 'recurring'],
        'icon' => 'fab fa-stripe',
        'color' => '#5433FF',
    ],
    'offline_payment' => [
        'payment_type' => ['one_time'],
        'icon' => 'fas fa-mobile-alt',
        'color' => '#00a859',
    ],
    'coinbase' => [
        'payment_type' => ['one_time'],
        'icon' => 'fab fa-bitcoin',
        'color' => '#0050FF',
    ],
    'payu' => [
        'payment_type' => ['one_time'],
        'icon' => 'fas fa-underline',
        'color' => '#A6C306',
    ],
    'iyzico' => [
        'payment_type' => ['one_time'],
        'icon' => 'fas fa-teeth',
        'color' => '#1E64FF',
    ],
    'paystack' => [
        'payment_type' => ['one_time', 'recurring'],
        'icon' => 'fas fa-money-check',
        'color' => '#00C3F7',
    ],
    'razorpay' => [
        'payment_type' => ['one_time', 'recurring'],
        'icon' => 'fas fa-heart',
        'color' => '#2b84ea',
    ],
    'mollie' => [
        'payment_type' => ['one_time', 'recurring'],
        'icon' => 'fas fa-shopping-basket',
        'color' => '#465975',
    ],
    'yookassa' => [
        'payment_type' => ['one_time'],
        'icon' => 'fas fa-ruble-sign',
        'color' => '#004CAA',
    ],
    'crypto_com' => [
        'payment_type' => ['one_time'],
        'icon' => 'fas fa-coins',
        'color' => '#4b71d7',
    ],
    'paddle' => [
        'payment_type' => ['one_time'],
        'icon' => 'fas fa-star',
        'color' => '#a6b0b9',
    ],
    'mercadopago' => [
        'payment_type' => ['one_time'],
        'icon' => 'fas fa-handshake',
        'color' => '#009EE3',
    ],
    'midtrans' => [
        'payment_type' => ['one_time'],
        'icon' => 'fas fa-grip-vertical',
        'color' => '#002855',
    ],
    'flutterwave' => [
        'payment_type' => ['one_time', 'recurring'],
        'icon' => 'fas fa-water',
        'color' => '#FB9129',
    ],
    'lemonsqueezy' => [
        'payment_type' => ['one_time', 'recurring'],
        'icon' => 'fas fa-lemon',
        'color' => '#F5C518',
    ],
    'myfatoorah' => [
        'payment_type' => ['one_time'],
        'icon' => 'fas fa-feather',
        'color' => '#0000ff',
    ],
];
