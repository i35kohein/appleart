<div id="view-calendar" class="view-section w-full" style="max-width: 1640px;">
    <!-- ============ HEADER ============ -->
    <div class="d-flex flex-wrap align-items-end justify-content-between gap-3 mb-4">
        <div>
            <div class="section-title-row">
                <span class="section-title-icon material-symbols-rounded">calendar_month</span>
                <h1 class="fw-bold m-0" style="font-size: 26px; letter-spacing: -0.8px;">Training Calendar</h1>
            </div>
            <p class="text-secondary mb-0 mt-1" style="font-size: 14px;">
                Big calendar — course + practical, taught daily per student, basic → advance. Auto-planned from curriculum &amp; progress.
            </p>
        </div>
        <div class="d-flex gap-2 flex-wrap" id="calendar-stats" style="font-size: 13px;">
            <span class="badge rounded-pill px-3 py-2" style="background: var(--brand-blue-light); color: var(--brand-blue); font-weight: 600;">Students: <span id="cal-stat-students">0</span></span>
            <span class="badge rounded-pill px-3 py-2" style="background: var(--brand-blue-light); color: var(--brand-blue); font-weight: 600;">Course: <span id="cal-stat-course">0</span></span>
            <span class="badge rounded-pill px-3 py-2" style="background: #dcfce7; color: #15803d; font-weight: 600;">Practical: <span id="cal-stat-practical">0</span></span>
            <span class="badge rounded-pill px-3 py-2" style="background: var(--brand-blue-light); color: var(--brand-blue); font-weight: 600;">Lessons <span id="cal-stat-lessons">0</span></span>
        </div>
    </div>

    <!-- ============ CONTROLS ============ -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
        <div class="d-flex align-items-center gap-2">
            <button class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1" onclick="calNav(-1)" title="Previous month" style="border-radius: 10px; padding: 7px 12px;">
                <span class="material-symbols-rounded" style="font-size: 18px;">chevron_left</span> Prev
            </button>
            <button class="btn btn-premium btn-sm d-flex align-items-center gap-1" onclick="calGoToday()" style="border-radius: 10px; padding: 7px 14px;">
                <span class="material-symbols-rounded" style="font-size: 18px;">today</span> Today
            </button>
            <button class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1" onclick="calNav(1)" title="Next month" style="border-radius: 10px; padding: 7px 12px;">
                Next <span class="material-symbols-rounded" style="font-size: 18px;">chevron_right</span>
            </button>
            <h2 id="cal-month-label" class="fw-bold m-0 ms-2" style="font-size: 18px; letter-spacing: -0.4px; min-width: 150px;">—</h2>
        </div>
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <div class="d-flex align-items-center gap-2" style="font-size: 12px;">
                <span class="d-inline-flex align-items-center gap-1"><span style="width: 10px; height: 10px; border-radius: 3px; background: var(--brand-blue); display: inline-block;"></span> Course (Theory)</span>
                <span class="d-inline-flex align-items-center gap-1"><span style="width: 10px; height: 10px; border-radius: 3px; background: var(--brand-green); display: inline-block;"></span> Practical</span>
                <span class="d-inline-flex align-items-center gap-1"><span style="width: 10px; height: 10px; border-radius: 3px; background: #f59e0b; display: inline-block;"></span> Scheduled</span>
            </div>
            <select id="cal-student-filter" class="form-select form-select-sm" onchange="calLoad()" style="border-radius: 10px; width: auto; min-width: 180px; font-size: 13px;">
                <option value="">All students</option>
            </select>
        </div>
    </div>

    <!-- ============ BIG MONTH GRID ============ -->
    <div style="overflow-x: auto; border-radius: 16px; border: 1px solid var(--separator); background: var(--bg-surface); box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);">
        <div id="cal-grid" style="min-width: 1150px;"></div>
    </div>

    <p class="text-secondary mt-3 mb-0" style="font-size: 12px;">
        💡 Click any lesson row to mark it done / not done — the student's next lessons automatically move forward (basic → advance). Click a student name to open their profile. Amber badges = lessons already in the schedule.
    </p>
</div>

