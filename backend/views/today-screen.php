<div id="view-today" class="view-section w-full" style="max-width: 1640px;">
    <!-- ============ HEADER ============ -->
    <div class="d-flex flex-wrap align-items-end justify-content-between gap-3 mb-3">
        <div>
            <div class="section-title-row">
                <span class="section-title-icon material-symbols-rounded">slideshow</span>
                <h1 class="fw-bold m-0" style="font-size: 26px; letter-spacing: -0.8px;">Today's Screen</h1>
            </div>
            <p class="text-secondary mb-0 mt-1" style="font-size: 14px;" id="today-date-label">Loading…</p>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <button class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1" onclick="todayPrev()" title="Previous trainee" style="border-radius: 10px; padding: 7px 12px;">
                <span class="material-symbols-rounded" style="font-size: 18px;">chevron_left</span>
            </button>
            <button class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1" onclick="todayNext()" title="Next trainee" style="border-radius: 10px; padding: 7px 12px;">
                <span class="material-symbols-rounded" style="font-size: 18px;">chevron_right</span>
            </button>
            <button id="today-play-btn" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1" onclick="todayTogglePlay()" title="Play / Pause auto rotation" style="border-radius: 10px; padding: 7px 12px;">
                <span class="material-symbols-rounded" id="today-play-icon" style="font-size: 18px;">pause</span>
            </button>
            <select id="today-interval" class="form-select form-select-sm" onchange="todayRestart()" style="border-radius: 10px; width: auto; font-size: 13px;">
                <option value="5000">5 sec</option>
                <option value="8000" selected>8 sec</option>
                <option value="12000">12 sec</option>
                <option value="20000">20 sec</option>
            </select>
            <button class="btn btn-premium d-flex align-items-center gap-2" onclick="todayFullscreen()" style="border-radius: 10px;">
                <span class="material-symbols-rounded" style="font-size: 18px;">fullscreen</span> Fullscreen
            </button>
        </div>
    </div>

    <!-- ============ ACTIVE TRAINEE SELECTOR ============ -->
    <div class="d-flex flex-wrap align-items-center gap-2 mb-3 p-3" style="border: 1px solid var(--separator); border-radius: 14px; background: var(--bg-surface);">
        <span class="d-flex align-items-center gap-1 me-1" style="font-size: 13px; font-weight: 700; color: var(--text-primary);">
            <span class="material-symbols-rounded" style="font-size: 18px; color: var(--brand-blue);">groups</span> Show trainees:
        </span>
        <div id="today-student-checkboxes" class="d-flex flex-wrap gap-2" style="font-size: 13px;"></div>
        <div class="ms-auto d-flex gap-2">
            <button class="btn btn-sm btn-outline-secondary" onclick="todaySelectAll(true)" style="border-radius: 8px; font-size: 12px;">All</button>
            <button class="btn btn-sm btn-outline-secondary" onclick="todaySelectAll(false)" style="border-radius: 8px; font-size: 12px;">None</button>
        </div>
    </div>

    <!-- ============ BIG STAGE ============ -->
    <div id="today-stage" class="today-stage" style="position: relative; border-radius: 20px; overflow: hidden; background: linear-gradient(135deg, #0f172a 0%, #1e293b 60%, #172554 100%); border: 1px solid var(--separator);">
        <div id="today-card"></div>
        <div id="today-dots" style="position: absolute; bottom: 16px; left: 0; right: 0; display: flex; justify-content: center; gap: 10px; z-index: 5;"></div>
    </div>

    <p class="text-secondary mt-3 mb-0" style="font-size: 12px;">
        💡 ဒီနေ့ ဘာသင်ရမယ်ဆိုတာကို TV / စခရင်ကြီးမှာ ပြဖို့အတွက် — trainee တွေကို ရွေး၊ Fullscreen နှိပ်၊ auto-rotate နဲ့ တစ်ယောက်ချင်းစီ အလှည့်ကျ ပြပေးပါမယ်။
    </p>
