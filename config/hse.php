<?php

return [

    /*
    |--------------------------------------------------------------------------
    | SILO Validity Period
    |--------------------------------------------------------------------------
    |
    | How long a SILO is valid from its issue date, in months. Previously
    | hardcoded as ->addYear() inside Silos::booted() (TECHNICAL_AUDIT.md
    | finding M6). SIO has no automatic validity period (M9) — its expiry
    | date is entered directly.
    |
    */
    'silo_validity_months' => 12,

    /*
    |--------------------------------------------------------------------------
    | Expiring Soon Threshold
    |--------------------------------------------------------------------------
    |
    | Number of days before its expiry date that a document is considered
    | "expiring soon" rather than fully active. Previously hardcoded as
    | addDays(30) in each of the badge columns this replaces.
    |
    */
    'expiring_soon_threshold_days' => 30,

];
