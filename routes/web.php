<?php

use App\Controllers\HomeController;
use App\Core\Router;

Router::get('/', [HomeController::class, 'home']);
