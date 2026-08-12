<div id="view-rollcall" class="view-section w-full max-w-[1640px] px-4 py-5 sm:px-6 lg:px-8">
    <div class="rollcall-hero grid grid-cols-1 gap-4 xl:grid-cols-[minmax(0,1fr)_auto]">
        <div>
            <div class="section-title-row">
                <span class="section-title-icon material-symbols-rounded">fact_check</span>
                <h1 class="fw-bold m-0" style="font-size: 26px; letter-spacing: -0.8px;">Live Roll Call</h1>
            </div>
            <p class="text-secondary mb-0 mt-2" id="rollcall-date-label"></p>
        </div>
        <div class="rollcall-summary-grid grid grid-cols-1 gap-2 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rollcall-summary-card">
                <span class="material-symbols-rounded">groups</span>
                <div>
                    <strong id="rollcall-total-count">0</strong>
                    <small>Total</small>
                </div>
            </div>
            <div class="rollcall-summary-card present">
                <span class="material-symbols-rounded">check_circle</span>
                <div>
                    <strong id="rollcall-present-count">0</strong>
                    <small>Present</small>
                </div>
            </div>
            <div class="rollcall-summary-card late">
                <span class="material-symbols-rounded">schedule</span>
                <div>
                    <strong id="rollcall-late-count">0</strong>
                    <small>Late</small>
                </div>
            </div>
            <div class="rollcall-summary-card absent">
                <span class="material-symbols-rounded">cancel</span>
                <div>
                    <strong id="rollcall-absent-count">0</strong>
                    <small>Absent</small>
                </div>
            </div>
        </div>
    </div>

    <div class="rollcall-window-banner" id="rollcall-window-banner"></div>
    
    <div class="rollcall-workspace grid grid-cols-1 gap-4 xl:grid-cols-[minmax(0,1fr)_380px]">
        <div class="ios-list rollcall-list-panel" id="rollcall-live-list">
            <!-- Loaded dynamically via JS -->
        </div>

        <aside class="rollcall-calendar-panel">
            <div class="rollcall-calendar-empty" id="rollcall-calendar-empty">
                <span class="material-symbols-rounded">calendar_month</span>
                <strong>Select Student</strong>
                <small>Attendance history shows here.</small>
            </div>
            <div class="rollcall-calendar-content" id="rollcall-calendar-content" style="display:none;">
                <div class="rollcall-calendar-head">
                    <div class="d-flex align-items-center gap-3" style="min-width:0;">
                        <div id="rollcall-calendar-avatar"></div>
                        <div style="min-width:0;">
                            <div class="rollcall-calendar-name" id="rollcall-calendar-name">Student</div>
                            <div class="rollcall-calendar-meta" id="rollcall-calendar-meta">Attendance history</div>
                        </div>
                    </div>
                    <span class="material-symbols-rounded">calendar_month</span>
                </div>

                <div class="rollcall-history-stats">
                    <div><strong id="rollcall-history-present">0</strong><small>Present</small></div>
                    <div><strong id="rollcall-history-late">0</strong><small>Late</small></div>
                    <div><strong id="rollcall-history-absent">0</strong><small>Absent</small></div>
                </div>

                <div class="rollcall-analytics-grid">
                    <div>
                        <span class="material-symbols-rounded">trending_up</span>
                        <strong id="rollcall-attendance-rate">0%</strong>
                        <small>Attendance rate</small>
                    </div>
                    <div>
                        <span class="material-symbols-rounded">event_available</span>
                        <strong id="rollcall-attended-days">0</strong>
                        <small>Attended days</small>
                    </div>
                    <div>
                        <span class="material-symbols-rounded">history</span>
                        <strong id="rollcall-latest-status">-</strong>
                        <small>Latest status</small>
                    </div>
                </div>



                <div class="d-flex justify-content-between align-items-center mb-2">
                    <button class="btn btn-icon text-secondary p-0" onclick="changeRollCallMonth(-1)"><span class="material-symbols-rounded">chevron_left</span></button>
                    <div class="rollcall-calendar-month m-0" id="rollcall-calendar-month">Month</div>
                    <button class="btn btn-icon text-secondary p-0" onclick="changeRollCallMonth(1)"><span class="material-symbols-rounded">chevron_right</span></button>
                </div>
                <div class="rollcall-calendar-weekdays">
                    <span>Sun</span><span>Mon</span><span>Tue</span><span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span>
                </div>
                <div class="rollcall-calendar-grid" id="rollcall-calendar-grid"></div>

                <div class="rollcall-calendar-legend">
                    <span><i class="present"></i>Present</span>
                    <span><i class="late"></i>Late</span>
                    <span><i class="absent"></i>Absent</span>
                </div>
            </div>
        </aside>
    </div>
</div>
