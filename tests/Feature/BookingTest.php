<?php

declare(strict_types=1);

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;
use Illuminate\Pagination\Paginator;
use Slim\Factory\AppFactory;
use Slim\Psr7\Factory\ServerRequestFactory;
use App\Controllers\BookingController;
use App\Controllers\Api\BookingController as ApiBookingController;
use App\Models\Booking;
use App\Models\User;
use App\Models\Client;

class BookingTest extends TestCase
{
    private static bool $dbInitialised = false;
    private \Slim\App $app;

    protected function setUp(): void
    {
        parent::setUp();

        if (!self::$dbInitialised) {
            $capsule = new Capsule();
            $capsule->addConnection([
                'driver'   => 'sqlite',
                'database' => ':memory:',
            ]);
            $capsule->setEventDispatcher(new Dispatcher(new Container()));
            $capsule->setAsGlobal();
            $capsule->bootEloquent();

            Paginator::currentPageResolver(fn () => 1);

            self::$dbInitialised = true;
        }

        $this->resetDatabase();

        $this->app = AppFactory::create();
        $this->app->addBodyParsingMiddleware();

        $this->app->get('/', [BookingController::class, 'index']);
        $this->app->post('/bookings', [BookingController::class, 'store']);
        $this->app->get('/api/bookings', [ApiBookingController::class, 'index']);
    }

    private function resetDatabase(): void
    {
        $schema = file_get_contents(__DIR__ . '/../../database/schema.sql');

        Capsule::connection()->statement('DROP TABLE IF EXISTS bookings');
        Capsule::connection()->statement('DROP TABLE IF EXISTS clients');
        Capsule::connection()->statement('DROP TABLE IF EXISTS users');

        foreach (explode(';', $schema) as $statement) {
            $statement = trim($statement);
            if (!empty($statement)) {
                Capsule::connection()->statement($statement);
            }
        }

        User::create(['name' => 'Alice Johnson', 'email' => 'alice@example.com']);
        User::create(['name' => 'Bob Smith', 'email' => 'bob@example.com']);
        Client::create(['name' => 'Acme Corp', 'email' => 'contact@acme.com']);
    }

    private function createRequest(
        string $method,
        string $uri,
        array $body = [],
        array $queryParams = []
    ): \Psr\Http\Message\ServerRequestInterface {
        $request = (new ServerRequestFactory())->createServerRequest($method, $uri);

        if (!empty($queryParams)) {
            $request = $request->withQueryParams($queryParams);
        }

        if (!empty($body)) {
            $request = $request->withParsedBody($body);
            $request = $request->withHeader('Content-Type', 'application/x-www-form-urlencoded');
        }

        return $request;
    }

    public function test_booking_can_be_created_successfully(): void
    {
        $request = $this->createRequest('POST', '/bookings', [
            'title'       => 'Strategy Meeting',
            'description' => 'Quarterly review',
            'user_id'     => 1,
            'client_id'   => 1,
            'start_time'  => '2025-08-05T10:00',
            'end_time'    => '2025-08-05T11:00',
        ]);

        $response = $this->app->handle($request);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('/', $response->getHeaderLine('Location'));

        $booking = Booking::first();
        $this->assertNotNull($booking);
        $this->assertEquals('Strategy Meeting', $booking->title);
        $this->assertEquals('Quarterly review', $booking->description);
        $this->assertEquals(1, $booking->user_id);
        $this->assertEquals(1, $booking->client_id);
        $this->assertEquals('2025-08-05 10:00:00', $booking->start_time->format('Y-m-d H:i:s'));
        $this->assertEquals('2025-08-05 11:00:00', $booking->end_time->format('Y-m-d H:i:s'));
    }

    public function test_overlapping_booking_is_rejected(): void
    {
        Booking::create([
            'user_id'    => 1,
            'client_id'  => 1,
            'title'      => 'Existing Meeting',
            'start_time' => '2025-08-05 10:00:00',
            'end_time'   => '2025-08-05 11:00:00',
        ]);

        $request = $this->createRequest('POST', '/bookings', [
            'title'      => 'Overlapping Meeting',
            'user_id'    => 1,
            'client_id'  => 1,
            'start_time' => '2025-08-05T10:30',
            'end_time'   => '2025-08-05T11:30',
        ]);

        $response = $this->app->handle($request);

        $this->assertEquals(422, $response->getStatusCode());
        $this->assertEquals(1, Booking::count());
    }

    public function test_adjacent_bookings_are_allowed(): void
    {
        Booking::create([
            'user_id'    => 1,
            'client_id'  => 1,
            'title'      => 'First Meeting',
            'start_time' => '2025-08-05 10:00:00',
            'end_time'   => '2025-08-05 11:00:00',
        ]);

        $request = $this->createRequest('POST', '/bookings', [
            'title'      => 'Back-to-back Meeting',
            'user_id'    => 1,
            'client_id'  => 1,
            'start_time' => '2025-08-05T11:00',
            'end_time'   => '2025-08-05T12:00',
        ]);

        $response = $this->app->handle($request);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals(2, Booking::count());
    }

    public function test_different_users_can_have_overlapping_bookings(): void
    {
        Booking::create([
            'user_id'    => 1,
            'client_id'  => 1,
            'title'      => 'Alice Meeting',
            'start_time' => '2025-08-05 10:00:00',
            'end_time'   => '2025-08-05 11:00:00',
        ]);

        $request = $this->createRequest('POST', '/bookings', [
            'title'      => 'Bob Meeting',
            'user_id'    => 2,
            'client_id'  => 1,
            'start_time' => '2025-08-05T10:00',
            'end_time'   => '2025-08-05T11:00',
        ]);

        $response = $this->app->handle($request);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals(2, Booking::count());
    }

    public function test_weekly_api_returns_bookings_for_correct_week(): void
    {
        Booking::create([
            'user_id'    => 1,
            'client_id'  => 1,
            'title'      => 'Week 1 Booking',
            'start_time' => '2025-08-05 09:00:00',
            'end_time'   => '2025-08-05 10:00:00',
        ]);

        Booking::create([
            'user_id'    => 1,
            'client_id'  => 1,
            'title'      => 'Week 2 Booking',
            'start_time' => '2025-08-12 09:00:00',
            'end_time'   => '2025-08-12 10:00:00',
        ]);

        $request = $this->createRequest('GET', '/api/bookings', [], ['week' => '2025-08-05']);
        $response = $this->app->handle($request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/json', $response->getHeaderLine('Content-Type'));

        $json = json_decode((string) $response->getBody(), true);

        $this->assertCount(1, $json['data']);
        $this->assertEquals('Week 1 Booking', $json['data'][0]['title']);
        $this->assertEquals('2025-08-04', $json['meta']['week_start']);
        $this->assertEquals('2025-08-10', $json['meta']['week_end']);
        $this->assertEquals(1, $json['meta']['total']);
    }

    public function test_weekly_api_requires_valid_week_parameter(): void
    {
        $request = $this->createRequest('GET', '/api/bookings');
        $response = $this->app->handle($request);

        $this->assertEquals(400, $response->getStatusCode());

        $json = json_decode((string) $response->getBody(), true);
        $this->assertArrayHasKey('error', $json);
    }

    public function test_weekly_api_returns_empty_for_week_with_no_bookings(): void
    {
        $request = $this->createRequest('GET', '/api/bookings', [], ['week' => '2025-01-06']);
        $response = $this->app->handle($request);

        $this->assertEquals(200, $response->getStatusCode());

        $json = json_decode((string) $response->getBody(), true);
        $this->assertCount(0, $json['data']);
        $this->assertEquals(0, $json['meta']['total']);
    }
}
