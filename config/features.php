<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Feature Flags for SI-KASEP
    |--------------------------------------------------------------------------
    | Digunakan untuk mengaktifkan / menonaktifkan modul fitur tertentu antara
    | lingkungan localhost dan server web hosting secara terpisah.
    |
    | Di Localhost (.env):
    | FEATURE_ASESMEN=true
    | FEATURE_PERPUSTAKAAN=true
    |
    | Di Hosting / Production (.env):
    | FEATURE_ASESMEN=false
    | FEATURE_PERPUSTAKAAN=false
    |
    | Nilai default jika belum didefinisikan di .env adalah FALSE (aman).
    |
    */

    'asesmen' => (bool) env('FEATURE_ASESMEN', false),
    'perpustakaan' => (bool) env('FEATURE_PERPUSTAKAAN', false),
];
