<?php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;
use Illuminate\Pagination\Paginator;

require_once __DIR__ . '/vendor/autoload.php';

$capsule = new Capsule();

$dbPath = getenv('DB_PATH') ?: __DIR__ . '/database/booking.db';

$capsule->addConnection([
    'driver'   => 'sqlite',
    'database' => $dbPath,
    'prefix'   => '',
]);

$capsule->setEventDispatcher(new Dispatcher(new Container()));
$capsule->setAsGlobal();
$capsule->bootEloquent();

Paginator::currentPageResolver(function () {
    return (int) ($_GET['page'] ?? 1);
});
