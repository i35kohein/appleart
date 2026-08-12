<style>
    .carbon-modal-content {
        background-color: var(--bg-surface) !important;
        border: 1px solid var(--separator) !important;
        box-shadow: 0 10px 20px rgba(0,0,0,0.2);
    }
</style>

<div class="modal fade" id="addStudentModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content carbon-modal-content">
      <div class="modal-body p-4">
        <h4 class="fw-bold mb-4">Add New Trainee</h4>
        
        <form onsubmit="addTrainee(event)">
            <input type="hidden" id="new-photo-path">
            <div class="profile-upload-row mb-4">
                <div class="profile-upload-preview" id="new-photo-preview">?</div>
                <div class="flex-grow-1" style="min-width:0;">
                    <div class="fw-bold mb-1" style="font-size:14px;">Profile Photo</div>
                    <div class="text-secondary mb-2" style="font-size:12px;">JPG, PNG, WebP or GIF up to 2MB.</div>
                    <label class="btn btn-dark px-3 py-2 m-0" style="font-size:13px;">
                        Upload Photo
                        <input type="file" id="new-photo-file" accept="image/*" hidden onchange="uploadStudentPhotoInput(this, 'new')">
                    </label>
                </div>
            </div>

            <label class="text-secondary mb-1" style="font-size: 13px;">Full Name *</label>
            <input type="text" id="new-name" class="apple-input mb-3" placeholder="e.g., Aung Aung" autocomplete="name" required>
            
            <label class="text-secondary mb-1" style="font-size: 13px;">Phone Number *</label>
            <input type="tel" id="new-phone" class="apple-input mb-3" placeholder="e.g., 09 123 456 789" inputmode="tel" autocomplete="tel" spellcheck="false" required>

            <label class="text-secondary mb-1" style="font-size: 13px;">Email Address (Optional)</label>
            <input type="email" id="new-email" class="apple-input mb-3" placeholder="e.g., trainee@example.com" autocomplete="email" spellcheck="false">

            <label class="text-secondary mb-1" style="font-size: 13px;">Shop / Business Name (Optional)</label>
            <input type="text" id="new-shop" class="apple-input mb-3" placeholder="e.g., Apple Art Service" autocomplete="organization">

            <label class="text-secondary mb-1" style="font-size: 13px;">Residential Address (Optional)</label>
            <input type="text" id="new-address" class="apple-input mb-4" placeholder="Enter street address, township, or city" autocomplete="street-address">

            <label class="text-secondary mb-1" style="font-size: 13px;">Roll Call Group</label>
            <select id="new-rollcall-group" class="apple-input mb-4">
                <option value="Weekday">Weekday Student</option>
                <option value="Weekend">Weekend Student</option>
            </select>
            
            <div class="d-flex justify-content-end gap-2 mt-3">
                <button type="button" class="btn btn-dark px-4 py-2" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-premium px-4 py-2">Add Trainee</button>
            </div>
        </form>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="editTraineeModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content carbon-modal-content">
      <div class="modal-body p-4">
        <h4 class="fw-bold mb-4">Edit Trainee</h4>
        <form onsubmit="submitEditTrainee(event)">
            <input type="hidden" id="edit-trainee-id">
            <input type="hidden" id="edit-photo-path">
            <div class="profile-upload-row mb-4">
                <div class="profile-upload-preview" id="edit-photo-preview">?</div>
                <div class="flex-grow-1" style="min-width:0;">
                    <div class="fw-bold mb-1" style="font-size:14px;">Profile Photo</div>
                    <div class="text-secondary mb-2" style="font-size:12px;">This photo appears in all profile and course views. Max 2MB.</div>
                    <label class="btn btn-dark px-3 py-2 m-0" style="font-size:13px;">
                        Upload Photo
                        <input type="file" id="edit-photo-file" accept="image/*" hidden onchange="uploadStudentPhotoInput(this, 'edit')">
                    </label>
                </div>
            </div>
            
            <label class="text-secondary mb-1" style="font-size: 13px;">Full Name *</label>
            <input type="text" id="edit-trainee-name" class="apple-input mb-3" placeholder="e.g., Aung Aung" autocomplete="name" required>
            
            <label class="text-secondary mb-1" style="font-size: 13px;">Phone Number *</label>
            <input type="tel" id="edit-trainee-phone" class="apple-input mb-3" placeholder="e.g., 09 123 456 789" inputmode="tel" autocomplete="tel" spellcheck="false" required>

            <label class="text-secondary mb-1" style="font-size: 13px;">Email Address (Optional)</label>
            <input type="email" id="edit-trainee-email" class="apple-input mb-3" placeholder="e.g., trainee@example.com" autocomplete="email" spellcheck="false">

            <label class="text-secondary mb-1" style="font-size: 13px;">Shop / Business Name (Optional)</label>
            <input type="text" id="edit-trainee-shop" class="apple-input mb-3" placeholder="e.g., Apple Art Service" autocomplete="organization">

            <label class="text-secondary mb-1" style="font-size: 13px;">Residential Address (Optional)</label>
            <input type="text" id="edit-trainee-address" class="apple-input mb-4" placeholder="Enter street address, township, or city" autocomplete="street-address">

            <label class="text-secondary mb-1" style="font-size: 13px;">Roll Call Group</label>
            <select id="edit-trainee-rollcall-group" class="apple-input mb-4">
                <option value="Weekday">Weekday Student</option>
                <option value="Weekend">Weekend Student</option>
            </select>

            <label class="profile-status-toggle mb-4">
                <input type="checkbox" id="edit-trainee-active">
                <span>
                    <strong>Active Student</strong>
                    <small>Show this trainee in main pages, roll call, course marking, and repair comments.</small>
                </span>
            </label>
            
            <div class="d-flex justify-content-between align-items-center mt-3">
                <button type="button" class="btn btn-outline-danger px-4 py-2" onclick="deleteTrainee()">Delete</button>
                <div class="d-flex gap-2 ms-auto">
                    <button type="button" class="btn btn-dark px-4 py-2" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-premium px-4 py-2">Save Changes</button>
                </div>
            </div>
        </form>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="curriculumModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content carbon-modal-content">
      <div class="modal-body p-4">
        <h4 class="fw-bold mb-4" id="curr-modal-title">Add Module</h4>
        <form onsubmit="submitCurriculum(event)">
            <input type="hidden" id="curr-id">
            
            <label class="text-secondary mb-2" style="font-size: 13px;">Module Type</label>
            <select id="curr-type" class="apple-input mb-3" style="cursor: pointer;" required>
                <option value="Course">Theory (Course)</option>
                <option value="Practical">Practical</option>
            </select>

            <label class="text-secondary mb-2" style="font-size: 13px;">Header / Category</label>
            <input type="text" id="curr-category" list="category-suggestions" class="apple-input mb-3" placeholder="e.g., Basic Electronics or Hardware Repair" autocomplete="off" required>
            <datalist id="category-suggestions"></datalist>
            
            <label class="text-secondary mb-2" style="font-size: 13px;">Module Name</label>
            <input type="text" id="curr-title" class="apple-input mb-4" placeholder="e.g., Micro-soldering Techniques" required>
            
            <div class="d-flex justify-content-between align-items-center mt-3">
                <button type="button" class="btn btn-outline-danger px-4 py-2" id="curr-delete-btn" style="display:none;" onclick="deleteCurriculum()">Delete</button>
                <div class="d-flex gap-2 ms-auto">
                    <button type="button" class="btn btn-dark px-4 py-2" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-premium px-4 py-2">Save Module</button>
                </div>
            </div>
        </form>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="studentProfileModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-xl">
    <div class="modal-content carbon-modal-content">
      <div class="modal-body p-0">
        <div class="modal-profile-header">
            <div class="profile-large-avatar" id="modal-profile-avatar">-</div>
            <div style="min-width:0;">
                <div class="curriculum-category m-0 mb-2">Student Profile</div>
                <h3 class="fw-bold m-0" id="modal-profile-name">Student</h3>
                <div class="text-secondary mt-2" id="modal-profile-sub" style="font-size:14px;"></div>
            </div>
            <div class="d-flex gap-2 ms-auto">
                <label class="btn btn-dark px-3 py-2 m-0" style="font-size:13px;">
                    Upload Photo
                    <input type="file" id="modal-photo-file" accept="image/*" hidden onchange="uploadActiveProfilePhoto(this, 'modal')">
                </label>
                <button type="button" class="btn btn-dark px-3 py-2" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
        <div class="p-4" id="modal-profile-content">
            <div class="segmented-control modal-profile-tabs" id="modal-profile-tabs">
                <button class="segment-btn active" id="modal-tab-theory" onclick="switchModalProfileTab('Theory')">Course</button>
                <button class="segment-btn" id="modal-tab-practical" onclick="switchModalProfileTab('Practical')">Practical</button>
                <button class="segment-btn" id="modal-tab-realworld" onclick="switchModalProfileTab('Realworld')">Live Repair Sessions</button>
                <button class="segment-btn" id="modal-tab-history" onclick="switchModalProfileTab('History')">Logs</button>
                <button class="segment-btn" id="modal-tab-attendance" onclick="switchModalProfileTab('Attendance')">Attendance</button>
            </div>
            <div id="modal-profile-pane-content">
                <div class="text-center py-5"><div class="spinner-border spinner-border-sm text-secondary"></div></div>
            </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="realWorldRepairModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content carbon-modal-content">
      <div class="modal-body p-4">
        <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
            <div>
                <div class="curriculum-category m-0">Live Repair Sessions</div>
                <h4 class="fw-bold mt-2 mb-1">Add Repair Comment</h4>
                <p class="text-secondary m-0" style="font-size: 14px;">This is a comment record, not a course or practical module.</p>
            </div>
        </div>

        <form onsubmit="submitRealWorldRepair(event)">
            <label class="text-secondary mb-2" style="font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Repair Title</label>
            <input type="text" id="real-repair-title" class="apple-input mb-3" placeholder="e.g., iPhone 13 Pro green screen repair" required>

            <label class="text-secondary mb-2" style="font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Authorized Instructor</label>
            <select id="real-repair-trainer" class="apple-input mb-3" style="cursor: pointer;" required>
                <option value="Instructor" disabled selected>Select an instructor...</option>
            </select>

            <label class="text-secondary mb-2" style="font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Repair Comment</label>
            <textarea id="real-repair-comment" class="apple-input mb-3" rows="4" placeholder="Write the live repair session note..." style="resize: none;" required></textarea>

            <div class="d-flex justify-content-between align-items-center mb-2">
                <div class="curriculum-category m-0">Student List</div>
                <button type="button" class="btn btn-dark px-3 py-1" style="font-size: 13px;" onclick="toggleRealRepairAllStudents()">Select All</button>
            </div>
            <div id="real-repair-student-list" class="module-mark-student-list"></div>

            <div class="d-flex justify-content-end gap-2 mt-4">
                <button type="button" class="btn btn-dark px-4 py-2" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-premium px-4 py-2">Save Repair Comment</button>
            </div>
        </form>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="moduleMarkModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content carbon-modal-content">
      <div class="modal-body p-4">
        <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
            <div>
                <div class="curriculum-category m-0" id="module-mark-type">Module</div>
                <h4 class="fw-bold mt-2 mb-1" id="module-mark-title">Mark Module</h4>
                <p class="text-secondary m-0" style="font-size: 14px;">Select students to show this module as completed.</p>
            </div>
        </div>

        <form onsubmit="submitModuleMark(event)">
            <input type="hidden" id="module-mark-item-id">

            <label class="text-secondary mb-2" style="font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Authorized Instructor</label>
            <select id="module-mark-trainer" class="apple-input mb-3" style="cursor: pointer;" required>
                <option value="Instructor" disabled selected>Select an instructor...</option>
            </select>

            <label class="text-secondary mb-2" style="font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Repair / Assessment Comment</label>
            <textarea id="module-mark-note" class="apple-input mb-3" rows="3" placeholder="Optional note for course/practical, required for live repair session..." style="resize: none;"></textarea>

            <div class="d-flex justify-content-between align-items-center mb-2">
                <div class="curriculum-category m-0">Student List</div>
                <button type="button" class="btn btn-dark px-3 py-1" style="font-size: 13px;" onclick="toggleModuleMarkAllStudents()">Select All</button>
            </div>
            <div id="module-mark-student-list" class="module-mark-student-list"></div>

            <div class="d-flex justify-content-end gap-2 mt-4">
                <button type="button" class="btn btn-dark px-4 py-2" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-premium px-4 py-2">Save Selected Students</button>
            </div>
        </form>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="trainerModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content carbon-modal-content">
      <div class="modal-body p-4">
        <h4 class="fw-bold mb-4" id="trainer-modal-title">Add Trainer</h4>
        <form onsubmit="submitTrainer(event)">
            <input type="hidden" id="trainer-id">
            <input type="hidden" id="trainer-photo-path">
            <div class="profile-upload-row mb-4">
                <div class="profile-upload-preview" id="trainer-photo-preview">?</div>
                <div class="flex-grow-1" style="min-width:0;">
                    <div class="fw-bold mb-1" style="font-size:14px;">Instructor Photo</div>
                    <div class="text-secondary mb-2" style="font-size:12px;">Shown in instructor list and trainer selectors. Max 2MB.</div>
                    <label class="btn btn-dark px-3 py-2 m-0" style="font-size:13px;">
                        Upload Photo
                        <input type="file" id="trainer-photo-file" accept="image/*" hidden onchange="uploadTrainerPhotoInput(this)">
                    </label>
                </div>
            </div>
            
            <label class="text-secondary mb-2" style="font-size: 13px;">Full Name</label>
            <input type="text" id="trainer-name" class="apple-input mb-3" placeholder="e.g., Kyaw Kyaw" autocomplete="name" required>
            
            <label class="text-secondary mb-2" style="font-size: 13px;">Assigned Role</label>
            <select id="trainer-role" class="apple-input mb-4" style="cursor: pointer;" required>
                <option value="Instructor">Instructor</option>
                <option value="Assistant Instructor">Assistant Instructor</option>
                <option value="Trainer">Trainer</option>
                <option value="Assistant Trainer">Assistant Trainer</option>
            </select>
            
            <div class="d-flex justify-content-between gap-2 mt-3">
                <button type="button" class="btn btn-outline-danger px-4 py-2" id="trainer-delete-btn" style="display:none;" onclick="deleteTrainer()">Delete</button>
                <div class="d-flex justify-content-end gap-2 ms-auto">
                    <button type="button" class="btn btn-dark px-4 py-2" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-premium px-4 py-2">Save Instructor</button>
                </div>
            </div>
        </form>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="confirmActionModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content carbon-modal-content">
      <div class="modal-body p-4">
        <div class="d-flex align-items-start gap-3 mb-3">
            <span class="material-symbols-rounded" id="confirm-action-icon" style="font-size:32px; color:var(--system-red);">warning</span>
            <div>
                <h4 class="fw-bold mb-1" id="confirm-action-title">Confirm Action</h4>
                <p class="text-secondary m-0" id="confirm-action-message" style="font-size:14px;">Continue?</p>
            </div>
        </div>
        <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" class="btn btn-dark px-4 py-2" id="confirm-action-cancel">Cancel</button>
            <button type="button" class="btn btn-outline-danger px-4 py-2" id="confirm-action-ok">Delete</button>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="signOffModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content carbon-modal-content">
      <div class="modal-body p-4">
        
        <div class="d-flex align-items-center gap-2 mb-1">
            <span class="material-symbols-rounded" style="color: var(--system-green); font-size: 28px;">check_circle</span>
            <h4 class="fw-bold m-0" style="letter-spacing: -0.5px;">Sign Off Module</h4>
        </div>
        <p class="text-secondary mb-4 mt-2" id="sign-off-title" style="font-size: 15px; line-height: 1.4;"></p>

        <form onsubmit="submitSignOff(event)">
            <input type="hidden" id="sign-off-item-id">

            <label class="text-secondary mb-2" style="font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Authorized Instructor</label>
            <select id="sign-off-trainer" class="apple-input mb-4" style="cursor: pointer;" required>
                <option value="Instructor" disabled selected>Select an instructor...</option>
            </select>

            <label class="text-secondary mb-2" style="font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Assessment Notes (Optional)</label>
            <textarea id="sign-off-note" class="apple-input mb-4" rows="3" placeholder="e.g., Demonstrated excellent practical skills during the hardware test today..." style="resize: none;"></textarea>

            <div class="d-flex justify-content-end gap-2 mt-3">
                <button type="button" class="btn btn-dark px-4 py-2" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn-success-soft">Confirm Completion</button>
            </div>
        </form>

      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="revertModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content carbon-modal-content">
      <div class="modal-body p-4">
        
        <div class="d-flex align-items-center gap-2 mb-1">
            <span class="material-symbols-rounded" style="color: var(--system-red); font-size: 28px;">undo</span>
            <h4 class="fw-bold m-0 text-danger" style="letter-spacing: -0.5px; color: var(--system-red) !important;">Revert Module</h4>
        </div>
        <p class="text-secondary mb-4 mt-2" id="revert-title" style="font-size: 15px; line-height: 1.4;"></p>

        <form onsubmit="submitRevert(event)">
            <input type="hidden" id="revert-item-id">

            <label class="text-secondary mb-2" style="font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Reason for Reverting</label>
            <textarea id="revert-note" class="apple-input mb-4" rows="3" placeholder="e.g., Trainee requires further practice on micro-soldering before proceeding..." style="resize: none;" required></textarea>

            <div class="d-flex justify-content-end gap-2 mt-3">
                <button type="button" class="btn btn-dark px-4 py-2" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn-danger-soft">Confirm Revert</button>
            </div>
        </form>

      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="userModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content carbon-modal-content">
      <div class="modal-body p-4">
        <h4 class="fw-bold mb-4">Add System User</h4>
        
        <form id="user-form" onsubmit="saveUser(event)">
            <label class="text-secondary mb-1" style="font-size: 13px;">Full Name *</label>
            <input type="text" id="user-name" class="apple-input mb-3" placeholder="e.g., Jane Doe" required>
            
            <label class="text-secondary mb-1" style="font-size: 13px;">Email Address *</label>
            <input type="email" id="user-email" class="apple-input mb-3" placeholder="e.g., admin@appleart.com" required>

            <label class="text-secondary mb-1" style="font-size: 13px;">Password *</label>
            <input type="password" id="user-password" class="apple-input mb-3" placeholder="Initial password" required>

            <label class="text-secondary mb-1" style="font-size: 13px;">System Role *</label>
            <select id="user-role" class="apple-input mb-4" required>
                <option value="user">Instructor / User (No Admin Access)</option>
                <option value="admin">Admin (Full Access)</option>
            </select>
            
            <div id="user-error" style="display:none; color: var(--system-red); font-size: 13px; margin-bottom: 12px;"></div>
            
            <div class="d-flex justify-content-end gap-2 mt-2">
                <button type="button" class="btn btn-dark px-4 py-2" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-premium px-4 py-2" id="user-save-btn">Create User</button>
            </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
async function saveUser(e) {
    e.preventDefault();
    const btn = document.getElementById('user-save-btn');
    const err = document.getElementById('user-error');
    btn.disabled = true; err.style.display = 'none';

    try {
        const formData = new FormData();
        formData.append('name', document.getElementById('user-name').value);
        formData.append('email', document.getElementById('user-email').value);
        formData.append('password', document.getElementById('user-password').value);
        formData.append('role', document.getElementById('user-role').value);

        const res = await fetch('api/register_user.php', { method: 'POST', body: formData });
        const result = await res.json();

        if (result.status === 'success') {
            bootstrap.Modal.getInstance(document.getElementById('userModal')).hide();
            loadSystemUsers();
        } else {
            err.innerText = result.message || 'Error saving user';
            err.style.display = 'block';
        }
    } catch(e) {
        err.innerText = 'Network error';
        err.style.display = 'block';
    } finally {
        btn.disabled = false;
    }
}
</script>
