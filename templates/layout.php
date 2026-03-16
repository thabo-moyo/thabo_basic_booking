<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Platform</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; line-height: 1.6; max-width: 960px; margin: 0 auto; padding: 20px; color: #333; }
        nav { padding: 10px 0; margin-bottom: 20px; border-bottom: 1px solid #ddd; }
        nav a { margin-right: 15px; text-decoration: none; color: #0066cc; }
        nav a:hover { text-decoration: underline; }
        h1 { margin-bottom: 15px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { padding: 8px 12px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #f5f5f5; font-weight: 600; }
        form { max-width: 500px; }
        label { display: block; margin-top: 12px; font-weight: 500; }
        input, select, textarea { width: 100%; padding: 8px; margin-top: 4px; border: 1px solid #ccc; border-radius: 4px; font-size: 14px; }
        textarea { resize: vertical; min-height: 60px; }
        button, .btn { display: inline-block; padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; text-decoration: none; margin-top: 12px; margin-right: 8px; }
        .btn-primary { background: #0066cc; color: #fff; }
        .btn-danger { background: #cc3333; color: #fff; }
        .btn-secondary { background: #666; color: #fff; }
        .error { color: #cc3333; font-size: 13px; margin-top: 2px; }
        .errors-summary { background: #fee; border: 1px solid #fcc; padding: 10px; border-radius: 4px; margin-bottom: 15px; }
        .pagination { margin-top: 15px; }
        .pagination a, .pagination span { padding: 4px 10px; margin-right: 4px; border: 1px solid #ddd; border-radius: 3px; text-decoration: none; color: #0066cc; }
        .pagination span { background: #0066cc; color: #fff; }
        .actions a, .actions form { display: inline; }
        .actions button { margin: 0; padding: 4px 8px; font-size: 12px; }
        #weekly-results { margin-top: 15px; }
        .weekly-controls { display: flex; gap: 10px; align-items: end; margin-bottom: 10px; }
        .weekly-controls input { width: auto; }
    </style>
</head>
<body>
    <nav>
        <a href="/">Bookings</a>
        <a href="/bookings/create">New Booking</a>
    </nav>
    <?= $content ?>
    <script src="/js/app.js"></script>
</body>
</html>
