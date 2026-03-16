<?php

declare(strict_types=1);

use Slim\Factory\AppFactory;
use App\Controllers\Api\BookingController as ApiBookingController;

require_once __DIR__ . '/../bootstrap.php';

$app = AppFactory::create();

$app->addBodyParsingMiddleware();
$app->addErrorMiddleware(true, true, true);

$app->get('/api/bookings', [ApiBookingController::class, 'index']);

$app->run();
