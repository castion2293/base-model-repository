<?php

namespace Pharaoh\BaseModelRepository;

use Illuminate\Support\ServiceProvider;

class BaseModelRepositoryProvider extends ServiceProvider
{
    public function boot()
    {
        // 引入 SqlHelper 方法
        require_once(__DIR__ . '/Helpers/SqlHelpers.php');
    }

    public function register()
    {
    }
}
