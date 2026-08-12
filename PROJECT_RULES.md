# Apple Art — React Rewrite: Project Rules & Policies

> **Set once, before writing UI.** Every contributor (human or AI) follows this.
> Version 1.0 — 2026-08-07

## 1. Goal

Rewrite the Apple Art Student Management System as a **React + shadcn/ui** SPA.
- **Keep**: the existing workflow (training academy operations) and the MySQL database (`zvryylsz_appleart`) exactly as-is.
- **Reuse**: the existing PHP API (`~/Documents/Codex/appleart 2/api/*.php`) as the backend — no backend rewrite.
- **Replace**: the entire UI. It must look **completely different** from the old PHP UI. Old UI = blue gradient pills, icon rail, segmented bars, heavy cards. New UI = neutral zinc + emerald accent, wide labeled sidebar, tables over cards, subtle surfaces.

## 2. Stack (locked)

- **Vite + React 19 + TypeScript** (strict)
- **Tailwind CSS v4** (CSS-first config)
- **shadcn/ui** components (radix primitives) — the ONLY button/dialog/table/etc. source
- **React Router v7** (data router)
- **TanStack Query v5** for server state
- **zod** for validation, **react-hook-form** for forms
- No other UI libraries. No CSS frameworks (no Bootstrap, no DaisyUI).

## 3. Architecture rules

1. **Backend contract**: React calls the PHP API at `/api/*` via Vite dev proxy → `http://127.0.0.1:8088`. Production build is served by PHP or any static host with the proxy replaced by same-origin `/api`.
2. **Never mutate server data except through the PHP endpoints** (`api/*.php`). No direct DB access from React.
3. **Auth**: `api/login.php` sets the PHP session cookie; send `credentials: 'include'`; a new `api/me.php` endpoint reports the current user (id, name, role) or 401. All fetches go through one `apiFetch()` wrapper (adds credentials, JSON, error handling, 401 → redirect to /login).
4. **Server state**: every backend call is a TanStack Query keyed by `[domain, params]`; mutations invalidate the affected keys. No ad-hoc fetch in components.
5. **Types**: one `types/` file per domain generated from the DB schema (see §6). Never use `any`.
6. **Routing**: feature routes mirror the old views but with new URLs:
   - `/` dashboard (stats + today's plan)
   - `/students` roster
   - `/rollcall` live roll call
   - `/courses` theory/practical marking
   - `/exams`, `/payments`, `/calendar`, `/today` (big screen), `/contacts`, `/admin/*`
   - `/login`
7. **Layout**: one app shell (`AppShell`) with collapsible sidebar + topbar; route content renders inside `<Outlet/>`. Mobile: sidebar becomes a Sheet; topbar shows breadcrumbs + user menu.

## 4. Responsive policy (design from the start, per breakpoint)

| Breakpoint | Behavior |
|---|---|
| ≥1280px (desktop) | Full sidebar (labeled), multi-column layouts, dense tables |
| 768–1279px (tablet) | Sidebar collapsed to icon rail; grids drop to 2 cols; tables stay scrollable |
| <768px (mobile) | Sidebar hidden → Sheet + hamburger; single column; tables become cards; touch targets ≥44px |

Rules:
- **Mobile-first CSS**: `grid-cols-1` → `md:grid-cols-2` → `xl:grid-cols-3`. No px-fixed widths on containers; only `minmax(0,1fr)` grids.
- Every page must be verified at 390, 768, 1280, 1920 before it's "done".
- No horizontal page scroll ever — internal scroll containers only (`overflow-x-auto` on tables/carousels).
- Sticky headers/tables keep `z-10`; modals ≥`z-50`; toasts topmost.

## 5. Design system (locked tokens)

- **Palette**: neutral zinc surfaces (`--background #fafafa`, `--card #ffffff`, `--border zinc-200`), **emerald** primary (`#059669`), amber warnings, red destructive. Dark mode: zinc-950 surfaces. (Deliberately NOT blue.)
- **Radius**: `--radius 0.5rem` (8px) — crisp, modern SaaS.
- **Typography**: Inter (system fallback), headings font-semibold tracking-tight; numbers `tabular-nums`.
- **Spacing**: 4px scale; page padding `p-4 md:p-6 lg:p-8`; consistent card padding `p-5`.
- **Components in use**: Button, Card, Table, Dialog, Tabs, Badge, Progress, Avatar, DropdownMenu, Select, Switch, Sheet, Breadcrumb, Command (⌘K nav), Skeleton, Toast.
- **States**: loading = Skeletons (never spinners everywhere); empty = icon + title + hint + CTA; error = inline Alert with retry.

## 6. Data layer (from DB schema — `zvryylsz_appleart`)

| Domain | Table(s) | Key columns |
|---|---|---|
| students | `students` | id, name, phone, email, address, photo_path, is_active, rollcall_group(Weekday/Weekend), shop_name, enrollment_date |
| auth | `users` | id, name, email, role(user/admin/master_admin) |
| curriculum | `curriculum_items` | id, type(Course/Practical), category, title, sort_order |
| progress | `student_progress` | student_id, item_id, status(Pending/Completed), completion_date, trainer_name |
| attendance | `attendance` | student_id, date, status(Present/Absent/Late) |
| rollcall | `rollcall_logs`, `app_settings`(rollcall_schedules) | student_id, status; days/start/end per group |
| payments | `student_payments` | student_id, total/first/second amounts + paid_at, reminder_date, note |
| exams | `student_exams` | student_id, exam_name, score, max_score, exam_date, note |
| repairs | `real_world_repairs` | student_id, repair_title, comment, trainer_name |
| practice boards | `student_practice_boards` | board_code, device_model, board_condition, task_type, workflow_status, result_status, attempts |
| trainers | `trainers` | id, name, role, photo_path |
| schedule | `teacher_schedule` | schedule_date, start/end_time, teacher_name, student_group, lesson_type, topic, status |

**Naming**: `Student`, `CurriculumItem`, `StudentProgress`, `AttendanceRecord`, `PaymentPlan`, `ExamResult`, `RepairNote`, `Trainer`, `ScheduleEntry`, `RollcallSettings`.

## 7. Code conventions

- Feature folders: `src/features/<domain>/` containing `api.ts`, `types.ts`, `components/`, `pages/`.
- Shared: `src/lib/` (apiFetch, utils, queryClient), `src/components/ui/` (shadcn only), `src/components/layout/`.
- All user-facing text in **English** (old app was English; Ko Hein reads both — keep English for UI, comments may be English).
- Components: function declarations, named exports, `cn()` for classes.
- No `useEffect` data fetching — TanStack Query only.
- Commit granular: one feature per commit.

## 8. Definition of done (each page)

1. Works with **real data** from the PHP API.
2. Responsive at 390 / 768 / 1280 / 1920 (no horizontal scroll).
3. Loading / empty / error states handled.
4. Dark mode works.
5. Keyboard: tabs focusable, dialogs trap focus, ⌘K nav works.
6. Visually distinct from the old UI.

## 9. Rollout

Phase 1 (foundation): scaffold, tokens, shell, auth, dashboard.
Phase 2 (core): students, curriculum/courses marking, rollcall, payments, exams.
Phase 3 (advanced): calendar, today screen, admin, practice boards, repairs.
Phase 4 (hardening): offline hints, perf, deploy decision (replace PHP UI or co-host).
