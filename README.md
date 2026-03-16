# Booking Platform

A simple booking platform where users can create and manage bookings associated with clients. Overlapping bookings per user are prevented.

## Setup

```bash
composer install
php database/setup.php
composer serve
```

Open **http://localhost:8080**.

## Tests

```bash
composer test
```

## API

### Get bookings for a calendar week

```
GET /api/bookings?week=2025-08-05
```

Accepts any date (YYYY-MM-DD) and returns all bookings for the Monday to Sunday week containing that date. Supports `page` and `per_page` query parameters.
