<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Booking;
use App\Models\User;
use App\Models\Client;
use App\Validators\BookingValidator;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class BookingController
{
    private BookingValidator $validator;

    public function __construct()
    {
        $this->validator = new BookingValidator();
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

    public function create(Request $request, Response $response): Response
    {
        return $this->render($response, 'bookings/create', [
            'users'   => User::orderBy('name')->get(),
            'clients' => Client::orderBy('name')->get(),
            'data'    => [],
            'errors'  => [],
        ]);
    }

    public function store(Request $request, Response $response): Response
    {
        $data = (array) $request->getParsedBody();
        $errors = $this->validator->validate($data);

        if (!empty($errors)) {
            return $this->render($response, 'bookings/create', [
                'users'   => User::orderBy('name')->get(),
                'clients' => Client::orderBy('name')->get(),
                'data'    => $data,
                'errors'  => $errors,
            ], 422);
        }

        Booking::create([
            'user_id'     => (int) $data['user_id'],
            'client_id'   => (int) $data['client_id'],
            'title'       => trim($data['title']),
            'description' => trim($data['description'] ?? ''),
            'start_time'  => $this->formatDateTime($data['start_time']),
            'end_time'    => $this->formatDateTime($data['end_time']),
        ]);

        return $response
            ->withHeader('Location', '/')
            ->withStatus(302);
    }

    public function edit(Request $request, Response $response, array $args): Response
    {
        $booking = Booking::find((int) $args['id']);

        if (!$booking) {
            return $response->withStatus(404);
        }

        return $this->render($response, 'bookings/edit', [
            'booking' => $booking,
            'users'   => User::orderBy('name')->get(),
            'clients' => Client::orderBy('name')->get(),
            'data'    => $booking->toArray(),
            'errors'  => [],
        ]);
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        $booking = Booking::find((int) $args['id']);

        if (!$booking) {
            return $response->withStatus(404);
        }

        $data = (array) $request->getParsedBody();
        $errors = $this->validator->validate($data, $booking->id);

        if (!empty($errors)) {
            return $this->render($response, 'bookings/edit', [
                'booking' => $booking,
                'users'   => User::orderBy('name')->get(),
                'clients' => Client::orderBy('name')->get(),
                'data'    => $data,
                'errors'  => $errors,
            ], 422);
        }

        $booking->update([
            'user_id'     => (int) $data['user_id'],
            'client_id'   => (int) $data['client_id'],
            'title'       => trim($data['title']),
            'description' => trim($data['description'] ?? ''),
            'start_time'  => $this->formatDateTime($data['start_time']),
            'end_time'    => $this->formatDateTime($data['end_time']),
        ]);

        return $response
            ->withHeader('Location', '/')
            ->withStatus(302);
    }

    public function destroy(Request $request, Response $response, array $args): Response
    {
        $booking = Booking::find((int) $args['id']);

        if (!$booking) {
            return $response->withStatus(404);
        }

        $booking->delete();

        return $response
            ->withHeader('Location', '/')
            ->withStatus(302);
    }

    private function formatDateTime(string $value): string
    {
        $dt = new \DateTime($value);
        return $dt->format('Y-m-d H:i:s');
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
