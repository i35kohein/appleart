<?php if (isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], ['admin', 'master_admin'])): ?>
<div id="view-admin" class="view-section w-full max-w-[1640px] px-4 py-5 sm:px-6 lg:px-8">
    <div class="mb-4">
        <h2 class="fw-bold m-0" style="font-size: 26px; letter-spacing: -0.8px;">System Admin</h2>
        <p class="text-secondary mt-1" style="font-size: 14px;">Manage instructors, global trainees, and training modules.</p>
    </div>
    
    <div class="segmented-control mb-4 overflow-x-auto">
        <button class="segment-btn active" id="tab-manage-trainers" onclick="switchAdminTab('Trainers')">Instructors</button>
        <button class="segment-btn" id="tab-manage-trainees" onclick="switchAdminTab('Trainees')">Trainee Controls</button>
        <button class="segment-btn" id="tab-manage-operations" onclick="switchAdminTab('Operations')">System Settings</button>
        <button class="segment-btn" id="tab-manage-curriculum" onclick="switchAdminTab('Curriculum')">Curriculum Editor</button>
        <button class="segment-btn" id="tab-manage-payments" onclick="switchAdminTab('Payments')">Payments</button>
        <button class="segment-btn" id="tab-manage-users" onclick="switchAdminTab('Users')">System Users</button>
    </div>
    
    <div id="admin-trainers">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="curriculum-category m-0">Authorized Instructors</div>
            <button class="btn-premium btn-icon-label" onclick="openTrainerModal()">
                <span class="material-symbols-rounded">person_add</span>
                Add Instructor
            </button>
        </div>
        <div id="admin-trainers-list" class="ios-list">
            </div>
    </div>

    <div id="admin-trainees" style="display:none;">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="curriculum-category m-0">Global Trainee Management</div>
            <button class="btn-premium btn-icon-label" onclick="addStudentModal.show()">
                <span class="material-symbols-rounded">person_add</span>
                Add Trainee
            </button>
        </div>
        <div id="admin-trainees-list" class="ios-list">
            </div>
    </div>

    <div id="admin-operations" style="display:none;">
        <div class="admin-settings-grid">
            <section class="admin-settings-card">
                <div class="profile-detail-panel-head">
                    <span class="material-symbols-rounded">fact_check</span>
                    <h3>Live Roll Call Window</h3>
                </div>
                <p class="text-secondary mb-4" style="font-size:13px;">Control roll call windows by trainee group. Weekday and weekend students follow their own schedule.</p>
                <form onsubmit="saveRollCallSettings(event)">
                    <div class="rollcall-schedule-set" data-rollcall-group="Weekday">
                        <div class="settings-summary-row border-0 p-0 mb-2"><span>Weekday Students <small id="rollcall-weekday-active-days">(Mon-Fri)</small></span><strong>Active days</strong></div>
                        <div class="rollcall-day-picker" id="rollcall-settings-days-weekday">
                            <label><input type="checkbox" value="1">Mon</label>
                            <label><input type="checkbox" value="2">Tue</label>
                            <label><input type="checkbox" value="3">Wed</label>
                            <label><input type="checkbox" value="4">Thu</label>
                            <label><input type="checkbox" value="5">Fri</label>
                            <label><input type="checkbox" value="6">Sat</label>
                            <label><input type="checkbox" value="0">Sun</label>
                        </div>
                        <div class="rollcall-time-grid">
                            <label>
                                <span>Start Time</span>
                                <input type="time" id="rollcall-start-time-weekday" class="apple-input" value="10:00" required>
                            </label>
                            <label>
                                <span>End Time</span>
                                <input type="time" id="rollcall-end-time-weekday" class="apple-input" value="15:00" required>
                            </label>
                        </div>
                    </div>

                    <div class="rollcall-schedule-set mt-4" data-rollcall-group="Weekend">
                        <div class="settings-summary-row border-0 p-0 mb-2"><span>Weekend Students <small id="rollcall-weekend-active-days">(Sat-Sun)</small></span><strong>Active days</strong></div>
                        <div class="rollcall-day-picker" id="rollcall-settings-days-weekend">
                            <label><input type="checkbox" value="1">Mon</label>
                            <label><input type="checkbox" value="2">Tue</label>
                            <label><input type="checkbox" value="3">Wed</label>
                            <label><input type="checkbox" value="4">Thu</label>
                            <label><input type="checkbox" value="5">Fri</label>
                            <label><input type="checkbox" value="6">Sat</label>
                            <label><input type="checkbox" value="0">Sun</label>
                        </div>
                        <div class="rollcall-time-grid">
                            <label>
                                <span>Start Time</span>
                                <input type="time" id="rollcall-start-time-weekend" class="apple-input" value="10:00" required>
                            </label>
                            <label>
                                <span>End Time</span>
                                <input type="time" id="rollcall-end-time-weekend" class="apple-input" value="15:00" required>
                            </label>
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-2 flex-wrap mt-4">
                        <button type="submit" class="btn-premium btn-icon-label">
                            <span class="material-symbols-rounded">save</span>
                            Save Schedule
                        </button>
                        <div class="settings-save-note" id="rollcall-settings-note"></div>
                    </div>
                </form>
            </section>

            <section class="admin-settings-card">
                <div class="profile-detail-panel-head">
                    <span class="material-symbols-rounded">palette</span>
                    <h3>Theme Settings</h3>
                </div>
                <p class="text-secondary mb-4" style="font-size:13px;">Select the global visual theme for the Student Management System. DaisyUI Light is highly recommended.</p>
                <div class="d-flex flex-column gap-3">
                    <select id="system-theme-select" class="apple-input w-100" style="padding: 12px 16px; border-radius: 12px;" onchange="changeSystemTheme(this.value)">
                        <option value="light">DaisyUI Light (Recommended)</option>
                        <option value="dark">DaisyUI Dark</option>
                        <option value="cupcake">DaisyUI Cupcake</option>
                        <option value="bumblebee">DaisyUI Bumblebee</option>
                        <option value="emerald">DaisyUI Emerald</option>
                        <option value="corporate">DaisyUI Corporate</option>
                        <option value="synthwave">DaisyUI Synthwave</option>
                        <option value="retro">DaisyUI Retro</option>
                        <option value="cyberpunk">DaisyUI Cyberpunk</option>
                        <option value="valentine">DaisyUI Valentine</option>
                        <option value="halloween">DaisyUI Halloween</option>
                        <option value="garden">DaisyUI Garden</option>
                        <option value="forest">DaisyUI Forest</option>
                        <option value="aqua">DaisyUI Aqua</option>
                        <option value="lofi">DaisyUI Lo-Fi</option>
                        <option value="pastel">DaisyUI Pastel</option>
                        <option value="fantasy">DaisyUI Fantasy</option>
                        <option value="wireframe">DaisyUI Wireframe</option>
                        <option value="black">DaisyUI Black</option>
                        <option value="luxury">DaisyUI Luxury</option>
                        <option value="dracula">DaisyUI Dracula</option>
                        <option value="cmyk">DaisyUI CMYK</option>
                        <option value="autumn">DaisyUI Autumn</option>
                        <option value="business">DaisyUI Business</option>
                        <option value="acid">DaisyUI Acid</option>
                        <option value="lemonade">DaisyUI Lemonade</option>
                        <option value="night">DaisyUI Night</option>
                        <option value="coffee">DaisyUI Coffee</option>
                        <option value="winter">DaisyUI Winter</option>
                        <option value="dim">DaisyUI Dim</option>
                        <option value="nord">DaisyUI Nord</option>
                        <option value="sunset">DaisyUI Sunset</option>
                        <option value="apple">Apple Art Custom</option>
                    </select>
                </div>
            </section>

            <section class="admin-settings-card">
                <div class="profile-detail-panel-head">
                    <span class="material-symbols-rounded">security</span>
                    <h3>Privacy Mode</h3>
                </div>
                <p class="text-secondary mb-4" style="font-size:13px;">Hide sensitive trainee information (Phone, Email, Address, Shop Name) across the system with ***.</p>
                <div class="d-flex align-items-center justify-content-between" style="padding: 16px; border: 1px solid var(--separator); border-radius: 12px; background: var(--bg-base);">
                    <div>
                        <strong>Mask Sensitive Data</strong>
                        <div class="text-secondary" style="font-size:12px;">Applies instantly to all views</div>
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox" id="privacy-mode-toggle" onchange="togglePrivacyMode(this.checked)">
                        <span class="toggle-slider"></span>
                    </label>
                </div>
            </section>

            <aside class="admin-settings-card">
                <div class="profile-detail-panel-head">
                    <span class="material-symbols-rounded">schedule</span>
                    <h3>Current Status</h3>
                </div>
                <div id="rollcall-settings-preview" class="rollcall-settings-preview"></div>
            </aside>
        </div>
    </div>

    <div id="admin-curriculum" style="display:none;">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="curriculum-category m-0">Theory & Practical Modules</div>
            <button class="btn-premium btn-icon-label" onclick="openCurriculumModal()">
                <span class="material-symbols-rounded">add_task</span>
                Add Module
            </button>
        </div>
        <div id="admin-curriculum-list" class="ios-list">
            </div>
    </div>

    <?php include 'payments.php'; ?>

    <div id="admin-users" style="display:none;">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="curriculum-category m-0">System Users</div>
            <button class="btn-premium btn-icon-label" onclick="openUserModal()">
                <span class="material-symbols-rounded">person_add</span>
                Add User
            </button>
        </div>
        <div id="admin-users-list" class="ios-list">
        </div>
    </div>
</div>
<?php endif; ?>
