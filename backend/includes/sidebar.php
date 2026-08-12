<div class="sidebar">
    <!-- Top Navigation Icons -->
    <div class="nav-icon" id="rail-roster" onclick="switchViewMode('roster')" title="Student List">
        <span class="material-symbols-rounded">groups</span>
        <span class="nav-label">Students</span>
    </div>
    
    <div class="nav-icon" id="rail-rollcall" onclick="switchViewMode('rollcall')" title="Roll Call">
        <span class="material-symbols-rounded">fact_check</span>
        <span class="nav-label">Roll Call</span>
    </div>

    <div class="nav-icon" id="rail-report" onclick="switchViewMode('report')" title="Course Pages">
        <span class="material-symbols-rounded">school</span>
        <span class="nav-label">Courses</span>
    </div>

    <div class="nav-icon" id="rail-contacts" onclick="switchViewMode('contacts')" title="Student Contacts">
        <span class="material-symbols-rounded">contact_page</span>
        <span class="nav-label">Contacts</span>
    </div>

    <div class="nav-icon" id="rail-exam" onclick="switchViewMode('exam')" title="Exams">
        <span class="material-symbols-rounded">assignment</span>
        <span class="nav-label">Exams</span>
    </div>

    <div class="nav-icon" id="rail-calendar" onclick="switchViewMode('calendar')" title="Training Calendar">
        <span class="material-symbols-rounded">calendar_month</span>
        <span class="nav-label">Calendar</span>
    </div>

    <div class="nav-icon" id="rail-today" onclick="switchViewMode('today')" title="Today's Screen (Big Display)">
        <span class="material-symbols-rounded">slideshow</span>
        <span class="nav-label">Today</span>
    </div>

    <!-- Spacer pushes everything below it to the bottom -->
    <div style="flex-grow: 1;"></div>

    <?php if (isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], ['admin', 'master_admin'])): ?>
    <div class="nav-icon" id="rail-admin" onclick="switchViewMode('admin')" title="System Settings">
        <span class="material-symbols-rounded">settings</span>
        <span class="nav-label">Settings</span>
    </div>
    <?php endif; ?>
</div>
