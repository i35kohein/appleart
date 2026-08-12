<div id="view-profile" class="view-section w-full max-w-[1640px] px-4 py-5 sm:px-6 lg:px-8">
    
    <div id="roster-overview" style="display: block;">
        <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-4">
            <div>
                <div class="section-title-row">
                    <span class="section-title-icon material-symbols-rounded">groups</span>
                    <h2 class="fw-bold m-0" style="font-size: 26px; letter-spacing: -0.8px;">Student List</h2>
                </div>
                <p class="text-secondary m-0" style="font-size: 14px;">Open a trainee profile to review personal data, theory progress, practical work, live repair sessions, and roll call history.</p>
            </div>
        </div>
        
        <div class="student-dashboard-view" id="student-dashboard-view" style="display:none;">
            <div class="student-dashboard-head">
                <div>
                    <strong>Active Student Dashboard</strong>
                    <small>Click any student card to open full profile.</small>
                </div>
                <span class="material-symbols-rounded">grid_view</span>
            </div>
            <div id="student-dashboard-list" class="student-dashboard-grid"></div>
        </div>

        <div class="student-crm-layout" id="student-list-view">
            <aside class="student-crm-list-panel" aria-label="Active student list">
                <div class="student-crm-panel-title">
                    <span class="material-symbols-rounded">how_to_reg</span>
                    <div>
                        <strong>Active Students</strong>
                        <small>Select student profile</small>
                    </div>
                </div>
                <div id="student-page-list" class="student-page-grid student-crm-list"></div>
            </aside>

            <section class="student-crm-profile-panel" aria-live="polite">
                <div id="profile-placeholder" class="student-profile-placeholder">
                    <span class="material-symbols-rounded">person_search</span>
                    <strong>Select Student</strong>
                    <small>Student Profile show here.</small>
                </div>

                <div id="profile-content" style="display: none; flex-direction: column; height: 100%;">
                    <button type="button" class="profile-back-btn" onclick="backToStudentDashboard()" aria-label="Back to student dashboard" style="align-self: flex-start; padding: 4px 8px; border-radius: 8px;">
                        <span class="material-symbols-rounded" style="font-size: 20px;">arrow_back</span>
                    </button>
                    
                    <div class="profile-header-widget">
                        <div class="student-profile-identity">
                            <div class="profile-large-avatar" id="p-avatar">-</div>
                            <div style="min-width:0;">
                                <div class="curriculum-category m-0 mb-2">Student Profile</div>
                                <h2 class="fw-bold m-0" id="p-name" style="font-size: 26px;">Trainee</h2>
                                <div class="student-profile-id" id="p-id">ID: #</div>
                            </div>
                        </div>
                        <div class="student-profile-meta" id="p-sub"></div>
                    </div>

                    <div class="profile-tabs-wrapper" style="position: sticky; top: -18px; z-index: 50; margin: 0 -18px 16px -18px; padding: 18px 18px 12px 18px; background: var(--bg-base); border-bottom: 1px solid var(--separator);">
                        <div class="segmented-control profile-tabs" style="margin-bottom: 0;">
                            <button class="segment-btn active" id="tab-detail" onclick="switchProfileTab('Detail')">Detail</button>
                            <button class="segment-btn" id="tab-theory" onclick="switchProfileTab('Theory')">Theory</button>
                            <button class="segment-btn" id="tab-practical" onclick="switchProfileTab('Practical')">Practical</button>
                            <button class="segment-btn" id="tab-realworld" onclick="switchProfileTab('Realworld')">Live Repair Sessions</button>
                            <button class="segment-btn" id="tab-history" onclick="switchProfileTab('History')">Logs</button>
                            <button class="segment-btn" id="tab-attendance" onclick="switchProfileTab('Attendance')">Attendance</button>
                        </div>
                    </div>

                    <div id="profile-pane-content"></div>
                </div>
            </section>
        </div>
    </div>

</div>
