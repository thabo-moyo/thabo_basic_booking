<h1>Edit Booking</h1>

<?php if (!empty($errors)): ?>
    <div class="errors-summary">
        <strong>Please fix the following errors:</strong>
        <ul>
            <?php foreach ($errors as $error): ?>
                <li><?= htmlspecialchars($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<?php
    $startVal = $data['start_time'] ?? '';
    $endVal = $data['end_time'] ?? '';
    if ($startVal instanceof \DateTimeInterface) {
        $startVal = $startVal->format('Y-m-d\TH:i');
    } elseif (is_string($startVal) && strlen($startVal) > 10) {
        $startVal = date('Y-m-d\TH:i', strtotime($startVal));
    }
    if ($endVal instanceof \DateTimeInterface) {
        $endVal = $endVal->format('Y-m-d\TH:i');
    } elseif (is_string($endVal) && strlen($endVal) > 10) {
        $endVal = date('Y-m-d\TH:i', strtotime($endVal));
    }
?>

<form method="POST" action="/bookings/<?= $booking->id ?>" id="booking-form">
    <label for="title">Title *</label>
    <input type="text" id="title" name="title" value="<?= htmlspecialchars($data['title'] ?? '') ?>" required>

    <label for="description">Description</label>
    <textarea id="description" name="description"><?= htmlspecialchars($data['description'] ?? '') ?></textarea>

    <label for="user_id">User *</label>
    <select id="user_id" name="user_id" required>
        <option value="">-- Select User --</option>
        <?php foreach ($users as $user): ?>
            <option value="<?= $user->id ?>" <?= ($data['user_id'] ?? '') == $user->id ? 'selected' : '' ?>>
                <?= htmlspecialchars($user->name) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label for="client_id">Client *</label>
    <select id="client_id" name="client_id" required>
        <option value="">-- Select Client --</option>
        <?php foreach ($clients as $client): ?>
            <option value="<?= $client->id ?>" <?= ($data['client_id'] ?? '') == $client->id ? 'selected' : '' ?>>
                <?= htmlspecialchars($client->name) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label for="start_time">Start Time *</label>
    <input type="datetime-local" id="start_time" name="start_time" value="<?= htmlspecialchars($startVal) ?>" required>

    <label for="end_time">End Time *</label>
    <input type="datetime-local" id="end_time" name="end_time" value="<?= htmlspecialchars($endVal) ?>" required>

    <div style="margin-top: 16px;">
        <button type="submit" class="btn btn-primary">Update Booking</button>
        <a href="/" class="btn btn-secondary">Cancel</a>
    </div>
</form>
