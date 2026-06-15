<script>
// ============================================
// PRAYER TRACKING
// ============================================
const PRAYER_KEYS = ['fajr', 'dhuhr', 'asr', 'maghrib', 'isha'];
const PRAYER_LABELS = {
    fajr: 'Fajr',
    dhuhr: 'Dhuhr',
    asr: 'Asr',
    maghrib: 'Maghrib',
    isha: 'Isha'
};

let currentWeekStart = null; // YYYY-MM-DD (Monday)
let weekDays = []; // [{date, day_short, ...}]
let weekPrayers = {}; // { [date]: {fajr:0.., ...} }

function toISODate(d) {
    const pad = (n) => String(n).padStart(2, '0');
    return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;
}

function getMondayOfWeek(dateObj) {
    const d = new Date(dateObj.getFullYear(), dateObj.getMonth(), dateObj.getDate());
    const day = d.getDay(); // 0=Sun..6=Sat
    const diff = (day === 0 ? -6 : 1) - day;
    d.setDate(d.getDate() + diff);
    return d;
}

function addDays(isoDate, days) {
    const [y, m, d] = isoDate.split('-').map(Number);
    const dt = new Date(y, m - 1, d);
    dt.setDate(dt.getDate() + days);
    return toISODate(dt);
}

function loadPrayerWeek(weekStartIso) {
    const studentId = <?= (int) $activeStudentId ?>;
    currentWeekStart = weekStartIso;
    const label = document.getElementById('prayerWeekLabel');
    if (label) {
        label.textContent = '…';
    }
    fetch('<?= base_url("student/get-prayer-week") ?>?student_id=' + studentId + '&week_start=' + encodeURIComponent(weekStartIso), {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            weekDays = data.days || [];
            weekPrayers = {};
            weekDays.forEach(day => {
                weekPrayers[day.date] = day.prayers || {};
            });
            if (label) {
                const wn = (data.week_name || data.week_label || weekStartIso || '').toString().trim();
                label.textContent = wn || weekStartIso;
            }
            updatePrayerUI();
        }
    })
    .catch(err => console.error('Error loading prayer week:', err));
}

function updatePrayerUI() {
    const tbody = document.getElementById('prayerWeekBody');
    if (!tbody) return;

    // Build rows
    const dayOrder = weekDays.length ? weekDays.map(d => d.date) : [
        currentWeekStart,
        addDays(currentWeekStart, 1),
        addDays(currentWeekStart, 2),
        addDays(currentWeekStart, 3),
        addDays(currentWeekStart, 4),
        addDays(currentWeekStart, 5),
        addDays(currentWeekStart, 6),
    ];

    let totalOffered = 0;
    let html = '';

    PRAYER_KEYS.forEach(prayer => {
        html += `<tr>
            <th class="namaz-tracker-row-label" scope="row">${PRAYER_LABELS[prayer] || prayer}</th>`;
        dayOrder.forEach(date => {
            const value = (weekPrayers?.[date]?.[prayer] === 1) ? 1 : 0;
            if (value === 1) totalOffered++;
            const classes = value === 1 ? 'namaz-tracker-cell namaz-tracker-cell--done' : 'namaz-tracker-cell';
            html += `<td class="text-center ${classes}" data-date="${date}" data-prayer="${prayer}">
                <button type="button" class="btn namaz-tracker-tick ${value === 1 ? 'namaz-tracker-tick--on' : ''}" tabindex="-1">
                    ${value === 1 ? '<i class="fa fa-check" aria-hidden="true"></i>' : '<span class="namaz-tracker-tick__dash">—</span>'}
                </button>
            </td>`;
        });
        html += `</tr>`;
    });

    tbody.innerHTML = html;

    tbody.querySelectorAll('.namaz-tracker-cell').forEach(cell => {
        cell.addEventListener('click', function() {
            const date = this.dataset.date;
            const prayer = this.dataset.prayer;
            togglePrayer(date, prayer);
        });
    });

    const percentage = (totalOffered / 35) * 100;
    document.getElementById('prayerProgressBar').style.width = percentage + '%';
    document.getElementById('prayerCount').innerText = totalOffered;
}

function togglePrayer(dateIso, prayerKey) {
    const studentId = <?= (int) $activeStudentId ?>;
    const currentVal = (weekPrayers?.[dateIso]?.[prayerKey] === 1) ? 1 : 0;
    const newValue = currentVal === 1 ? 0 : 1;

    // optimistic UI
    weekPrayers[dateIso] = weekPrayers[dateIso] || {};
    weekPrayers[dateIso][prayerKey] = newValue;
    updatePrayerUI();

    fetch('<?= base_url("student/save-prayer") ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: new URLSearchParams({
            student_id: studentId,
            prayer_date: dateIso,
            prayer_name: prayerKey,
            value: newValue,
            csrf_test_name: document.querySelector('input[name="csrf_test_name"]')?.value || ''
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data && data.success) {
            // Keep local state; reload stats for streak counters
            loadPrayerStats();
            return;
        }
        // revert on failure
        weekPrayers[dateIso][prayerKey] = currentVal;
        updatePrayerUI();
    })
    .catch(err => {
        console.error('Error saving prayer:', err);
        weekPrayers[dateIso][prayerKey] = currentVal;
        updatePrayerUI();
    });
}

function loadPrayerStats() {
    const studentId = <?= (int) $activeStudentId ?>;
    fetch('<?= base_url("student/get-prayer-stats") ?>?student_id=' + studentId, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('weeklyStreak').innerText = data.weekly_streak || 0;
            document.getElementById('monthlyStreak').innerText = data.monthly_streak || 0;
            document.getElementById('totalDays').innerText = data.total_days || 0;
        }
    })
    .catch(err => console.error('Error loading prayer stats:', err));
}

function wireWeekNav() {
    const prevBtn = document.getElementById('prayerPrevWeek');
    const nextBtn = document.getElementById('prayerNextWeek');
    const thisBtn = document.getElementById('prayerThisWeek');

    if (prevBtn) {
        prevBtn.addEventListener('click', () => {
            loadPrayerWeek(addDays(currentWeekStart, -7));
        });
    }
    if (nextBtn) {
        nextBtn.addEventListener('click', () => {
            loadPrayerWeek(addDays(currentWeekStart, 7));
        });
    }
    if (thisBtn) {
        thisBtn.addEventListener('click', () => {
            const monday = getMondayOfWeek(new Date());
            loadPrayerWeek(toISODate(monday));
        });
    }
}

// Initialize
wireWeekNav();
const monday = getMondayOfWeek(new Date());
loadPrayerWeek(toISODate(monday));
loadPrayerStats();
</script>
