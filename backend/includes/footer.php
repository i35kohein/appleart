<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script>
    // --- GLOBAL VARIABLES & SMART CACHE ---
    let allStudents = [];
    let paymentRows = [];
    let selectedPaymentStudentId = null;
    let adminCurriculumData = [];
    let allTrainers = [];
    let activeStudentId = null; 
    let currentProfileTab = 'Detail';
    let currentStudentListView = 'list';
    let selectedReportStudentIds = [];
    let currentReportTab = 'Theory';
    let currentCoursePage = 'Course';
    let currentModuleMarkItem = null;
    let currentModuleMarkedStudentIds = [];
    let modalProfileStudentId = null;
    let currentModalProfileTab = 'Theory';
    let rollCallSelectedStudentId = null;
    let adminContactSelectedStudentId = null;
    let appSettings = {
        rollcall_schedule: { days: [1, 2, 3, 4, 5], start_time: '10:00', end_time: '15:00' },
        rollcall_schedules: {
            Weekday: { days: [1, 2, 3, 4, 5], start_time: '10:00', end_time: '15:00' },
            Weekend: { days: [6, 0], start_time: '10:00', end_time: '15:00' }
        }
    };
    
    // Privacy Mode
    let isPrivacyMode = localStorage.getItem('appleart_privacy_mode') === 'true';
    // Theme System
    const currentTheme = localStorage.getItem('appleart_system_theme') || 'light';
    
    function changeSystemTheme(theme) {
        localStorage.setItem('appleart_system_theme', theme);
        document.documentElement.setAttribute('data-theme', theme);
    }

    // UI MODE SYSTEM: 'premium' (decorated) <-> 'basic' (completely plain)
    function toggleBasicUIMode() {
        const next = document.documentElement.getAttribute('data-ui') === 'basic' ? 'premium' : 'basic';
        setUIMode(next);
    }

    function setUIMode(mode) {
        localStorage.setItem('appleart_ui_mode', mode);
        document.documentElement.setAttribute('data-ui', mode);
        syncUIModeButton();
    }

    function syncUIModeButton() {
        const label = document.getElementById('basic-ui-label');
        const btn = document.getElementById('basic-ui-toggle');
        if (!label || !btn) return;
        const isBasic = document.documentElement.getAttribute('data-ui') === 'basic';
        label.textContent = isBasic ? 'Premium' : 'Basic';
        btn.style.background = isBasic ? '#111111' : 'transparent';
        btn.style.color = isBasic ? '#ffffff' : '';
        btn.style.borderColor = isBasic ? '#111111' : '';
    }


    document.addEventListener('DOMContentLoaded', () => {
        const toggle = document.getElementById('privacy-mode-toggle');
        if(toggle) toggle.checked = isPrivacyMode;
        
        const themeSelect = document.getElementById('system-theme-select');
        if (themeSelect) themeSelect.value = currentTheme;
        
        syncUIModeButton();
    });

    function togglePrivacyMode(enabled) {
        isPrivacyMode = enabled;
        localStorage.setItem('appleart_privacy_mode', enabled);
        // Refresh all views to apply masking immediately
        renderMasterRoster(getActiveStudents());
        renderStudentPageList();
        renderStudentDashboardList();
        loadAdminContacts();
        if (activeStudentId) openStudentFullProfile(activeStudentId);
        if (modalProfileStudentId) openStudentProfileModal(modalProfileStudentId);
    }

    function formatPrivacy(str, fallback = '-') {
        if (!str || String(str).trim() === '') return fallback;
        if (isPrivacyMode) return '***';
        return escapeHtml(str);
    }
    
    // SMART CACHE: Only updates when you make a change, making clicks instant!
    let cacheVersion = Date.now(); 
    
    // Edit Mode Tracking
    let isCurriculumEditMode = false;
    let sortableInstances = [];

    function escapeHtml(value) {
        return String(value ?? '').replace(/[&<>"']/g, char => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#39;'
        }[char]));
    }

    function getStudentInitial(student) {
        return (student && student.name ? student.name.charAt(0).toUpperCase() : '?');
    }

    function isStudentActive(student) {
        return !student || student.is_active === undefined || String(student.is_active) === '1';
    }

    function getActiveStudents() {
        return allStudents.filter(isStudentActive);
    }

    function getRollCallGroup(student) {
        return student && student.rollcall_group === 'Weekend' ? 'Weekend' : 'Weekday';
    }

    function getRollCallGroupLabel(student) {
        return `${getRollCallGroup(student)} Student`;
    }

    function getStudentPhotoUrl(student) {
        if (!student || !student.photo_path) return '';
        return String(student.photo_path).replace(/\\/g, '/').replace(/^\/+/, '');
    }

    function renderStudentAvatar(student, className = 'student-avatar', style = '') {
        const photoUrl = getStudentPhotoUrl(student);
        const hasPhoto = !!photoUrl;
        
        let bgStyle = '';
        if (hasPhoto) {
            bgStyle = `background-image:url('${escapeHtml(photoUrl)}')`;
        }

        let finalStyle = style;
        if (bgStyle) {
            finalStyle = style ? `${style}; ${bgStyle}` : bgStyle;
        }

        const safeStyleAttr = finalStyle ? ` style="${finalStyle}"` : '';
        const photoClass = hasPhoto ? ' has-photo' : ' placeholder-photo';
        
        return `<div class="${className}${photoClass}"${safeStyleAttr}></div>`;
    }

    function getTrainerPhotoUrl(trainer) {
        if (!trainer || !trainer.photo_path) return '';
        return String(trainer.photo_path).replace(/\\/g, '/').replace(/^\/+/, '');
    }

    function renderTrainerAvatar(trainer, className = 'student-avatar', style = '') {
        const photoUrl = getTrainerPhotoUrl(trainer);
        const hasPhoto = !!photoUrl;

        let bgStyle = '';
        if (hasPhoto) {
            bgStyle = `background-image:url('${escapeHtml(photoUrl)}')`;
        }

        let finalStyle = style;
        if (bgStyle) {
            finalStyle = style ? `${style}; ${bgStyle}` : bgStyle;
        }

        const safeStyleAttr = finalStyle ? ` style="${finalStyle}"` : '';
        const photoClass = hasPhoto ? ' has-photo' : ' placeholder-photo';
        
        return `<div class="${className}${photoClass}"${safeStyleAttr}></div>`;
    }

    function showConfirmAction({ title = 'Confirm Action', message = 'Continue?', okText = 'Delete', icon = 'warning', danger = true } = {}) {
        return new Promise(resolve => {
            const titleEl = document.getElementById('confirm-action-title');
            const messageEl = document.getElementById('confirm-action-message');
            const okBtn = document.getElementById('confirm-action-ok');
            const cancelBtn = document.getElementById('confirm-action-cancel');
            const iconEl = document.getElementById('confirm-action-icon');
            if(!confirmActionModal || !titleEl || !messageEl || !okBtn || !cancelBtn) {
                resolve(window.confirm(message));
                return;
            }
            titleEl.innerText = title;
            messageEl.innerText = message;
            okBtn.innerText = okText;
            okBtn.className = danger ? 'btn btn-outline-danger px-4 py-2' : 'btn btn-premium px-4 py-2';
            if(iconEl) iconEl.innerText = icon;
            okBtn.onclick = () => { confirmActionModal.hide(); resolve(true); };
            cancelBtn.onclick = () => { confirmActionModal.hide(); resolve(false); };
            confirmActionModal.show();
        });
    }

    function renderMarkedStudentChip(student, fallback = {}) {
        const record = student || fallback;
        return `
            <span class="marked-student-chip" onclick="openStudentProfileFromCourse(${Number(record.id)})">
                ${renderStudentAvatar(record, 'marked-student-avatar', 'width:24px; height:24px; font-size:11px;')}
                <span>${escapeHtml(record.name || 'Student')}</span>
            </span>`;
    }

    function setStudentAvatarElement(element, student) {
        if (!element) return;
        const photoUrl = getStudentPhotoUrl(student);
        const hasPhoto = !!photoUrl;
        element.classList.toggle('has-photo', hasPhoto);
        element.classList.toggle('placeholder-photo', !hasPhoto);
        element.style.backgroundImage = hasPhoto ? `url('${photoUrl}')` : '';
        element.innerText = '';
    }

    function setUploadPreview(prefix, studentOrPath = null) {
        const preview = document.getElementById(`${prefix}-photo-preview`);
        if (!preview) return;
        const path = typeof studentOrPath === 'string' ? studentOrPath : getStudentPhotoUrl(studentOrPath);
        const hasPhoto = !!path;
        preview.classList.toggle('has-photo', hasPhoto);
        preview.classList.toggle('placeholder-photo', !hasPhoto);
        preview.style.backgroundImage = hasPhoto ? `url('${path}')` : '';
        preview.innerText = '';
    }

    function showUploadMessage(message, type = 'info') {
        let notice = document.getElementById('profile-upload-notice');
        if(!notice) {
            notice = document.createElement('div');
            notice.id = 'profile-upload-notice';
            notice.style.cssText = 'position:fixed;right:24px;bottom:24px;z-index:3000;max-width:320px;padding:12px 14px;border-radius:8px;font-size:13px;font-weight:700;box-shadow:var(--shadow-md);';
            document.body.appendChild(notice);
        }
        const isError = type === 'error';
        notice.style.background = isError ? 'rgba(239, 68, 68, 0.95)' : 'rgba(22, 163, 74, 0.95)';
        notice.style.color = '#fff';
        notice.innerText = message;
        clearTimeout(notice._timer);
        notice._timer = setTimeout(() => notice.remove(), 3200);
    }

    function formatStudentJoinDate(student) {
        if (!student || !student.created_at || student.created_at === '0000-00-00 00:00:00') return 'Recent';
        const safeDateString = student.created_at.replace(/-/g, '/');
        const date = new Date(safeDateString);
        return isNaN(date.getTime()) ? 'Recent' : date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
    }

    function buildStudentHeaderBadges(student, showProgress = false) {
        const theoryPct = student.total_course > 0 ? Math.round((student.course_completed / student.total_course) * 100) : 0;
        const practicalPct = student.total_practical > 0 ? Math.round((student.practical_completed / student.total_practical) * 100) : 0;
        const infoRows = [
            { icon: 'call', label: 'Phone', value: formatPrivacy(student.phone, '-') },
            { icon: 'mail', label: 'Email', value: formatPrivacy(student.email, '-') },
            { icon: 'storefront', label: 'Shop', value: formatPrivacy(student.shop_name, '-') },
            { icon: 'location_on', label: 'Address', value: formatPrivacy(student.address, '-') },
            { icon: 'calendar_month', label: 'Joined', value: formatStudentJoinDate(student) }
        ];
        const progressHtml = showProgress ? `
            <div class="student-profile-progress-strip">
                <div class="student-profile-progress-card">
                    <span class="material-symbols-rounded">menu_book</span>
                    <div>
                        <small>Theory Progress</small>
                        <strong>${student.course_completed}/${student.total_course}</strong>
                        <div class="student-profile-progress-track"><div class="student-profile-progress-fill" style="width:${theoryPct}%;"></div></div>
                    </div>
                </div>
                <div class="student-profile-progress-card">
                    <span class="material-symbols-rounded">construction</span>
                    <div>
                        <small>Practical Progress</small>
                        <strong>${student.practical_completed}/${student.total_practical}</strong>
                        <div class="student-profile-progress-track"><div class="student-profile-progress-fill practical" style="width:${practicalPct}%;"></div></div>
                    </div>
                </div>
            </div>
        ` : '';
        return `
            <div class="student-profile-info-grid">
                ${infoRows.map(row => `
                    <div class="student-profile-info-cell" title="${escapeHtml(row.value)}">
                        <span class="material-symbols-rounded">${row.icon}</span>
                        <div>
                            <small>${row.label}</small>
                            <strong>${escapeHtml(row.value)}</strong>
                        </div>
                    </div>
                `).join('')}
            </div>
            ${progressHtml}
        `;
    }
    
    // Global Modal Variables
    let addStudentModal, signOffModal, curriculumModal, editTraineeModal, trainerModal, moduleMarkModal, realWorldRepairModal, studentProfileModal, revertModal, confirmActionModal;
    
    // --- LIVE CLOCK ---
    function updateLiveClock() {
        const clockEl = document.getElementById('live-clock');
        if (!clockEl) return;
        clockEl.innerText = new Date().toLocaleString('en-US', { weekday: 'short', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
    }
    setInterval(updateLiveClock, 1000); 
    updateLiveClock();

    // --- MATH ---
    function updateRosterOverview() {
        const activeStudents = getActiveStudents();
        if(activeStudents.length === 0) {
            const tStat = document.getElementById('avg-theory-stat'); const pStat = document.getElementById('avg-prac-stat');
            if(tStat) tStat.innerText = '0%';
            if(pStat) pStat.innerText = '0%';
            return;
        }
        let totalTheory = 0; let totalPrac = 0;
        activeStudents.forEach(s => {
            totalTheory += s.total_course > 0 ? (s.course_completed / s.total_course) * 100 : 0;
            totalPrac += s.total_practical > 0 ? (s.practical_completed / s.total_practical) * 100 : 0;
        });
        const tStat = document.getElementById('avg-theory-stat'); const pStat = document.getElementById('avg-prac-stat');
        if(tStat) tStat.innerText = Math.round(totalTheory / activeStudents.length) + '%';
        if(pStat) pStat.innerText = Math.round(totalPrac / activeStudents.length) + '%';
    }

    // --- VIEW ROUTER ---
    function switchViewMode(target, studentId = null) {
        document.querySelectorAll('.view-section').forEach(el => el.classList.remove('active'));
        document.querySelectorAll('.sidebar .nav-icon').forEach(el => el.classList.remove('active'));
        
        let viewId = '';
        if (target === 'roster') viewId = 'view-profile';
        if (target === 'rollcall') viewId = 'view-rollcall';
        if (target === 'report') viewId = 'view-report';
        if (target === 'contacts') viewId = 'view-contacts';
        if (target === 'calendar') viewId = 'view-calendar';
        if (target === 'today') viewId = 'view-today';
        if (target === 'admin') viewId = 'view-admin';
        
        const viewEl = document.getElementById(viewId);
        if(viewEl) viewEl.classList.add('active');
        
        const navEl = document.getElementById(`rail-${target}`);
        if(navEl) navEl.classList.add('active');

        const topbarControls = document.getElementById('topbar-view-controls');
        if (topbarControls) {
            topbarControls.style.display = target === 'roster' ? 'block' : 'none';
        }

        if (target === 'roster') {
            document.getElementById('roster-overview').style.display = 'block';
            updateRosterOverview();
            switchStudentListView(currentStudentListView, { keepSelection: true });
            renderStudentPageList();
            renderStudentDashboardList();
            if (studentId) {
                currentStudentListView = 'list';
                switchStudentListView('list', { keepSelection: true });
                selectStudent(studentId);
            } else {
                const activeStudents = getActiveStudents();
                const current = activeStudents.find(s => String(s.id) === String(activeStudentId));
                if(current && currentStudentListView === 'list') selectStudent(current.id);
                else if(activeStudents.length > 0 && currentStudentListView === 'list') selectStudent(activeStudents[0].id);
                else {
                    if(currentStudentListView === 'list') activeStudentId = null;
                    const placeholder = document.getElementById('profile-placeholder');
                    if(placeholder) placeholder.style.display = 'flex';
                    document.getElementById('profile-content').style.display = 'none';
                    document.querySelectorAll('.student-card, .student-page-card').forEach(c => c.classList.remove('active'));
                }
            }
        } 
        else if (target === 'rollcall') {
            document.getElementById('view-rollcall').classList.add('active'); document.getElementById('rail-rollcall').classList.add('active');
            activeStudentId = null; loadLiveRollCallView();
        }
        else if (target === 'report') {
            document.getElementById('view-report').classList.add('active'); document.getElementById('rail-report').classList.add('active');
            activeStudentId = null; loadTrainingPagesView();
        }
        else if (target === 'exam') {
            document.getElementById('view-exam').classList.add('active'); document.getElementById('rail-exam').classList.add('active');
            activeStudentId = null; loadGlobalExamStudents();
        }
        else if (target === 'contacts') {
            document.getElementById('view-contacts').classList.add('active'); document.getElementById('rail-contacts').classList.add('active');
            activeStudentId = null; loadAdminContacts();
        }
        else if (target === 'admin') {
            document.getElementById('view-admin').classList.add('active'); document.getElementById('rail-admin').classList.add('active');
            activeStudentId = null; loadAdminTrainers();
        }
    }

    function switchProfileTab(tabName) {
        currentProfileTab = tabName;
        document.querySelectorAll('.profile-tabs .segment-btn').forEach(b => b.classList.remove('active'));
        const activeBtn = document.getElementById(`tab-${tabName.toLowerCase()}`);
        if(activeBtn) activeBtn.classList.add('active');

        const container = document.getElementById('profile-pane-content');
        if(!container) return;
        container.innerHTML = '<div class="text-center py-5"><div class="spinner-border spinner-border-sm text-secondary"></div></div>';

        if(tabName === 'Detail') loadStudentProfileDetail();
        else if(tabName === 'Overview') loadStudentProfileOverview();
        else if(tabName === 'Theory') loadTickLists('Course');
        else if(tabName === 'Practical') loadTickLists('Practical');
        else if(tabName === 'Realworld') loadRealWorldExperience();
        else if(tabName === 'Exam') loadStudentExam();
        else if(tabName === 'History') loadHistoryLogs();
        else if(tabName === 'Attendance') loadAttendanceLogs();
    }

    function switchAdminTab(tabName) {
        ['Trainers', 'Trainees', 'Operations', 'Curriculum', 'Payments', 'Users'].forEach(t => {
            const btn = document.getElementById(`tab-manage-${t.toLowerCase()}`);
            const sec = document.getElementById(`admin-${t.toLowerCase()}`);
            if(btn) btn.classList.remove('active');
            if(sec) sec.style.display = 'none';
        });
        
        const actBtn = document.getElementById(`tab-manage-${tabName.toLowerCase()}`);
        const actSec = document.getElementById(`admin-${tabName.toLowerCase()}`);
        if(actBtn) actBtn.classList.add('active');
        if(actSec) actSec.style.display = 'block';

        if(tabName === 'Curriculum') loadAdminCurriculum();
        if(tabName === 'Trainees') loadAdminTrainees();
        if(tabName === 'Operations') renderRollCallSettings();
        if(tabName === 'Trainers') loadAdminTrainers();
        if(tabName === 'Payments') loadPaymentsView();
        if(tabName === 'Users') loadSystemUsers();
    }

    function loadTrainingPagesView() {
        switchCoursePage(currentCoursePage);
    }

    function switchCoursePage(pageName) {
        currentCoursePage = pageName;
        document.querySelectorAll('.course-page-tabs .segment-btn').forEach(b => {
            b.classList.remove('active');
            b.setAttribute('aria-selected', 'false');
        });
        const activeBtn = document.getElementById(`course-page-tab-${pageName.toLowerCase()}`);
        if(activeBtn) {
            activeBtn.classList.add('active');
            activeBtn.setAttribute('aria-selected', 'true');
        }
        if(pageName === 'RealWork') loadRealWorldRepairPage();
        else loadCoursePageModules();
    }

    function getCoursePageConfig() {
        if(currentCoursePage === 'Practical') {
            return { title: 'Practical', type: 'Practical', realWorkOnly: false, empty: 'No practical modules found.' };
        }
        return { title: 'Theory', type: 'Course', realWorkOnly: false, empty: 'No course modules found.' };
    }

    function getCoursePageActionIcon() {
        if(currentCoursePage === 'Practical') return 'construction';
        if(currentCoursePage === 'RealWork') return 'rate_review';
        return 'task_alt';
    }

    async function loadCoursePageModules() {
        const container = document.getElementById('course-page-content');
        if(!container) return;
        container.innerHTML = '<div class="text-center py-5"><div class="spinner-border spinner-border-sm text-secondary"></div></div>';

        try {
            const config = getCoursePageConfig();
            const res = await fetch(`api/get_curriculum.php?v=${cacheVersion}`);
            const result = await res.json();
            if(result.status !== 'success') {
                container.innerHTML = '<div class="text-center text-secondary py-5">Unable to load modules.</div>';
                return;
            }

            let modules = result.data.filter(item => item.type === config.type);
            if(config.type === 'Practical') {
                modules = modules.filter(item => !`${item.category} ${item.title}`.toLowerCase().includes('live repair'));
            }

            if(modules.length === 0) {
                container.innerHTML = `<div class="text-center text-secondary py-5">${config.empty}</div>`;
                return;
            }

            const progressEntries = await Promise.all(modules.map(async item => {
                const markedRes = await fetch(`api/get_item_progress.php?item_id=${item.id}&v=${cacheVersion}`);
                const markedResult = await markedRes.json();
                return [item.id, markedResult.status === 'success' ? markedResult.data : []];
            }));
            const markedMap = Object.fromEntries(progressEntries);

            const grouped = new Map();
            modules.forEach(item => {
                if(!grouped.has(item.category)) grouped.set(item.category, []);
                grouped.get(item.category).push(item);
            });

            const activeStudentCount = getActiveStudents().length;
            let html = '';
            grouped.forEach((items, category) => {
                const categoryMarked = items.reduce((sum, item) => {
                    const rows = markedMap[item.id] || [];
                    return sum + rows.filter(s => {
                        const student = allStudents.find(x => String(x.id) === String(s.id));
                        return isStudentActive(student);
                    }).length;
                }, 0);
                html += `
                    <section class="course-category-block" aria-label="${escapeHtml(category)} modules">
                        <div class="course-category-header">
                            <div>
                                <span class="course-category-kicker">${escapeHtml(config.title)} Section</span>
                                <h3>${escapeHtml(category)}</h3>
                            </div>
                            <div class="course-category-meta">${items.length} modules • ${categoryMarked} marks</div>
                        </div>`;
                items.forEach(item => {
                    const markedStudents = (markedMap[item.id] || [])
                        .filter(s => {
                            const student = allStudents.find(x => String(x.id) === String(s.id));
                            return isStudentActive(student);
                        });
                    const completionPct = activeStudentCount > 0 ? Math.round((markedStudents.length / activeStudentCount) * 100) : 0;
                    const statusText = markedStudents.length === 0 ? 'Not started' : (completionPct >= 100 ? 'Complete' : 'In progress');
                    const statusClass = markedStudents.length === 0 ? 'empty' : (completionPct >= 100 ? 'done' : 'progress');
                    const studentChips = markedStudents.length > 0
                        ? markedStudents.map(s => {
                            const student = allStudents.find(x => String(x.id) === String(s.id));
                            return renderMarkedStudentChip(student, s);
                        }).join('')
                        : '<span class="course-module-empty-note">No trainees marked yet</span>';
                    html += `
                        <div class="course-module-card" id="course-module-${item.id}">
                            <div class="course-module-head">
                                <div class="course-module-copy">
                                    <div class="course-module-kicker">
                                        <span>${escapeHtml(config.title)}</span>
                                        <span class="course-module-status ${statusClass}">${statusText}</span>
                                    </div>
                                    <div class="course-module-title">${escapeHtml(item.title)}</div>
                                    <div class="course-module-meta">${markedStudents.length}/${activeStudentCount} active trainees marked • ${completionPct}% completion</div>
                                </div>
                                <button class="icon-action-btn course-module-mark-btn" title="Mark students for ${escapeHtml(item.title)}" aria-label="Mark students for ${escapeHtml(item.title)}" onclick="openModuleMarkModal(${item.id})">
                                    <span class="material-symbols-rounded">${getCoursePageActionIcon()}</span>
                                    <span>Mark</span>
                                </button>
                            </div>
                            <div class="course-module-progress" aria-hidden="true"><span style="width:${completionPct}%;"></span></div>
                            <div class="marked-student-grid">${studentChips}</div>
                        </div>
                    `;
                });
                html += '</section>';
            });

            container.innerHTML = html;
        } catch(e) {
            console.error('Course page load error:', e);
            container.innerHTML = '<div class="text-center text-secondary py-5">Unable to load modules.</div>';
        }
    }

    let globalRepairLogs = [];
    let repairCalendarDate = new Date();
    let repairSelectedDate = null;

    function changeRepairCalendarMonth(delta) {
        repairCalendarDate.setMonth(repairCalendarDate.getMonth() + delta);
        renderRealWorldRepairPage();
    }

    function filterRepairByDate(dateStr) {
        repairSelectedDate = dateStr;
        renderRealWorldRepairPage();
    }

    async function loadRealWorldRepairPage() {
        const container = document.getElementById('course-page-content');
        if(!container) return;
        container.innerHTML = '<div class="text-center py-5"><div class="spinner-border spinner-border-sm text-secondary"></div></div>';

        try {
            const res = await fetch(`api/get_real_world_repairs.php?v=${cacheVersion}`);
            const result = await res.json();
            globalRepairLogs = result.status === 'success' ? result.data : [];
            
            renderRealWorldRepairPage();

        } catch(e) {
            console.error('Live repair session load error:', e);
            container.innerHTML = '<div class="text-center text-secondary py-5">Unable to load live repair session comments.</div>';
        }
    }

    function renderRealWorldRepairPage() {
        const container = document.getElementById('course-page-content');
        if(!container) return;

        const rows = globalRepairLogs.filter(row => {
            const student = allStudents.find(s => String(s.id) === String(row.student_id));
            return isStudentActive(student);
        });

        if(rows.length === 0) {
            container.innerHTML = '<div class="text-center text-secondary py-5">No live repair session comments yet. Use + Live Repair Sessions to add one.</div>';
            return;
        }

        const groupedRepairs = new Map();
        rows.forEach(row => {
            if (repairSelectedDate) {
                const rowDate = new Date(row.created_at);
                const filterDate = new Date(repairSelectedDate);
                if (rowDate.getFullYear() !== filterDate.getFullYear() || 
                    rowDate.getMonth() !== filterDate.getMonth() || 
                    rowDate.getDate() !== filterDate.getDate()) {
                    return;
                }
            }

            const key = [
                row.repair_title || '',
                row.comment || '',
                row.trainer_name || '',
                row.created_at || ''
            ].join('||');
            if(!groupedRepairs.has(key)) {
                groupedRepairs.set(key, { ...row, students: [] });
            }
            const group = groupedRepairs.get(key);
            if(!group.students.some(student => String(student.id) === String(row.student_id))) {
                const studentRecord = allStudents.find(student => String(student.id) === String(row.student_id));
                if(!isStudentActive(studentRecord)) return;
                group.students.push({
                    id: row.student_id,
                    name: row.student_name,
                    phone: row.phone,
                    photo_path: studentRecord ? studentRecord.photo_path : ''
                });
            }
        });

        const repairCards = Array.from(groupedRepairs.values()).map(group => {
            const studentChips = group.students.length > 0
                ? group.students.map(student => renderMarkedStudentChip(student)).join('')
                : '<span class="text-secondary" style="font-size: 13px;">No students selected.</span>';
            return `
                <div class="timeline-record">
                    <div class="timeline-dot"></div>
                    <div class="fw-bold" style="font-size:15px;">${escapeHtml(group.repair_title)}</div>
                    <div class="text-secondary mb-2" style="font-size:12px;">${new Date(group.created_at).toLocaleString([], { dateStyle: 'short', timeStyle: 'short' })}  •  ${escapeHtml(group.trainer_name || 'Instructor')}  •  ${group.students.length} affected student${group.students.length === 1 ? '' : 's'}</div>
                    <div class="student-profile-panel" style="padding:12px; box-shadow:none;">
                        ${escapeHtml(group.comment)}
                        <div class="mt-3 pt-3" style="border-top: 1px solid var(--separator);">
                            <div class="text-secondary mb-2" style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 600;">Affected Students</div>
                            <div class="marked-student-grid">${studentChips}</div>
                        </div>
                    </div>
                </div>
            `;
        }).join('');

        const calendarHtml = generateRepairCalendarHtml();

        container.innerHTML = `
            <section class="course-page-overview" aria-label="Live repair sessions overview">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <div class="curriculum-category m-0">Live Practice</div>
                        <h3>Live Repair Session Comments</h3>
                    </div>
                </div>
                
                <div class="course-page-metrics" aria-label="Live repair session metrics">
                    <div><strong>${groupedRepairs.size}</strong><span>Comments</span></div>
                    <div><strong>${rows.length}</strong><span>Entries</span></div>
                    <div><strong>${getActiveStudents().length}</strong><span>Active</span></div>
                </div>
            </section>
            
            <div class="grid grid-cols-1 xl:grid-cols-[minmax(0,1fr)_340px] gap-4">
                <div>
                    <div class="course-category-header">
                        <div>
                            <span class="course-category-kicker">Repair Comment Log</span>
                            <h3>Field Experience</h3>
                        </div>
                        <div class="course-category-meta">${groupedRepairs.size} records</div>
                    </div>
                    <div class="timeline-container mt-2">
                        ${groupedRepairs.size > 0 ? repairCards : '<div class="text-secondary mt-3">No records found for the selected date.</div>'}
                    </div>
                </div>
                <aside>
                    <div class="student-profile-panel sticky top-24" style="padding: 16px;">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <button class="btn btn-icon text-secondary p-0" onclick="changeRepairCalendarMonth(-1)"><span class="material-symbols-rounded">chevron_left</span></button>
                            <div class="fw-bold" style="font-size: 15px;">${repairCalendarDate.toLocaleDateString([], { month: 'long', year: 'numeric' })}</div>
                            <button class="btn btn-icon text-secondary p-0" onclick="changeRepairCalendarMonth(1)"><span class="material-symbols-rounded">chevron_right</span></button>
                        </div>
                        <div class="rollcall-calendar-weekdays mb-2" style="font-size: 12px; color: var(--text-secondary);">
                            <span>S</span><span>M</span><span>T</span><span>W</span><span>T</span><span>F</span><span>S</span>
                        </div>
                        <div class="rollcall-calendar-grid gap-1">
                            ${calendarHtml}
                        </div>
                        ${repairSelectedDate ? `<div class="mt-3 text-center"><button class="btn btn-secondary btn-sm rounded-pill px-3" onclick="filterRepairByDate(null)">Clear Filter</button></div>` : ''}
                    </div>
                </aside>
            </div>
        `;
    }

    function generateRepairCalendarHtml() {
        const year = repairCalendarDate.getFullYear();
        const month = repairCalendarDate.getMonth();
        const firstDay = new Date(year, month, 1).getDay();
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        
        let html = '';
        for(let i = 0; i < firstDay; i++) {
            html += '<div class="rollcall-calendar-day muted" style="min-height: 36px; cursor: default;"></div>';
        }
        
        const now = new Date();
        const rows = globalRepairLogs;
        
        const datesWithRecords = new Set();
        rows.forEach(r => {
            const d = new Date(r.created_at);
            if(d.getFullYear() === year && d.getMonth() === month) {
                datesWithRecords.add(d.getDate());
            }
        });

        for(let day = 1; day <= daysInMonth; day++) {
            const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
            const isToday = day === now.getDate() && month === now.getMonth() && year === now.getFullYear();
            const hasRecord = datesWithRecords.has(day);
            const isSelected = repairSelectedDate === dateStr;
            
            let classes = 'rollcall-calendar-day';
            let style = 'min-height: 36px; cursor: pointer; transition: all 0.2s;';
            
            if (isSelected) {
                style += ' background: var(--system-blue); color: white; border-radius: 8px; font-weight: 600; box-shadow: 0 4px 12px rgba(10,132,255,0.3);';
            } else if (isToday) {
                style += ' border: 1px solid var(--system-blue); color: var(--system-blue); border-radius: 8px; font-weight: 600;';
            } else if (hasRecord) {
                style += ' background: rgba(10,132,255,0.1); color: var(--system-blue); border-radius: 8px; font-weight: 600;';
            } else {
                style += ' border-radius: 8px;';
            }

            html += `
                <div class="${classes}" style="${style}" onclick="filterRepairByDate('${dateStr}')" 
                     onmouseover="if(!${isSelected}) this.style.background='var(--hover-bg)'"
                     onmouseout="if(!${isSelected}) this.style.background='${hasRecord ? 'rgba(10,132,255,0.1)' : 'transparent'}'">
                    <span>${day}</span>
                    ${hasRecord && !isSelected ? '<div style="width: 4px; height: 4px; background: var(--system-blue); border-radius: 50%; margin-top: 2px;"></div>' : ''}
                </div>`;
        }
        return html;
    }

    async function openModuleMarkModal(itemId) {
        try {
            const curriculumRes = await fetch(`api/get_curriculum.php?v=${cacheVersion}`);
            const curriculumResult = await curriculumRes.json();
            if(curriculumResult.status !== 'success') return;

            const item = curriculumResult.data.find(i => String(i.id) === String(itemId));
            if(!item) return;

            currentModuleMarkItem = item;
            document.getElementById('module-mark-item-id').value = item.id;
            document.getElementById('module-mark-type').innerText = currentCoursePage === 'RealWork' ? 'Live Repair Sessions' : item.type;
            document.getElementById('module-mark-title').innerText = item.title;
            document.getElementById('module-mark-note').value = '';
            document.getElementById('module-mark-note').required = currentCoursePage === 'RealWork';

            const markedRes = await fetch(`api/get_item_progress.php?item_id=${item.id}&v=${cacheVersion}`);
            const markedResult = await markedRes.json();
            currentModuleMarkedStudentIds = markedResult.status === 'success' ? markedResult.data.map(s => String(s.id)) : [];
            renderModuleMarkStudents();

            moduleMarkModal.show();
        } catch(e) {
            console.error('Open mark modal error:', e);
        }
    }

    function renderModuleMarkStudents() {
        const container = document.getElementById('module-mark-student-list');
        if(!container) return;
        const activeStudents = getActiveStudents();
        if(activeStudents.length === 0) {
            container.innerHTML = '<div class="text-secondary py-4 text-center">No students found.</div>';
            return;
        }

        container.innerHTML = activeStudents.map(student => {
            const checked = currentModuleMarkedStudentIds.includes(String(student.id));
            return `
                <label class="module-student-option ${checked ? 'selected' : ''}" for="module-student-${student.id}">
                    <input type="checkbox" id="module-student-${student.id}" value="${student.id}" ${checked ? 'checked' : ''} onchange="syncModuleStudentOption(this)">
                    ${renderStudentAvatar(student, 'report-photo', 'width:36px; height:36px; font-size:14px;')}
                    <div style="min-width:0;">
                        <div class="report-name">${escapeHtml(student.name)}</div>
                        <div class="report-meta">${formatPrivacy(student.phone || student.address, 'Trainee')}</div>
                    </div>
                </label>
            `;
        }).join('');
    }

    function syncModuleStudentOption(input) {
        const option = input.closest('.module-student-option');
        if(option) option.classList.toggle('selected', input.checked);
    }

    function toggleModuleMarkAllStudents() {
        const boxes = document.querySelectorAll('#module-mark-student-list input[type="checkbox"]');
        const shouldSelectAll = Array.from(boxes).some(box => !box.checked);
        boxes.forEach(box => {
            box.checked = shouldSelectAll;
            syncModuleStudentOption(box);
        });
    }

    async function submitModuleMark(event) {
        event.preventDefault();
        if(!currentModuleMarkItem) return;

        const selectedIds = Array.from(document.querySelectorAll('#module-mark-student-list input[type="checkbox"]:checked'))
            .map(box => box.value)
            .filter(id => !currentModuleMarkedStudentIds.includes(String(id)));
        const trainerEl = document.getElementById('module-mark-trainer');
        const noteEl = document.getElementById('module-mark-note');
        const trainer = trainerEl ? trainerEl.value : 'Instructor';
        const note = noteEl ? noteEl.value : '';

        try {
            await Promise.all(selectedIds.map(studentId => {
                const fd = new FormData();
                fd.append('student_id', studentId);
                fd.append('item_id', currentModuleMarkItem.id);
                fd.append('status', 'Completed');
                fd.append('comment', note);
                fd.append('trainer_name', trainer);
                return fetch('api/update_progress.php', { method: 'POST', body: fd });
            }));

            cacheVersion = Date.now();
            moduleMarkModal.hide();
            await loadStudents();
            await loadCoursePageModules();
        } catch(e) {
            console.error('Module mark save error:', e);
        }
    }

    function openRealWorkRepairModal() {
        document.getElementById('real-repair-title').value = '';
        document.getElementById('real-repair-comment').value = '';
        renderRealRepairStudents();
        realWorldRepairModal.show();
    }

    function renderRealRepairStudents() {
        const container = document.getElementById('real-repair-student-list');
        if(!container) return;
        const activeStudents = getActiveStudents();
        container.innerHTML = activeStudents.map(student => `
            <label class="module-student-option" for="real-repair-student-${student.id}">
                <input type="checkbox" id="real-repair-student-${student.id}" value="${student.id}" onchange="syncModuleStudentOption(this)">
                ${renderStudentAvatar(student, 'report-photo', 'width:36px; height:36px; font-size:14px;')}
                <div style="min-width:0;">
                    <div class="report-name">${escapeHtml(student.name)}</div>
                    <div class="report-meta">${formatPrivacy(student.phone || student.address, 'Trainee')}</div>
                </div>
            </label>
        `).join('');
    }

    function toggleRealRepairAllStudents() {
        const boxes = document.querySelectorAll('#real-repair-student-list input[type="checkbox"]');
        const shouldSelectAll = Array.from(boxes).some(box => !box.checked);
        boxes.forEach(box => {
            box.checked = shouldSelectAll;
            syncModuleStudentOption(box);
        });
    }

    async function submitRealWorldRepair(event) {
        event.preventDefault();
        const selectedIds = Array.from(document.querySelectorAll('#real-repair-student-list input[type="checkbox"]:checked')).map(box => box.value);
        if(selectedIds.length === 0) {
            alert('Select at least one student.');
            return;
        }

        const fd = new FormData();
        fd.append('student_ids', JSON.stringify(selectedIds));
        fd.append('repair_title', document.getElementById('real-repair-title').value);
        fd.append('comment', document.getElementById('real-repair-comment').value);
        fd.append('trainer_name', document.getElementById('real-repair-trainer').value);

        try {
            const res = await fetch('api/save_real_world_repair.php', { method: 'POST', body: fd });
            const result = await res.json();
            if(result.status !== 'success') {
                alert(result.message || 'Unable to save repair comment.');
                return;
            }
            cacheVersion = Date.now();
            realWorldRepairModal.hide();
            currentCoursePage = 'RealWork';
            switchCoursePage('RealWork');
        } catch(e) {
            alert('Unable to save repair comment.');
        }
    }

    async function openStudentProfileFromCourse(studentId) {
        await openStudentProfileModal(studentId);
    }

    async function openStudentProfileModal(studentId) {
        const student = allStudents.find(s => String(s.id) === String(studentId));
        if(!student) return;
        modalProfileStudentId = student.id;
        currentModalProfileTab = 'Theory';

        const avatar = document.getElementById('modal-profile-avatar');
        const name = document.getElementById('modal-profile-name');
        const sub = document.getElementById('modal-profile-sub');

        setStudentAvatarElement(avatar, student);
        if(name) name.innerText = student.name;
        if(sub) sub.innerHTML = buildStudentHeaderBadges(student);

        studentProfileModal.show();
        await switchModalProfileTab('Theory');
    }

    async function switchModalProfileTab(tabName) {
        currentModalProfileTab = tabName;
        document.querySelectorAll('#modal-profile-tabs .segment-btn').forEach(b => b.classList.remove('active'));
        const activeBtn = document.getElementById(`modal-tab-${tabName.toLowerCase()}`);
        if(activeBtn) activeBtn.classList.add('active');

        const container = document.getElementById('modal-profile-pane-content');
        if(!container || !modalProfileStudentId) return;
        container.innerHTML = '<div class="text-center py-5"><div class="spinner-border spinner-border-sm text-secondary"></div></div>';

        if(tabName === 'Overview') await loadModalProfileOverview();
        else if(tabName === 'Theory') await loadModalTickLists('Course');
        else if(tabName === 'Practical') await loadModalTickLists('Practical');
        else if(tabName === 'Realworld') await loadModalRealWorldExperience();
        else if(tabName === 'Rollcall') await loadModalRollCallProfile();
        else if(tabName === 'History') await loadModalHistoryLogs();
        else if(tabName === 'Attendance') await loadModalAttendanceLogs();
    }

    async function loadModalProfileOverview() {
        const container = document.getElementById('modal-profile-pane-content');
        const student = allStudents.find(s => String(s.id) === String(modalProfileStudentId));
        if(!container || !student) return;

        const coursePct = student.total_course > 0 ? Math.round((student.course_completed / student.total_course) * 100) : 0;
        const practicalPct = student.total_practical > 0 ? Math.round((student.practical_completed / student.total_practical) * 100) : 0;

        try {
            const [attendanceRes, realRepairRes] = await Promise.all([
                fetch(`api/get_attendance.php?student_id=${modalProfileStudentId}&v=${cacheVersion}`),
                fetch(`api/get_real_world_repairs.php?student_id=${modalProfileStudentId}&v=${cacheVersion}`)
            ]);
            const attendance = await attendanceRes.json();
            const realRepair = await realRepairRes.json();
            const attendanceRows = attendance.status === 'success' ? attendance.data : [];
            const realRepairRows = realRepair.status === 'success' ? realRepair.data : [];
            const attendanceCounts = getScheduledAttendanceCounts(attendanceRows, student);
            const presentCount = attendanceCounts.Present;
            const lateCount = attendanceCounts.Late;
            const absentCount = attendanceCounts.Absent;
            const lastRollCall = attendanceRows[0] ? `${attendanceRows[0].status}  •  ${new Date(attendanceRows[0].created_at).toLocaleDateString([], { month: 'short', day: 'numeric' })}` : 'No roll call yet';

            container.innerHTML = `
                <div class="modal-overview-layout">
                    <section class="modal-overview-card">
                        <div class="curriculum-category">Student Data</div>
                        <div class="modal-profile-data-list">
                            <div class="modal-profile-data-row"><span>Name</span><strong>${escapeHtml(student.name)}</strong></div>
                            <div class="modal-profile-data-row"><span>Phone</span><strong>${formatPrivacy(student.phone, '-')}</strong></div>
                            <div class="modal-profile-data-row"><span>Email</span><strong>${formatPrivacy(student.email, '-')}</strong></div>
                            <div class="modal-profile-data-row"><span>Shop</span><strong>${formatPrivacy(student.shop_name, '-')}</strong></div>
                            <div class="modal-profile-data-row"><span>Address</span><strong>${formatPrivacy(student.address, '-')}</strong></div>
                        </div>
                    </section>

                    <section class="modal-overview-card">
                        <div class="curriculum-category">Theory Progress</div>
                        <div class="modal-progress-block">
                            <div class="modal-progress-head">
                                <div>
                                    <strong>Course</strong>
                                    <span>${student.course_completed}/${student.total_course} completed</span>
                                </div>
                                <div class="modal-progress-percent">${coursePct}%</div>
                            </div>
                            <div class="profile-progress-track"><div class="profile-progress-fill" style="width:${coursePct}%;"></div></div>
                        </div>
                        <div class="modal-progress-block">
                            <div class="modal-progress-head">
                                <div>
                                    <strong>Practical</strong>
                                    <span>${student.practical_completed}/${student.total_practical} completed</span>
                                </div>
                                <div class="modal-progress-percent">${practicalPct}%</div>
                            </div>
                            <div class="profile-progress-track"><div class="profile-progress-fill" style="width:${practicalPct}%; background: var(--brand-purple);"></div></div>
                        </div>
                    </section>

                    <div class="modal-overview-stack">
                        <section class="modal-overview-card">
                            <div class="curriculum-category">Roll Call</div>
                            <div class="modal-stat-value">${attendanceRows.length}</div>
                            <div class="modal-stat-label">Total attendance records</div>
                            <div class="modal-rollcall-chips">
                                <span class="modal-rollcall-chip">${presentCount} Present</span>
                                <span class="modal-rollcall-chip late">${lateCount} Late</span>
                                <span class="modal-rollcall-chip absent">${absentCount} Absent</span>
                            </div>
                            <div class="modal-latest-note">Latest: ${escapeHtml(lastRollCall)}</div>
                        </section>
                        <section class="modal-overview-card">
                            <div class="curriculum-category">Live Repair Sessions</div>
                            <div class="modal-stat-value">${realRepairRows.length}</div>
                            <div class="modal-stat-label">Repair comment records</div>
                        </section>
                    </div>
                </div>
            `;
        } catch(e) {
            container.innerHTML = '<div class="text-center text-secondary py-5">Unable to load profile overview.</div>';
        }
    }

    async function loadModalTickLists(type) {
        const container = document.getElementById('modal-profile-pane-content');
        if(!container || !modalProfileStudentId) return;

        try {
            const [curriculumRes, progressRes] = await Promise.all([
                fetch(`api/get_curriculum.php?v=${cacheVersion}`),
                fetch(`api/get_student_progress.php?student_id=${modalProfileStudentId}&v=${cacheVersion}`)
            ]);
            const curriculum = await curriculumRes.json();
            const progress = await progressRes.json();
            const completedMap = {};
            if(progress.status === 'success') progress.data.forEach(p => completedMap[p.item_id] = p);

            if(curriculum.status !== 'success') {
                container.innerHTML = '<div class="text-center text-secondary py-5">Unable to load modules.</div>';
                return;
            }

            let finalHtml = '';
            let categoryName = '';
            let chunkHtml = '';
            const filtered = curriculum.data.filter(i => i.type === type);

            filtered.forEach((item, index) => {
                if(item.category !== categoryName) {
                    if(chunkHtml !== '') finalHtml += `<div class="ios-list">${chunkHtml}</div>`;
                    finalHtml += `<div class="curriculum-category">${escapeHtml(item.category)}</div>`;
                    categoryName = item.category;
                    chunkHtml = '';
                }

                const compData = completedMap[item.id];
                let metaHtml = '';
                if(compData && compData.completion_date) {
                    const date = new Date(compData.completion_date);
                    const timeStr = date.toLocaleDateString([], { month: 'short', day: 'numeric' }) + ' ' + date.toLocaleTimeString([], { hour: '2-digit', minute:'2-digit' });
                    metaHtml = `<div class="item-meta"><div class="date-badge"><span class="material-symbols-rounded" style="font-size:14px;">schedule</span> ${escapeHtml(timeStr)}</div><div class="trainer-badge"><span class="material-symbols-rounded" style="font-size:14px;">verified_user</span> ${escapeHtml(compData.trainer_name || 'Instructor')}</div></div>`;
                }

                chunkHtml += `<div class="ios-list-item clickable ${compData ? 'checked' : ''}"><div class="d-flex align-items-center"><div class="circle-check"></div><span class="reminder-text">${escapeHtml(item.title)}</span></div>${metaHtml}</div>`;
                if(index === filtered.length - 1) finalHtml += `<div class="ios-list">${chunkHtml}</div>`;
            });

            container.innerHTML = finalHtml || `<div class="text-center text-secondary py-5">No ${escapeHtml(type.toLowerCase())} modules found.</div>`;
        } catch(e) {
            container.innerHTML = '<div class="text-center text-secondary py-5">Unable to load modules.</div>';
        }
    }

    async function loadModalHistoryLogs() {
        const container = document.getElementById('modal-profile-pane-content');
        if(!container || !modalProfileStudentId) return;
        try {
            const res = await fetch(`api/get_history.php?student_id=${modalProfileStudentId}&v=${cacheVersion}`);
            const result = await res.json();
            if(result.status === 'success' && result.data.length > 0) {
                let html = '<div class="timeline-container">';
                result.data.forEach(log => {
                    const dateStr = new Date(log.created_at).toLocaleString([], { dateStyle: 'short', timeStyle: 'short' });
                    html += `<div class="timeline-record"><div class="timeline-dot ${log.status === 'Completed' ? '' : 'absent'}"></div><div class="fw-bold" style="font-size:15px;">${escapeHtml(log.title)} <span class="text-secondary font-monospace" style="font-size:11px;">[${escapeHtml(log.status)}]</span></div><div class="text-secondary mb-1" style="font-size:12px;">${escapeHtml(dateStr)}  •  ${escapeHtml(log.type)}  •  Instructor: ${escapeHtml(log.trainer_name || 'System')}</div>${log.comment ? `<div style="background: var(--bg-base); padding:8px 12px; border-radius:8px; font-size:13px; border: 0.5px solid var(--separator);">"${escapeHtml(log.comment)}"</div>` : ''}</div>`;
                });
                container.innerHTML = html + '</div>';
            } else {
                container.innerHTML = '<div class="text-center text-secondary py-5">No log entries found.</div>';
            }
        } catch(e) {
            container.innerHTML = '<div class="text-center text-secondary py-5">Unable to load logs.</div>';
        }
    }

    async function loadModalRealWorldExperience() {
        const container = document.getElementById('modal-profile-pane-content');
        if(!container || !modalProfileStudentId) return;
        try {
            const res = await fetch(`api/get_real_world_repairs.php?student_id=${modalProfileStudentId}&v=${cacheVersion}`);
            const result = await res.json();
            const commentRows = result.status === 'success' ? result.data : [];
            const commentsHtml = commentRows.length > 0 ? commentRows.map(row => `
                <div class="timeline-record">
                    <div class="timeline-dot"></div>
                    <div class="fw-bold" style="font-size:15px;">${escapeHtml(row.repair_title)}</div>
                    <div class="text-secondary mb-2" style="font-size:12px;">${new Date(row.created_at).toLocaleString([], { dateStyle: 'short', timeStyle: 'short' })}  •  ${escapeHtml(row.trainer_name || 'Instructor')}</div>
                    <div class="student-profile-panel" style="padding:12px; box-shadow:none;">${escapeHtml(row.comment)}</div>
                </div>
            `).join('') : '<div class="text-center text-secondary py-4">No live repair session comments found.</div>';

            container.innerHTML = `<div class="curriculum-category">Live Repair Session Comments</div><div class="timeline-container">${commentsHtml}</div>`;
        } catch(e) {
            container.innerHTML = '<div class="text-center text-secondary py-5">Unable to load live repair sessions.</div>';
        }
    }

    async function loadModalRollCallProfile() {
        const container = document.getElementById('modal-profile-pane-content');
        if(!container || !modalProfileStudentId) return;
        try {
            const res = await fetch(`api/get_attendance.php?student_id=${modalProfileStudentId}&v=${cacheVersion}`);
            const result = await res.json();
            const rows = result.status === 'success' ? result.data : [];
            const student = allStudents.find(s => String(s.id) === String(modalProfileStudentId));
            const counts = getScheduledAttendanceCounts(rows, student);
            const listHtml = rows.length > 0 ? rows.map(log => {
                const color = log.status === 'Present' ? 'var(--system-green)' : (log.status === 'Late' ? 'var(--system-orange)' : 'var(--system-red)');
                return `
                    <div class="ios-list-item">
                        <div>
                            <span class="material-symbols-rounded" style="font-size:16px; vertical-align:middle; color:${color};">fiber_manual_record</span>
                            <span class="fw-bold" style="font-size:15px; margin-left:8px;">${new Date(log.created_at).toLocaleDateString([], { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' })}</span>
                        </div>
                        <div class="badge" style="background-color: transparent; border: 1px solid ${color}; color: ${color}; width: 72px; text-align: center; font-size: 12px; display: inline-block; padding: 4px 0; font-weight: 600;">${escapeHtml(log.status)}</div>
                    </div>
                `;
            }).join('') : '<div class="text-center text-secondary py-5">No roll call records found.</div>';

            container.innerHTML = `
                <div class="student-profile-grid">
                    <div class="student-profile-panel"><div class="curriculum-category m-0 mb-2">Present</div><h3 class="fw-bold m-0" style="color:var(--system-green);">${counts.Present}</h3></div>
                    <div class="student-profile-panel"><div class="curriculum-category m-0 mb-2">Late</div><h3 class="fw-bold m-0" style="color:var(--system-orange);">${counts.Late}</h3></div>
                    <div class="student-profile-panel"><div class="curriculum-category m-0 mb-2">Absent</div><h3 class="fw-bold m-0" style="color:var(--system-red);">${counts.Absent}</h3></div>
                </div>
                <div class="curriculum-category">Roll Call History</div>
                <div class="ios-list">${listHtml}</div>
            `;
        } catch(e) {
            container.innerHTML = '<div class="text-center text-secondary py-5">Unable to load roll call records.</div>';
        }
    }

    async function loadModalAttendanceLogs() {
        const container = document.getElementById('modal-profile-pane-content');
        if(!container || !modalProfileStudentId) return;
        try {
            const res = await fetch(`api/get_attendance.php?student_id=${modalProfileStudentId}&v=${cacheVersion}`);
            const result = await res.json();
            if(result.status === 'success' && result.data.length > 0) {
                let html = '<div class="curriculum-category">Permanent Attendance Record</div><div class="ios-list">';
                result.data.forEach(log => {
                    const dateStr = new Date(log.created_at).toLocaleDateString([], { weekday: 'short', month: 'short', day: 'numeric' });
                    const color = log.status === 'Present' ? 'var(--system-green)' : (log.status === 'Late' ? 'var(--system-orange)' : 'var(--system-red)');
                    html += `<div class="ios-list-item d-flex justify-content-between align-items-center"><div><span class="material-symbols-rounded" style="font-size:16px; vertical-align:middle; color:${color};">fiber_manual_record</span> <span class="fw-bold" style="font-size:15px; margin-left:8px;">${escapeHtml(dateStr)}</span></div><div class="badge" style="background-color: transparent; border: 1px solid ${color}; color: ${color}; width: 72px; text-align: center; font-size: 12px; display: inline-block; padding: 4px 0; font-weight: 600;">${escapeHtml(log.status)}</div></div>`;
                });
                container.innerHTML = html + '</div>';
            } else {
                container.innerHTML = '<div class="curriculum-category">Permanent Attendance Record</div><div class="text-center text-secondary py-5">No attendance records found yet.</div>';
            }
        } catch(e) {
            container.innerHTML = '<div class="text-center text-secondary py-5">Unable to load attendance history.</div>';
        }
    }

    function openStudentFullProfile(studentId) {
        currentProfileTab = 'Detail';
        switchViewMode('roster', studentId);
    }

    function switchStudentListView(view, options = {}) {
        currentStudentListView = view === 'dashboard' ? 'dashboard' : (view === 'profile' ? 'profile' : 'list');
        const listView = document.getElementById('student-list-view');
        const dashboardView = document.getElementById('student-dashboard-view');
        const listBtn = document.getElementById('student-view-list-btn');
        const dashboardBtn = document.getElementById('student-view-dashboard-btn');
        if(listView) {
            listView.style.display = currentStudentListView === 'dashboard' ? 'none' : 'grid';
            listView.classList.toggle('profile-only', currentStudentListView === 'profile');
        }
        if(dashboardView) dashboardView.style.display = currentStudentListView === 'dashboard' ? 'block' : 'none';
        if(listBtn) listBtn.classList.toggle('active', currentStudentListView === 'list');
        if(dashboardBtn) dashboardBtn.classList.toggle('active', currentStudentListView === 'dashboard');
        renderStudentPageList();
        renderStudentDashboardList();
        if(currentStudentListView === 'list' && !options.keepSelection) {
            const activeStudents = getActiveStudents();
            const current = activeStudents.find(s => String(s.id) === String(activeStudentId));
            if(current) selectStudent(current.id);
            else if(activeStudents.length > 0) selectStudent(activeStudents[0].id);
        }
    }

    function openStudentFromDashboard(studentId) {
        currentProfileTab = 'Detail';
        switchStudentListView('profile', { keepSelection: true });
        selectStudent(studentId);
    }

    function backToStudentDashboard() {
        switchStudentListView('dashboard', { keepSelection: true });
    }

    function getReportSelectedStudents() {
        return selectedReportStudentIds
            .map(id => allStudents.find(s => String(s.id) === String(id)))
            .filter(student => student && isStudentActive(student));
    }

    function loadReportView() {
        const activeStudents = getActiveStudents();
        if(selectedReportStudentIds.length === 0 && activeStudents.length > 0) {
            selectedReportStudentIds = [String(activeStudents[0].id)];
        }
        renderReportStudentSelection();
        switchReportTab(currentReportTab);
    }

    function renderReportStudentSelection() {
        const activeContainer = document.getElementById('report-active-students');
        const pickerContainer = document.getElementById('report-student-picker');
        if(!activeContainer || !pickerContainer) return;

        const selected = getReportSelectedStudents();
        if(selected.length === 0) {
            activeContainer.innerHTML = '<div class="text-secondary py-4 text-center">No active students selected.</div>';
        } else {
            activeContainer.innerHTML = selected.map(s => `
                <div class="report-person-card active">
                    ${renderStudentAvatar(s, 'report-photo')}
                    <div style="min-width:0;">
                        <div class="report-name">${escapeHtml(s.name)}</div>
                        <div class="report-meta">ID: #${escapeHtml(s.id)}  •  ${escapeHtml(s.phone || 'No phone')}</div>
                    </div>
                </div>
            `).join('');
        }

        const activeStudents = getActiveStudents();
        pickerContainer.innerHTML = activeStudents.map(s => {
            const selectedClass = selectedReportStudentIds.includes(String(s.id)) ? 'active' : '';
            return `
                <div class="report-person-card report-picker-card ${selectedClass}" onclick="toggleReportStudent(${s.id})">
                    ${renderStudentAvatar(s, 'report-photo')}
                    <div style="min-width:0;">
                        <div class="report-name">${escapeHtml(s.name)}</div>
                        <div class="report-meta">${escapeHtml(s.address || s.shop_name || s.phone || 'Trainee')}</div>
                    </div>
                </div>
            `;
        }).join('');
    }

    function toggleReportStudent(id) {
        const student = allStudents.find(s => String(s.id) === String(id));
        if(!isStudentActive(student)) return;
        const key = String(id);
        if(selectedReportStudentIds.includes(key)) {
            selectedReportStudentIds = selectedReportStudentIds.filter(x => x !== key);
        } else {
            selectedReportStudentIds.push(key);
        }
        renderReportStudentSelection();
        loadReportPane();
    }

    function toggleAllReportStudents() {
        const activeStudents = getActiveStudents();
        if(selectedReportStudentIds.length === activeStudents.length) {
            selectedReportStudentIds = [];
        } else {
            selectedReportStudentIds = activeStudents.map(s => String(s.id));
        }
        renderReportStudentSelection();
        loadReportPane();
    }

    function switchReportTab(tabName) {
        currentReportTab = tabName;
        document.querySelectorAll('.report-tabs .segment-btn').forEach(b => b.classList.remove('active'));
        const activeBtn = document.getElementById(`report-tab-${tabName.toLowerCase()}`);
        if(activeBtn) activeBtn.classList.add('active');
        loadReportPane();
    }

    function renderReportEmpty(message) {
        const container = document.getElementById('report-pane-content');
        if(container) container.innerHTML = `<div class="text-center text-secondary py-5">${message}</div>`;
    }

    async function loadReportPane() {
        const container = document.getElementById('report-pane-content');
        if(!container) return;
        const selected = getReportSelectedStudents();
        if(selected.length === 0) {
            renderReportEmpty('Select at least one student to show data.');
            return;
        }

        container.innerHTML = '<div class="text-center py-5"><div class="spinner-border spinner-border-sm text-secondary"></div></div>';

        if(currentReportTab === 'Theory') await loadReportModules('Course');
        else if(currentReportTab === 'Practical') await loadReportModules('Practical');
        else if(currentReportTab === 'Realworld') await loadReportModules('Realworld');
        else if(currentReportTab === 'Comment') await loadReportComments();
        else if(currentReportTab === 'Attendance') await loadReportAttendance();
    }

    async function loadReportModules(type) {
        const container = document.getElementById('report-pane-content');
        const selected = getReportSelectedStudents();
        try {
            const curriculumRes = await fetch(`api/get_curriculum.php?v=${cacheVersion}`);
            const curriculumResult = await curriculumRes.json();
            const modules = curriculumResult.status === 'success' ? curriculumResult.data.filter(i => i.type === type) : [];

            if(modules.length === 0) {
                const label = type === 'Realworld' ? 'realworld' : type.toLowerCase();
                renderReportEmpty(`No ${label} modules are configured yet.`);
                return;
            }

            const progressByStudent = {};
            await Promise.all(selected.map(async student => {
                const res = await fetch(`api/get_student_progress.php?student_id=${student.id}&v=${cacheVersion}`);
                const result = await res.json();
                progressByStudent[student.id] = result.status === 'success' ? result.data : [];
            }));

            container.innerHTML = selected.map(student => {
                const doneMap = {};
                progressByStudent[student.id].forEach(p => doneMap[p.item_id] = p);
                const doneCount = modules.filter(m => doneMap[m.id]).length;
                const percent = Math.round((doneCount / modules.length) * 100);
                const rows = modules.map(module => {
                    const done = doneMap[module.id];
                    const meta = done && done.completion_date ? `${new Date(done.completion_date).toLocaleDateString([], { month: 'short', day: 'numeric' })}  •  ${escapeHtml(done.trainer_name || 'Instructor')}` : escapeHtml(module.category);
                    return `
                        <div class="report-module-row">
                            <div style="min-width:0;">
                                <div class="fw-bold" style="font-size:14px;">${escapeHtml(module.title)}</div>
                                <div class="text-secondary" style="font-size:12px;">${meta}</div>
                            </div>
                            <div class="report-status ${done ? 'done' : ''}">${done ? 'Completed' : 'Pending'}</div>
                        </div>
                    `;
                }).join('');

                return `
                    <div class="report-result-card">
                        <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap mb-3">
                            <div class="d-flex align-items-center gap-3" style="min-width:0;">
                                ${renderStudentAvatar(student, 'report-photo')}
                                <div style="min-width:0;">
                                    <div class="report-name">${escapeHtml(student.name)}</div>
                                    <div class="report-meta">${doneCount}/${modules.length} completed</div>
                                </div>
                            </div>
                            <div class="fw-bold" style="color: var(--system-blue);">${percent}%</div>
                        </div>
                        ${rows}
                    </div>
                `;
            }).join('');
        } catch(e) {
            renderReportEmpty('Unable to load report data.');
        }
    }

    async function loadReportComments() {
        const container = document.getElementById('report-pane-content');
        const selected = getReportSelectedStudents();
        try {
            const groups = await Promise.all(selected.map(async student => {
                const res = await fetch(`api/get_history.php?student_id=${student.id}&v=${cacheVersion}`);
                const result = await res.json();
                return { student, logs: result.status === 'success' ? result.data.filter(log => (log.comment || '').trim() !== '') : [] };
            }));

            const html = groups.map(group => {
                const rows = group.logs.length > 0 ? group.logs.map(log => {
                    const dateStr = new Date(log.created_at).toLocaleString([], { dateStyle: 'short', timeStyle: 'short' });
                    return `
                        <div class="report-module-row">
                            <div style="min-width:0;">
                                <div class="fw-bold" style="font-size:14px;">${escapeHtml(log.title)}</div>
                                <div class="text-secondary mb-2" style="font-size:12px;">${dateStr}  •  ${escapeHtml(log.type)}  •  ${escapeHtml(log.status)}</div>
                                <div style="font-size:13px;">${escapeHtml(log.comment)}</div>
                            </div>
                        </div>
                    `;
                }).join('') : '<div class="text-secondary py-3">No comments found.</div>';

                return `
                    <div class="report-result-card">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            ${renderStudentAvatar(group.student, 'report-photo')}
                            <div>
                                <div class="report-name">${escapeHtml(group.student.name)}</div>
                                <div class="report-meta">${group.logs.length} comments</div>
                            </div>
                        </div>
                        ${rows}
                    </div>
                `;
            }).join('');

            container.innerHTML = html;
        } catch(e) {
            renderReportEmpty('Unable to load comments.');
        }
    }

    async function loadReportAttendance() {
        const container = document.getElementById('report-pane-content');
        const selected = getReportSelectedStudents();
        try {
            const groups = await Promise.all(selected.map(async student => {
                const res = await fetch(`api/get_attendance.php?student_id=${student.id}&v=${cacheVersion}`);
                const result = await res.json();
                return { student, logs: result.status === 'success' ? result.data : [] };
            }));

            container.innerHTML = groups.map(group => {
                const rows = group.logs.length > 0 ? group.logs.map(log => {
                    const dateStr = new Date(log.created_at).toLocaleDateString([], { weekday: 'short', month: 'short', day: 'numeric' });
                    const color = log.status === 'Present' ? 'var(--system-green)' : (log.status === 'Late' ? 'var(--system-orange)' : 'var(--system-red)');
                    return `
                        <div class="report-module-row">
                            <div class="fw-bold" style="font-size:14px;">${dateStr}</div>
                            <div class="report-status" style="color:${color}; border-color:${color};">${escapeHtml(log.status)}</div>
                        </div>
                    `;
                }).join('') : '<div class="text-secondary py-3">No attendance records found.</div>';

                return `
                    <div class="report-result-card">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            ${renderStudentAvatar(group.student, 'report-photo')}
                            <div>
                                <div class="report-name">${escapeHtml(group.student.name)}</div>
                                <div class="report-meta">${group.logs.length} attendance records</div>
                            </div>
                        </div>
                        ${rows}
                    </div>
                `;
            }).join('');
        } catch(e) {
            renderReportEmpty('Unable to load attendance history.');
        }
    }

    // --- CURRICULUM TICK LISTS (Speed Optimized) ---
    async function loadTickLists(type) {
        const container = document.getElementById('profile-pane-content');
        if(!activeStudentId || !container) return;
        
        try {
            // Uses cacheVersion: Instant load for browsing, fresh load only after saves
            const [curriculumRes, progressRes] = await Promise.all([ 
                fetch(`api/get_curriculum.php?v=${cacheVersion}`), 
                fetch(`api/get_student_progress.php?student_id=${activeStudentId}&v=${cacheVersion}`) 
            ]);
            
            const curriculum = await curriculumRes.json(); 
            const progress = await progressRes.json();
            
            const completedMap = {};
            if(progress.status === 'success') progress.data.forEach(p => completedMap[p.item_id] = p);

            if(curriculum.status === 'success') {
                let finalHtml = ''; 
                let categoryName = ''; let chunkHtml = '';
                
                const filtered = curriculum.data.filter(i => i.type === type);
                filtered.forEach((item, index) => {
                    if(item.category !== categoryName) {
                        if(chunkHtml !== '') finalHtml += `<div class="ios-list">${chunkHtml}</div>`;
                        finalHtml += `<div class="curriculum-category">${item.category}</div>`;
                        categoryName = item.category; chunkHtml = '';
                    }

                    const compData = completedMap[item.id];
                    let metaHtml = '';
                    if (compData && compData.completion_date) {
                        let d = new Date(compData.completion_date);
                        let timeStr = d.toLocaleDateString([], { month: 'short', day: 'numeric' }) + ' ' + d.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                        metaHtml = `<div class="item-meta"><div class="date-badge"><span class="material-symbols-rounded" style="font-size:14px;">schedule</span> ${timeStr}</div><div class="trainer-badge"><span class="material-symbols-rounded" style="font-size:14px;">verified_user</span> ${compData.trainer_name || 'Instructor'}</div></div>`;
                    }

                    const safeTitle = item.title.replace(/'/g, "\\'").replace(/"/g, '&quot;');
                    chunkHtml += `<div class="ios-list-item clickable ${compData ? 'checked' : ''}" onclick="processTickToggle(this, ${item.id}, '${safeTitle}')"><div class="d-flex align-items-center"><div class="circle-check"></div><span class="reminder-text">${item.title}</span></div>${metaHtml}</div>`;
                    if(index === filtered.length - 1) finalHtml += `<div class="ios-list">${chunkHtml}</div>`;
                });
                container.innerHTML = finalHtml; 
            }
        } catch(e) { console.error("Tick List Load Error:", e); }
    }

    function processTickToggle(element, itemId, title) {
        if (!element.classList.contains('checked')) {
            const titleEl = document.getElementById('sign-off-title');
            if(titleEl) {
                titleEl.innerText = title; 
                document.getElementById('sign-off-item-id').value = itemId; 
                document.getElementById('sign-off-note').value = '';
                signOffModal.show();
            }
        } else {
            const revTitle = document.getElementById('revert-title');
            if(revTitle) {
                revTitle.innerText = title;
                document.getElementById('revert-item-id').value = itemId;
                document.getElementById('revert-note').value = '';
                revertModal.show();
            }
        }
    }

    function submitSignOff(event) {
        event.preventDefault(); signOffModal.hide();
        const trainerEl = document.getElementById('sign-off-trainer'); const trainerVal = trainerEl ? trainerEl.value : 'Instructor';
        const noteEl = document.getElementById('sign-off-note'); const noteVal = noteEl ? noteEl.value : '';
        executeTickSave(document.getElementById('sign-off-item-id').value, 'Completed', noteVal, trainerVal);
    }

    function submitRevert(event) {
        event.preventDefault(); revertModal.hide();
        const noteEl = document.getElementById('revert-note'); const noteVal = noteEl ? noteEl.value : '';
        executeTickSave(document.getElementById('revert-item-id').value, 'Pending', noteVal, '');
    }

    async function executeTickSave(itemId, status, comment, trainer) {
        const fd = new FormData(); fd.append('student_id', activeStudentId); fd.append('item_id', itemId); fd.append('status', status); fd.append('comment', comment || ''); fd.append('trainer_name', trainer);
        try {
            // Wait for DB to update
            await fetch('api/update_progress.php', { method: 'POST', body: fd });
            
            // Bump the cache version ONLY when data actually changes
            cacheVersion = Date.now(); 
            
            // Reload UI
            let dbType = (currentProfileTab === 'Theory') ? 'Course' : 'Practical';
            await loadTickLists(dbType); 
            
            const res = await fetch(`api/get_students.php?v=${cacheVersion}`); 
            const result = await res.json();
            if(result.status === 'success') {
                allStudents = result.data;
                const s = allStudents.find(x => x.id == activeStudentId);
                if(s) {
                    let coursePct = s.total_course > 0 ? Math.round((s.course_completed / s.total_course) * 100) : 0;
                    let pracPct = s.total_practical > 0 ? Math.round((s.practical_completed / s.total_practical) * 100) : 0;
                    const tTheory = document.getElementById(`text-theory-${activeStudentId}`); const tPrac = document.getElementById(`text-prac-${activeStudentId}`); const bTheory = document.getElementById(`bar-theory-${activeStudentId}`); const bPrac = document.getElementById(`bar-prac-${activeStudentId}`);
                    if(tTheory) tTheory.innerText = coursePct + '%'; if(tPrac) tPrac.innerText = pracPct + '%'; if(bTheory) bTheory.style.width = coursePct + '%'; if(bPrac) bPrac.style.width = pracPct + '%';
                    updateRosterOverview();
                }
            }
        } catch(e) { console.error("Save Error:", e); }
    }

    // --- EXAM GLOBAL VIEW ---
    let globalExamSelectedStudentId = null;

    function loadGlobalExamStudents() {
        const list = document.getElementById('global-exam-student-list');
        const detail = document.getElementById('global-exam-pane');
        if(!list || !detail) return;
        if(allStudents.length === 0) {
            list.innerHTML = '<div class="text-secondary py-4 text-center">No students found.</div>';
            detail.innerHTML = '<div class="student-contact-empty"><span class="material-symbols-rounded">assignment</span><strong>No Students</strong><small>Add trainees in Trainee Controls.</small></div>';
            return;
        }

        if(!allStudents.some(s => String(s.id) === String(globalExamSelectedStudentId))) {
            globalExamSelectedStudentId = String(allStudents[0].id);
        }

        list.innerHTML = allStudents.map(student => {
            const activeClass = String(globalExamSelectedStudentId) === String(student.id) ? 'active' : '';
            return `
                <div class="student-page-card ${activeClass}" id="global-exam-card-${student.id}" onclick="selectGlobalExamStudent(${student.id})" style="cursor: pointer; margin-bottom: 12px;">
                    <div class="d-flex align-items-center gap-3 mb-1">
                        ${renderStudentAvatar(student, 'report-photo')}
                        <div style="min-width:0;">
                            <div class="report-name">${escapeHtml(student.name)}</div>
                            <div class="report-meta">ID: #${escapeHtml(student.id)}  •  ${formatPrivacy(student.phone, 'No phone')}</div>
                        </div>
                    </div>
                </div>
            `;
        }).join('');

        selectGlobalExamStudent(globalExamSelectedStudentId, false);
    }

    async function selectGlobalExamStudent(studentId, rerenderList = true) {
        globalExamSelectedStudentId = String(studentId);
        const student = allStudents.find(s => String(s.id) === String(studentId));
        const pane = document.getElementById('global-exam-pane');
        if(!student || !pane) return;
        
        if(rerenderList) {
            document.querySelectorAll('#global-exam-student-list .student-page-card').forEach(card => card.classList.remove('active'));
            const card = document.getElementById(`global-exam-card-${studentId}`);
            if(card) {
                card.classList.add('active');
            }
        }
        
        pane.innerHTML = '<div class="text-center py-5"><div class="spinner-border spinner-border-sm text-secondary"></div></div>';

        try {
            const examRes = await fetch(`api/get_student_exam.php?student_id=${studentId}&v=${cacheVersion}`);
            const result = await examRes.json();
            const exam = result.status === 'success' ? result.data : null;
            
            const scoreValue = exam ? Number(exam.score || 0) : 0;
            const maxValue = exam ? Number(exam.max_score || 100) : 100;
            const percentStr = maxValue > 0 ? Math.round((scoreValue / maxValue) * 100) + '%' : '0%';
            const updatedText = exam ? `Updated ${new Date(exam.updated_at || exam.created_at).toLocaleDateString([], { month: 'short', day: 'numeric' })}` : 'No exam score yet';
            
            pane.innerHTML = `
                <form class="rollcall-exam-card" style="margin: 0; padding: 24px;" onsubmit="saveGlobalExam(event)">
                    <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 1px solid var(--separator);">
                        ${renderStudentAvatar(student, 'report-photo')}
                        <div>
                            <strong style="font-size: 18px; display: block;">${escapeHtml(student.name)}</strong>
                            <small class="text-secondary">ID: #${student.id}</small>
                        </div>
                    </div>
                    
                    <input type="hidden" id="global-exam-student-id" value="${studentId}">
                    <div class="rollcall-exam-head">
                        <span class="material-symbols-rounded">assignment</span>
                        <div>
                            <strong>Exam Data</strong>
                            <small>Score for selected student</small>
                        </div>
                    </div>
                    <label>
                        <span>Exam Name</span>
                        <input type="text" id="global-exam-name" class="apple-input" value="${escapeHtml(exam?.exam_name || 'Final Exam')}">
                    </label>
                    <div class="rollcall-exam-score-grid">
                        <label>
                            <span>Score</span>
                            <input type="number" min="0" step="0.5" id="global-exam-score" class="apple-input" value="${scoreValue}">
                        </label>
                        <label>
                            <span>Max</span>
                            <input type="number" min="1" step="0.5" id="global-exam-max" class="apple-input" value="${maxValue}">
                        </label>
                    </div>
                    <label>
                        <span>Exam Date</span>
                        <input type="date" id="global-exam-date" class="apple-input" value="${exam?.exam_date || ''}">
                    </label>
                    <label>
                        <span>Note</span>
                        <textarea id="global-exam-note" class="apple-input" rows="2" style="resize:none;">${escapeHtml(exam?.note || '')}</textarea>
                    </label>
                    <div class="rollcall-exam-result">
                        <div>
                            <strong id="global-exam-percent">${percentStr}</strong>
                            <small>Exam result</small>
                        </div>
                        <button type="submit" class="btn-premium btn-icon-label">
                            <span class="material-symbols-rounded">save</span>
                            Save
                        </button>
                    </div>
                    <div id="global-exam-save-note" class="settings-save-note">${updatedText}</div>
                </form>
            `;

            const scoreInp = document.getElementById('global-exam-score');
            const maxInp = document.getElementById('global-exam-max');
            const pctEl = document.getElementById('global-exam-percent');
            const calc = () => {
                const s = Number(scoreInp.value) || 0;
                const m = Number(maxInp.value) || 100;
                pctEl.innerText = m > 0 ? Math.round((s / m) * 100) + '%' : '0%';
            };
            if(scoreInp) scoreInp.addEventListener('input', calc);
            if(maxInp) maxInp.addEventListener('input', calc);

        } catch(e) {
            pane.innerHTML = '<div class="text-center text-secondary py-5">Unable to load exam data.</div>';
        }
    }

    async function saveGlobalExam(event) {
        event.preventDefault();
        const note = document.getElementById('global-exam-save-note');
        const fd = new FormData();
        fd.append('student_id', document.getElementById('global-exam-student-id').value);
        fd.append('exam_name', document.getElementById('global-exam-name').value);
        fd.append('score', document.getElementById('global-exam-score').value || '0');
        fd.append('max_score', document.getElementById('global-exam-max').value || '100');
        fd.append('exam_date', document.getElementById('global-exam-date').value);
        fd.append('note', document.getElementById('global-exam-note').value);
        if(note) note.innerText = 'Saving...';
        const res = await fetch('api/save_student_exam.php', { method: 'POST', body: fd });
        const result = await res.json();
        if(result.status !== 'success') {
            if(note) note.innerText = result.message || 'Unable to save exam.';
            return;
        }
        cacheVersion = Date.now();
        if(note) note.innerText = 'Saved Successfully';
    }

    // --- OTHER CORE FUNCTIONS (Speed Optimized) ---
    async function loadStudents(autoSelectId = null) {
        const res = await fetch(`api/get_students.php?v=${cacheVersion}`); const result = await res.json();
        if (result.status === 'success') {
            allStudents = result.data;
            selectedReportStudentIds = selectedReportStudentIds.filter(id => allStudents.some(s => String(s.id) === String(id) && isStudentActive(s)));
            renderMasterRoster(getActiveStudents());
            renderStudentPageList();
            renderStudentDashboardList();
            if (autoSelectId) selectStudent(autoSelectId);
            if(document.getElementById('view-report') && document.getElementById('view-report').classList.contains('active')) {
                if(currentCoursePage === 'RealWork') loadRealWorldRepairPage();
                else loadCoursePageModules();
            }
            if(document.getElementById('view-contacts')?.classList.contains('active')) loadAdminContacts();
        }
    }

    function renderMasterRoster(data) {
        const container = document.getElementById('student-list'); if(!container) return;
        let html = '';
        data.forEach(s => {
            let coursePct = s.total_course > 0 ? Math.round((s.course_completed / s.total_course) * 100) : 0;
            let pracPct = s.total_practical > 0 ? Math.round((s.practical_completed / s.total_practical) * 100) : 0;
            const isActive = activeStudentId == s.id ? 'active' : '';
            html += `<div class="student-card ${isActive}" id="student-card-${s.id}" onclick="openStudentFullProfile(${s.id})">${renderStudentAvatar(s, 'student-avatar')}<div class="student-info flex-grow-1"><h4>${escapeHtml(s.name)}</h4><div class="roster-prog-container"><div class="roster-prog-label"><span>Theory</span><span id="text-theory-${s.id}">${coursePct}%</span></div><div class="roster-prog-track"><div class="roster-prog-fill-theory" id="bar-theory-${s.id}" style="width: ${coursePct}%;"></div></div><div class="roster-prog-label"><span>Practical</span><span id="text-prac-${s.id}" style="color: var(--system-blue);">${pracPct}%</span></div><div class="roster-prog-track" style="margin-bottom: 0;"><div class="roster-prog-fill-practical" id="bar-prac-${s.id}" style="width: ${pracPct}%;"></div></div></div></div></div>`;
        });
        container.innerHTML = html;
    }

    function renderStudentPageList() {
        const container = document.getElementById('student-page-list');
        if(!container) return;
        const activeStudents = getActiveStudents();
        if(activeStudents.length === 0) {
            container.innerHTML = '<div class="text-center text-secondary py-5">No active students found. Use Admin Trainee Controls to activate students.</div>';
            return;
        }

        container.innerHTML = activeStudents.map(student => {
            const coursePct = student.total_course > 0 ? Math.round((student.course_completed / student.total_course) * 100) : 0;
            const practicalPct = student.total_practical > 0 ? Math.round((student.practical_completed / student.total_practical) * 100) : 0;
            const activeClass = String(activeStudentId) === String(student.id) ? 'active' : '';
            return `
                <div class="student-page-card ${activeClass}" id="student-page-card-${student.id}" onclick="selectStudent(${student.id})">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        ${renderStudentAvatar(student, 'report-photo')}
                        <div style="min-width:0;">
                            <div class="report-name">${escapeHtml(student.name)}</div>
                            <div class="report-meta">ID: #${escapeHtml(student.id)}  •  ${formatPrivacy(student.phone, 'No phone')}</div>
                        </div>
                    </div>
                    <div class="roster-prog-label"><span>Course</span><span>${coursePct}%</span></div>
                    <div class="roster-prog-track"><div class="roster-prog-fill-theory" style="width:${coursePct}%;"></div></div>
                    <div class="roster-prog-label"><span>Practical</span><span>${practicalPct}%</span></div>
                    <div class="roster-prog-track" style="margin-bottom:0;"><div class="roster-prog-fill-practical" style="width:${practicalPct}%;"></div></div>
                </div>
            `;
        }).join('');
    }

    function renderStudentDashboardList() {
        const container = document.getElementById('student-dashboard-list');
        if(!container) return;
        const activeStudents = getActiveStudents();
        if(activeStudents.length === 0) {
            container.innerHTML = '<div class="text-center text-secondary py-5">No active students found. Use Admin Trainee Controls to activate students.</div>';
            return;
        }
        container.innerHTML = activeStudents.map(student => {
            const coursePct = student.total_course > 0 ? Math.round((student.course_completed / student.total_course) * 100) : 0;
            const practicalPct = student.total_practical > 0 ? Math.round((student.practical_completed / student.total_practical) * 100) : 0;
            const joined = formatStudentJoinDate(student);
            return `
                <button type="button" class="student-dashboard-card" onclick="openStudentFromDashboard(${student.id})">
                    <div class="student-dashboard-card-head">
                        ${renderStudentAvatar(student, 'report-photo')}
                        <div>
                            <strong>${escapeHtml(student.name)}</strong>
                            <small>ID: #${escapeHtml(student.id)} • ${formatPrivacy(student.phone, 'No phone')}</small>
                        </div>
                    </div>
                    <div class="student-dashboard-info">
                        <span><i class="material-symbols-rounded">mail</i>${formatPrivacy(student.email, '-')}</span>
                        <span><i class="material-symbols-rounded">storefront</i>${formatPrivacy(student.shop_name, '-')}</span>
                        <span><i class="material-symbols-rounded">calendar_month</i>${escapeHtml(joined)}</span>
                    </div>
                    <div>
                        <div class="roster-prog-label"><span>Theory</span><span>${coursePct}%</span></div>
                        <div class="roster-prog-track"><div class="roster-prog-fill-theory" style="width:${coursePct}%;"></div></div>
                        <div class="roster-prog-label"><span>Practical</span><span>${practicalPct}%</span></div>
                        <div class="roster-prog-track" style="margin-bottom:0;"><div class="roster-prog-fill-practical" style="width:${practicalPct}%;"></div></div>
                    </div>
                </button>
            `;
        }).join('');
    }

    function filterStudents() { renderMasterRoster(getActiveStudents().filter(s => s.name.toLowerCase().includes(document.getElementById('searchBox').value.toLowerCase()) || s.phone.includes(document.getElementById('searchBox').value))); }
    
    function selectStudent(id) {
        activeStudentId = id; const student = allStudents.find(s => s.id == id); if(!student) return;
        const rosterOverview = document.getElementById('roster-overview');
        const profilePlaceholder = document.getElementById('profile-placeholder');
        if(rosterOverview) rosterOverview.style.display = 'block';
        if(profilePlaceholder) profilePlaceholder.style.display = 'none';
        document.getElementById('profile-content').style.display = 'grid';
        setStudentAvatarElement(document.getElementById('p-avatar'), student);
        document.getElementById('p-name').innerText = student.name; 
        const profileId = document.getElementById('p-id');
        if(profileId) profileId.innerHTML = `<span class="material-symbols-rounded">badge</span> ID: #${escapeHtml(student.id)}`;
        
        document.getElementById('p-sub').innerHTML = buildStudentHeaderBadges(student, currentStudentListView === 'profile');
        
        document.querySelectorAll('.student-card, .student-page-card').forEach(c => c.classList.remove('active')); 
        const card = document.getElementById(`student-card-${id}`); 
        if(card) card.classList.add('active');
        const pageCard = document.getElementById(`student-page-card-${id}`);
        if(pageCard) pageCard.classList.add('active');
        
        switchProfileTab(currentProfileTab);
    }

    async function loadStudentProfileOverview() {
        const container = document.getElementById('profile-pane-content');
        const student = allStudents.find(s => s.id == activeStudentId);
        if(!container || !student) return;

        const coursePct = student.total_course > 0 ? Math.round((student.course_completed / student.total_course) * 100) : 0;
        const practicalPct = student.total_practical > 0 ? Math.round((student.practical_completed / student.total_practical) * 100) : 0;

        try {
            const [attendanceRes, realRepairRes] = await Promise.all([
                fetch(`api/get_attendance.php?student_id=${activeStudentId}&v=${cacheVersion}`),
                fetch(`api/get_real_world_repairs.php?student_id=${activeStudentId}&v=${cacheVersion}`)
            ]);
            const attendance = await attendanceRes.json();
            const realRepair = await realRepairRes.json();
            const attendanceRows = attendance.status === 'success' ? attendance.data : [];
            const realRepairRows = realRepair.status === 'success' ? realRepair.data : [];
            const attendanceCounts = getScheduledAttendanceCounts(attendanceRows, student);
            const presentCount = attendanceCounts.Present;
            const lateCount = attendanceCounts.Late;
            const absentCount = attendanceCounts.Absent;
            const lastRollCall = attendanceRows[0] ? `${attendanceRows[0].status}  •  ${new Date(attendanceRows[0].created_at).toLocaleDateString([], { month: 'short', day: 'numeric' })}` : 'No roll call yet';

            container.innerHTML = `
                <div class="profile-overview-layout">
                    <section class="modal-overview-card">
                        <div class="curriculum-category">Student Data</div>
                        <div class="modal-profile-data-list">
                            <div class="modal-profile-data-row"><span>Name</span><strong>${escapeHtml(student.name)}</strong></div>
                            <div class="modal-profile-data-row"><span>Phone</span><strong>${formatPrivacy(student.phone, '-')}</strong></div>
                            <div class="modal-profile-data-row"><span>Email</span><strong>${formatPrivacy(student.email, '-')}</strong></div>
                            <div class="modal-profile-data-row"><span>Shop</span><strong>${formatPrivacy(student.shop_name, '-')}</strong></div>
                            <div class="modal-profile-data-row"><span>Address</span><strong>${formatPrivacy(student.address, '-')}</strong></div>
                        </div>
                    </section>

                    <section class="modal-overview-card">
                        <div class="curriculum-category">Theory Progress</div>
                        <div class="modal-progress-block">
                            <div class="modal-progress-head">
                                <div>
                                    <strong>Course</strong>
                                    <span>${student.course_completed}/${student.total_course} completed</span>
                                </div>
                                <div class="modal-progress-percent">${coursePct}%</div>
                            </div>
                            <div class="profile-progress-track"><div class="profile-progress-fill" style="width:${coursePct}%;"></div></div>
                        </div>
                        <div class="modal-progress-block">
                            <div class="modal-progress-head">
                                <div>
                                    <strong>Practical</strong>
                                    <span>${student.practical_completed}/${student.total_practical} completed</span>
                                </div>
                                <div class="modal-progress-percent">${practicalPct}%</div>
                            </div>
                            <div class="profile-progress-track"><div class="profile-progress-fill" style="width:${practicalPct}%; background: var(--brand-purple);"></div></div>
                        </div>
                    </section>

                    <div class="modal-overview-stack">
                        <section class="modal-overview-card">
                            <div class="curriculum-category">Roll Call</div>
                            <div class="modal-stat-value">${attendanceRows.length}</div>
                            <div class="modal-stat-label">Total attendance records</div>
                            <div class="modal-rollcall-chips">
                                <span class="modal-rollcall-chip">${presentCount} Present</span>
                                <span class="modal-rollcall-chip late">${lateCount} Late</span>
                                <span class="modal-rollcall-chip absent">${absentCount} Absent</span>
                            </div>
                            <div class="modal-latest-note">Latest: ${escapeHtml(lastRollCall)}</div>
                        </section>
                        <section class="modal-overview-card">
                            <div class="curriculum-category">Live Repair Sessions</div>
                            <div class="modal-stat-value">${realRepairRows.length}</div>
                            <div class="modal-stat-label">Repair comment records</div>
                        </section>
                    </div>
                </div>
            `;
        } catch(e) {
            container.innerHTML = '<div class="text-center text-secondary py-5">Unable to load profile overview.</div>';
        }
    }

    async function loadStudentProfileDetail() {
        const container = document.getElementById('profile-pane-content');
        const student = allStudents.find(s => s.id == activeStudentId);
        if(!container || !student) return;

        try {
            const [curriculumRes, progressRes, attendanceRes, realRepairRes] = await Promise.all([
                fetch(`api/get_curriculum.php?v=${cacheVersion}`),
                fetch(`api/get_student_progress.php?student_id=${activeStudentId}&v=${cacheVersion}`),
                fetch(`api/get_attendance.php?student_id=${activeStudentId}&v=${cacheVersion}`),
                fetch(`api/get_real_world_repairs.php?student_id=${activeStudentId}&v=${cacheVersion}`)
            ]);
            const curriculum = await curriculumRes.json();
            const progress = await progressRes.json();
            const attendance = await attendanceRes.json();
            const realRepair = await realRepairRes.json();

            const modules = curriculum.status === 'success' ? curriculum.data : [];
            const completed = progress.status === 'success' ? progress.data : [];
            const completedMap = {};
            completed.forEach(row => completedMap[row.item_id] = row);

            const courseModules = modules.filter(item => item.type === 'Course');
            const practicalModules = modules.filter(item => item.type === 'Practical' && !`${item.category} ${item.title}`.toLowerCase().includes('live repair'));
            const courseDone = courseModules.filter(item => completedMap[item.id]);
            const practicalDone = practicalModules.filter(item => completedMap[item.id]);
            const attendanceRows = attendance.status === 'success' ? attendance.data : [];
            const realRepairRows = realRepair.status === 'success' ? realRepair.data : [];

            const renderMiniMarks = (allRows, emptyText) => allRows.length
                ? allRows.map(item => {
                    const isDone = !!completedMap[item.id];
                    if (isDone) {
                        return `<span class="profile-mini-mark"><span class="material-symbols-rounded">check_circle</span>${escapeHtml(item.title)}</span>`;
                    } else {
                        return `<span class="profile-mini-mark" style="color:var(--text-secondary); opacity:0.6;"><span class="material-symbols-rounded" style="color:var(--text-secondary);">radio_button_unchecked</span>${escapeHtml(item.title)}</span>`;
                    }
                }).join('')
                : `<span class="profile-mini-empty">${emptyText}</span>`;

            const renderRepairComments = () => realRepairRows.length
                ? realRepairRows.map(row => `
                    <div class="profile-repair-comment">
                        <div class="profile-repair-title">${escapeHtml(row.repair_title || 'Repair Comment')}</div>
                        <div class="profile-repair-meta">${new Date(row.created_at).toLocaleDateString([], { month: 'short', day: 'numeric' })} • ${escapeHtml(row.trainer_name || 'Instructor')}</div>
                        <p>${escapeHtml(row.comment || '-')}</p>
                    </div>
                `).join('')
                : '<span class="profile-mini-empty">No repair comments yet</span>';

            const renderAttendanceCalendar = () => {
                const now = new Date();
                const year = now.getFullYear();
                const month = now.getMonth();
                const byDay = new Map();
                attendanceRows.forEach(row => {
                    const d = new Date(row.created_at);
                    if(d.getFullYear() === year && d.getMonth() === month) byDay.set(d.getDate(), row.status);
                });
                const firstDay = new Date(year, month, 1).getDay();
                const daysInMonth = new Date(year, month + 1, 0).getDate();
                let days = '';
                for(let i = 0; i < firstDay; i++) days += '<div class="profile-attendance-day muted"></div>';
                for(let day = 1; day <= daysInMonth; day++) {
                    const date = new Date(year, month, day);
                    const scheduled = getScheduledAttendanceStatus(date, byDay.get(day) || '', student);
                    const status = scheduled.status;
                    const todayClass = day === now.getDate() ? ' today' : '';
                    const offClass = scheduled.off ? ' off' : '';
                    const statusClass = status ? ` ${status.toLowerCase()}` : '';
                    const title = status || (scheduled.off ? 'Off day' : 'No record');
                    days += `<div class="profile-attendance-day${todayClass}${offClass}${statusClass}" title="${title}"><span>${day}</span></div>`;
                }
                return `
                    <div class="profile-attendance-month">${now.toLocaleDateString([], { month: 'long', year: 'numeric' })}</div>
                    <div class="profile-attendance-weekdays"><span>Sun</span><span>Mon</span><span>Tue</span><span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span></div>
                    <div class="profile-attendance-grid">${days}</div>
                    <div class="rollcall-calendar-legend profile-attendance-legend">
                        <span><i class="present"></i>Present</span>
                        <span><i class="late"></i>Late</span>
                        <span><i class="absent"></i>Absent</span>
                    </div>
                `;
            };

            container.innerHTML = `
                <div class="profile-detail-shell">
                    <div class="profile-mark-columns">
                        <section class="profile-detail-panel profile-detail-column">
                            <div class="profile-mark-primary">
                                <div class="profile-detail-panel-head">
                                    <span class="material-symbols-rounded">menu_book</span>
                                    <h3>Course Marks</h3>
                                    <b>${courseDone.length}/${courseModules.length}</b>
                                </div>
                                <div class="profile-mini-mark-list">${renderMiniMarks(courseModules, 'No course modules defined')}</div>
                            </div>
                            <div class="profile-repair-section">
                                <div class="profile-detail-panel-head compact">
                                    <span class="material-symbols-rounded">rate_review</span>
                                    <h3>Repair Comments</h3>
                                    <b>${realRepairRows.length}</b>
                                </div>
                                <div class="profile-repair-list">${renderRepairComments()}</div>
                            </div>
                        </section>

                        <section class="profile-detail-panel profile-detail-column">
                            <div class="profile-mark-primary">
                                <div class="profile-detail-panel-head">
                                    <span class="material-symbols-rounded">construction</span>
                                    <h3>Practical Marks</h3>
                                    <b>${practicalDone.length}/${practicalModules.length}</b>
                                </div>
                                <div class="profile-mini-mark-list">${renderMiniMarks(practicalModules, 'No practical modules defined')}</div>
                            </div>
                            <div class="profile-repair-section profile-attendance-section">
                                <div class="profile-detail-panel-head compact">
                                    <span class="material-symbols-rounded">calendar_month</span>
                                    <h3>Attendance Calendar</h3>
                                    <b>${attendanceRows.length}</b>
                                </div>
                                <div class="profile-attendance-calendar">${renderAttendanceCalendar()}</div>
                            </div>
                        </section>
                    </div>
                </div>
            `;
        } catch(e) {
            container.innerHTML = '<div class="text-center text-secondary py-5">Unable to load student detail.</div>';
        }
    }
    
    async function loadDashboardStats() {
        document.getElementById('stat-total-students').innerText = getActiveStudents().length;
        const res = await fetch(`api/get_curriculum.php?v=${cacheVersion}`); const result = await res.json();
        if (result.status === 'success') document.getElementById('stat-total-modules').innerText = result.data.length;
    }

    async function loadHistoryLogs() {
        const container = document.getElementById('profile-pane-content');
        const res = await fetch(`api/get_history.php?student_id=${activeStudentId}&v=${cacheVersion}`); const result = await res.json();
        if(result.status === 'success' && result.data.length > 0) {
            let html = '<div class="timeline-container">';
            result.data.forEach(log => {
                let dateStr = new Date(log.created_at).toLocaleString([], { dateStyle: 'short', timeStyle: 'short' });
                html += `<div class="timeline-record"><div class="timeline-dot ${log.status === 'Completed' ? '' : 'absent'}"></div><div class="fw-bold" style="font-size:15px;">${log.title} <span class="text-secondary font-monospace" style="font-size:11px;">[${log.status}]</span></div><div class="text-secondary mb-1" style="font-size:12px;">${dateStr}  •  ${log.type}  •  Instructor: ${log.trainer_name || 'System'}</div>${log.comment ? `<div class="student-profile-panel" style="padding:12px; box-shadow:none; margin-top:8px;">"${log.comment}"</div>` : ''}</div>`;
            });
            container.innerHTML = html + '</div>';
        } else { container.innerHTML = '<div class="text-center text-secondary py-5">No log entries found.</div>'; }
    }

    let profileRepairLogs = [];
    let profileRepairCalendarDate = new Date();
    let profileRepairSelectedDate = null;

    function changeProfileRepairMonth(delta) {
        profileRepairCalendarDate.setMonth(profileRepairCalendarDate.getMonth() + delta);
        renderProfileRealWorldExperience();
    }
    
    function filterProfileRepairByDate(dateStr) {
        profileRepairSelectedDate = dateStr;
        renderProfileRealWorldExperience();
    }

    async function loadRealWorldExperience() {
        const container = document.getElementById('profile-pane-content');
        if(!container || !activeStudentId) return;
        container.innerHTML = '<div class="text-center py-5"><div class="spinner-border spinner-border-sm text-secondary"></div></div>';

        try {
            const res = await fetch(`api/get_real_world_repairs.php?student_id=${activeStudentId}&v=${cacheVersion}`);
            const result = await res.json();
            profileRepairLogs = result.status === 'success' ? result.data : [];
            profileRepairCalendarDate = new Date();
            profileRepairSelectedDate = null;
            renderProfileRealWorldExperience();
        } catch(e) {
            container.innerHTML = '<div class="text-center text-secondary py-5">Unable to load live repair sessions.</div>';
        }
    }

    function renderProfileRealWorldExperience() {
        const container = document.getElementById('profile-pane-content');
        if(!container) return;
        
        const year = profileRepairCalendarDate.getFullYear();
        const month = profileRepairCalendarDate.getMonth();

        const filteredRows = profileRepairLogs.filter(row => {
            if (!profileRepairSelectedDate) return true;
            const rowDate = new Date(row.created_at);
            const filterDate = new Date(profileRepairSelectedDate);
            return rowDate.getFullYear() === filterDate.getFullYear() && 
                   rowDate.getMonth() === filterDate.getMonth() && 
                   rowDate.getDate() === filterDate.getDate();
        });

        let commentsHtml = filteredRows.length > 0 ? filteredRows.map(row => `
            <div class="timeline-record">
                <div class="timeline-dot"></div>
                <div class="fw-bold" style="font-size:15px;">${escapeHtml(row.repair_title)}</div>
                <div class="text-secondary mb-2" style="font-size:12px;">${new Date(row.created_at).toLocaleString([], { dateStyle: 'short', timeStyle: 'short' })}  •  ${escapeHtml(row.trainer_name || 'Instructor')}</div>
                <div class="student-profile-panel" style="padding:12px; box-shadow:none;">${escapeHtml(row.comment)}</div>
            </div>
        `).join('') : `<div class="text-center text-secondary py-4">${profileRepairSelectedDate ? 'No repair comments for this date.' : 'No live repair session comments found.'}</div>`;

        const firstDay = new Date(year, month, 1).getDay();
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        
        let calHtml = '';
        for(let i = 0; i < firstDay; i++) calHtml += '<div class="rollcall-calendar-day muted" style="min-height: 36px; cursor: default;"></div>';
        
        const datesWithRecords = new Set();
        profileRepairLogs.forEach(r => {
            const d = new Date(r.created_at);
            if(d.getFullYear() === year && d.getMonth() === month) datesWithRecords.add(d.getDate());
        });

        const now = new Date();
        for(let day = 1; day <= daysInMonth; day++) {
            const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
            const isToday = day === now.getDate() && month === now.getMonth() && year === now.getFullYear();
            const hasRecord = datesWithRecords.has(day);
            const isSelected = profileRepairSelectedDate === dateStr;
            
            let classes = 'rollcall-calendar-day';
            let style = 'min-height: 36px; cursor: pointer; transition: all 0.2s;';
            
            if (isSelected) {
                style += ' background: var(--system-blue); color: white; border-radius: 8px; font-weight: 600; box-shadow: 0 4px 12px rgba(10,132,255,0.3);';
            } else if (isToday) {
                style += ' border: 1px solid var(--system-blue); color: var(--system-blue); border-radius: 8px; font-weight: 600;';
            } else if (hasRecord) {
                style += ' background: rgba(10,132,255,0.1); color: var(--system-blue); border-radius: 8px; font-weight: 600;';
            } else {
                style += ' border-radius: 8px;';
            }

            calHtml += `
                <div class="${classes}" style="${style}" onclick="filterProfileRepairByDate('${dateStr}')" 
                     onmouseover="if(!${isSelected}) this.style.background='var(--hover-bg)'"
                     onmouseout="if(!${isSelected}) this.style.background='${hasRecord ? 'rgba(10,132,255,0.1)' : 'transparent'}'">
                    <span>${day}</span>
                    ${hasRecord && !isSelected ? '<div style="width: 4px; height: 4px; background: var(--system-blue); border-radius: 50%; margin-top: 2px;"></div>' : ''}
                </div>`;
        }

        container.innerHTML = `
            <div class="grid grid-cols-1 xl:grid-cols-[minmax(0,1fr)_340px] gap-4">
                <div>
                    <div class="curriculum-category">Live Repair Session Comments</div>
                    <div class="timeline-container">${commentsHtml}</div>
                </div>
                <aside>
                    <div class="student-profile-panel sticky top-24" style="padding: 16px;">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <button class="btn btn-icon text-secondary p-0" onclick="changeProfileRepairMonth(-1)"><span class="material-symbols-rounded">chevron_left</span></button>
                            <div class="fw-bold" style="font-size: 15px;">${profileRepairCalendarDate.toLocaleDateString([], { month: 'long', year: 'numeric' })}</div>
                            <button class="btn btn-icon text-secondary p-0" onclick="changeProfileRepairMonth(1)"><span class="material-symbols-rounded">chevron_right</span></button>
                        </div>
                        <div class="rollcall-calendar-weekdays mb-2" style="font-size: 12px; color: var(--text-secondary);">
                            <span>S</span><span>M</span><span>T</span><span>W</span><span>T</span><span>F</span><span>S</span>
                        </div>
                        <div class="rollcall-calendar-grid gap-1">
                            ${calHtml}
                        </div>
                        ${profileRepairSelectedDate ? `<div class="mt-3 text-center"><button class="btn btn-secondary btn-sm rounded-pill px-3" onclick="filterProfileRepairByDate(null)">Clear Filter</button></div>` : ''}
                    </div>
                </aside>
            </div>
        `;
    }

    async function loadRollCallProfile() {
        const container = document.getElementById('profile-pane-content');
        if(!container || !activeStudentId) return;
        try {
            const res = await fetch(`api/get_attendance.php?student_id=${activeStudentId}&v=${cacheVersion}`);
            const result = await res.json();
            const rows = result.status === 'success' ? result.data : [];
            const counts = getScheduledAttendanceCounts(rows, allStudents.find(s => String(s.id) === String(activeStudentId)));

            const listHtml = rows.length > 0 ? rows.map(log => {
                const color = log.status === 'Present' ? 'var(--system-green)' : (log.status === 'Late' ? 'var(--system-orange)' : 'var(--system-red)');
                return `
                    <div class="ios-list-item">
                        <div>
                            <span class="material-symbols-rounded" style="font-size:16px; vertical-align:middle; color:${color};">fiber_manual_record</span>
                            <span class="fw-bold" style="font-size:15px; margin-left:8px;">${new Date(log.created_at).toLocaleDateString([], { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' })}</span>
                        </div>
                        <div class="badge" style="background-color: transparent; border: 1px solid ${color}; color: ${color}; width: 72px; text-align: center; font-size: 12px; display: inline-block; padding: 4px 0; font-weight: 600;">${log.status}</div>
                    </div>
                `;
            }).join('') : '<div class="text-center text-secondary py-5">No roll call records found.</div>';

            container.innerHTML = `
                <div class="student-profile-grid">
                    <div class="student-profile-panel"><div class="curriculum-category m-0 mb-2">Present</div><h3 class="fw-bold m-0" style="color:var(--system-green);">${counts.Present}</h3></div>
                    <div class="student-profile-panel"><div class="curriculum-category m-0 mb-2">Late</div><h3 class="fw-bold m-0" style="color:var(--system-orange);">${counts.Late}</h3></div>
                    <div class="student-profile-panel"><div class="curriculum-category m-0 mb-2">Absent</div><h3 class="fw-bold m-0" style="color:var(--system-red);">${counts.Absent}</h3></div>
                </div>
                <div class="curriculum-category">Roll Call History</div>
                <div class="ios-list">${listHtml}</div>
            `;
        } catch(e) {
            container.innerHTML = '<div class="text-center text-secondary py-5">Unable to load roll call records.</div>';
        }
    }

    let profileAttendanceLogs = [];
    let profileAttendanceCalendarDate = new Date();

    function changeProfileAttendanceMonth(delta) {
        profileAttendanceCalendarDate.setMonth(profileAttendanceCalendarDate.getMonth() + delta);
        renderProfileAttendance();
    }

    async function loadAttendanceLogs() {
        const container = document.getElementById('profile-pane-content');
        if(!activeStudentId) return;
        container.innerHTML = '<div class="text-center py-5"><div class="spinner-border spinner-border-sm text-secondary"></div></div>';
        try {
            const res = await fetch(`api/get_attendance.php?student_id=${activeStudentId}&v=${cacheVersion}`);
            const result = await res.json();
            profileAttendanceLogs = result.status === 'success' ? result.data : [];
            profileAttendanceCalendarDate = new Date();
            renderProfileAttendance();
        } catch(e) {
            container.innerHTML = '<div class="text-center text-secondary py-5">Unable to load attendance records.</div>';
        }
    }

    function renderProfileAttendance() {
        const container = document.getElementById('profile-pane-content');
        if(!container) return;

        const student = allStudents.find(s => String(s.id) === String(activeStudentId));
        if(!student) return;

        const year = profileAttendanceCalendarDate.getFullYear();
        const month = profileAttendanceCalendarDate.getMonth();

        const byDay = new Map();
        profileAttendanceLogs.forEach(row => {
            const d = new Date(row.created_at);
            if(d.getFullYear() === year && d.getMonth() === month) byDay.set(d.getDate(), row);
        });

        const firstDay = new Date(year, month, 1).getDay();
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        
        let daysHtml = '';
        for(let i = 0; i < firstDay; i++) {
            daysHtml += '<div class="profile-attendance-day muted"></div>';
        }

        const now = new Date();
        for(let day = 1; day <= daysInMonth; day++) {
            const date = new Date(year, month, day);
            const row = byDay.get(day);
            const scheduled = getScheduledAttendanceStatus(date, row ? row.status : '', student);
            const status = scheduled.status;
            
            const isToday = day === now.getDate() && month === now.getMonth() && year === now.getFullYear();
            const todayClass = isToday ? ' today' : '';
            const offClass = scheduled.off ? ' off' : '';
            const statusClass = status ? ` ${status.toLowerCase()}` : '';
            const title = status || (scheduled.off ? 'Off day' : 'No record');
            
            daysHtml += `
                <div class="profile-attendance-day${todayClass}${offClass}${statusClass}" title="${title}">
                    <span>${day}</span>
                </div>
            `;
        }

        const counts = getScheduledAttendanceCounts(profileAttendanceLogs, student);

        container.innerHTML = `
            <div class="grid grid-cols-1 xl:grid-cols-[minmax(0,1fr)_340px] gap-4">
                <div>
                    <div class="student-profile-grid mb-4">
                        <div class="student-profile-panel"><div class="curriculum-category m-0 mb-2">Present</div><h3 class="fw-bold m-0" style="color:var(--system-green);">${counts.Present}</h3></div>
                        <div class="student-profile-panel"><div class="curriculum-category m-0 mb-2">Late</div><h3 class="fw-bold m-0" style="color:var(--system-orange);">${counts.Late}</h3></div>
                        <div class="student-profile-panel"><div class="curriculum-category m-0 mb-2">Absent</div><h3 class="fw-bold m-0" style="color:var(--system-red);">${counts.Absent}</h3></div>
                    </div>
                    
                    <div class="student-profile-panel">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <button class="btn btn-icon text-secondary p-0" onclick="changeProfileAttendanceMonth(-1)"><span class="material-symbols-rounded">chevron_left</span></button>
                            <h3 class="fw-bold m-0" style="font-size: 18px;">${profileAttendanceCalendarDate.toLocaleDateString([], { month: 'long', year: 'numeric' })}</h3>
                            <button class="btn btn-icon text-secondary p-0" onclick="changeProfileAttendanceMonth(1)"><span class="material-symbols-rounded">chevron_right</span></button>
                        </div>
                        <div class="profile-attendance-weekdays mt-3"><span>Sun</span><span>Mon</span><span>Tue</span><span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span></div>
                        <div class="profile-attendance-grid mb-4">${daysHtml}</div>
                        <div class="rollcall-calendar-legend profile-attendance-legend mt-2 border-top pt-3">
                            <span><i class="present"></i>Present</span>
                            <span><i class="late"></i>Late</span>
                            <span><i class="absent"></i>Absent</span>
                        </div>
                    </div>
                </div>
                <aside>
                    <div class="curriculum-category">Permanent Attendance Record</div>
                    <div class="ios-list" style="max-height: 500px; overflow-y: auto;">
                        ${profileAttendanceLogs.length > 0 ? profileAttendanceLogs.map(log => {
                            let dateStr = new Date(log.created_at).toLocaleDateString([], { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' });
                            let color = log.status === 'Present' ? 'var(--system-green)' : (log.status === 'Late' ? 'var(--system-orange)' : 'var(--system-red)');
                            return `<div class="ios-list-item d-flex justify-content-between align-items-center"><div><span class="material-symbols-rounded" style="font-size:16px; vertical-align:middle; color:${color};">fiber_manual_record</span> <span class="fw-bold" style="font-size:15px; margin-left:8px;">${dateStr}</span></div><div class="badge" style="background-color: transparent; border: 1px solid ${color}; color: ${color}; width: 72px; text-align: center; font-size: 12px; display: inline-block; padding: 4px 0; font-weight: 600;">${log.status}</div></div>`;
                        }).join('') : '<div class="text-center text-secondary py-4">No records found</div>'}
                    </div>
                </aside>
            </div>
        `;
    }

    async function loadAppSettings() {
        try {
            const res = await fetch(`api/get_settings.php?v=${cacheVersion}`);
            const result = await res.json();
            if(result.status === 'success' && result.data) appSettings = { ...appSettings, ...result.data };
        } catch(e) {
            console.error('Settings load error:', e);
        }
    }

    function getRollCallSchedule(studentOrGroup = 'Weekday') {
        const group = typeof studentOrGroup === 'string' ? studentOrGroup : getRollCallGroup(studentOrGroup);
        const schedules = appSettings.rollcall_schedules || {};
        const fallback = group === 'Weekend'
            ? { days: [6, 0], start_time: '10:00', end_time: '15:00' }
            : (appSettings.rollcall_schedule || { days: [1, 2, 3, 4, 5], start_time: '10:00', end_time: '15:00' });
        const schedule = schedules[group] || fallback;
        return {
            days: Array.isArray(schedule.days) ? schedule.days.map(Number) : fallback.days,
            start_time: schedule.start_time || '10:00',
            end_time: schedule.end_time || '15:00'
        };
    }

    function getScheduledAttendanceStatus(date, status, studentOrGroup = 'Weekday') {
        const schedule = getRollCallSchedule(studentOrGroup);
        const isScheduledDay = schedule.days.includes(date.getDay());
        if(!isScheduledDay) return { status: '', off: true };
        return { status: status || '', off: !isScheduledDay };
    }

    function getScheduledAttendanceCounts(rows, studentOrGroup = 'Weekday') {
        return rows.reduce((counts, row) => {
            const date = new Date(row.created_at);
            const normalized = getScheduledAttendanceStatus(date, row.status, studentOrGroup).status;
            if(normalized && counts[normalized] !== undefined) counts[normalized] += 1;
            return counts;
        }, { Present: 0, Late: 0, Absent: 0 });
    }

    function formatScheduleDays(days) {
        const names = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        return days.map(day => names[Number(day)]).filter(Boolean).join(', ') || 'No active days';
    }

    function updateRollCallGroupSelectLabels() {
        const labels = {
            Weekday: `Weekday Student (${formatScheduleDays(getRollCallSchedule('Weekday').days)})`,
            Weekend: `Weekend Student (${formatScheduleDays(getRollCallSchedule('Weekend').days)})`
        };
        ['new-rollcall-group', 'edit-trainee-rollcall-group'].forEach(id => {
            const select = document.getElementById(id);
            if(!select) return;
            Array.from(select.options).forEach(option => {
                option.textContent = labels[option.value] || `${option.value} Student`;
            });
        });
    }

    function getRollCallWindowState(studentOrGroup = 'Weekday') {
        const schedule = getRollCallSchedule(studentOrGroup);
        const now = new Date();
        const day = now.getDay();
        const hh = String(now.getHours()).padStart(2, '0');
        const mm = String(now.getMinutes()).padStart(2, '0');
        const current = `${hh}:${mm}`;
        const dayAllowed = schedule.days.includes(day);
        const timeAllowed = current >= schedule.start_time && current <= schedule.end_time;
        return {
            open: dayAllowed && timeAllowed,
            dayAllowed,
            timeAllowed,
            current,
            group: typeof studentOrGroup === 'string' ? studentOrGroup : getRollCallGroup(studentOrGroup),
            ...schedule
        };
    }

    function renderRollCallWindowBanner(student = null) {
        const banner = document.getElementById('rollcall-window-banner');
        if(!banner) return;
        const selected = student || allStudents.find(s => String(s.id) === String(rollCallSelectedStudentId)) || null;
        const state = getRollCallWindowState(selected || 'Weekday');
        const label = state.open ? 'Roll call open' : 'Roll call closed';
        const icon = state.open ? 'lock_open' : 'lock';
        banner.className = `rollcall-window-banner ${state.open ? 'open' : 'closed'}`;
        banner.innerHTML = `
            <span class="material-symbols-rounded">${icon}</span>
            <div>
                <strong>${selected ? `${escapeHtml(getRollCallGroupLabel(selected))} • ` : ''}${label}</strong>
                <small>${formatScheduleDays(state.days)} • ${state.start_time} - ${state.end_time} • Current ${state.current}</small>
            </div>
        `;
    }

    function renderRollCallSettings() {
        updateRollCallGroupSelectLabels();
        ['Weekday', 'Weekend'].forEach(group => {
            const key = group.toLowerCase();
            const schedule = getRollCallSchedule(group);
            document.querySelectorAll(`#rollcall-settings-days-${key} input[type="checkbox"]`).forEach(input => {
                input.checked = schedule.days.includes(Number(input.value));
            });
            const start = document.getElementById(`rollcall-start-time-${key}`);
            const end = document.getElementById(`rollcall-end-time-${key}`);
            if(start) start.value = schedule.start_time;
            if(end) end.value = schedule.end_time;
            const activeDays = document.getElementById(`rollcall-${key}-active-days`);
            if(activeDays) activeDays.innerText = `(${formatScheduleDays(schedule.days)})`;
        });

        const preview = document.getElementById('rollcall-settings-preview');
        if(preview) {
            preview.innerHTML = ['Weekday', 'Weekend'].map(group => {
                const schedule = getRollCallSchedule(group);
                const state = getRollCallWindowState(group);
                return `
                    <div class="rollcall-window-status ${state.open ? 'open' : 'closed'}">
                        <span class="material-symbols-rounded">${group === 'Weekend' ? 'weekend' : 'work'}</span>
                        <div><strong>${group}: ${state.open ? 'Open now' : 'Closed now'}</strong><small>Current time ${state.current}</small></div>
                    </div>
                    <div class="settings-summary-row"><span>${group} days</span><strong>${formatScheduleDays(schedule.days)}</strong></div>
                    <div class="settings-summary-row"><span>${group} hours</span><strong>${schedule.start_time} - ${schedule.end_time}</strong></div>
                `;
            }).join('');
        }
    }

    async function saveRollCallSettings(event) {
        event.preventDefault();
        const note = document.getElementById('rollcall-settings-note');
        const schedules = {};
        ['Weekday', 'Weekend'].forEach(group => {
            const key = group.toLowerCase();
            schedules[group] = {
                days: Array.from(document.querySelectorAll(`#rollcall-settings-days-${key} input[type="checkbox"]:checked`)).map(input => Number(input.value)),
                start_time: document.getElementById(`rollcall-start-time-${key}`).value,
                end_time: document.getElementById(`rollcall-end-time-${key}`).value
            };
        });
        const fd = new FormData();
        fd.append('schedules', JSON.stringify(schedules));

        const res = await fetch('api/save_rollcall_settings.php', { method: 'POST', body: fd });
        const result = await res.json();
        if(result.status !== 'success') {
            if(note) note.innerText = result.message || 'Unable to save schedule.';
            return;
        }

        appSettings.rollcall_schedules = schedules;
        appSettings.rollcall_schedule = schedules.Weekday;
        if(note) note.innerText = 'Saved.';
        updateRollCallGroupSelectLabels();
        renderRollCallSettings();
        renderRollCallWindowBanner();
        if(document.getElementById('view-rollcall')?.classList.contains('active')) loadLiveRollCallView();
    }

    function loadLiveRollCallView() {
        const dateEl = document.getElementById('rollcall-date-label');
        if(dateEl) dateEl.innerText = new Date().toLocaleDateString([], { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
        const list = document.getElementById('rollcall-live-list'); if(!list) return;
        const activeStudents = getActiveStudents();
        if(activeStudents.length === 0) {
            list.innerHTML = '<div class="ios-list-item text-secondary py-4 justify-content-center">No active trainees enrolled.</div>';
            updateRollCallSummary();
            renderRollCallCalendarPlaceholder();
            return;
        }
        if(!activeStudents.some(s => String(s.id) === String(rollCallSelectedStudentId))) {
            rollCallSelectedStudentId = activeStudents[0] ? String(activeStudents[0].id) : null;
        }
        const selectedStudent = activeStudents.find(s => String(s.id) === String(rollCallSelectedStudentId));
        renderRollCallWindowBanner(selectedStudent);
        list.innerHTML = activeStudents.map(s => {
            const windowState = getRollCallWindowState(s);
            return `
            <div class="ios-list-item rollcall-row ${String(s.id) === String(rollCallSelectedStudentId) ? 'active' : ''}" id="rollcall-row-${s.id}">
                <div class="rollcall-student" onclick="selectRollCallStudent(${s.id})">
                    ${renderStudentAvatar(s, 'student-avatar', 'width: 42px; height: 42px; font-size: 15px;')}
                    <div style="min-width:0;">
                        <div class="fw-bold rollcall-student-name">${escapeHtml(s.name)}</div>
                        <div class="text-secondary rollcall-student-meta">
                            <span class="material-symbols-rounded">badge</span>
                            ID: #${escapeHtml(s.id)}
                            <span class="rollcall-meta-separator">•</span>
                            <span class="material-symbols-rounded">${getRollCallGroup(s) === 'Weekend' ? 'weekend' : 'work'}</span>
                            ${escapeHtml(getRollCallGroup(s))}
                            <span class="rollcall-meta-separator">•</span>
                            <span class="material-symbols-rounded">call</span>
                            ${escapeHtml(s.phone || 'No phone')}
                        </div>
                    </div>
                </div>
                <div class="rollcall-options" data-student-id="${s.id}">
                    <button type="button" class="rollcall-chip" data-status="Present" onclick="saveLiveRollCallChip(${s.id}, 'Present', this)" ${windowState.open ? '' : 'disabled'} aria-disabled="${windowState.open ? 'false' : 'true'}">
                        <span class="material-symbols-rounded">check_circle</span>
                        Present
                    </button>
                    <button type="button" class="rollcall-chip" data-status="Late" onclick="saveLiveRollCallChip(${s.id}, 'Late', this)" ${windowState.open ? '' : 'disabled'} aria-disabled="${windowState.open ? 'false' : 'true'}">
                        <span class="material-symbols-rounded">schedule</span>
                        Late
                    </button>
                    <button type="button" class="rollcall-chip" data-status="Absent" onclick="saveLiveRollCallChip(${s.id}, 'Absent', this)" ${windowState.open ? '' : 'disabled'} aria-disabled="${windowState.open ? 'false' : 'true'}">
                        <span class="material-symbols-rounded">cancel</span>
                        Absent
                    </button>
                </div>
            </div>
        `}).join('');
        updateRollCallSummary();
        if(rollCallSelectedStudentId) loadRollCallStudentCalendar(rollCallSelectedStudentId);
    }
    
    async function saveLiveRollCallChip(studentId, status, element) {
        const student = allStudents.find(s => String(s.id) === String(studentId));
        if(!getRollCallWindowState(student).open) {
            renderRollCallWindowBanner(student);
            return;
        }
        element.parentElement.querySelectorAll('.rollcall-chip').forEach(c => c.classList.remove('active')); element.classList.add('active');
        updateRollCallSummary();
        const fd = new FormData(); fd.append('student_id', studentId); fd.append('status', status); 
        await fetch('api/save_rollcall.php', { method: 'POST', body: fd });
        cacheVersion = Date.now(); // Bust cache to ensure history logs reflect the rollcall
        if(String(rollCallSelectedStudentId) === String(studentId)) loadRollCallStudentCalendar(studentId);
    }

    function selectRollCallStudent(studentId) {
        rollCallSelectedStudentId = String(studentId);
        document.querySelectorAll('.rollcall-row').forEach(row => row.classList.remove('active'));
        const row = document.getElementById(`rollcall-row-${studentId}`);
        if(row) row.classList.add('active');
        renderRollCallWindowBanner(allStudents.find(s => String(s.id) === String(studentId)));
        loadRollCallStudentCalendar(studentId);
    }

    function renderRollCallCalendarPlaceholder() {
        const empty = document.getElementById('rollcall-calendar-empty');
        const content = document.getElementById('rollcall-calendar-content');
        if(empty) empty.style.display = 'flex';
        if(content) content.style.display = 'none';
    }

    async function loadRollCallStudentCalendar(studentId) {
        const student = allStudents.find(s => String(s.id) === String(studentId));
        if(!student) { renderRollCallCalendarPlaceholder(); return; }

        const empty = document.getElementById('rollcall-calendar-empty');
        const content = document.getElementById('rollcall-calendar-content');
        const avatar = document.getElementById('rollcall-calendar-avatar');
        const name = document.getElementById('rollcall-calendar-name');
        const meta = document.getElementById('rollcall-calendar-meta');
        const grid = document.getElementById('rollcall-calendar-grid');
        if(empty) empty.style.display = 'none';
        if(content) content.style.display = 'block';
        if(avatar) avatar.innerHTML = renderStudentAvatar(student, 'student-avatar', 'width: 44px; height: 44px; font-size: 15px; border-radius:50% !important;');
        if(name) name.innerText = student.name || 'Student';
        if(meta) meta.innerText = `ID: #${student.id}  •  ${formatPrivacy(student.phone, 'No phone')}`;
        if(grid) grid.innerHTML = '<div class="text-secondary py-4 text-center" style="grid-column:1/-1;">Loading history...</div>';

        try {
            const [attendanceRes, examRes] = await Promise.all([
                fetch(`api/get_attendance.php?student_id=${studentId}&v=${cacheVersion}`),
                fetch(`api/get_student_exam.php?student_id=${studentId}&v=${cacheVersion}`)
            ]);
            const result = await attendanceRes.json();
            const examResult = await examRes.json();
            const rows = result.status === 'success' ? result.data : [];
            const exam = examResult.status === 'success' ? examResult.data : null;
            renderRollCallCalendar(student, rows, exam);
        } catch(e) {
            if(grid) grid.innerHTML = '<div class="text-secondary py-4 text-center" style="grid-column:1/-1;">Unable to load attendance.</div>';
        }
    }

    let rollCallCalendarDate = new Date();

    function changeRollCallMonth(delta) {
        rollCallCalendarDate.setMonth(rollCallCalendarDate.getMonth() + delta);
        if(rollCallSelectedStudentId) {
            loadRollCallStudentCalendar(rollCallSelectedStudentId);
        }
    }

    function renderRollCallCalendar(student, rows, exam = null) {
        const now = new Date();
        const year = rollCallCalendarDate.getFullYear();
        const month = rollCallCalendarDate.getMonth();
        const monthLabel = document.getElementById('rollcall-calendar-month');
        const grid = document.getElementById('rollcall-calendar-grid');
        if(monthLabel) monthLabel.innerText = rollCallCalendarDate.toLocaleDateString([], { month: 'long', year: 'numeric' });
        if(!grid) return;

        const byDay = new Map();
        rows.forEach(row => {
            const d = new Date(row.created_at);
            if(d.getFullYear() === year && d.getMonth() === month) byDay.set(d.getDate(), row.status);
        });

        const counts = getScheduledAttendanceCounts(rows, student);
        const present = counts.Present;
        const late = counts.Late;
        const absent = counts.Absent;
        const attended = present + late;
        const scheduledTotal = present + late + absent;
        const rate = scheduledTotal > 0 ? Math.round((attended / scheduledTotal) * 100) : 0;
        const latest = rows[0] ? `${rows[0].status}` : '-';
        const presentEl = document.getElementById('rollcall-history-present');
        const lateEl = document.getElementById('rollcall-history-late');
        const absentEl = document.getElementById('rollcall-history-absent');
        if(presentEl) presentEl.innerText = present;
        if(lateEl) lateEl.innerText = late;
        if(absentEl) absentEl.innerText = absent;
        const rateEl = document.getElementById('rollcall-attendance-rate');
        const attendedEl = document.getElementById('rollcall-attended-days');
        const latestEl = document.getElementById('rollcall-latest-status');
        if(rateEl) rateEl.innerText = `${rate}%`;
        if(attendedEl) attendedEl.innerText = attended;
        if(latestEl) latestEl.innerText = latest;

        const firstDay = new Date(year, month, 1).getDay();
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        let html = '';
        for(let i = 0; i < firstDay; i++) html += '<div class="rollcall-calendar-day muted"></div>';
        for(let day = 1; day <= daysInMonth; day++) {
            const date = new Date(year, month, day);
            const scheduled = getScheduledAttendanceStatus(date, byDay.get(day) || '', student);
            const status = scheduled.status;
            const todayClass = day === now.getDate() ? ' today' : '';
            const offClass = scheduled.off ? ' off' : '';
            const statusClass = status ? ` ${status.toLowerCase()}` : '';
            const title = status || (scheduled.off ? 'Off day' : 'No record');
            html += `
                <div class="rollcall-calendar-day${todayClass}${offClass}${statusClass}" title="${title}">
                    <span>${day}</span>
                </div>`;
        }
        grid.innerHTML = html;
    }

    async function loadStudentExam() {
        const container = document.getElementById('profile-pane-content');
        if(!activeStudentId || !container) return;
        
        try {
            const examRes = await fetch(`api/get_student_exam.php?student_id=${activeStudentId}&v=${cacheVersion}`);
            const result = await examRes.json();
            const exam = result.status === 'success' ? result.data : null;
            
            const scoreValue = exam ? Number(exam.score || 0) : 0;
            const maxValue = exam ? Number(exam.max_score || 100) : 100;
            const percentStr = maxValue > 0 ? Math.round((scoreValue / maxValue) * 100) + '%' : '0%';
            const updatedText = exam ? `Updated ${new Date(exam.updated_at || exam.created_at).toLocaleDateString([], { month: 'short', day: 'numeric' })}` : 'No exam score yet';
            
            container.innerHTML = `
                <form class="rollcall-exam-card" style="margin: 0;" onsubmit="saveProfileExam(event)">
                    <input type="hidden" id="profile-exam-student-id" value="${activeStudentId}">
                    <div class="rollcall-exam-head">
                        <span class="material-symbols-rounded">assignment</span>
                        <div>
                            <strong>Exam Data</strong>
                            <small>Score for selected student</small>
                        </div>
                    </div>
                    <label>
                        <span>Exam Name</span>
                        <input type="text" id="profile-exam-name" class="apple-input" value="${escapeHtml(exam?.exam_name || 'Final Exam')}">
                    </label>
                    <div class="rollcall-exam-score-grid">
                        <label>
                            <span>Score</span>
                            <input type="number" min="0" step="0.5" id="profile-exam-score" class="apple-input" value="${scoreValue}">
                        </label>
                        <label>
                            <span>Max</span>
                            <input type="number" min="1" step="0.5" id="profile-exam-max" class="apple-input" value="${maxValue}">
                        </label>
                    </div>
                    <label>
                        <span>Exam Date</span>
                        <input type="date" id="profile-exam-date" class="apple-input" value="${exam?.exam_date || ''}">
                    </label>
                    <label>
                        <span>Note</span>
                        <textarea id="profile-exam-note" class="apple-input" rows="2" style="resize:none;">${escapeHtml(exam?.note || '')}</textarea>
                    </label>
                    <div class="rollcall-exam-result">
                        <div>
                            <strong id="profile-exam-percent">${percentStr}</strong>
                            <small>Exam result</small>
                        </div>
                        <button type="submit" class="btn-premium btn-icon-label">
                            <span class="material-symbols-rounded">save</span>
                            Save
                        </button>
                    </div>
                    <div id="profile-exam-save-note" class="settings-save-note">${updatedText}</div>
                </form>
            `;

            const scoreInp = document.getElementById('profile-exam-score');
            const maxInp = document.getElementById('profile-exam-max');
            const pctEl = document.getElementById('profile-exam-percent');
            const calc = () => {
                const s = Number(scoreInp.value) || 0;
                const m = Number(maxInp.value) || 100;
                pctEl.innerText = m > 0 ? Math.round((s / m) * 100) + '%' : '0%';
            };
            if(scoreInp) scoreInp.addEventListener('input', calc);
            if(maxInp) maxInp.addEventListener('input', calc);

        } catch(e) {
            container.innerHTML = '<div class="text-center text-secondary py-5">Unable to load exam data.</div>';
        }
    }

    async function saveProfileExam(event) {
        event.preventDefault();
        const note = document.getElementById('profile-exam-save-note');
        const fd = new FormData();
        fd.append('student_id', document.getElementById('profile-exam-student-id').value);
        fd.append('exam_name', document.getElementById('profile-exam-name').value);
        fd.append('score', document.getElementById('profile-exam-score').value || '0');
        fd.append('max_score', document.getElementById('profile-exam-max').value || '100');
        fd.append('exam_date', document.getElementById('profile-exam-date').value);
        fd.append('note', document.getElementById('profile-exam-note').value);
        if(note) note.innerText = 'Saving...';
        const res = await fetch('api/save_student_exam.php', { method: 'POST', body: fd });
        const result = await res.json();
        if(result.status !== 'success') {
            if(note) note.innerText = result.message || 'Unable to save exam.';
            return;
        }
        cacheVersion = Date.now();
        if(note) note.innerText = 'Saved Successfully';
    }

    function updateRollCallSummary() {
        const total = document.querySelectorAll('#rollcall-live-list .rollcall-row').length;
        const present = document.querySelectorAll('#rollcall-live-list .rollcall-chip[data-status="Present"].active').length;
        const late = document.querySelectorAll('#rollcall-live-list .rollcall-chip[data-status="Late"].active').length;
        const absent = document.querySelectorAll('#rollcall-live-list .rollcall-chip[data-status="Absent"].active').length;
        const totalEl = document.getElementById('rollcall-total-count');
        const presentEl = document.getElementById('rollcall-present-count');
        const lateEl = document.getElementById('rollcall-late-count');
        const absentEl = document.getElementById('rollcall-absent-count');
        if(totalEl) totalEl.innerText = total;
        if(presentEl) presentEl.innerText = present;
        if(lateEl) lateEl.innerText = late;
        if(absentEl) absentEl.innerText = absent;
    }

    // --- ADMIN PANEL FUNCTIONS (Speed Optimized) ---
    let allSystemUsers = [];
    async function loadSystemUsers() {
        const container = document.getElementById('admin-users-list'); if(!container) return;
        try {
            const res = await fetch(`api/get_users.php?v=${cacheVersion}`); const result = await res.json();
            if (result.status === 'success') {
                allSystemUsers = result.data; let html = '';
                allSystemUsers.forEach(u => {
                    html += `
                        <div class="ios-list-item">
                            <div class="d-flex align-items-center gap-3">
                                <div>
                                    <div class="fw-bold" style="font-size: 15px;">${escapeHtml(u.name)}</div>
                                    <div class="text-secondary" style="font-size: 12px;">${escapeHtml(u.email)}</div>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <div class="badge" style="background-color: transparent; border: 1px solid var(--system-blue); color: var(--system-blue); font-size: 12px;">${u.role}</div>
                            </div>
                        </div>
                    `;
                });
                container.innerHTML = html || '<div class="text-center text-secondary py-5">No users found.</div>';
            } else { container.innerHTML = '<div class="text-center text-secondary py-5">Failed to load users.</div>'; }
        } catch(e) { container.innerHTML = '<div class="text-center text-secondary py-5">Error loading users.</div>'; }
    }

    function openUserModal() {
        const modal = new bootstrap.Modal(document.getElementById('userModal'));
        document.getElementById('user-form').reset();
        modal.show();
    }

    async function loadAdminTrainers() {
        const container = document.getElementById('admin-trainers-list'); if(!container) return;
        try {
            const res = await fetch(`api/get_trainers.php?v=${cacheVersion}`); const result = await res.json();
            if (result.status === 'success') {
                allTrainers = result.data; let html = '';
                allTrainers.forEach(t => {
                    html += `
                        <div class="ios-list-item">
                            <div class="d-flex align-items-center gap-3">
                                ${renderTrainerAvatar(t, 'student-avatar', 'width: 36px; height: 36px; font-size: 14px;')}
                                <div>
                                    <div class="fw-bold" style="font-size: 15px;">${escapeHtml(t.name || 'Instructor')}</div>
                                    <div class="text-secondary" style="font-size: 12px;">${escapeHtml(t.role || 'Instructor')}</div>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <button class="icon-action-btn" title="Edit instructor" aria-label="Edit instructor" onclick="openTrainerModal(${Number(t.id)})">
                                    <span class="material-symbols-rounded">edit</span>
                                </button>
                                <button class="icon-action-btn danger" title="Delete instructor" aria-label="Delete instructor" onclick="deleteTrainer(${Number(t.id)})">
                                    <span class="material-symbols-rounded">delete</span>
                                </button>
                            </div>
                        </div>`;
                });
                container.innerHTML = html;
                const signOffSelect = document.getElementById('sign-off-trainer');
                if(signOffSelect) signOffSelect.innerHTML = allTrainers.map(tr => `<option value="${escapeHtml(tr.name)}">${escapeHtml(tr.name)}</option>`).join('');
                const moduleMarkSelect = document.getElementById('module-mark-trainer');
                if(moduleMarkSelect) moduleMarkSelect.innerHTML = allTrainers.map(tr => `<option value="${escapeHtml(tr.name)}">${escapeHtml(tr.name)}</option>`).join('');
                const realRepairSelect = document.getElementById('real-repair-trainer');
                if(realRepairSelect) realRepairSelect.innerHTML = allTrainers.map(tr => `<option value="${escapeHtml(tr.name)}">${escapeHtml(tr.name)}</option>`).join('');
            }
        } catch(e){}
    }

    function openTrainerModal(id = '') {
        const trainer = id ? allTrainers.find(t => Number(t.id) === Number(id)) : null;
        const name = trainer ? trainer.name : '';
        const role = trainer ? trainer.role : 'Instructor';
        const photoPath = trainer ? getTrainerPhotoUrl(trainer) : '';
        document.getElementById('trainer-id').value = id; document.getElementById('trainer-name').value = name; document.getElementById('trainer-role').value = role;
        document.getElementById('trainer-photo-path').value = photoPath;
        document.getElementById('trainer-photo-file').value = '';
        const preview = document.getElementById('trainer-photo-preview');
        if(preview) {
            preview.classList.toggle('has-photo', !!photoPath);
            preview.style.backgroundImage = photoPath ? `url('${photoPath}')` : '';
            preview.innerText = photoPath ? '' : (name ? name.charAt(0).toUpperCase() : '?');
        }
        document.getElementById('trainer-delete-btn').style.display = id ? 'inline-flex' : 'none';
        document.getElementById('trainer-modal-title').innerText = id ? "Edit Instructor" : "Add Instructor"; trainerModal.show();
    }

    async function submitTrainer(e) {
        e.preventDefault(); const fd = new FormData(); fd.append('id', document.getElementById('trainer-id').value); fd.append('name', document.getElementById('trainer-name').value); fd.append('role', document.getElementById('trainer-role').value); fd.append('photo_path', document.getElementById('trainer-photo-path').value);
        const res = await fetch('api/save_trainer.php', { method: 'POST', body: fd });
        const result = await res.json();
        if(result.status !== 'success') { alert(result.message || 'Unable to save instructor.'); return; }
        cacheVersion = Date.now();
        trainerModal.hide(); loadAdminTrainers();
    }

    async function uploadTrainerPhotoInput(input) {
        const file = input.files && input.files[0];
        if(!file) return;
        const row = input.closest('.profile-upload-row');
        if(row) row.classList.add('uploading');
        try {
            const fd = new FormData();
            fd.append('photo', file);
            const trainerId = document.getElementById('trainer-id').value;
            if(trainerId) fd.append('trainer_id', trainerId);
            const res = await fetch('api/upload_trainer_photo.php', { method: 'POST', body: fd });
            const result = await res.json();
            input.value = '';
            if(result.status !== 'success') { alert(result.message || 'Unable to upload photo.'); return; }
            document.getElementById('trainer-photo-path').value = result.photo_path;
            const preview = document.getElementById('trainer-photo-preview');
            if(preview) {
                preview.classList.add('has-photo');
                preview.style.backgroundImage = `url('${result.photo_path}')`;
                preview.innerText = '';
            }
            cacheVersion = Date.now();
            if(trainerId) loadAdminTrainers();
        } finally {
            if(row) row.classList.remove('uploading');
        }
    }

    async function deleteTrainer(id = null) {
        const trainerId = id || document.getElementById('trainer-id').value;
        if(!trainerId) return;
        const ok = await showConfirmAction({
            title: 'Delete Instructor',
            message: 'Delete this instructor profile? Course history keeps instructor name text.',
            okText: 'Delete',
            icon: 'person_remove'
        });
        if(!ok) return;

        const fd = new FormData();
        fd.append('id', trainerId);
        const res = await fetch('api/delete_trainer.php', { method: 'POST', body: fd });
        const result = await res.json();
        if(result.status !== 'success') { alert(result.message || 'Unable to delete instructor.'); return; }

        cacheVersion = Date.now();
        if(trainerModal) trainerModal.hide();
        loadAdminTrainers();
    }

    function money(value) {
        return Number(value || 0).toLocaleString('en-US', { maximumFractionDigits: 0 });
    }

    function getPaymentState(row) {
        const total = Number(row.total_amount || 0);
        const first = row.first_paid_at ? Number(row.first_amount || 0) : 0;
        const second = row.second_paid_at ? Number(row.second_amount || 0) : 0;
        const paid = first + second;
        const balance = Math.max(total - paid, 0);
        const today = new Date().toISOString().slice(0, 10);
        const reminderDue = !!row.reminder_date && row.reminder_date <= today && balance > 0;
        const status = balance <= 0 && total > 0 ? 'Paid' : (paid > 0 ? 'Partial' : 'Unpaid');
        const next = !row.first_paid_at ? 'First payment' : (!row.second_paid_at ? 'Second payment' : 'Complete');
        return { total, paid, balance, reminderDue, status, next };
    }

    async function loadPaymentsView() {
        const list = document.getElementById('payment-student-list');
        const detail = document.getElementById('payment-detail-panel');
        if(!list || !detail) return;
        list.innerHTML = '<div class="text-secondary py-4 text-center">Loading payments...</div>';
        try {
            const res = await fetch(`api/get_payments.php?v=${cacheVersion}`);
            const result = await res.json();
            if(result.status !== 'success') {
                list.innerHTML = '<div class="text-secondary py-4 text-center">Unable to load payments.</div>';
                return;
            }
            paymentRows = result.data;
            if(!paymentRows.some(row => String(row.student_id) === String(selectedPaymentStudentId))) {
                selectedPaymentStudentId = paymentRows[0] ? String(paymentRows[0].student_id) : null;
            }
            renderPaymentSummary();
            renderPaymentList();
            if(selectedPaymentStudentId) selectPaymentStudent(selectedPaymentStudentId, false);
        } catch(e) {
            list.innerHTML = '<div class="text-secondary py-4 text-center">Unable to load payments.</div>';
        }
    }

    function renderPaymentSummary() {
        const summary = document.getElementById('payment-summary-grid');
        if(!summary) return;
        const totals = paymentRows.reduce((acc, row) => {
            const state = getPaymentState(row);
            acc.total += state.total;
            acc.paid += state.paid;
            acc.balance += state.balance;
            if(state.reminderDue) acc.reminders += 1;
            return acc;
        }, { total: 0, paid: 0, balance: 0, reminders: 0 });
        summary.innerHTML = `
            <div><span class="material-symbols-rounded">request_quote</span><strong>${money(totals.total)}</strong><small>Total</small></div>
            <div><span class="material-symbols-rounded">paid</span><strong>${money(totals.paid)}</strong><small>Paid</small></div>
            <div><span class="material-symbols-rounded">pending_actions</span><strong>${money(totals.balance)}</strong><small>Balance</small></div>
            <div><span class="material-symbols-rounded">notifications_active</span><strong>${totals.reminders}</strong><small>Reminders</small></div>
        `;
    }

    function renderPaymentList() {
        const list = document.getElementById('payment-student-list');
        if(!list) return;
        if(paymentRows.length === 0) {
            list.innerHTML = '<div class="text-secondary py-4 text-center">No students found.</div>';
            return;
        }
        list.innerHTML = paymentRows.map(row => {
            const state = getPaymentState(row);
            const active = String(row.student_id) === String(selectedPaymentStudentId) ? ' active' : '';
            const statusClass = state.status.toLowerCase();
            return `
                <button type="button" class="payment-student-card${active}" onclick="selectPaymentStudent(${Number(row.student_id)})">
                    ${renderStudentAvatar({ name: row.name, photo_path: row.photo_path }, 'student-contact-avatar')}
                    <div class="payment-student-main">
                        <strong>${escapeHtml(row.name)}</strong>
                        <small>ID #${escapeHtml(row.student_id)} • ${escapeHtml(row.phone || 'No phone')}</small>
                        <div class="payment-mini-track"><span style="width:${state.total > 0 ? Math.min(100, Math.round((state.paid / state.total) * 100)) : 0}%;"></span></div>
                    </div>
                    <span class="payment-status ${statusClass}${state.reminderDue ? ' due' : ''}">
                        <span class="material-symbols-rounded">${state.reminderDue ? 'notifications_active' : (state.status === 'Paid' ? 'check_circle' : 'schedule')}</span>
                        ${state.reminderDue ? 'Reminder' : state.status}
                    </span>
                </button>
            `;
        }).join('');
    }

    function selectPaymentStudent(studentId, rerenderList = true) {
        selectedPaymentStudentId = String(studentId);
        const row = paymentRows.find(item => String(item.student_id) === String(studentId));
        const detail = document.getElementById('payment-detail-panel');
        if(!row || !detail) return;
        if(rerenderList) renderPaymentList();
        const state = getPaymentState(row);
        detail.innerHTML = `
            <form class="payment-detail-form" onsubmit="savePayment(event)">
                <input type="hidden" id="payment-student-id" value="${escapeHtml(row.student_id)}">
                <div class="payment-profile-head">
                    ${renderStudentAvatar({ name: row.name, photo_path: row.photo_path }, 'profile-large-avatar', 'width:72px;height:72px;font-size:26px;border-radius:50% !important;')}
                    <div>
                        <h3>${escapeHtml(row.name)}</h3>
                        <small>ID #${escapeHtml(row.student_id)} • ${escapeHtml(row.rollcall_group || 'Weekday')}</small>
                    </div>
                    <span class="payment-status ${state.status.toLowerCase()}${state.reminderDue ? ' due' : ''}">${state.reminderDue ? 'Reminder Due' : state.status}</span>
                </div>

                <div class="payment-total-row">
                    <div><small>Total</small><strong>${money(state.total)}</strong></div>
                    <div><small>Paid</small><strong>${money(state.paid)}</strong></div>
                    <div><small>Balance</small><strong>${money(state.balance)}</strong></div>
                </div>

                <label>Total Course Fee</label>
                <input type="number" min="0" step="1000" id="payment-total-amount" class="apple-input" value="${Number(row.total_amount || 0)}">

                <div class="payment-installment-grid">
                    <section>
                        <div class="payment-installment-title"><span class="material-symbols-rounded">looks_one</span>First Payment</div>
                        <label>Amount</label>
                        <input type="number" min="0" step="1000" id="payment-first-amount" class="apple-input" value="${Number(row.first_amount || 0)}">
                        <label>Paid Date</label>
                        <input type="date" id="payment-first-paid-at" class="apple-input" value="${escapeHtml(row.first_paid_at || '')}">
                    </section>
                    <section>
                        <div class="payment-installment-title"><span class="material-symbols-rounded">looks_two</span>Second Payment</div>
                        <label>Amount</label>
                        <input type="number" min="0" step="1000" id="payment-second-amount" class="apple-input" value="${Number(row.second_amount || 0)}">
                        <label>Paid Date</label>
                        <input type="date" id="payment-second-paid-at" class="apple-input" value="${escapeHtml(row.second_paid_at || '')}">
                    </section>
                </div>

                <label>Reminder Date</label>
                <input type="date" id="payment-reminder-date" class="apple-input" value="${escapeHtml(row.reminder_date || '')}">

                <label>Reminder Note</label>
                <textarea id="payment-note" class="apple-input" rows="3" style="resize:none;">${escapeHtml(row.note || '')}</textarea>

                <div class="d-flex justify-content-end gap-2 mt-3">
                    <button type="button" class="btn btn-dark btn-icon-label" onclick="markPaymentToday('first')"><span class="material-symbols-rounded">looks_one</span>First Paid Today</button>
                    <button type="button" class="btn btn-dark btn-icon-label" onclick="markPaymentToday('second')"><span class="material-symbols-rounded">looks_two</span>Second Paid Today</button>
                    <button type="submit" class="btn-premium btn-icon-label"><span class="material-symbols-rounded">save</span>Save Payment</button>
                </div>
            </form>
        `;
    }

    function markPaymentToday(slot) {
        const today = new Date().toISOString().slice(0, 10);
        const target = slot === 'first' ? 'payment-first-paid-at' : 'payment-second-paid-at';
        const input = document.getElementById(target);
        if(input) input.value = today;
    }

    async function savePayment(event) {
        event.preventDefault();
        const fd = new FormData();
        fd.append('student_id', document.getElementById('payment-student-id').value);
        fd.append('total_amount', document.getElementById('payment-total-amount').value || '0');
        fd.append('first_amount', document.getElementById('payment-first-amount').value || '0');
        fd.append('first_paid_at', document.getElementById('payment-first-paid-at').value);
        fd.append('second_amount', document.getElementById('payment-second-amount').value || '0');
        fd.append('second_paid_at', document.getElementById('payment-second-paid-at').value);
        fd.append('reminder_date', document.getElementById('payment-reminder-date').value);
        fd.append('note', document.getElementById('payment-note').value);
        const res = await fetch('api/save_payment.php', { method: 'POST', body: fd });
        const result = await res.json();
        if(result.status !== 'success') { alert(result.message || 'Unable to save payment.'); return; }
        cacheVersion = Date.now();
        await loadPaymentsView();
    }

    function getStudentCompletionState(student) {
        const courseDone = Number(student.course_completed || 0);
        const courseTotal = Number(student.total_course || 0);
        const practicalDone = Number(student.practical_completed || 0);
        const practicalTotal = Number(student.total_practical || 0);
        const hasCourse = courseTotal > 0;
        const hasPractical = practicalTotal > 0;
        const finished = hasCourse && hasPractical && courseDone >= courseTotal && practicalDone >= practicalTotal;
        return {
            finished,
            coursePct: courseTotal > 0 ? Math.round((courseDone / courseTotal) * 100) : 0,
            practicalPct: practicalTotal > 0 ? Math.round((practicalDone / practicalTotal) * 100) : 0,
            courseText: `${courseDone}/${courseTotal}`,
            practicalText: `${practicalDone}/${practicalTotal}`
        };
    }

    function loadAdminContacts() {
        const list = document.getElementById('admin-contact-list');
        const detail = document.getElementById('admin-contact-detail');
        if(!list || !detail) return;
        if(allStudents.length === 0) {
            list.innerHTML = '<div class="text-secondary py-4 text-center">No students found.</div>';
            detail.innerHTML = '<div class="student-contact-empty"><span class="material-symbols-rounded">contact_page</span><strong>No Students</strong><small>Add trainees in Trainee Controls.</small></div>';
            return;
        }

        if(!allStudents.some(s => String(s.id) === String(adminContactSelectedStudentId))) {
            adminContactSelectedStudentId = String(allStudents[0].id);
        }

        list.innerHTML = allStudents.map(student => {
            const coursePct = student.total_course > 0 ? Math.round((student.course_completed / student.total_course) * 100) : 0;
            const practicalPct = student.total_practical > 0 ? Math.round((student.practical_completed / student.total_practical) * 100) : 0;
            const activeClass = String(adminContactSelectedStudentId) === String(student.id) ? 'active' : '';
            return `
                <div class="student-page-card ${activeClass}" id="admin-contact-card-${student.id}" onclick="selectAdminContactStudent(${student.id})" style="cursor: pointer; margin-bottom: 12px;">
                    <div class="d-flex align-items-center gap-3 mb-1">
                        ${renderStudentAvatar(student, 'report-photo')}
                        <div style="min-width:0;">
                            <div class="report-name">${escapeHtml(student.name)}</div>
                            <div class="report-meta">ID: #${escapeHtml(student.id)}  •  ${formatPrivacy(student.phone, 'No phone')}</div>
                        </div>
                    </div>
                </div>
            `;
        }).join('');

        selectAdminContactStudent(adminContactSelectedStudentId, false);
    }

    function selectAdminContactStudent(studentId, rerenderList = true) {
        adminContactSelectedStudentId = String(studentId);
        const student = allStudents.find(s => String(s.id) === String(studentId));
        const detail = document.getElementById('admin-contact-detail');
        if(!student || !detail) return;
        if(rerenderList) {
            document.querySelectorAll('#admin-contact-list .student-page-card').forEach(card => card.classList.remove('active'));
            const card = document.getElementById(`admin-contact-card-${studentId}`);
            if(card) {
                card.classList.add('active');
            }
        }

        const progress = getStudentCompletionState(student);
        const active = isStudentActive(student);
        detail.innerHTML = `
            <div class="student-contact-profile">
                <div class="student-contact-profile-head">
                    ${renderStudentAvatar(student, 'profile-large-avatar', 'width:72px; height:72px; font-size:26px; border-radius:50% !important;')}
                    <div style="min-width:0;">
                        <div class="student-contact-profile-name">${escapeHtml(student.name)}</div>
                        <div class="student-contact-profile-sub">ID #${escapeHtml(student.id)}  •  ${active ? 'Active Student' : 'Inactive Student'}</div>
                    </div>
                </div>

                <div class="student-contact-status-row">
                    <span class="contact-status ${active ? 'active' : 'inactive'}"><span class="material-symbols-rounded">${active ? 'check_circle' : 'cancel'}</span>${active ? 'Active' : 'Inactive'}</span>
                    <span class="contact-status ${progress.finished ? 'finished' : 'unfinished'}"><span class="material-symbols-rounded">${progress.finished ? 'verified' : 'pending'}</span>${progress.finished ? 'Finished' : 'Unfinished'}</span>
                </div>

                <div class="student-contact-info-grid">
                    <div><span class="material-symbols-rounded">call</span><small>Phone</small><strong>${formatPrivacy(student.phone, '-')}</strong></div>
                    <div><span class="material-symbols-rounded">mail</span><small>Email</small><strong>${formatPrivacy(student.email, '-')}</strong></div>
                    <div><span class="material-symbols-rounded">storefront</span><small>Shop</small><strong>${formatPrivacy(student.shop_name, '-')}</strong></div>
                    <div><span class="material-symbols-rounded">location_on</span><small>Address</small><strong>${formatPrivacy(student.address, '-')}</strong></div>
                </div>

                <div class="student-contact-progress">
                    <div class="student-contact-progress-head"><span>Course</span><strong>${progress.courseText}</strong></div>
                    <div class="roster-prog-track"><div class="roster-prog-fill-theory" style="width:${progress.coursePct}%;"></div></div>
                    <div class="student-contact-progress-head"><span>Practical</span><strong>${progress.practicalText}</strong></div>
                    <div class="roster-prog-track"><div class="roster-prog-fill-practical" style="width:${progress.practicalPct}%;"></div></div>
                </div>

            </div>`;
    }

    function loadAdminTrainees() {
        const container = document.getElementById('admin-trainees-list'); if(!container) return;
        if(allStudents.length === 0) { container.innerHTML = '<div class="ios-list-item text-secondary py-4 justify-content-center">No trainees found.</div>'; return; }
        let html = '';
        allStudents.forEach(s => { 
            const statusClass = isStudentActive(s) ? '' : ' inactive';
            const statusText = isStudentActive(s) ? 'Active' : 'Inactive';
            const group = getRollCallGroup(s);
            html += `
                <div class="ios-list-item">
                    <div class="d-flex align-items-center gap-3">
                        ${renderStudentAvatar(s, 'student-avatar', 'width: 36px; height: 36px;')}
                        <div>
                            <div class="fw-bold" style="font-size: 15px;">${escapeHtml(s.name)}<span class="student-status-badge${statusClass}">${statusText}</span></div>
                            <div class="text-secondary" style="font-size: 12px;">ID: #${escapeHtml(s.id)}  •  ${escapeHtml(group)}  •  ${escapeHtml(s.phone || '')}</div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <button class="icon-action-btn" title="Edit trainee" aria-label="Edit trainee" onclick="openEditTraineeModal(${Number(s.id)})">
                            <span class="material-symbols-rounded">edit</span>
                        </button>
                        <button class="icon-action-btn danger" title="Delete trainee" aria-label="Delete trainee" onclick="deleteTrainee(${Number(s.id)})">
                            <span class="material-symbols-rounded">delete</span>
                        </button>
                    </div>
                </div>`;
        });
        container.innerHTML = html;
    }

    function openEditTraineeModal(id) {
        const s = allStudents.find(x => x.id == id);
        if(!s) return;
        updateRollCallGroupSelectLabels();
        document.getElementById('edit-trainee-id').value = s.id; 
        document.getElementById('edit-trainee-name').value = s.name; 
        document.getElementById('edit-trainee-phone').value = s.phone;
        document.getElementById('edit-trainee-email').value = s.email || '';
        document.getElementById('edit-trainee-shop').value = s.shop_name || '';
        document.getElementById('edit-trainee-address').value = s.address || '';
        document.getElementById('edit-trainee-rollcall-group').value = getRollCallGroup(s);
        document.getElementById('edit-trainee-active').checked = isStudentActive(s);
        document.getElementById('edit-photo-path').value = getStudentPhotoUrl(s);
        document.getElementById('edit-photo-file').value = '';
        setUploadPreview('edit', s);
        editTraineeModal.show();
    }

    async function uploadStudentPhotoFile(file, studentId = '') {
        if(!file) return null;
        const maxBytes = 2 * 1024 * 1024;
        const allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        if(file.size > maxBytes) {
            showUploadMessage('Photo must be 2MB or smaller.', 'error');
            return null;
        }
        if(!allowedTypes.includes(file.type)) {
            showUploadMessage('Please choose a JPG, PNG, WebP, or GIF image.', 'error');
            return null;
        }
        const fd = new FormData();
        fd.append('photo', file);
        if(studentId) fd.append('student_id', studentId);
        const res = await fetch('api/upload_student_photo.php', { method: 'POST', body: fd });
        let result = null;
        try {
            result = await res.json();
        } catch(e) {
            showUploadMessage('Upload failed. Please try a smaller photo.', 'error');
            return null;
        }
        if(!res.ok || result.status !== 'success') {
            showUploadMessage(result.message || 'Unable to upload photo.', 'error');
            return null;
        }
        showUploadMessage('Profile photo uploaded.');
        return result.photo_path;
    }

    async function uploadStudentPhotoInput(input, prefix) {
        const file = input.files && input.files[0];
        if(!file) return;
        const row = input.closest('.profile-upload-row');
        if(row) row.classList.add('uploading');
        try {
            const studentId = prefix === 'edit' ? document.getElementById('edit-trainee-id').value : '';
            const photoPath = await uploadStudentPhotoFile(file, studentId);
            input.value = '';
            if(!photoPath) return;
            document.getElementById(`${prefix}-photo-path`).value = photoPath;
            setUploadPreview(prefix, photoPath);

            if(studentId) {
                cacheVersion = Date.now();
                await loadStudents();
                loadAdminTrainees();
            }
        } finally {
            if(row) row.classList.remove('uploading');
        }
    }

    async function uploadActiveProfilePhoto(input, surface) {
        const file = input.files && input.files[0];
        if(!file) return;
        const studentId = surface === 'modal' ? modalProfileStudentId : activeStudentId;
        if(!studentId) return;
        const photoPath = await uploadStudentPhotoFile(file, studentId);
        input.value = '';
        if(!photoPath) return;

        cacheVersion = Date.now();
        await loadStudents();
        const updated = allStudents.find(s => String(s.id) === String(studentId));
        if(updated) {
            if(surface === 'modal') {
                setStudentAvatarElement(document.getElementById('modal-profile-avatar'), updated);
                await switchModalProfileTab(currentModalProfileTab);
            } else {
                setStudentAvatarElement(document.getElementById('p-avatar'), updated);
                switchProfileTab(currentProfileTab);
            }
        }
        loadAdminTrainees();
    }

    async function submitEditTrainee(e) {
        e.preventDefault(); 
        const fd = new FormData(); 
        fd.append('id', document.getElementById('edit-trainee-id').value); 
        fd.append('name', document.getElementById('edit-trainee-name').value); 
        fd.append('phone', document.getElementById('edit-trainee-phone').value);
        fd.append('email', document.getElementById('edit-trainee-email').value);
        fd.append('shop_name', document.getElementById('edit-trainee-shop').value);
        fd.append('address', document.getElementById('edit-trainee-address').value);
        fd.append('photo_path', document.getElementById('edit-photo-path').value);
        fd.append('is_active', document.getElementById('edit-trainee-active').checked ? '1' : '0');
        fd.append('rollcall_group', document.getElementById('edit-trainee-rollcall-group').value);

        await fetch('api/edit_student.php', { method: 'POST', body: fd });
        cacheVersion = Date.now();
        editTraineeModal.hide(); await loadStudents(); loadAdminTrainees(); loadAdminContacts();
    }

    async function deleteTrainee(id = null) {
        const traineeId = id || document.getElementById('edit-trainee-id').value;
        const ok = await showConfirmAction({
            title: 'Delete Trainee',
            message: 'Delete this trainee and related records forever?',
            okText: 'Delete',
            icon: 'person_remove'
        });
        if(!ok) return;
        const fd = new FormData(); fd.append('id', traineeId);
        await fetch('api/delete_student.php', { method: 'POST', body: fd });
        cacheVersion = Date.now();
        editTraineeModal.hide(); if(activeStudentId == traineeId) activeStudentId = null;
        if(adminContactSelectedStudentId == traineeId) adminContactSelectedStudentId = null;
        await loadStudents(); loadAdminTrainees(); loadAdminContacts();
    }

    async function addTrainee(event) {
        event.preventDefault(); 
        const fd = new FormData(); 
        fd.append('name', document.getElementById('new-name').value); 
        fd.append('phone', document.getElementById('new-phone').value);
        fd.append('email', document.getElementById('new-email').value); 
        fd.append('shop_name', document.getElementById('new-shop').value);
        fd.append('address', document.getElementById('new-address').value);
        fd.append('photo_path', document.getElementById('new-photo-path').value);
        fd.append('rollcall_group', document.getElementById('new-rollcall-group').value);

        const res = await fetch('api/add_student.php', { method: 'POST', body: fd });
        cacheVersion = Date.now();
        if((await res.json()).status === 'success') {
            document.getElementById('new-name').value = ''; 
            document.getElementById('new-phone').value = '';
            document.getElementById('new-email').value = ''; 
            document.getElementById('new-shop').value = '';
            document.getElementById('new-address').value = '';
            document.getElementById('new-photo-path').value = '';
            document.getElementById('new-photo-file').value = '';
            document.getElementById('new-rollcall-group').value = 'Weekday';
            setUploadPreview('new', '');
            
            addStudentModal.hide(); await loadStudents(); 
            if(document.getElementById('view-admin') && document.getElementById('view-admin').classList.contains('active')) loadAdminTrainees();
            if(document.getElementById('view-contacts')?.classList.contains('active')) loadAdminContacts();
        }
    }

    // --- UPGRADED CURRICULUM MANAGEMENT ---
    async function loadAdminCurriculum(flashId = null) {
        const container = document.getElementById('admin-curriculum-list'); 
        if(!container) return;
        
        try {
            const res = await fetch(`api/get_curriculum.php?v=${cacheVersion}`); 
            const result = await res.json();
            
            if (result.status === 'success') {
                adminCurriculumData = result.data;
                const groupedByType = { Course: new Map(), Practical: new Map() };
                
                adminCurriculumData.forEach(item => {
                    const type = item.type === 'Practical' ? 'Practical' : 'Course';
                    if (!groupedByType[type].has(item.category)) {
                        groupedByType[type].set(item.category, { type, category: item.category, items: [] });
                    }
                    groupedByType[type].get(item.category).items.push(item);
                });

                let html = `
                <div class="admin-curriculum-toolbar">
                    <div>
                        <div class="fw-bold text-secondary" style="font-size:14px; text-transform:uppercase; letter-spacing: 0.5px;">Curriculum Structure</div>
                        <div class="text-secondary" style="font-size:12px;">Theory and Practical kept separate for easier management.</div>
                    </div>
                    <div class="d-flex gap-2">
                        <button id="btn-edit-order" class="btn btn-dark btn-icon-label px-3 py-1" style="font-size:13px;" onclick="toggleCurriculumEditMode()"><span class="material-symbols-rounded">swap_vert</span>Reorder</button>
                        <button id="btn-save-order" class="btn btn-premium btn-icon-label px-3 py-1" style="font-size:13px; display:none;" onclick="saveCurriculumOrder()"><span class="material-symbols-rounded">save</span>Save Order</button>
                    </div>
                </div>
                <div id="curriculum-sort-container" class="curriculum-type-grid ${isCurriculumEditMode ? 'edit-mode-active' : ''}">`;

                let categoriesSet = new Set();
                ['Course', 'Practical'].forEach(type => {
                    const icon = type === 'Course' ? 'menu_book' : 'construction';
                    const title = type === 'Course' ? 'Theory Course' : 'Practical Work';
                    html += `
                    <section class="curriculum-type-panel" data-type-panel="${type}">
                        <div class="curriculum-type-head">
                            <span class="material-symbols-rounded">${icon}</span>
                            <div>
                                <h3>${title}</h3>
                                <small>${groupedByType[type].size} sections • ${adminCurriculumData.filter(item => item.type === type).length} modules</small>
                            </div>
                        </div>`;

                    if(groupedByType[type].size === 0) {
                        html += `<div class="course-module-empty-note">No ${type === 'Course' ? 'theory' : 'practical'} modules yet</div>`;
                    }

                    groupedByType[type].forEach(group => {
                        categoriesSet.add(group.category);
                        html += `
                        <div class="category-block" data-category="${escapeHtml(group.category)}" data-type="${group.type}">
                            <div class="category-header drag-handle-category">
                                <span class="material-symbols-rounded drag-handle">drag_indicator</span>
                                <span class="material-symbols-rounded">${icon}</span>
                                ${escapeHtml(group.category)}
                            </div>
                            <div class="items-sort-container p-3" style="display:flex;flex-direction:column;gap:8px;">`;
                        
                        group.items.forEach(item => {
                            const flashClass = (item.id == flashId) ? 'flash-green' : '';
                            html += `
                                <div class="ios-list-item d-flex align-items-center p-3 ${flashClass}" data-id="${item.id}" style="margin-bottom:0; border-radius:8px;">
                                    <span class="material-symbols-rounded drag-handle drag-handle-item">drag_indicator</span>
                                    <div class="flex-grow-1 clickable" onclick="if(!isCurriculumEditMode) openCurriculumModal(${item.id})">
                                        <div class="fw-bold" style="font-size: 15px;">${escapeHtml(item.title)}</div>
                                    </div>
                                    <span class="material-symbols-rounded text-secondary clickable edit-icon" style="font-size:18px;" onclick="if(!isCurriculumEditMode) openCurriculumModal(${item.id})">edit</span>
                                </div>`;
                        });
                        
                        html += `</div></div>`;
                    });
                    html += `</section>`;
                });
                html += '</div>';
                
                container.innerHTML = html;

                const datalist = document.getElementById('category-suggestions');
                if(datalist) datalist.innerHTML = Array.from(categoriesSet).map(c => `<option value="${c}">`).join('');

                initDragAndDrop();
            }
        } catch (e) {
            console.error("Failed to load curriculum:", e);
        }
    }

    function toggleCurriculumEditMode() {
        isCurriculumEditMode = !isCurriculumEditMode;
        const container = document.getElementById('curriculum-sort-container');
        const btnEdit = document.getElementById('btn-edit-order');
        const btnSave = document.getElementById('btn-save-order');

        if (isCurriculumEditMode) {
            container.classList.add('edit-mode-active');
            btnEdit.innerText = "Cancel";
            btnSave.style.display = "inline-block";
        } else {
            container.classList.remove('edit-mode-active');
            btnEdit.innerText = "Reorder";
            btnSave.style.display = "none";
            loadAdminCurriculum(); 
            return;
        }
        sortableInstances.forEach(inst => inst.option("disabled", !isCurriculumEditMode));
    }

    function initDragAndDrop() {
        sortableInstances.forEach(inst => inst.destroy());
        sortableInstances = [];

        document.querySelectorAll('.curriculum-type-panel').forEach(categoryContainer => {
            sortableInstances.push(new Sortable(categoryContainer, {
                handle: '.drag-handle-category',
                animation: 150,
                disabled: !isCurriculumEditMode 
            }));
        });

        document.querySelectorAll('.items-sort-container').forEach(container => {
            sortableInstances.push(new Sortable(container, {
                group: 'shared', 
                handle: '.drag-handle-item',
                animation: 150,
                disabled: !isCurriculumEditMode 
            }));
        });
    }

    async function saveCurriculumOrder() {
        const btnSave = document.getElementById('btn-save-order');
        btnSave.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Saving...';
        btnSave.disabled = true;

        let orderedData = [];
        let globalRank = 1;

        document.querySelectorAll('.category-block').forEach(categoryBlock => {
            const categoryName = categoryBlock.getAttribute('data-category');
            const categoryType = categoryBlock.getAttribute('data-type');

            categoryBlock.querySelectorAll('.ios-list-item').forEach(itemEl => {
                orderedData.push({
                    id: itemEl.getAttribute('data-id'),
                    sort_order: globalRank++,
                    category: categoryName,
                    type: categoryType      
                });
            });
        });

        const fd = new FormData();
        fd.append('order_data', JSON.stringify(orderedData));

        try {
            await fetch('api/update_curriculum_order.php', { method: 'POST', body: fd });
            cacheVersion = Date.now();
            isCurriculumEditMode = false;
            await loadAdminCurriculum();
            
            if (activeStudentId) { switchProfileTab(currentProfileTab); }

            btnSave.innerHTML = 'Saved!';
            setTimeout(() => {
                btnSave.style.display = "none";
                document.getElementById('btn-edit-order').innerText = "Reorder";
                btnSave.disabled = false;
                btnSave.innerHTML = 'Save Order';
            }, 1000);

        } catch (e) {
            console.error("Failed to save curriculum order", e);
            btnSave.innerHTML = 'Save Order';
            btnSave.disabled = false;
        }
    }

    function openCurriculumModal(id = null) {
        if (id) {
            const item = adminCurriculumData.find(i => i.id == id);
            document.getElementById('curr-modal-title').innerText = "Edit Module";
            document.getElementById('curr-id').value = item.id; document.getElementById('curr-type').value = item.type; document.getElementById('curr-category').value = item.category; document.getElementById('curr-title').value = item.title; document.getElementById('curr-delete-btn').style.display = 'block';
        } else {
            document.getElementById('curr-modal-title').innerText = "Add New Module";
            document.getElementById('curr-id').value = ''; document.getElementById('curr-type').value = 'Course'; document.getElementById('curr-category').value = ''; document.getElementById('curr-title').value = ''; document.getElementById('curr-delete-btn').style.display = 'none';
        }
        curriculumModal.show();
    }

    async function submitCurriculum(e) {
        e.preventDefault(); const fd = new FormData();
        fd.append('id', document.getElementById('curr-id').value); fd.append('type', document.getElementById('curr-type').value);
        fd.append('category', document.getElementById('curr-category').value); fd.append('title', document.getElementById('curr-title').value);
        const res = await fetch('api/save_curriculum.php', { method: 'POST', body: fd });
        cacheVersion = Date.now();
        const result = await res.json();
        curriculumModal.hide();
        if(document.getElementById('view-report') && document.getElementById('view-report').classList.contains('active')) {
            currentCoursePage = 'RealWork';
            loadTrainingPagesView();
        }
        if(document.getElementById('view-admin') && document.getElementById('view-admin').classList.contains('active')) {
            loadAdminCurriculum(result.id);
        }
    }

    async function deleteCurriculum() {
        const ok = await showConfirmAction({
            title: 'Delete Module',
            message: 'Delete this curriculum module forever?',
            okText: 'Delete',
            icon: 'delete'
        });
        if(!ok) return;
        const fd = new FormData(); fd.append('id', document.getElementById('curr-id').value);
        await fetch('api/delete_curriculum.php', { method: 'POST', body: fd });
        cacheVersion = Date.now();
        curriculumModal.hide(); loadAdminCurriculum();
    }

    function filterCourseModules() {
        const query = (document.getElementById('courseSearchBox')?.value || '').toLowerCase();
        const cards = document.querySelectorAll('#course-page-content .course-module-card');
        
        cards.forEach(card => {
            const titleEl = card.querySelector('.course-module-title');
            const commentEl = card.querySelector('.student-profile-panel');
            let text = '';
            if (titleEl) text += titleEl.innerText.toLowerCase();
            if (commentEl) text += ' ' + commentEl.innerText.toLowerCase();
            
            if (text.includes(query)) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
        
        const categories = document.querySelectorAll('#course-page-content .course-category-block');
        categories.forEach(cat => {
            const visibleCards = cat.querySelectorAll('.course-module-card[style="display: block;"], .course-module-card:not([style="display: none;"])');
            if (visibleCards.length === 0 && query !== '') {
                cat.style.display = 'none';
            } else {
                cat.style.display = 'block';
            }
        });
    }

    // --- INITIALIZE APPLICATION SAFELY ---
    document.addEventListener('DOMContentLoaded', async () => {
        const initModal = (id) => document.getElementById(id) ? new bootstrap.Modal(document.getElementById(id)) : {show:()=>{}, hide:()=>{}};
        addStudentModal = initModal('addStudentModal');
        signOffModal = initModal('signOffModal');
        curriculumModal = initModal('curriculumModal');
        editTraineeModal = initModal('editTraineeModal');
        trainerModal = initModal('trainerModal');
        moduleMarkModal = initModal('moduleMarkModal');
        realWorldRepairModal = initModal('realWorldRepairModal');
        studentProfileModal = initModal('studentProfileModal');
        revertModal = initModal('revertModal');
        confirmActionModal = initModal('confirmActionModal');

        await loadAppSettings();
        updateRollCallGroupSelectLabels();
        await loadStudents(); 
        await loadAdminTrainers(); 
        switchViewMode('roster');   
    });
</script>