<script>
(function () {
    const WEEKDAYS = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    const MONTHS = ['January','February','March','April','May','June','July','August','September','October','November','December'];
    let calMonth = null;   // Date object (1st of month)
    let calData = null;

    function calApi() {
        const y = calMonth.getFullYear(), m = String(calMonth.getMonth() + 1).padStart(2, '0');
        const sid = document.getElementById('cal-student-filter').value;
        return `api/get_calendar.php?month=${y}-${m}&student_id=${encodeURIComponent(sid)}`;
    }

    window.calNav = function (delta) {
        calMonth = new Date(calMonth.getFullYear(), calMonth.getMonth() + delta, 1);
        calLoad();
    };

    window.calGoToday = function () {
        const now = new Date();
        calMonth = new Date(now.getFullYear(), now.getMonth(), 1);
        calLoad();
    };

    window.calToggle = async function (studentId, itemId, btn) {
        const done = btn.dataset.done === '1';
        btn.disabled = true;
        try {
            const fd = new FormData();
            fd.append('student_id', studentId);
            fd.append('item_id', itemId);
            fd.append('status', done ? 'Pending' : 'Completed');
            fd.append('trainer_name', 'Instructor');
            const r = await fetch('api/update_progress.php', { method: 'POST', body: fd });
            const j = await r.json();
            if (j.status === 'success') calLoad();
            else alert(j.message || 'Failed to update');
        } catch (e) { alert('Error: ' + e.message); }
        btn.disabled = false;
    };

    function lessonChip(item, studentId, type) {
        if (!item) {
            return `<div class="d-flex align-items-center gap-1" style="opacity: 0.45; font-size: 11px; color: var(--text-secondary);">
                <span class="material-symbols-rounded" style="font-size: 13px;">${type === 'course' ? 'school' : 'build'}</span> ${type === 'course' ? 'Course complete' : 'Practical complete'} 🎓
            </div>`;
        }
        const color = type === 'course' ? 'var(--brand-blue)' : 'var(--brand-green)';
        const bg    = type === 'course' ? 'var(--brand-blue-light)' : '#dcfce7';
        const done = item.done;
        // Whole row is clickable (not just the tiny icon)
        return `<div role="button" tabindex="0" onclick="calToggle(${studentId}, ${item.id}, this)" onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();calToggle(${studentId}, ${item.id}, this);}"
                data-done="${done ? 1 : 0}" title="${done ? 'Click to mark as not done' : 'Click to mark as done'}"
                style="display: flex; align-items: flex-start; gap: 5px; margin-bottom: 3px; cursor: pointer; border-radius: 6px; padding: 2px; transition: background 0.15s;"
                onmouseover="this.style.background='var(--bg-surface-hover)'" onmouseout="this.style.background='transparent'">
            <span style="flex: 0 0 auto; color: ${done ? 'var(--brand-green)' : 'var(--text-secondary)'}; margin-top: 1px; line-height: 1;">
                <span class="material-symbols-rounded" style="font-size: 16px;">${done ? 'check_circle' : 'radio_button_unchecked'}</span>
            </span>
            <span style="min-width: 0;">
                <span class="d-inline-flex align-items-center" style="font-size: 9.5px; font-weight: 700; letter-spacing: 0.4px; text-transform: uppercase; color: ${color}; background: ${bg}; border-radius: 5px; padding: 1px 6px; margin-bottom: 2px;">
                    ${type === 'course' ? 'Course' : 'Practical'}${done ? ' ✓' : ''}
                </span>
                <span style="display: -webkit-box; font-size: 11px; line-height: 1.25; color: var(--text-primary); font-weight: 500; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;"
                     title="${item.title.replace(/"/g, '&quot;')}">${item.title}</span>
            </span>
        </div>`;
    }

    function scheduledChip(ts) {
        return `<div class="d-flex align-items-center gap-1 mt-1" style="background: #fef3c7; color: #92400e; border-radius: 5px; padding: 1px 6px; font-size: 10.5px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="${ts.topic}">
            <span class="material-symbols-rounded" style="font-size: 12px;">event_available</span>
            ${ts.start_time ? ts.start_time.substring(0, 5) : ''} ${ts.lesson_type}: ${ts.topic}
        </div>`;
    }

    window.calLoad = async function () {
        if (!calMonth) {
            const now = new Date();
            calMonth = new Date(now.getFullYear(), now.getMonth(), 1);
        }
        const grid = document.getElementById('cal-grid');
        grid.innerHTML = '<div class="p-5 text-center text-secondary" style="font-size: 14px;">Loading calendar…</div>';
        try {
            const r = await fetch(calApi());
            const d = await r.json();
            if (d.status !== 'success') throw new Error(d.message || 'API error');
            calData = d;
            renderCalendar();
        } catch (e) {
            grid.innerHTML = `<div class="p-5 text-center" style="color: var(--brand-red); font-size: 14px;">Failed to load calendar: ${e.message}</div>`;
        }
    };

    function renderCalendar() {
        const d = calData;
        document.getElementById('cal-month-label').textContent = MONTHS[d.month_num - 1] + ' ' + d.year;

        // Student filter options (preserve selection)
        const sel = document.getElementById('cal-student-filter');
        const curVal = sel.value;
        sel.innerHTML = '<option value="">All students</option>' + d.students.map(s =>
            `<option value="${s.id}" ${String(s.id) === curVal ? 'selected' : ''}>${s.name.replace(/</g, '&lt;')} (${s.rollcall_group})</option>`).join('');

        // Stats
        document.getElementById('cal-stat-students').textContent = d.students.length;
        document.getElementById('cal-stat-course').textContent = d.course_total;
        document.getElementById('cal-stat-practical').textContent = d.practical_total;
        let lessons = 0;
        Object.values(d.days).forEach(entries => entries.forEach(e => { if (e.course) lessons++; if (e.practical) lessons++; }));
        document.getElementById('cal-stat-lessons').textContent = lessons;

        const firstDow = new Date(d.year, d.month_num - 1, 1).getDay(); // 0=Sun
        const lead = (firstDow + 6) % 7; // days before first Monday
        const today = new Date();
        const todayStr = `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}`;

        let html = '<div class="cal-week-row" style="display: grid; grid-template-columns: repeat(7, 1fr); border-bottom: 1px solid var(--separator); background: var(--bg-surface-hover);">';
        for (let i = 0; i < 7; i++) {
            html += `<div style="padding: 10px 12px; font-size: 11px; font-weight: 700; letter-spacing: 0.6px; text-transform: uppercase; color: var(--text-secondary); text-align: center;">${WEEKDAYS[(i + 1) % 7]}</div>`;
        }
        html += '</div><div style="display: grid; grid-template-columns: repeat(7, 1fr);">';

        // Leading blanks from previous month
        const prev = new Date(d.year, d.month_num - 1, 0);
        for (let i = 0; i < lead; i++) {
            const pd = prev.getDate() - lead + i + 1;
            html += `<div style="min-height: 104px; background: var(--bg-base); opacity: 0.45; border-right: 1px solid var(--separator); border-bottom: 1px solid var(--separator); padding: 8px 10px; font-size: 12px; color: var(--text-secondary);">${pd}</div>`;
        }

        // Month days
        for (let day = 1; day <= d.days_in_month; day++) {
            const dateStr = `${d.year}-${String(d.month_num).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
            const entries = d.days[dateStr] || [];
            const isToday = dateStr === todayStr;
            const isWeekend = [0, 6].includes(new Date(d.year, d.month_num - 1, day).getDay());
            const anyTraining = entries.some(e => e.is_training);

            let cellBg = 'var(--bg-surface)';
            if (isToday) cellBg = 'rgba(37, 99, 235, 0.06)';
            else if (isWeekend && !anyTraining) cellBg = 'var(--bg-base)';

            html += `<div style="min-height: 104px; background: ${cellBg}; border-right: 1px solid var(--separator); border-bottom: 1px solid var(--separator); padding: 8px 10px; position: relative;">`;
            html += `<div class="d-flex align-items-center justify-content-between mb-1">
                <span class="d-flex align-items-center gap-1" style="font-size: 13px; font-weight: ${isToday ? 800 : 600}; color: ${isToday ? 'var(--brand-blue)' : 'var(--text-primary)'};">
                    ${isToday ? '<span class="material-symbols-rounded" style="font-size: 14px;">radio_button_checked</span>' : ''}${day}
                </span>
                ${anyTraining ? `<span style="font-size: 9.5px; font-weight: 700; color: var(--brand-blue); background: var(--brand-blue-light); border-radius: 20px; padding: 2px 8px; white-space: nowrap;">CLASS ${d.schedules[entries[0].group]?.start_time?.substring(0,5) || '10:00'}–${d.schedules[entries[0].group]?.end_time?.substring(0,5) || '15:00'}</span>` : ''}
            </div>`;

            entries.forEach(e => {
                const name = e.student_name.split(' ').slice(0, 2).join(' ');
                html += `<div style="border: 1px solid var(--separator); border-radius: 8px; padding: 4px 6px; margin-bottom: 4px; background: ${e.is_training ? 'var(--bg-surface)' : 'var(--bg-surface-hover)'};">
                    <div role="button" tabindex="0" onclick="switchViewMode('roster', ${e.student_id})" onkeydown="if(event.key==='Enter'){event.preventDefault();switchViewMode('roster', ${e.student_id});}" title="Open ${e.student_name.replace(/'/g, "\\'").replace(/"/g, '&quot;')}'s profile" style="font-size: 10.5px; font-weight: 700; color: var(--brand-blue); margin-bottom: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; cursor: pointer;">${name}</div>`;
                if (e.is_training) {
                    html += lessonChip(e.course, e.student_id, 'course');
                    html += lessonChip(e.practical, e.student_id, 'practical');
                } else {
                    html += '<div style="font-size: 11px; color: var(--text-secondary); opacity: 0.7;">— off day —</div>';
                }
                (e.scheduled || []).forEach(ts => html += scheduledChip(ts));
                html += '</div>';
            });
            if (entries.length === 0) html += '<div style="font-size: 11px; color: var(--text-secondary); opacity: 0.6;">No students</div>';
            html += '</div>';
        }

        // Trailing blanks
        const totalCells = lead + d.days_in_month;
        const trail = (7 - (totalCells % 7)) % 7;
        for (let i = 1; i <= trail; i++) {
            html += `<div style="min-height: 104px; background: var(--bg-base); opacity: 0.45; border-right: 1px solid var(--separator); border-bottom: 1px solid var(--separator); padding: 8px 10px; font-size: 12px; color: var(--text-secondary);">${i}</div>`;
        }
        html += '</div>';

        document.getElementById('cal-grid').innerHTML = html;
    }

    document.addEventListener('DOMContentLoaded', calLoad);
})();
</script>