</div>

<style>
    .today-stage:fullscreen {
        border-radius: 0;
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .today-stage:fullscreen #today-card {
        width: 100%;
        height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .today-stage:fullscreen .today-card-inner {
        width: 100%;
        max-width: 1600px;
    }
    .today-dot {
        width: 12px; height: 12px; border-radius: 50%;
        background: rgba(148, 163, 184, 0.35);
        border: none; padding: 0; cursor: pointer; transition: all 0.2s;
    }
    .today-dot.active { background: #3b82f6; transform: scale(1.25); }
    @keyframes todayBarGrow { from { width: 0%; } to { width: 100%; } }
    .today-bar { height: 6px; background: #3b82f6; border-radius: 0 0 20px 20px; animation-name: todayBarGrow; animation-timing-function: linear; animation-fill-mode: forwards; }
</style>

<script>
(function () {
    let todayData = null;
    let rotationIds = [];      // ordered student ids in rotation
    let currentIdx = 0;
    let timer = null;
    let playing = true;

    function api() { return 'api/get_today.php'; }

    function selIds() {
        return [...document.querySelectorAll('#today-student-checkboxes input:checked')].map(c => Number(c.value));
    }

    function renderCheckboxes() {
        const box = document.getElementById('today-student-checkboxes');
        box.innerHTML = todayData.students.map(s => `
            <label style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; border: 1px solid var(--separator); border-radius: 20px; background: var(--bg-surface-hover); cursor: pointer; user-select: none;" title="${s.phone || ''}">
                <input type="checkbox" value="${s.student_id}" checked onchange="todayRebuild()" style="accent-color: var(--brand-blue); width: 15px; height: 15px;">
                <span style="font-weight: 600;">${s.student_name.replace(/</g, '&lt;')}</span>
                <span style="color: var(--text-secondary); font-size: 11px;">${s.group === 'Weekend' ? 'Sat–Sun' : 'Tue–Fri'}</span>
            </label>`).join('');
    }

    window.todayRebuild = function () {
        const ids = selIds();
        const prevId = rotationIds.length ? rotationIds[currentIdx] : null;
        rotationIds = ids;
        if (!rotationIds.length) { currentIdx = -1; renderCard(); return; }
        let idx = prevId ? rotationIds.indexOf(prevId) : -1;
        currentIdx = idx >= 0 ? idx : 0;
        renderCard();
        todayRestart();
    };

    window.todayNext = function () { if (!rotationIds.length) return; currentIdx = (currentIdx + 1) % rotationIds.length; renderCard(); todayRestart(); };
    window.todayPrev = function () { if (!rotationIds.length) return; currentIdx = (currentIdx - 1 + rotationIds.length) % rotationIds.length; renderCard(); todayRestart(); };

    window.todayTogglePlay = function () {
        playing = !playing;
        document.getElementById('today-play-icon').textContent = playing ? 'pause' : 'play_arrow';
        if (playing) todayRestart(); else { clearInterval(timer); timer = null; clearBar(); }
    };

    window.todayRestart = function () {
        if (timer) clearInterval(timer);
        clearBar();
        if (!playing || rotationIds.length < 2) { if (rotationIds.length < 2 && timer) timer = null; return; }
        const iv = Number(document.getElementById('today-interval').value || 8000);
        timer = setInterval(() => { currentIdx = (currentIdx + 1) % rotationIds.length; renderCard(); }, iv);
        startBar(iv);
    };

    window.todaySelectAll = function (on) {
        document.querySelectorAll('#today-student-checkboxes input').forEach(c => c.checked = on);
        todayRebuild();
    };

    window.todayFullscreen = function () {
        const stage = document.getElementById('today-stage');
        if (document.fullscreenElement) document.exitFullscreen();
        else if (stage.requestFullscreen) stage.requestFullscreen();
        else if (stage.webkitRequestFullscreen) stage.webkitRequestFullscreen();
    };

    function barEl() {
        let b = document.getElementById('today-bar');
        if (!b) {
            b = document.createElement('div');
            b.id = 'today-bar';
            b.className = 'today-bar';
            document.getElementById('today-stage').appendChild(b);
        }
        return b;
    }
    function clearBar() { const b = document.getElementById('today-bar'); if (b) b.style.animation = 'none'; }
    function startBar(ms) {
        const b = barEl();
        b.style.animation = 'none';
        void b.offsetWidth; // restart animation
        b.style.animation = `todayBarGrow ${ms}ms linear forwards`;
    }

    function progressBar(label, done, total, color) {
        const pct = total ? Math.round(done / total * 100) : 0;
        return `<div style="margin-top: 10px;">
            <div style="display: flex; justify-content: space-between; font-size: 12px; color: #94a3b8; margin-bottom: 4px;">
                <span style="font-weight: 600;">${label}</span><span style="font-weight: 700; color: #f8fafc;">${done}/${total} · ${pct}%</span>
            </div>
            <div style="height: 10px; border-radius: 8px; background: rgba(148,163,184,0.18); overflow: hidden;">
                <div style="height: 100%; width: ${pct}%; border-radius: 8px; background: ${color}; transition: width 0.4s;"></div>
            </div>
        </div>`;
    }

    function lessonCard(type, item) {
        const isCourse = type === 'course';
        const accent = isCourse ? '#60a5fa' : '#4ade80';
        const icon = isCourse ? 'school' : 'build';
        const label = isCourse ? 'Course / Theory' : 'Practical';
        if (!item) {
            return `<div style="background: rgba(30,41,59,0.8); border: 1px solid #334155; border-radius: 16px; padding: 28px; display: flex; flex-direction: column; justify-content: center; min-height: 150px;">
                <div style="color: ${accent}; font-size: 13px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; display: flex; align-items: center; gap: 8px;"><span class="material-symbols-rounded" style="font-size: 20px;">${icon}</span> ${label}</div>
                <div style="font-size: 30px; font-weight: 800; margin-top: 14px; color: #f8fafc;">🎓 All done!</div>
                <div style="color: #94a3b8; margin-top: 4px; font-size: 15px;">${isCourse ? 'Course' : 'Practical'} အပိုင်းပြီးပါပြီ</div>
            </div>`;
        }
        return `<div style="background: rgba(30,41,59,0.8); border: 1px solid #334155; border-radius: 16px; padding: 28px; display: flex; flex-direction: column; justify-content: center; min-height: 150px;">
            <div style="color: ${accent}; font-size: 13px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; display: flex; align-items: center; gap: 8px;"><span class="material-symbols-rounded" style="font-size: 20px;">${icon}</span> ${label}</div>
            <div style="font-size: clamp(18px, 2.6vw, 28px); font-weight: 800; margin-top: 12px; line-height: 1.2; color: #f8fafc;">${item.title}</div>
            <div style="color: #94a3b8; margin-top: 6px; font-size: 14px;">${item.category || ''}</div>
        </div>`;
    }

    function renderCard() {
        const card = document.getElementById('today-card');
        const dots = document.getElementById('today-dots');
        document.getElementById('today-date-label').textContent = todayData
            ? `Today — ${todayData.weekday}, ${todayData.date}`
            : 'Loading…';

        // Dots
        dots.innerHTML = rotationIds.map((id, i) =>
            `<button class="today-dot ${i === currentIdx ? 'active' : ''}" onclick="todayJump(${i})" title="Show trainee #${i + 1}"></button>`).join('');

        if (!rotationIds.length) {
            card.innerHTML = `<div style="min-height: 400px; display: flex; flex-direction: column; align-items: center; justify-content: center; color: #94a3b8; font-size: 22px; font-weight: 600; text-align: center; padding: 40px;">
                <span class="material-symbols-rounded" style="font-size: 64px; color: #3b82f6;">slideshow</span>
                <div style="margin-top: 16px;">Active သင်တန်းသား အနည်းဆုံး တစ်ယောက် ရွေးပါ</div>
                <div style="font-size: 14px; margin-top: 6px;">Select at least one trainee to show</div>
            </div>`;
            return;
        }

        const s = todayData.students.find(x => x.student_id === rotationIds[currentIdx]);
        if (!s) { card.innerHTML = '<div style="min-height: 400px;"></div>'; return; }

        const schHtml = (s.scheduled || []).map(t => `
            <div style="margin-top: 10px; background: rgba(245,158,11,0.15); border: 1px solid rgba(245,158,11,0.4); color: #fbbf24; border-radius: 10px; padding: 8px 14px; font-size: 13px; font-weight: 600;">
                📅 ${t.start_time ? t.start_time.substring(0,5) : ''} ${t.lesson_type}: ${t.topic}
            </div>`).join('');

        let body;
        if (!s.is_training) {
            body = `<div style="min-height: 400px; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; color: #f8fafc; padding: 40px;">
                <span class="material-symbols-rounded" style="font-size: 72px; color: #64748b;">event_busy</span>
                <div style="font-size: 40px; font-weight: 800; margin-top: 12px;">Rest Day</div>
                <div style="color: #94a3b8; font-size: 18px; margin-top: 8px;">${s.student_name} — class day မဟုတ်ပါ (${s.group === 'Weekend' ? 'Sat–Sun' : 'Tue–Fri'})</div>
            </div>`;
        } else {
            body = `<div class="today-card-inner" style="padding: 42px 48px; color: #f8fafc; min-height: 400px; display: flex; flex-direction: column;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 24px; flex-wrap: wrap;">
                    <div>
                        <div style="font-size: 13px; letter-spacing: 1.5px; text-transform: uppercase; color: #94a3b8;">Today • ${todayData.weekday}, ${todayData.date}</div>
                        <div style="font-size: clamp(28px, 3.8vw, 44px); font-weight: 800; letter-spacing: -1px; line-height: 1.1; margin-top: 6px;">${s.student_name}</div>
                        <div style="color: #94a3b8; font-size: 16px; margin-top: 4px;">${s.phone || ''} • ${s.group === 'Weekend' ? 'Weekend Student (Sat–Sun)' : 'Weekday Student (Tue–Fri)'}</div>
                    </div>
                    <div style="text-align: right; color: #94a3b8; font-size: 15px; line-height: 1.7;">
                        <div>⏰ Class ${todayData.schedules[s.group]?.start_time?.substring(0,5) || '10:00'} – ${todayData.schedules[s.group]?.end_time?.substring(0,5) || '15:00'}</div>
                        <div>📖 Course ${s.course_done}/${s.course_total} &nbsp;•&nbsp; 🔧 Practical ${s.practical_done}/${s.practical_total}</div>
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 28px;">
                    ${lessonCard('course', s.course)}
                    ${lessonCard('practical', s.practical)}
                </div>
                ${schHtml}
                <div style="margin-top: auto; padding-top: 20px;">
                    ${progressBar('Course progress', s.course_done, s.course_total, '#3b82f6')}
                    ${progressBar('Practical progress', s.practical_done, s.practical_total, '#22c55e')}
                </div>
            </div>`;
        }
        card.innerHTML = body;
    }

    window.todayJump = function (i) { if (rotationIds[i] !== undefined) { currentIdx = i; renderCard(); todayRestart(); } };

    async function load() {
        try {
            const r = await fetch(api());
            const d = await r.json();
            if (d.status !== 'success') throw new Error(d.message || 'API error');
            todayData = d;
            renderCheckboxes();
            todayRebuild();
        } catch (e) {
            document.getElementById('today-card').innerHTML =
                `<div style="min-height: 400px; display: flex; align-items: center; justify-content: center; color: #f87171; font-size: 18px;">Failed to load: ${e.message}</div>`;
        }
    }

    document.addEventListener('DOMContentLoaded', load);
})();
</script>
