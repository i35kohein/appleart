<div id="view-report" class="view-section w-full max-w-[1640px] px-4 py-5 sm:px-6 lg:px-8">
    <div class="course-page-main-header d-flex justify-content-between align-items-start gap-3 flex-wrap mb-4">
        <div class="course-page-title-block">
            <div class="section-title-row">
                <span class="section-title-icon material-symbols-rounded">school</span>
                <h2 class="fw-bold m-0" style="font-size: 26px; letter-spacing: -0.8px;">Theory Operations</h2>
            </div>
            <p class="text-secondary m-0" style="font-size: 14px;">Track module completion, practical progress, and real repair comments for active trainees.</p>
        </div>
        <button onclick="openRealWorkRepairModal()" class="btn-premium btn-icon-label" aria-label="Add live repair session comment" style="border-radius: 10px;">
            <span class="material-symbols-rounded" style="font-size: 18px;">add_circle</span>
            Add Live Repair Session
        </button>
    </div>

    <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap mb-4">
        <div class="segmented-control course-page-tabs overflow-x-auto mb-0" role="tablist" aria-label="Course operations sections" style="margin-bottom: 0;">
            <button class="segment-btn active" id="course-page-tab-course" role="tab" aria-selected="true" aria-controls="course-page-content" onclick="switchCoursePage('Course')">
                <span class="material-symbols-rounded">menu_book</span>
                Theory
            </button>
            <button class="segment-btn" id="course-page-tab-practical" role="tab" aria-selected="false" aria-controls="course-page-content" onclick="switchCoursePage('Practical')">
                <span class="material-symbols-rounded">construction</span>
                Practical
            </button>
            <button class="segment-btn" id="course-page-tab-realwork" role="tab" aria-selected="false" aria-controls="course-page-content" onclick="switchCoursePage('RealWork')">
                <span class="material-symbols-rounded">rate_review</span>
                Live Repair Sessions
            </button>
        </div>
        <div style="flex: 1; min-width: 250px; max-width: 320px;">
            <input type="text" id="courseSearchBox" class="apple-input w-100" placeholder="Search modules..." oninput="filterCourseModules()" style="padding: 10px 14px; border-radius: 8px;">
        </div>
    </div>


    <div id="course-page-content"></div>
</div>
