<?php

declare(strict_types=1);

use Slim\Factory\AppFactory;
use App\Controllers\BookingController;
use App\Controllers\Api\BookingController as ApiBookingController;

require_once __DIR__ . '/../bootstrap.php';

$app = AppFactory::create();

$app->addBodyParsingMiddleware();
$app->addErrorMiddleware(true, true, true);

$app->get('/', [BookingController::class, 'index']);
$app->get('/bookings/create', [BookingController::class, 'create']);
$app->post('/bookings', [BookingController::class, 'store']);
$app->get('/bookings/{id}/edit', [BookingController::class, 'edit']);
$app->post('/bookings/{id}', [BookingController::class, 'update']);
$app->post('/bookings/{id}/delete', [BookingController::class, 'destroy']);

$app->get('/api/bookings', [ApiBookingController::class, 'index']);

$app->run();
