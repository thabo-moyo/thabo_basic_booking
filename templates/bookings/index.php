<h1>Bookings</h1>

<?php if ($bookings->isEmpty()): ?>
    <p>No bookings yet. <a href="/bookings/create">Create one</a>.</p>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th>Title</th>
                <th>User</th>
                <th>Client</th>
                <th>Start</th>
                <th>End</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($bookings as $booking): ?>
                <tr>
                    <td><?= htmlspecialchars($booking->title) ?></td>
                    <td><?= htmlspecialchars($booking->user->name) ?></td>
                    <td><?= htmlspecialchars($booking->client->name) ?></td>
                    <td><?= $booking->start_time->format('M j, Y H:i') ?></td>
                    <td><?= $booking->end_time->format('M j, Y H:i') ?></td>
                    <td class="actions">
                        <a href="/bookings/<?= $booking->id ?>/edit" class="btn btn-secondary" style="padding:4px 8px;font-size:12px;">Edit</a>
                        <form method="POST" action="/bookings/<?= $booking->id ?>/delete" onsubmit="return confirm('Delete this booking?')">
                            <button type="submit" class="btn btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="pagination">
        <?php if ($bookings->currentPage() > 1): ?>
            <a href="?page=<?= $bookings->currentPage() - 1 ?>">&laquo; Previous</a>
        <?php endif; ?>

        <span>Page <?= $bookings->currentPage() ?> of <?= $bookings->lastPage() ?></span>

        <?php if ($bookings->hasMorePages()): ?>
            <a href="?page=<?= $bookings->currentPage() + 1 ?>">Next &raquo;</a>
        <?php endif; ?>
    </div>
<?php endif; ?>

<hr style="margin: 30px 0;">

<h2>Weekly View</h2>
<div class="weekly-controls">
    <div>
        <label for="week-date">Pick a date:</label>
        <input type="date" id="week-date" value="<?= date('Y-m-d') ?>">
    </div>
    <button class="btn btn-primary" onclick="loadWeeklyBookings()">Load Week</button>
</div>
<div id="weekly-results"></div>
