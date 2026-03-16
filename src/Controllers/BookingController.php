<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Booking;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class BookingController
{

    public function __construct()
    {
    }

    public function index(Request $request, Response $response): Response
    {
        $bookings = Booking::with(['user', 'client'])
            ->orderBy('start_time', 'desc')
            ->paginate(10);

        return $this->render($response, 'bookings/index', [
            'bookings' => $bookings,
        ]);
    }

    private function render(Response $response, string $template, array $data = [], int $status = 200): Response
    {
        extract($data);

        ob_start();
        require __DIR__ . '/../../templates/' . $template . '.php';
        $content = ob_get_clean();

        ob_start();
        require __DIR__ . '/../../templates/layout.php';
        $html = ob_get_clean();

        $response->getBody()->write($html);
        return $response->withStatus($status)->withHeader('Content-Type', 'text/html');
    }
}
