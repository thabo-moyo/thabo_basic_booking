<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Models\Booking;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class BookingController
{
    public function index(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        $weekParam = $params['week'] ?? null;

        $date = new \DateTimeImmutable($weekParam);
        $dayOfWeek = (int) $date->format('N');
        $monday = $date->modify('-' . ($dayOfWeek - 1) . ' days')->setTime(0, 0, 0);
        $sunday = $monday->modify('+6 days')->setTime(23, 59, 59);

        $page = max(1, (int) ($params['page'] ?? 1));
        $perPage = min(50, max(1, (int) ($params['per_page'] ?? 15)));

        $bookings = Booking::with(['user', 'client'])
            ->where('start_time', '<', $sunday->format('Y-m-d H:i:s'))
            ->where('end_time', '>', $monday->format('Y-m-d H:i:s'))
            ->orderBy('start_time')
            ->paginate($perPage, ['*'], 'page', $page);

        return $this->json($response, [
            'data' => $bookings->map(fn (Booking $b) => [
                'id'          => $b->id,
                'title'       => $b->title,
                'description' => $b->description,
                'start_time'  => $b->start_time->format('Y-m-d H:i:s'),
                'end_time'    => $b->end_time->format('Y-m-d H:i:s'),
                'user'        => [
                    'id'   => $b->user->id,
                    'name' => $b->user->name,
                ],
                'client' => [
                    'id'   => $b->client->id,
                    'name' => $b->client->name,
                ],
            ]),
            'meta' => [
                'week_start'   => $monday->format('Y-m-d'),
                'week_end'     => $sunday->format('Y-m-d'),
                'current_page' => $bookings->currentPage(),
                'per_page'     => $bookings->perPage(),
                'total'        => $bookings->total(),
                'last_page'    => $bookings->lastPage(),
            ],
        ]);
    }


    private function json(Response $response, array $data, int $status = 200): Response
    {
        $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        return $response
            ->withStatus($status)
            ->withHeader('Content-Type', 'application/json');
    }
}
