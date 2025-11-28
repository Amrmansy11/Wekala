<?php

use Illuminate\Support\Facades\Route;

Route::prefix('admin')
    ->middleware(['localization'])
    ->group(base_path('routes/api/admin.php'));

Route::prefix('vendor')
    ->middleware(['vendor.switch', 'localization'])
    ->group(base_path('routes/api/vendor.php'));

Route::prefix('consumer')
    ->middleware(['localization'])
    ->group(base_path('routes/api/consumer.php'));
