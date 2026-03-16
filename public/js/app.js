document.addEventListener('DOMContentLoaded', function () {
    initFormValidation();
    initWeeklyView();
});

function initFormValidation() {
    var form = document.getElementById('booking-form');
    if (!form) return;

    form.addEventListener('submit', function (e) {
        var startInput = document.getElementById('start_time');
        var endInput = document.getElementById('end_time');

        if (!startInput || !endInput) return;

        var start = new Date(startInput.value);
        var end = new Date(endInput.value);

        if (isNaN(start.getTime()) || isNaN(end.getTime())) {
            e.preventDefault();
            alert('Please enter valid start and end times.');
            return;
        }

        if (start >= end) {
            e.preventDefault();
            alert('End time must be after start time.');
            endInput.focus();
        }
    });
}

function initWeeklyView() {
    var dateInput = document.getElementById('week-date');
    if (!dateInput) return;

    loadWeeklyBookings();
}

function loadWeeklyBookings() {
    var dateInput = document.getElementById('week-date');
    var container = document.getElementById('weekly-results');

    if (!dateInput || !container) return;

    var date = dateInput.value;
    if (!date) {
        container.innerHTML = '<p>Please select a date.</p>';
        return;
    }

    container.innerHTML = '<p>Loading...</p>';

    fetch('/api/bookings?week=' + encodeURIComponent(date))
        .then(function (res) { return res.json(); })
        .then(function (json) {
            if (json.error) {
                container.innerHTML = '<p class="error">' + escapeHtml(json.error) + '</p>';
                return;
            }

            var bookings = json.data || [];
            var meta = json.meta || {};

            if (bookings.length === 0) {
                container.innerHTML = '<p>No bookings for the week of ' +
                    escapeHtml(meta.week_start || date) + ' to ' +
                    escapeHtml(meta.week_end || '') + '.</p>';
                return;
            }

            var html = '<p><strong>Week:</strong> ' + escapeHtml(meta.week_start) +
                ' to ' + escapeHtml(meta.week_end) +
                ' (' + meta.total + ' booking' + (meta.total !== 1 ? 's' : '') + ')</p>';

            html += '<table><thead><tr>' +
                '<th>Title</th><th>User</th><th>Client</th><th>Start</th><th>End</th>' +
                '</tr></thead><tbody>';

            bookings.forEach(function (b) {
                html += '<tr>' +
                    '<td>' + escapeHtml(b.title) + '</td>' +
                    '<td>' + escapeHtml(b.user.name) + '</td>' +
                    '<td>' + escapeHtml(b.client.name) + '</td>' +
                    '<td>' + formatDateTime(b.start_time) + '</td>' +
                    '<td>' + formatDateTime(b.end_time) + '</td>' +
                    '</tr>';
            });

            html += '</tbody></table>';
            container.innerHTML = html;
        })
        .catch(function (err) {
            container.innerHTML = '<p class="error">Failed to load bookings: ' + escapeHtml(err.message) + '</p>';
        });
}

function formatDateTime(str) {
    var d = new Date(str);
    return d.toLocaleDateString('en-GB', {
        day: 'numeric', month: 'short', year: 'numeric'
    }) + ' ' + d.toLocaleTimeString('en-GB', {
        hour: '2-digit', minute: '2-digit'
    });
}

function escapeHtml(str) {
    if (!str) return '';
    var div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}
