<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apple Art | Student Management System</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.12.10/dist/full.min.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,1,0" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script>
        const savedTheme = localStorage.getItem('appleart_system_theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
        const savedUIMode = localStorage.getItem('appleart_ui_mode') || 'premium';
        document.documentElement.setAttribute('data-ui', savedUIMode);
    </script>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
    <style>


/* =======================================================================
           DRAG AND DROP & EDIT MODE STYLES
           ======================================================================= */
        .category-block {
            background: var(--bg-surface); border: 1px solid var(--separator);
            border-radius: 12px; margin-bottom: 24px; overflow: hidden;
        }
        .category-header {
            padding: 16px 20px; background: var(--bg-base); border-bottom: 1px solid var(--separator);
            font-weight: 700; font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px;
            display: flex; align-items: center;
        }
        .sortable-ghost { opacity: 0.4; background-color: var(--brand-blue-light) !important; }
        [data-theme="dark"] .sortable-ghost { background-color: rgba(0, 93, 255, 0.1) !important; }

        /* Drag Handles are HIDDEN normally */
        .drag-handle {
            cursor: grab; color: var(--text-secondary); opacity: 0.5; transition: opacity 0.2s;
            margin-right: 12px; display: none; 
        }
        .drag-handle:active { cursor: grabbing; }
        
        /* When EDIT MODE is active */
        .edit-mode-active .drag-handle {
            display: inline-block; /* Show handles */
        }
        .edit-mode-active .ios-list-item {
            border: 1px dashed var(--system-blue); /* Visual cue that it can be moved */
            background-color: var(--bg-base);
        }
        .edit-mode-active .edit-icon {
            display: none !important; /* Hide the pencil icon while reordering */
        }
		
        /* =======================================================================
           "SOFT BRIGHT UI" - PRECISION COLOR PALETTE
           ======================================================================= */
        :root {
            /* Map to DaisyUI OKLCH variables to apply themes to custom components */
            --brand-blue: var(--fallback-p, oklch(var(--p, 0.5 0.2 260)));        
            --brand-blue-hover: var(--fallback-pf, oklch(var(--pf, 0.45 0.2 260)));
            --brand-blue-light: oklch(var(--p, 0.5 0.2 260) / 0.1);  
            --brand-green: var(--fallback-su, oklch(var(--su, 0.6 0.2 150)));       
            --brand-red: var(--fallback-er, oklch(var(--er, 0.6 0.2 20)));         
            --brand-purple: var(--fallback-s, oklch(var(--s, 0.5 0.2 300)));      

            --bg-base: var(--fallback-b2, oklch(var(--b2, 0.96 0 0)));
            --bg-surface: var(--fallback-b1, oklch(var(--b1, 1 0 0)));
            --bg-surface-hover: var(--fallback-b3, oklch(var(--b3, 0.92 0 0)));
            --text-primary: var(--fallback-bc, oklch(var(--bc, 0.2 0 0)));
            --text-secondary: oklch(var(--bc, 0.2 0 0) / 0.65);
            --separator: oklch(var(--bc, 0.2 0 0) / 0.12);
            
            --system-blue: var(--brand-blue);
            --system-green: var(--brand-green);
            --system-red: var(--brand-red);
            --system-orange: var(--fallback-wa, oklch(var(--wa, 0.8 0.2 80)));
        }

        /* Apple Custom Theme (Fallback when data-theme="apple") */
        [data-theme="apple"] {
            --bg-base: #eaf1fa; 
            --bg-surface: #ffffff;
            --bg-surface-hover: #f8fafc;
            --text-primary: #0f172a;
            --text-secondary: #64748b;
            --separator: #d2dceb; 
            --brand-blue: #005dff;        
            --brand-blue-hover: #004ecc;
            --brand-blue-light: #e6efff;
            --chip-bg: #e6efff;
            --brand-green: #00c853;       
            --brand-red: #ff3d00;         
            --brand-purple: #9c27b0;      
            --system-blue: var(--brand-blue);
            --system-green: var(--brand-green);
            --system-red: var(--brand-red);
            --system-orange: #f59e0b;
        }

        /* =======================================================================
           GLOBAL RESET & TYPOGRAPHY
           ======================================================================= */
        body {
            background-color: var(--bg-base) !important;
            color: var(--text-primary) !important;
            font-family: 'Inter', sans-serif !important;
            margin: 0; padding: 0; height: 100vh; overflow: hidden;
            transition: background-color 0.2s ease, color 0.2s ease;
        }

        .btn, .modal-content, .apple-input, .student-card, .ios-list, .ios-list-item, .rollcall-chip, .dash-stat-card {
            border-radius: 6px !important; 
            font-family: 'Inter', sans-serif;
        }

        /* =======================================================================
           CORE APP LAYOUT & ALIGNMENT
           ======================================================================= */
        .topbar {
            height: 60px; background-color: var(--bg-surface); color: var(--text-primary);
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 32px; border-bottom: 1px solid var(--separator); z-index: 100;
            box-shadow: 0 1px 2px rgba(0,0,0,0.02);
        }
        .topbar-brand { font-weight: 700; font-size: 16px; letter-spacing: -0.3px; color: var(--system-blue); }
        .topbar-time { color: var(--text-secondary); font-size: 13px; font-weight: 500; letter-spacing: 0.2px; }
        
        /* THEME TOGGLE & PROFILE ALIGNMENT */
        .theme-toggle { 
            cursor: pointer; display: flex; align-items: center; justify-content: center; 
            width: 36px; height: 36px; border-radius: 50%; color: var(--text-secondary); 
            transition: 0.2s; margin-right: 8px; 
        }
        .theme-toggle:hover { background-color: var(--bg-surface-hover); color: var(--system-blue); }
        
        .topbar-user { 
            display: flex; align-items: center; gap: 12px; padding: 4px 8px; 
            border-radius: 8px; transition: 0.2s ease; cursor: pointer; 
        }
        .topbar-user:hover { background-color: var(--bg-surface-hover); }
        
        .topbar-avatar { 
            width: 32px; height: 32px; background-color: var(--system-blue); 
            color: #fff; border-radius: 6px; display: flex; align-items: center; 
            justify-content: center; font-weight: 700; box-shadow: 0 2px 4px rgba(0,93,255,0.2); 
        }

        .app-body { display: flex; height: calc(100vh - 60px); width: 100vw; }

        /* Sidebar Nav */
        .sidebar {
            width: 80px; background-color: var(--bg-surface); border-right: 1px solid var(--separator);
            display: flex; flex-direction: column; align-items: center; padding-top: 32px; gap: 12px;
        }
        .nav-icon {
            width: 48px; height: 48px; display: flex; justify-content: center; align-items: center;
            color: var(--text-secondary); cursor: pointer; transition: all 0.2s ease;
            border-radius: 8px;
        }
        .nav-icon:hover { background-color: var(--bg-surface-hover); color: var(--system-blue); }
        .nav-icon.active { background-color: var(--brand-blue-light); color: var(--system-blue); }
        [data-theme="dark"] .nav-icon.active { background-color: rgba(0, 93, 255, 0.15); }

        /* Master List Pane */
        .master-pane { width: 360px; background-color: var(--bg-surface); border-right: 1px solid var(--separator); display: flex; flex-direction: column; z-index: 10; }
        .master-header { padding: 32px 24px 20px; border-bottom: 1px solid var(--separator); background-color: var(--bg-surface); }
        .master-title { font-size: 22px; font-weight: 700; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; letter-spacing: -0.5px; }
        .add-btn { background: transparent; border: none; color: var(--system-blue); cursor: pointer; padding: 0; display: flex; transition: 0.2s; }
        .add-btn:hover { transform: scale(1.1); }
        
        .student-list-container { flex: 1; overflow-y: auto; padding: 12px; background-color: var(--bg-base); }
        
        /* Detail Profile Pane */
        .detail-pane { flex: 1; background-color: var(--bg-base); display: flex; flex-direction: column; overflow-y: auto; }
        
        .view-section { display: none; padding: 40px 48px; max-width: 1200px; margin: 0 auto; width: 100%; }
        .view-section.active { display: block; }
        .sidebar ul li a.active span { font-variation-settings: 'FILL' 1; }

        /* PREMIUM ANIMATED BACKGROUND ORBS */
        body::before, body::after, .global-bg-orb-3 {
            content: '';
            position: fixed;
            border-radius: 50%;
            filter: blur(140px);
            z-index: -1;
            animation: float-orbs 25s infinite alternate ease-in-out;
            pointer-events: none;
        }
        body::before {
            width: 800px; height: 800px;
            background: rgba(10, 132, 255, 0.08); /* Apple Blue */
            top: -200px; left: -200px;
        }
        body::after {
            width: 700px; height: 700px;
            background: rgba(94, 92, 230, 0.06); /* Apple Purple */
            bottom: -100px; right: 10%;
            animation-delay: -7s;
            animation-duration: 30s;
        }
        .global-bg-orb-3 {
            width: 600px; height: 600px;
            background: rgba(48, 209, 88, 0.05); /* Apple Green */
            top: 30%; right: -150px;
            animation-delay: -14s;
            animation-duration: 35s;
        }
        @keyframes float-orbs {
            0% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(60px, 90px) scale(1.05); }
            100% { transform: translate(-40px, 50px) scale(0.95); }
        }

        /* Ensure content layers above orbs */
        .topbar, .sidebar, .app-body { position: relative; z-index: 10; }
        .app-body { background: transparent !important; }
        .topbar { background: oklch(var(--b1) / 0.85); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); }
        .sidebar { background: oklch(var(--b2) / 0.9); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); }

        .hidden-pane { display: none !important; }

        /* =======================================================================
           COMPONENTS
           ======================================================================= */
        .btn-premium, .btn-gold {
            background-color: var(--system-blue) !important; color: #ffffff !important;
            border: none; padding: 10px 18px; font-size: 13px; font-weight: 600; cursor: pointer; transition: all 0.2s ease;
            box-shadow: 0 2px 4px rgba(0, 93, 255, 0.15);
        }
        .btn-premium:hover, .btn-gold:hover { background-color: var(--brand-blue-hover) !important; transform: translateY(-1px); box-shadow: 0 4px 8px rgba(0, 93, 255, 0.25); }
        
        .btn-dark { background-color: var(--bg-surface) !important; color: var(--text-primary) !important; border: 1px solid var(--separator); font-weight: 500; }
        .btn-dark:hover { background-color: var(--bg-surface-hover) !important; border-color: var(--text-secondary); }
        .btn-icon-label {
            display: inline-flex !important;
            align-items: center;
            justify-content: center;
            gap: 8px;
            white-space: nowrap;
        }
        .btn-icon-label .material-symbols-rounded {
            font-size: 18px;
            line-height: 1;
        }
        
        .btn-outline-danger { border: 1px solid var(--system-red) !important; color: var(--system-red) !important; background: transparent; font-weight: 500; }
        .btn-outline-danger:hover { background-color: rgba(255, 61, 0, 0.1) !important; }

        .apple-input {
            background-color: var(--bg-surface) !important; border: 1px solid var(--separator) !important;
            color: var(--text-primary) !important; padding: 12px 16px; width: 100%; box-sizing: border-box; outline: none;
            transition: all 0.2s ease; box-shadow: inset 0 1px 2px rgba(0,0,0,0.01);
        }
        .apple-input:focus { border: 1px solid var(--system-blue) !important; box-shadow: 0 0 0 3px var(--brand-blue-light) !important; }
        [data-theme="dark"] .apple-input:focus { box-shadow: 0 0 0 3px rgba(0, 93, 255, 0.2) !important; }

        .segmented-control { display: flex; gap: 12px; background: transparent; margin-bottom: 32px; padding: 0; width: 100%; flex-wrap: wrap; }
        .segmented-control.profile-tabs { flex-wrap: nowrap; justify-content: flex-start; align-items: center; overflow-x: auto; padding-bottom: 8px; -webkit-overflow-scrolling: touch; }
        .segment-btn { flex: none; flex-shrink: 0; white-space: nowrap; background: transparent; color: var(--text-secondary); border: 1px solid transparent; padding: 8px 16px; font-size: 14px; font-weight: 500; cursor: pointer; transition: all 0.2s ease; border-radius: 6px !important; }
        .segment-btn:hover { background: var(--bg-surface-hover); color: var(--text-primary); }
        .segment-btn.active { background: var(--system-blue); color: #fff; box-shadow: 0 2px 4px rgba(0, 93, 255, 0.2); }

        /* =======================================================================
           SPECIFIC UI ELEMENTS
           ======================================================================= */
        .dash-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 24px; margin-bottom: 40px; }
        .dash-stat-card { background-color: var(--bg-surface); padding: 28px; border: 1px solid var(--separator); box-shadow: 0 1px 3px rgba(0,0,0,0.02); display: flex; flex-direction: column; }
        .dash-stat-card h3 { font-size: 40px; font-weight: 700; margin: 0 0 4px; color: var(--system-blue); line-height: 1; }
        .dash-stat-card p { font-size: 13px; color: var(--text-secondary); margin: 0; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
        .section-title-row {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 8px;
        }
        .section-title-icon {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: var(--brand-blue-light);
            color: var(--brand-blue);
            font-size: 24px;
            flex: 0 0 auto;
        }
        [data-theme="dark"] .section-title-icon {
            background: rgba(37, 99, 235, 0.18);
            color: #93c5fd;
        }
        .stat-card-head {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 16px;
        }
        .stat-card-head .material-symbols-rounded {
            color: var(--brand-blue);
            font-size: 21px;
        }

        .student-card { padding: 16px; margin-bottom: 8px; border: 1px solid var(--separator); cursor: pointer; display: flex; align-items: flex-start; gap: 16px; transition: all 0.2s ease; background: var(--bg-surface); box-shadow: 0 1px 2px rgba(0,0,0,0.01); }
        .student-card:hover { transform: translateY(-1px); box-shadow: 0 4px 6px rgba(0,0,0,0.03); border-color: #cbd5e1; }
        [data-theme="dark"] .student-card:hover { border-color: #475569; }
        .student-card.active { border: 1px solid var(--system-blue); box-shadow: 0 0 0 2px var(--brand-blue-light); }
        [data-theme="dark"] .student-card.active { box-shadow: 0 0 0 2px rgba(0, 93, 255, 0.2); }
        
        .student-avatar { width: 48px; height: 48px; background-color: var(--brand-blue-light); color: var(--system-blue); display: flex; justify-content: center; align-items: center; font-weight: 700; font-size: 18px; border-radius: 50% !important; flex-shrink: 0; }
        [data-theme="dark"] .student-avatar { background-color: rgba(0, 93, 255, 0.15); }
        
        .student-info { flex-grow: 1; margin-top: 2px; }
        .student-info h4 { margin: 0 0 8px; font-size: 15px; font-weight: 700; color: var(--text-primary); letter-spacing: -0.2px; }

        .roster-prog-container { margin-top: 10px; padding-right: 4px; }
        .roster-prog-label { display: flex; justify-content: space-between; font-size: 11px; font-weight: 600; color: var(--text-secondary); margin-bottom: 6px; letter-spacing: 0.2px; }
        .roster-prog-track { width: 100%; height: 6px; background-color: var(--bg-base); display: block; border-radius: 3px; border: 1px solid var(--separator); }
        .roster-prog-fill-theory { height: 100%; background-color: var(--system-blue); width: 0%; transition: width 0.6s ease; border-radius: 2px; }
        .roster-prog-fill-practical { height: 100%; background-color: var(--brand-purple); width: 0%; transition: width 0.6s ease; border-radius: 2px; }

        .profile-header-widget {
            display: flex;
            align-items: center;
            gap: 24px;
            margin-bottom: 24px;
            background:
                radial-gradient(circle at top right, rgba(37, 99, 235, 0.10), transparent 30rem),
                var(--bg-surface);
            padding: 28px 32px;
            border: 1px solid var(--separator);
            box-shadow: var(--shadow-sm);
            border-radius: 10px;
            flex-wrap: wrap;
        }
        .profile-header-actions {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }
        .profile-tabs {
            position: sticky;
            top: 0;
            z-index: 5;
            justify-content: flex-start;
            overflow-x: auto;
            margin-bottom: 24px;
            box-shadow: var(--shadow-sm);
        }
        .profile-tabs .segment-btn {
            white-space: nowrap;
        }
        .profile-large-avatar { width: 80px; height: 80px; background: var(--system-blue); color: #fff; border-radius: 50% !important; display: flex; align-items: center; justify-content: center; font-size: 32px; font-weight: 700; box-shadow: 0 4px 12px rgba(0, 93, 255, 0.2); flex-shrink: 0; }
        .student-avatar.has-photo,
        .report-photo.has-photo,
        .profile-large-avatar.has-photo,
        .profile-upload-preview.has-photo {
            background-size: cover;
            background-position: center;
            color: transparent !important;
            text-shadow: none;
        }
        .student-avatar.placeholder-photo,
        .report-photo.placeholder-photo,
        .profile-large-avatar.placeholder-photo,
        .profile-upload-preview.placeholder-photo,
        .student-contact-avatar.placeholder-photo,
        .marked-student-avatar.placeholder-photo {
            background-image: url('aalogo.png') !important;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-color: var(--bg-base);
            border-radius: 50% !important;
            color: transparent !important;
        }
        .profile-upload-row {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 14px;
            border: 1px solid var(--separator);
            border-radius: 10px;
            background: var(--bg-base);
        }
        .profile-upload-preview {
            width: 64px;
            height: 64px;
            border-radius: 10px;
            background: var(--brand-blue);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: 800;
            flex: 0 0 auto;
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.20);
        }
        .profile-upload-row.uploading {
            opacity: 0.7;
            pointer-events: none;
        }
        .profile-status-toggle {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px;
            border: 1px solid var(--separator);
            border-radius: 10px;
            background: var(--bg-base);
            cursor: pointer;
        }
        .profile-status-toggle input {
            width: 18px;
            height: 18px;
            accent-color: var(--brand-blue);
            flex: 0 0 auto;
        }
        .profile-status-toggle strong {
            display: block;
            font-size: 14px;
            line-height: 1.2;
        }
        .profile-status-toggle small {
            display: block;
            color: var(--text-secondary);
            font-size: 12px;
            line-height: 1.35;
            margin-top: 3px;
        }
        .student-status-badge {
            display: inline-flex;
            align-items: center;
            border: 1px solid rgba(22, 163, 74, 0.35);
            border-radius: 7px;
            color: var(--system-green);
            background: rgba(22, 163, 74, 0.08);
            font-size: 11px;
            font-weight: 800;
            padding: 4px 7px;
            margin-left: 8px;
        }
        .student-status-badge.inactive {
            border-color: rgba(239, 68, 68, 0.35);
            color: var(--system-red);
            background: rgba(239, 68, 68, 0.08);
        }

        .curriculum-category { color: var(--text-secondary); font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; margin: 32px 0 12px 4px; }
        
        /* =======================================================================
           UPGRADED LISTS (Individual Floating Cards to completely stop overlap)
           ======================================================================= */
        .ios-list { 
            background: transparent !important; 
            border: none !important; 
            box-shadow: none !important; 
            margin-bottom: 24px; 
            display: flex;
            flex-direction: column;
            gap: 12px; /* Physical space between every card */
        }
        
        .ios-list-item { 
            background-color: var(--bg-surface);
            border: 1px solid var(--separator);
            border-radius: 12px !important; /* Rounded corners for individual cards */
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
            padding: 20px 24px; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            flex-wrap: wrap; 
            gap: 16px; 
            transition: all 0.2s ease; 
        }

        .ios-list-item:hover { 
            background-color: var(--bg-surface-hover); 
            transform: translateY(-2px); /* Premium lift effect */
            box-shadow: 0 6px 12px rgba(0,0,0,0.04);
            border-color: #cbd5e1;
        }

        [data-theme="dark"] .ios-list-item:hover { 
            border-color: #475569; 
        }
        
        /* Ensures the left side (names/info) allows chips to wrap gracefully */
        .ios-list-item > div:first-child {
            flex: 1 1 200px; 
            min-width: 0; 
        }
        
        .circle-check { 
            width: 22px; height: 22px; border: 2px solid #cbd5e1; margin-right: 16px; 
            display: flex; justify-content: center; align-items: center; transition: all 0.2s ease; 
            border-radius: 4px !important; background-color: var(--bg-surface);
        }
        [data-theme="dark"] .circle-check { border-color: #475569; }
        .ios-list-item.checked .circle-check { background-color: var(--system-blue); border-color: var(--system-blue); }
        .ios-list-item.checked .circle-check::after { content: '✓'; color: #fff; font-size: 14px; font-weight: 700; }
        .ios-list-item.checked .reminder-text { text-decoration: line-through; color: var(--text-secondary); opacity: 0.8; }

        .item-meta { display: flex; gap: 12px; font-size: 12px; font-weight: 500; color: var(--text-secondary); flex-wrap: wrap; }
        .date-badge, .trainer-badge { display: flex; align-items: center; gap: 6px; background: var(--bg-base); padding: 4px 10px; border-radius: 4px; border: 1px solid var(--separator); }

        .timeline-container { border-left: 2px solid var(--separator); margin-left: 12px; padding-left: 28px; margin-top: 24px; }
        .timeline-record { position: relative; margin-bottom: 36px; }
        .timeline-dot { position: absolute; left: -35px; top: 4px; width: 14px; height: 14px; border-radius: 50%; background: var(--system-green); border: 2px solid var(--bg-base); box-shadow: 0 0 0 2px var(--system-green); }
        .timeline-dot.absent { background: var(--system-red); box-shadow: 0 0 0 2px var(--system-red); }
        .timeline-dot.late { background: var(--system-orange); box-shadow: 0 0 0 2px var(--system-orange); }

        /* =======================================================================
           UPGRADED PROFESSIONAL ROLL CALL CHIPS
           ======================================================================= */
        .rollcall-options { 
            display: flex; 
            gap: 8px; /* Slightly tighter gap to fit better on standard screens */
            flex-wrap: nowrap; /* Keep on one line initially */
            flex: 0 0 auto; /* Prevent from shrinking */
            justify-content: flex-end; /* Align right naturally */
        }
        
        .rollcall-chip { 
            padding: 8px 20px; 
            font-size: 13px; 
            font-weight: 600; 
            cursor: pointer; 
            border: 1px solid var(--separator); 
            color: var(--text-secondary); 
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); 
            border-radius: 8px !important; 
            background: var(--bg-surface); 
            user-select: none;
        }

        .rollcall-chip:hover {
            background: var(--bg-base);
            color: var(--text-primary);
            border-color: var(--text-secondary);
        }
        
        .rollcall-chip[data-status="Present"].active { 
            background: var(--system-green); 
            color: #fff; 
            border-color: var(--system-green); 
            box-shadow: 0 4px 12px rgba(0, 200, 83, 0.25); 
            transform: translateY(-1px);
        }
        .rollcall-chip[data-status="Absent"].active { 
            background: var(--system-red); 
            color: #fff; 
            border-color: var(--system-red); 
            box-shadow: 0 4px 12px rgba(255, 61, 0, 0.25); 
            transform: translateY(-1px);
        }
        .rollcall-chip[data-status="Late"].active { 
            background: var(--system-orange); 
            color: #fff; 
            border-color: var(--system-orange); 
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.25); 
            transform: translateY(-1px);
        }
        .rollcall-hero {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 24px;
            align-items: start;
            margin-bottom: 28px;
        }
        .rollcall-summary-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(104px, 1fr));
            gap: 10px;
            min-width: min(540px, 100%);
        }
        .rollcall-summary-card {
            background: var(--bg-surface);
            border: 1px solid var(--separator);
            border-radius: 10px;
            padding: 12px 14px;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: var(--shadow-sm);
        }
        .rollcall-summary-card .material-symbols-rounded {
            width: 34px;
            height: 34px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: var(--bg-base);
            color: var(--brand-blue);
            font-size: 20px;
        }
        .rollcall-summary-card strong {
            display: block;
            font-size: 20px;
            line-height: 1;
        }
        .rollcall-summary-card small {
            display: block;
            color: var(--text-secondary);
            font-size: 11px;
            font-weight: 700;
            margin-top: 4px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .rollcall-summary-card.present .material-symbols-rounded { color: var(--system-green); }
        .rollcall-summary-card.late .material-symbols-rounded { color: var(--system-orange); }
        .rollcall-summary-card.absent .material-symbols-rounded { color: var(--system-red); }
        .rollcall-row {
            align-items: center;
        }
        .rollcall-student {
            display: flex;
            align-items: center;
            gap: 14px;
            min-width: 0;
        }
        .rollcall-student-name {
            font-size: 15px;
            line-height: 1.25;
        }
        .rollcall-student-meta {
            display: flex;
            align-items: center;
            gap: 6px;
            margin-top: 5px;
            font-size: 12px;
            flex-wrap: wrap;
        }
        .rollcall-student-meta .material-symbols-rounded {
            font-size: 15px;
        }
        .rollcall-chip {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            min-height: 38px;
        }
        .rollcall-chip .material-symbols-rounded {
            font-size: 18px;
            line-height: 1;
        }
        .rollcall-workspace {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 380px;
            gap: 24px;
            align-items: start;
        }
        .rollcall-list-panel {
            min-width: 0;
        }
        .rollcall-row {
            position: relative;
            border-left: 3px solid transparent;
        }
        .rollcall-row.active {
            border-color: var(--brand-blue);
            box-shadow: var(--surface-ring), var(--shadow-sm);
        }
        .rollcall-student {
            cursor: pointer;
        }
        .rollcall-calendar-panel {
            position: sticky;
            top: 24px;
            background:
                radial-gradient(circle at top right, rgba(37, 99, 235, 0.10), transparent 18rem),
                var(--bg-surface);
            border: 1px solid var(--separator);
            border-radius: 12px;
            box-shadow: var(--shadow-sm);
            padding: 20px;
            min-height: 460px;
        }
        .rollcall-calendar-empty {
            min-height: 420px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            gap: 10px;
            color: var(--text-secondary);
        }
        .rollcall-calendar-empty .material-symbols-rounded {
            width: 62px;
            height: 62px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 16px;
            background: var(--brand-blue-light);
            color: var(--brand-blue);
            font-size: 32px;
        }
        .rollcall-calendar-empty strong {
            color: var(--text-primary);
            font-size: 18px;
        }
        .rollcall-calendar-empty small {
            font-size: 12px;
        }
        .rollcall-calendar-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            padding-bottom: 16px;
            border-bottom: 1px solid var(--separator);
        }
        .rollcall-calendar-head > .material-symbols-rounded {
            color: var(--brand-blue);
            font-size: 24px;
        }
        
        /* iOS Contact List Styling */
        .ios-contact-container {
            background: var(--bg-surface);
            border-radius: 12px;
            border: 1px solid var(--separator);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            box-shadow: 0 1px 3px rgba(0,0,0,0.02);
        }
        .ios-contact-header {
            background: var(--bg-surface-hover);
            padding: 2px 12px;
            font-size: 12px;
            font-weight: 600;
            color: #64748b;
            position: sticky;
            top: 0;
            z-index: 2;
            border-bottom: 1px solid var(--separator);
            border-top: 1px solid var(--separator);
            margin-top: -1px;
            letter-spacing: 0.3px;
        }
        .ios-contact-item {
            display: flex;
            align-items: center;
            padding: 0 0 0 12px;
            width: 100%;
            text-align: left;
            border: none;
            background: transparent;
            cursor: pointer;
            transition: background-color 0.15s ease;
            outline: none;
        }
        .ios-contact-item:hover {
            background: #f8fafc;
        }
        .ios-contact-item.active {
            background: var(--system-blue) !important;
        }
        .ios-contact-item-inner {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            border-bottom: 1px solid var(--separator);
            padding: 6px 12px 6px 0;
            min-height: 44px;
            margin-left: 10px;
        }
        .ios-contact-item:last-child .ios-contact-item-inner {
            border-bottom: none;
        }
        .ios-contact-name {
            font-size: 15px;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0px;
            letter-spacing: -0.2px;
            transition: color 0.15s ease;
        }
        .ios-contact-meta {
            font-size: 12px;
            color: var(--text-secondary);
            transition: color 0.15s ease;
        }
        .ios-contact-item.active .ios-contact-name,
        .ios-contact-item.active .ios-contact-meta {
            color: #fff !important;
        }
        .ios-contact-item .student-contact-avatar {
            width: 32px !important;
            height: 32px !important;
            font-size: 13px !important;
            box-shadow: 0 0 0 1px rgba(0,0,0,0.05);
            transition: box-shadow 0.15s ease;
        }
        .ios-contact-item.active .student-contact-avatar {
            box-shadow: 0 0 0 2px rgba(255,255,255,0.4);
        }

        .rollcall-calendar-name {
            font-size: 16px;
            font-weight: 800;
            color: var(--text-primary);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .rollcall-calendar-meta {
            margin-top: 4px;
            font-size: 12px;
            color: var(--text-secondary);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .rollcall-history-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin: 16px 0;
        }
        .rollcall-history-stats div {
            background: var(--bg-base);
            border: 1px solid var(--separator);
            border-radius: 10px;
            padding: 10px;
        }
        .rollcall-history-stats strong {
            display: block;
            font-size: 19px;
            line-height: 1;
        }
        .rollcall-history-stats small {
            display: block;
            color: var(--text-secondary);
            font-size: 10px;
            font-weight: 800;
            letter-spacing: 0.04em;
            margin-top: 5px;
            text-transform: uppercase;
        }
        .rollcall-analytics-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 8px;
            margin: -4px 0 12px;
        }
        .rollcall-analytics-grid div {
            min-width: 0;
            border: 1px solid var(--separator);
            border-radius: 10px;
            background: var(--bg-base);
            padding: 9px;
        }
        .rollcall-analytics-grid .material-symbols-rounded {
            display: block;
            color: var(--brand-blue);
            font-size: 18px;
            margin-bottom: 4px;
        }
        .rollcall-analytics-grid strong,
        .rollcall-analytics-grid small {
            display: block;
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .rollcall-analytics-grid strong {
            color: var(--text-primary);
            font-size: 15px;
            font-weight: 850;
        }
        .rollcall-analytics-grid small {
            color: var(--text-secondary);
            font-size: 9px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }
        .rollcall-exam-card {
            display: grid;
            gap: 9px;
            border: 1px solid var(--separator);
            border-radius: 12px;
            background: var(--bg-base);
            padding: 12px;
            margin: 12px 0;
        }
        .rollcall-exam-head {
            display: flex;
            gap: 9px;
            align-items: center;
            min-width: 0;
        }
        .rollcall-exam-head > .material-symbols-rounded {
            width: 32px;
            height: 32px;
            border-radius: 9px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: var(--chip-bg);
            color: var(--brand-blue);
            flex: 0 0 auto;
        }
        .rollcall-exam-head strong,
        .rollcall-exam-head small,
        .rollcall-exam-card label span,
        .rollcall-exam-result small {
            display: block;
        }
        .rollcall-exam-head strong {
            color: var(--text-primary);
            font-size: 14px;
            font-weight: 850;
            line-height: 1.2;
        }
        .rollcall-exam-head small,
        .rollcall-exam-card label span,
        .rollcall-exam-result small {
            color: var(--text-secondary);
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .rollcall-exam-card .apple-input {
            padding: 8px 10px;
            font-size: 13px;
        }
        .rollcall-exam-score-grid,
        .rollcall-exam-result {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 8px;
            align-items: end;
        }
        .rollcall-exam-result {
            align-items: center;
        }
        .rollcall-exam-result strong {
            display: block;
            color: var(--brand-blue);
            font-size: 22px;
            font-weight: 900;
            line-height: 1;
        }
        .rollcall-exam-result .btn-premium {
            justify-content: center;
            padding: 9px 12px;
        }
        .rollcall-calendar-month {
            font-size: 13px;
            font-weight: 800;
            color: var(--text-primary);
            margin-bottom: 12px;
        }
        .rollcall-calendar-weekdays,
        .rollcall-calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, minmax(0, 1fr));
            gap: 6px;
        }
        .rollcall-calendar-weekdays {
            margin-bottom: 8px;
        }
        .rollcall-calendar-weekdays span {
            color: var(--text-secondary);
            font-size: 10px;
            font-weight: 800;
            text-align: center;
            text-transform: uppercase;
        }
        .rollcall-calendar-day {
            aspect-ratio: 1;
            border: 1px solid var(--separator);
            border-radius: 9px;
            background: var(--bg-base);
            color: var(--text-secondary);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            font-size: 12px;
            font-weight: 800;
        }
        .rollcall-calendar-day.muted {
            border-color: transparent;
            background: transparent;
        }
        .rollcall-calendar-day.today {
            color: var(--brand-blue);
            border-color: rgba(37, 99, 235, 0.45);
            box-shadow: inset 0 0 0 1px rgba(37, 99, 235, 0.20);
        }
        .rollcall-calendar-day::after {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 13px;
            height: 13px;
            border-top-right-radius: 8px;
            clip-path: polygon(100% 0, 100% 100%, 0 0);
            background: transparent;
        }
        .rollcall-calendar-day.present::after,
        .rollcall-calendar-legend i.present { background: var(--system-green); }
        .rollcall-calendar-day.late::after,
        .rollcall-calendar-legend i.late { background: var(--system-orange); }
        .rollcall-calendar-day.absent::after,
        .rollcall-calendar-legend i.absent { background: var(--system-red); }
        .rollcall-calendar-day.present { border-color: rgba(34, 197, 94, 0.35); }
        .rollcall-calendar-day.late { border-color: rgba(245, 158, 11, 0.40); }
        .rollcall-calendar-day.absent { border-color: rgba(255, 61, 0, 0.35); }
        .rollcall-calendar-legend {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 16px;
            color: var(--text-secondary);
            font-size: 11px;
            font-weight: 700;
        }
        .rollcall-calendar-legend span {
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .rollcall-calendar-legend i {
            width: 11px;
            height: 11px;
            border-radius: 2px;
            clip-path: polygon(100% 0, 100% 100%, 0 0);
            display: inline-block;
        }

        .report-layout {
            display: grid;
            grid-template-columns: minmax(280px, 360px) minmax(0, 1fr);
            gap: 24px;
            align-items: start;
        }
        .report-panel {
            background: var(--bg-surface);
            border: 1px solid var(--separator);
            border-radius: 8px;
            padding: 24px;
            min-width: 0;
        }
        .report-active-grid,
        .report-picker-grid {
            display: grid;
            gap: 10px;
        }
        .report-active-grid {
            margin-bottom: 18px;
        }
        .report-person-card {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            border: 1px solid var(--separator);
            background: var(--bg-base);
            border-radius: 8px;
            min-width: 0;
        }
        .report-person-card.active {
            border-color: var(--system-blue);
            box-shadow: 0 0 0 2px var(--brand-blue-light);
        }
        [data-theme="dark"] .report-person-card.active {
            box-shadow: 0 0 0 2px rgba(0, 93, 255, 0.2);
        }
        .report-photo {
            width: 44px;
            height: 44px;
            border-radius: 8px;
            background: var(--system-blue);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            flex: 0 0 auto;
        }
        .report-picker-card {
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .report-picker-card:hover {
            transform: translateY(-1px);
            border-color: var(--system-blue);
        }
        .report-name {
            font-size: 14px;
            font-weight: 700;
            color: var(--text-primary);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .report-meta {
            font-size: 12px;
            color: var(--text-secondary);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .report-result-card {
            background: var(--bg-base);
            border: 1px solid var(--separator);
            border-radius: 8px;
            padding: 18px;
            margin-bottom: 14px;
        }
        .report-module-row {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 12px;
            align-items: center;
            padding: 10px 0;
            border-top: 1px solid var(--separator);
        }
        .report-module-row:first-child {
            border-top: 0;
        }
        .report-status {
            font-size: 12px;
            font-weight: 700;
            border: 1px solid var(--separator);
            padding: 4px 8px;
            border-radius: 4px;
            color: var(--text-secondary);
        }
        .report-status.done {
            color: var(--system-green);
            border-color: var(--system-green);
        }
        .report-tabs {
            margin-bottom: 24px;
        }

        .course-page-tabs {
            margin-bottom: 24px;
        }
        .course-category-block {
            margin-bottom: 28px;
        }
        .course-module-card {
            background: var(--bg-surface);
            border: 1px solid var(--separator);
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 12px;
        }
        .course-module-head {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 16px;
            align-items: start;
        }
        .icon-action-btn {
            width: 38px;
            height: 38px;
            border: 0;
            border-radius: 8px;
            background: var(--brand-blue);
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 auto;
            box-shadow: 0 8px 18px rgba(37, 99, 235, 0.22);
            transition: transform 0.18s ease, box-shadow 0.18s ease, background-color 0.18s ease;
        }
        .icon-action-btn:hover {
            transform: translateY(-1px);
            background: var(--brand-blue-hover);
            box-shadow: 0 12px 24px rgba(37, 99, 235, 0.28);
        }
        .icon-action-btn.danger {
            background: rgba(220, 38, 38, 0.1);
            color: #dc2626;
            box-shadow: none;
        }
        .icon-action-btn.danger:hover {
            background: #dc2626;
            color: #fff;
            box-shadow: 0 12px 24px rgba(220, 38, 38, 0.22);
        }
        .icon-action-btn .material-symbols-rounded {
            font-size: 21px;
            line-height: 1;
        }
        .course-module-title {
            font-size: 16px;
            font-weight: 700;
            color: var(--text-primary);
            line-height: 1.35;
        }
        .course-module-meta {
            color: var(--text-secondary);
            font-size: 12px;
            margin-top: 4px;
        }
        .marked-student-grid {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-top: 16px;
        }
        .marked-student-chip {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--bg-base);
            border: 1px solid var(--separator);
            color: var(--text-primary);
            padding: 6px 10px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
        }
        .marked-student-avatar {
            width: 24px;
            height: 24px;
            border-radius: 50% !important;
            background-color: var(--brand-blue-light);
            color: var(--brand-blue);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 11px;
            flex: 0 0 auto;
            background-size: cover;
            background-position: center;
            box-shadow: inset 0 0 0 1px rgba(37, 99, 235, 0.14);
        }
        [data-theme="dark"] .marked-student-avatar {
            background-color: rgba(37, 99, 235, 0.18);
            color: #93c5fd;
        }
        .module-mark-student-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 10px;
            max-height: 340px;
            overflow-y: auto;
            padding-right: 4px;
        }
        .module-student-option {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            border: 1px solid var(--separator);
            background: var(--bg-base);
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
            min-width: 0;
        }
        .module-student-option:hover {
            border-color: var(--system-blue);
        }
        .module-student-option input {
            width: 18px;
            height: 18px;
            accent-color: var(--system-blue);
            flex: 0 0 auto;
        }
        .module-student-option.selected {
            border-color: var(--system-blue);
            box-shadow: 0 0 0 2px var(--brand-blue-light);
        }
        [data-theme="dark"] .module-student-option.selected {
            box-shadow: 0 0 0 2px rgba(0, 93, 255, 0.2);
        }

        .student-page-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 16px;
            margin-top: 24px;
        }
        .student-page-card {
            background: var(--bg-surface);
            border: 1px solid var(--separator);
            border-radius: 10px;
            padding: 18px;
            cursor: pointer;
            box-shadow: var(--shadow-sm);
            transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
        }
        .student-page-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
            border-color: rgba(37, 99, 235, 0.30);
        }
        .student-crm-layout {
            display: grid;
            grid-template-columns: minmax(280px, 360px) minmax(0, 1fr);
            gap: 18px;
            align-items: stretch;
        }
        .student-crm-layout.profile-only {
            grid-template-columns: minmax(0, 1fr);
        }
        .student-crm-layout.profile-only .student-crm-list-panel {
            display: none;
        }
        .student-crm-list-panel,
        .student-crm-profile-panel {
            min-width: 0;
            height: calc(100vh - 112px);
            background: var(--bg-surface);
            border: 1px solid var(--separator);
            border-radius: 12px;
            box-shadow: var(--shadow-sm);
        }
        .student-crm-list-panel {
            position: sticky;
            top: 82px;
            padding: 14px;
            overflow: hidden;
        }
        .student-crm-panel-title {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 2px 2px 12px;
            border-bottom: 1px solid var(--separator);
            margin-bottom: 12px;
        }
        .student-crm-panel-title > .material-symbols-rounded {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: var(--chip-bg);
            color: var(--brand-blue);
            font-size: 21px;
            flex: 0 0 auto;
        }
        .student-crm-panel-title strong {
            display: block;
            font-size: 14px;
            line-height: 1.2;
        }
        .student-crm-panel-title small {
            display: block;
            color: var(--text-secondary);
            font-size: 12px;
            margin-top: 2px;
        }
        .student-crm-list {
            grid-template-columns: 1fr !important;
            gap: 8px;
            margin-top: 0;
            max-height: calc(100% - 62px);
            overflow-y: auto;
            padding-right: 4px;
        }
        .student-crm-list .student-page-card {
            padding: 12px;
            border-radius: 10px;
            box-shadow: none;
        }
        .student-crm-list .student-page-card:hover {
            transform: none;
        }
        .student-page-card.active {
            border-color: rgba(37, 99, 235, 0.55);
            background: var(--chip-bg);
            box-shadow: 0 0 0 1px rgba(37, 99, 235, 0.18);
        }
        .student-view-toggle {
            width: auto;
            margin: 0;
            flex: 0 0 auto;
        }
        .student-view-toggle .segment-btn {
            display: inline-flex;
            align-items: center;
            gap: 7px;
        }
        .student-view-toggle .material-symbols-rounded {
            font-size: 18px;
        }
        .student-dashboard-view {
            min-width: 0;
        }
        .student-dashboard-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            background: var(--bg-surface);
            border: 1px solid var(--separator);
            border-radius: 12px;
            padding: 16px 18px;
            box-shadow: var(--shadow-sm);
            margin-bottom: 14px;
        }
        .student-dashboard-head strong {
            display: block;
            font-size: 16px;
            font-weight: 850;
        }
        .student-dashboard-head small {
            display: block;
            color: var(--text-secondary);
            font-size: 12px;
            margin-top: 2px;
        }
        .student-dashboard-head > .material-symbols-rounded {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: var(--chip-bg);
            color: var(--brand-blue);
        }
        .student-dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 14px;
            align-items: stretch;
        }
        .student-dashboard-card {
            width: 100%;
            min-width: 0;
            text-align: left;
            border: 1px solid var(--separator);
            background: var(--bg-surface);
            color: var(--text-primary);
            border-radius: 12px;
            padding: 16px;
            box-shadow: var(--shadow-sm);
            cursor: pointer;
            transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
        }
        .student-dashboard-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
            border-color: rgba(37, 99, 235, 0.35);
        }
        .student-dashboard-card:focus-visible {
            outline: 3px solid rgba(37, 99, 235, 0.24);
            outline-offset: 2px;
        }
        .student-dashboard-card-head {
            display: grid;
            grid-template-columns: auto minmax(0, 1fr);
            gap: 12px;
            align-items: center;
            margin-bottom: 14px;
        }
        .student-dashboard-card-head strong,
        .student-dashboard-card-head small,
        .student-dashboard-info span {
            display: block;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            min-width: 0;
        }
        .student-dashboard-card-head strong {
            font-size: 15px;
            font-weight: 850;
        }
        .student-dashboard-card-head small {
            color: var(--text-secondary);
            font-size: 12px;
            margin-top: 3px;
        }
        .student-dashboard-info {
            display: grid;
            gap: 7px;
            padding: 12px;
            border: 1px solid var(--separator);
            border-radius: 10px;
            background: var(--bg-base);
            margin-bottom: 14px;
        }
        .student-dashboard-info span {
            display: grid;
            grid-template-columns: 20px minmax(0, 1fr);
            align-items: flex-start;
            gap: 7px;
            color: var(--text-secondary);
            font-size: 12px;
            overflow-wrap: anywhere;
            word-break: break-word;
        }
        .student-dashboard-info .material-symbols-rounded {
            font-size: 16px;
            color: var(--brand-blue);
            font-style: normal;
        }
        .student-crm-profile-panel {
            padding: 18px;
            overflow-y: auto;
            scrollbar-gutter: stable;
        }
        .student-crm-profile-panel .profile-back-btn {
            display: none;
        }
        .student-crm-layout.profile-only .profile-back-btn {
            display: inline-flex;
            margin-bottom: 14px;
        }
        .student-profile-placeholder {
            min-height: 610px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: var(--text-secondary);
            border: 1px dashed var(--separator);
            border-radius: 12px;
            background: linear-gradient(180deg, rgba(248, 250, 252, 0.85), rgba(255, 255, 255, 0.35));
        }
        .student-profile-placeholder .material-symbols-rounded {
            font-size: 44px;
            color: var(--brand-blue);
            margin-bottom: 10px;
        }
        .student-profile-placeholder strong {
            color: var(--text-primary);
            font-size: 18px;
        }
        .student-profile-placeholder small {
            margin-top: 4px;
            font-size: 13px;
        }
        .student-contact-layout {
            display: grid;
            grid-template-columns: minmax(340px, 430px) minmax(0, 1fr);
            gap: 22px;
            align-items: start;
        }
        .student-contact-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
            max-height: calc(100vh - 250px);
            overflow-y: auto;
            padding-right: 4px;
        }
        .student-contact-card {
            width: 100%;
            border: 1px solid var(--separator);
            background: var(--bg-surface);
            color: var(--text-primary);
            border-radius: 10px;
            padding: 14px;
            display: grid;
            grid-template-columns: auto minmax(0, 1fr);
            gap: 12px;
            text-align: left;
            box-shadow: var(--shadow-sm);
            cursor: pointer;
            transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
        }
        .student-contact-card:hover {
            transform: translateY(-1px);
            border-color: rgba(37, 99, 235, 0.30);
            box-shadow: var(--shadow-md);
        }
        .student-contact-card.active {
            border-color: var(--brand-blue);
            box-shadow: var(--surface-ring), var(--shadow-sm);
        }
        .student-contact-avatar {
            width: 48px;
            height: 48px;
            border-radius: 50% !important;
            background: var(--brand-blue-light);
            color: var(--brand-blue);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 17px;
            background-size: cover;
            background-position: center;
            box-shadow: inset 0 0 0 1px rgba(37, 99, 235, 0.14);
        }
        .student-contact-main {
            min-width: 0;
        }
        .student-contact-name {
            font-size: 15px;
            font-weight: 800;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .student-contact-meta {
            color: var(--text-secondary);
            font-size: 12px;
            margin-top: 4px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .student-contact-badges {
            grid-column: 1 / -1;
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        .contact-status {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            border: 1px solid var(--separator);
            border-radius: 999px;
            padding: 5px 9px;
            font-size: 11px;
            font-weight: 800;
            color: var(--text-secondary);
            background: var(--bg-base);
        }
        .contact-status .material-symbols-rounded {
            font-size: 15px;
            line-height: 1;
        }
        .contact-status.active,
        .contact-status.finished {
            color: var(--system-green);
            border-color: rgba(34, 197, 94, 0.35);
        }
        .contact-status.inactive,
        .contact-status.unfinished {
            color: var(--system-orange);
            border-color: rgba(245, 158, 11, 0.38);
        }
        .student-contact-detail {
            min-height: 520px;
            background:
                radial-gradient(circle at top right, rgba(37, 99, 235, 0.10), transparent 22rem),
                var(--bg-surface);
            border: 1px solid var(--separator);
            border-radius: 12px;
            padding: 24px;
            box-shadow: var(--shadow-sm);
            position: sticky;
            top: 24px;
        }
        .student-contact-empty {
            min-height: 460px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            gap: 10px;
            text-align: center;
            color: var(--text-secondary);
        }
        .student-contact-empty .material-symbols-rounded {
            width: 64px;
            height: 64px;
            border-radius: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: var(--brand-blue-light);
            color: var(--brand-blue);
            font-size: 32px;
        }
        .student-contact-empty strong {
            color: var(--text-primary);
            font-size: 18px;
        }
        .student-contact-profile-head {
            display: flex;
            align-items: center;
            gap: 16px;
            min-width: 0;
        }
        .student-contact-profile-name {
            font-size: 24px;
            font-weight: 800;
            color: var(--text-primary);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .student-contact-profile-sub {
            color: var(--text-secondary);
            font-size: 13px;
            margin-top: 4px;
        }
        .student-contact-status-row {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin: 18px 0;
        }
        .student-contact-info-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
            margin-bottom: 18px;
        }
        .student-contact-info-grid div {
            background: var(--bg-base);
            border: 1px solid var(--separator);
            border-radius: 10px;
            padding: 14px;
            min-width: 0;
        }
        .student-contact-info-grid .material-symbols-rounded {
            color: var(--brand-blue);
            font-size: 20px;
            margin-bottom: 10px;
        }
        .student-contact-info-grid small {
            display: block;
            color: var(--text-secondary);
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .student-contact-info-grid strong {
            display: block;
            margin-top: 5px;
            font-size: 13px;
            color: var(--text-primary);
            overflow-wrap: anywhere;
        }
        .student-contact-progress {
            background: var(--bg-base);
            border: 1px solid var(--separator);
            border-radius: 10px;
            padding: 16px;
        }
        .student-contact-progress-head {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            color: var(--text-secondary);
            font-size: 12px;
            font-weight: 800;
            margin: 12px 0 7px;
        }
        .student-contact-progress-head:first-child {
            margin-top: 0;
        }
        .student-contact-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 18px;
        }
        .student-profile-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 14px;
            margin-bottom: 24px;
        }
        .student-profile-panel {
            background: var(--bg-surface);
            border: 1px solid var(--separator);
            border-radius: 10px;
            padding: 18px;
            box-shadow: var(--shadow-sm);
            min-width: 0;
        }
        .modal-profile-header {
            display: flex;
            align-items: center;
            gap: 18px;
            padding: 24px;
            border-bottom: 1px solid var(--separator);
            background:
                radial-gradient(circle at top right, rgba(37, 99, 235, 0.10), transparent 24rem),
                var(--bg-surface);
            border-radius: 14px 14px 0 0;
            flex-wrap: wrap;
        }
        #studentProfileModal .modal-dialog {
            max-width: 1120px;
        }
        #studentProfileModal .modal-content {
            height: min(92vh, 860px);
            overflow: hidden;
        }
        #studentProfileModal .modal-body {
            display: flex;
            flex-direction: column;
            min-height: 0;
        }
        #modal-profile-content {
            display: flex;
            flex: 1 1 auto;
            flex-direction: column;
            min-height: 0;
            overflow: hidden;
        }
        .modal-profile-tabs {
            flex: 0 0 auto;
            margin-bottom: 24px;
            overflow-x: auto;
            justify-content: flex-start;
            scrollbar-gutter: stable;
        }
        .modal-profile-tabs .segment-btn {
            white-space: nowrap;
        }
        #modal-profile-pane-content {
            flex: 1 1 auto;
            min-height: 0;
            overflow-y: auto;
            overflow-x: hidden;
            padding-right: 4px;
            scrollbar-gutter: stable;
        }
        #modal-profile-pane-content .ios-list-item.clickable {
            cursor: default;
        }
        .modal-overview-layout {
            display: grid;
            grid-template-columns: minmax(330px, 1.05fr) minmax(280px, 0.9fr) minmax(320px, 1fr);
            gap: 14px;
            align-items: start;
        }
        .profile-overview-layout {
            display: grid;
            grid-template-columns: minmax(340px, 1.05fr) minmax(300px, 0.95fr) minmax(320px, 1fr);
            gap: 16px;
            align-items: start;
        }
        .profile-overview-layout .modal-overview-card {
            padding: 22px;
        }
        .modal-overview-stack {
            display: grid;
            gap: 14px;
            min-width: 0;
        }
        .modal-overview-card {
            background: var(--bg-surface);
            border: 1px solid var(--separator);
            border-radius: 10px;
            padding: 20px;
            box-shadow: var(--shadow-sm);
            min-width: 0;
        }
        .modal-overview-card .curriculum-category {
            margin: 0 0 16px 0;
        }
        .modal-profile-data-list {
            display: grid;
            gap: 0;
        }
        .modal-profile-data-row {
            display: grid;
            grid-template-columns: 92px minmax(0, 1fr);
            gap: 16px;
            align-items: start;
            padding: 13px 0;
            border-top: 1px solid var(--separator);
        }
        .modal-profile-data-row:first-child {
            border-top: 0;
            padding-top: 0;
        }
        .modal-profile-data-row span {
            color: var(--text-secondary);
            font-size: 13px;
            font-weight: 600;
        }
        .modal-profile-data-row strong {
            color: var(--text-primary);
            font-size: 14px;
            line-height: 1.35;
            text-align: right;
            overflow-wrap: anywhere;
            min-width: 0;
        }
        .modal-progress-block + .modal-progress-block {
            margin-top: 24px;
        }
        .modal-progress-head {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            align-items: flex-start;
        }
        .modal-progress-head strong {
            display: block;
            font-size: 15px;
        }
        .modal-progress-head span {
            display: block;
            color: var(--text-secondary);
            font-size: 12px;
            margin-top: 4px;
        }
        .modal-progress-percent {
            flex: 0 0 auto;
            border-radius: 7px;
            border: 1px solid var(--separator);
            background: var(--bg-base);
            color: var(--text-primary);
            font-size: 13px;
            font-weight: 700;
            padding: 6px 9px;
        }
        .modal-stat-value {
            font-size: 34px;
            line-height: 1;
            font-weight: 800;
            margin-bottom: 8px;
        }
        .modal-stat-label {
            color: var(--text-secondary);
            font-size: 13px;
            line-height: 1.4;
        }
        .modal-rollcall-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 16px;
        }
        .modal-rollcall-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border: 1px solid var(--separator);
            border-radius: 7px;
            background: var(--bg-base);
            color: var(--text-primary);
            font-size: 12px;
            font-weight: 700;
            padding: 8px 10px;
            min-height: 32px;
        }
        .modal-rollcall-chip::before {
            content: "";
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: var(--system-green);
            flex: 0 0 auto;
        }
        .modal-rollcall-chip.late::before {
            background: var(--system-orange);
        }
        .modal-rollcall-chip.absent::before {
            background: var(--system-red);
        }
        .modal-latest-note {
            margin-top: 16px;
            color: var(--text-secondary);
            font-size: 12px;
        }
        @media (max-width: 1100px) {
            .modal-overview-layout,
            .profile-overview-layout {
                grid-template-columns: 1fr 1fr;
            }
            .modal-overview-stack {
                grid-column: 1 / -1;
                grid-template-columns: 1fr 1fr;
            }
        }
        @media (max-width: 760px) {
            .modal-overview-layout,
            .profile-overview-layout,
            .modal-overview-stack {
                grid-template-columns: 1fr;
            }
            .modal-overview-stack {
                grid-column: auto;
            }
            .profile-header-widget {
                padding: 22px;
            }
            .profile-header-actions {
                width: 100%;
            }
            .profile-header-actions .btn {
                flex: 1 1 auto;
            }
            .modal-profile-data-row {
                grid-template-columns: 1fr;
                gap: 4px;
            }
            .modal-profile-data-row strong {
                text-align: left;
            }
        }
        .profile-progress-track {
            width: 100%;
            height: 8px;
            border-radius: 999px;
            background: var(--bg-base);
            border: 1px solid var(--separator);
            overflow: hidden;
            margin-top: 12px;
        }
        .profile-progress-fill {
            height: 100%;
            border-radius: 999px;
            background: var(--brand-blue);
        }
        .marked-student-chip {
            cursor: pointer;
        }
        .marked-student-chip:hover {
            border-color: var(--brand-blue);
            color: var(--brand-blue);
        }

        .flash-green { animation: flashGreen 2s ease-out; }
        @keyframes flashGreen { 0% { background-color: var(--brand-blue-light); } 100% { background-color: transparent; } }
        [data-theme="dark"] @keyframes flashGreen { 0% { background-color: rgba(0, 93, 255, 0.2); } 100% { background-color: transparent; } }

        /* =======================================================================
           MOBILE OPTIMIZATIONS
           ======================================================================= */
        @media (max-width: 576px) {
            .rollcall-options {
                flex-wrap: wrap; /* Let chips stack on small phones */
                justify-content: flex-start; /* Align left when they stack below the name */
                width: 100%;
                margin-top: 8px; /* Give some breathing room from the name */
            }
        }

        @media (max-width: 900px) {
            .report-layout {
                grid-template-columns: 1fr;
            }
            .rollcall-workspace {
                grid-template-columns: 1fr;
            }
            .student-contact-layout {
                grid-template-columns: 1fr;
            }
            .student-contact-list {
                max-height: none;
            }
            .student-contact-detail {
                position: static;
            }
            .rollcall-calendar-panel {
                position: static;
                min-height: 0;
            }
            .rollcall-hero {
                grid-template-columns: 1fr;
            }
            .rollcall-summary-grid {
                min-width: 0;
                width: 100%;
            }
        }

        /* =======================================================================
           PROFESSIONAL PRODUCT UI PASS
           ======================================================================= */
        :root {
            --brand-blue: #2563eb;
            --brand-blue-hover: #1d4ed8;
            --brand-blue-light: #dbeafe;
            --chip-bg: rgba(37, 99, 235, 0.18);
            --brand-green: #16a34a;
            --brand-red: #dc2626;
            --brand-purple: #7c3aed;
            --bg-base: #0b1120;
            --bg-surface: #111827;
            --bg-surface-hover: #1f2937;
            --text-primary: #f9fafb;
            --text-secondary: #9ca3af;
            --separator: #263244;
            --shadow-sm: 0 1px 2px rgba(15, 23, 42, 0.08);
            --shadow-md: 0 12px 28px rgba(15, 23, 42, 0.14);
            --surface-ring: 0 0 0 1px rgba(37, 99, 235, 0.14);
        }

        [data-theme="light"] {
            --bg-base: #f4f6f9;
            --bg-surface: #ffffff;
            --bg-surface-hover: #f8fafc;
            --text-primary: #101827;
            --text-secondary: #687386;
            --separator: #dce3ed;
            --brand-blue-light: #e8f0ff;
            --chip-bg: #eff6ff;
        }

        :root[data-theme="apple"] {
            --bg-base: #eaf1fa;
            --bg-surface: #ffffff;
            --bg-surface-hover: #f8fafc;
            --text-primary: #0f172a;
            --text-secondary: #64748b;
            --separator: #d2dceb;
            --brand-blue: #005dff;
            --brand-blue-hover: #004ecc;
            --brand-blue-light: #e6efff;
            --brand-green: #00c853;
            --brand-red: #ff3d00;
            --brand-purple: #9c27b0;
            --system-blue: var(--brand-blue);
            --system-green: var(--brand-green);
            --system-red: var(--brand-red);
            --system-orange: #f59e0b;
        }

        body {
            -webkit-font-smoothing: antialiased;
            text-rendering: optimizeLegibility;
        }

        .topbar {
            height: 64px;
            padding: 0 28px;
            border-bottom: 1px solid var(--separator);
            box-shadow: var(--shadow-sm);
        }
        [data-theme="light"] .topbar,
        [data-theme="light"] .sidebar,
        [data-theme="light"] .master-pane {
            background: rgba(255, 255, 255, 0.96);
        }
        .topbar-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            color: var(--text-primary);
            font-size: 15px;
            letter-spacing: 0;
        }
        .topbar-brand strong {
            color: var(--brand-blue);
            font-weight: 800;
        }
        .brand-mark {
            width: 32px;
            height: 32px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            background: var(--brand-blue);
            color: #fff;
            font-weight: 800;
            box-shadow: 0 8px 18px rgba(37, 99, 235, 0.24);
        }
        .topbar-time {
            font-size: 13px;
            color: var(--text-secondary);
            background: var(--bg-base);
            border: 1px solid var(--separator);
            border-radius: 999px;
            padding: 7px 12px;
        }
        .topbar-user {
            border: 1px solid transparent;
            padding: 5px 8px 5px 12px;
        }
        .topbar-user:hover {
            border-color: var(--separator);
        }
        .theme-toggle,
        .topbar-avatar {
            border-radius: 8px !important;
        }

        .app-body {
            height: calc(100vh - 56px);
        }
        .sidebar {
            width: 80px;
            padding-top: 24px;
            gap: 10px;
        }
        .nav-icon {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            color: var(--text-secondary);
        }
        .nav-icon span {
            font-size: 22px;
        }
        .nav-icon:hover {
            transform: translateY(-1px);
            background: var(--bg-base);
        }
        .nav-icon.active {
            background: var(--brand-blue);
            color: #fff;
            box-shadow: 0 10px 22px rgba(37, 99, 235, 0.22);
        }
        [data-theme="dark"] .nav-icon.active {
            background: var(--brand-blue);
            color: #fff;
        }

        .master-pane {
            width: 344px;
        }
        .master-header {
            padding: 26px 22px 18px;
        }
        .master-title {
            font-size: 20px;
            letter-spacing: 0;
        }
        .student-list-container {
            padding: 14px;
            background: var(--bg-base);
        }
        .detail-pane {
            background:
                radial-gradient(circle at top right, rgba(37, 99, 235, 0.08), transparent 32rem),
                var(--bg-base);
        }
        [data-theme="light"] .detail-pane {
            background:
                radial-gradient(circle at top right, rgba(37, 99, 235, 0.08), transparent 34rem),
                linear-gradient(180deg, #f7f9fc 0%, #eef3f8 100%);
        }
        .view-section {
            max-width: 1280px;
            padding: 44px 52px 64px;
        }

        .btn,
        .btn-premium,
        .btn-gold,
        .segment-btn,
        .apple-input,
        .student-card,
        .ios-list-item,
        .dash-stat-card,
        .course-module-card,
        .report-panel,
        .report-result-card,
        .modal-content {
            border-radius: 10px !important;
        }
        .btn-premium,
        .btn-gold {
            background: var(--brand-blue) !important;
            padding: 10px 18px;
            font-size: 13px;
            letter-spacing: 0;
            box-shadow: 0 8px 18px rgba(37, 99, 235, 0.20);
        }
        .btn-premium:hover,
        .btn-gold:hover {
            background: var(--brand-blue-hover) !important;
            box-shadow: 0 12px 24px rgba(37, 99, 235, 0.26);
        }
        .btn-dark {
            background: var(--bg-base) !important;
            border-color: var(--separator) !important;
        }
        .apple-input {
            min-height: 42px;
            background: var(--bg-base) !important;
            box-shadow: none;
        }
        [data-theme="light"] .apple-input {
            background: #f8fafc !important;
        }
        .apple-input:focus {
            border-color: var(--brand-blue) !important;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12) !important;
        }

        .segmented-control {
            gap: 6px;
            background: var(--bg-surface);
            border: 1px solid var(--separator);
            border-radius: 12px;
            padding: 5px;
            width: fit-content;
            max-width: 100%;
            margin-bottom: 28px;
            box-shadow: var(--shadow-sm);
        }
        .segment-btn {
            padding: 9px 16px;
            font-size: 13px;
            font-weight: 700;
            color: var(--text-secondary);
        }
        .segment-btn:hover {
            background: var(--bg-base);
        }
        .segment-btn.active {
            background: var(--brand-blue);
            box-shadow: 0 8px 18px rgba(37, 99, 235, 0.20);
        }

        .dash-grid {
            gap: 18px;
        }
        .dash-stat-card {
            padding: 24px;
            box-shadow: var(--shadow-sm);
        }
        .dash-stat-card h3 {
            font-size: 36px;
            letter-spacing: 0;
        }
        .dash-stat-card p,
        .curriculum-category {
            letter-spacing: 0.06em;
        }

        .student-card {
            padding: 14px;
            margin-bottom: 10px;
            box-shadow: var(--shadow-sm);
        }
        .student-card:hover,
        .ios-list-item:hover {
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }
        .student-avatar,
        .report-photo {
            background: var(--brand-blue-light);
            color: var(--brand-blue);
            box-shadow: inset 0 0 0 1px rgba(37, 99, 235, 0.10);
        }
        [data-theme="dark"] .student-avatar,
        [data-theme="dark"] .report-photo {
            background: rgba(37, 99, 235, 0.18);
            color: #93c5fd;
        }
        .profile-large-avatar {
            background: var(--brand-blue);
            box-shadow: 0 14px 30px rgba(37, 99, 235, 0.25);
        }

        .ios-list {
            gap: 10px;
        }
        .ios-list-item {
            padding: 18px 20px;
            box-shadow: var(--shadow-sm);
        }
        .date-badge,
        .trainer-badge,
        .marked-student-chip {
            border-radius: 7px;
            background: var(--bg-base);
        }

        .course-module-card {
            padding: 22px 24px;
            border-color: var(--separator);
            box-shadow: var(--shadow-sm);
            transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
        }
        .course-module-card:hover {
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
            border-color: rgba(37, 99, 235, 0.30);
        }
        .course-module-title {
            font-size: 16px;
            letter-spacing: 0;
        }
        .course-module-meta {
            font-size: 12px;
            margin-top: 6px;
        }
        .marked-student-chip {
            padding: 7px 10px;
        }

        .module-student-option,
        .report-person-card,
        .report-result-card {
            border-radius: 10px;
            box-shadow: var(--shadow-sm);
        }
        .module-student-option.selected,
        .report-person-card.active,
        .student-card.active {
            border-color: var(--brand-blue);
            box-shadow: var(--surface-ring), var(--shadow-sm);
        }
        [data-theme="dark"] .module-student-option.selected,
        [data-theme="dark"] .report-person-card.active,
        [data-theme="dark"] .student-card.active {
            box-shadow: 0 0 0 1px rgba(37, 99, 235, 0.45), var(--shadow-sm);
        }

        .carbon-modal-content {
            border-radius: 14px !important;
            box-shadow: 0 24px 60px rgba(2, 6, 23, 0.30);
        }
        /* FIX: hidden modals must never swallow clicks (Bootstrap .modal-content pointer-events:auto vs DaisyUI display:grid conflict) */
        .modal:not(.show),
        .modal:not(.show) *,
        .modal-backdrop:not(.show),
        .modal-backdrop:not(.show) * {
            pointer-events: none !important;
        }
        .modal.show .modal-content,
        .modal.show .modal-body {
            pointer-events: auto !important;
        }
        .modal-backdrop.show {
            opacity: 0.36;
        }

        @media (max-width: 900px) {
            .topbar {
                padding: 0 16px;
            }
            .topbar-time {
                display: none;
            }
            .view-section {
                padding: 34px 28px 54px;
            }
            .segmented-control {
                width: 100%;
            }
            .segment-btn {
                flex: 1 1 auto;
                text-align: center;
            }
            .course-module-head {
                grid-template-columns: 1fr;
            }
            .course-module-head .btn,
            .course-module-head .icon-action-btn {
                width: 100%;
            }
            .rollcall-summary-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
            .rollcall-row {
                align-items: flex-start;
            }
            .rollcall-options {
                width: 100%;
                justify-content: flex-start;
            }
            .rollcall-calendar-weekdays,
            .rollcall-calendar-grid {
                gap: 5px;
            }
        }

        @media (max-width: 576px) {
            .topbar {
                gap: 12px;
            }
            .topbar-user > span {
                display: none;
            }
            .topbar-brand {
                font-size: 14px;
                gap: 10px;
            }
            .brand-mark,
            .topbar-avatar {
                width: 32px;
                height: 32px;
                flex: 0 0 auto;
            }
            .theme-toggle {
                margin-right: 0;
            }
            .section-title-row {
                align-items: flex-start;
            }
            .rollcall-summary-grid {
                grid-template-columns: 1fr;
            }
            .rollcall-chip {
                flex: 1 1 100%;
            }
            .rollcall-history-stats {
                grid-template-columns: 1fr;
            }
            .student-contact-info-grid {
                grid-template-columns: 1fr;
            }
            .student-contact-profile-head {
                align-items: flex-start;
            }
            .student-contact-actions .btn {
                flex: 1 1 100%;
            }
        }

        /* =======================================================================
           STUDENT CRM + COURSE OPERATIONS DASHBOARD
           ======================================================================= */
        :root,
        [data-theme="light"] {
            --brand-blue: #2563eb;
            --brand-blue-hover: #1d4ed8;
            --brand-blue-light: #eff6ff;
            --brand-green: #16a34a;
            --brand-red: #dc2626;
            --brand-purple: #7c3aed;
            --bg-base: #f6f8fb;
            --bg-surface: #ffffff;
            --bg-surface-hover: #f8fafc;
            --text-primary: #0f172a;
            --text-secondary: #64748b;
            --separator: #e2e8f0;
            --system-blue: var(--brand-blue);
            --system-green: var(--brand-green);
            --system-red: var(--brand-red);
            --system-orange: #f59e0b;
            --shadow-sm: 0 1px 2px rgba(15, 23, 42, 0.06);
            --shadow-md: 0 10px 24px rgba(15, 23, 42, 0.08);
            --surface-ring: 0 0 0 3px rgba(37, 99, 235, 0.10);
        }

        body {
            background:
                linear-gradient(180deg, #f8fafc 0%, #eef3f8 100%);
        }
        .topbar {
            height: 56px;
            padding: 0 24px;
            background: rgba(255, 255, 255, 0.94);
            backdrop-filter: blur(12px);
            box-shadow: 0 1px 0 rgba(15, 23, 42, 0.06);
        }
        .topbar-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--text-primary);
        }
        .brand-mark {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: var(--brand-blue);
            color: #fff;
            box-shadow: 0 8px 18px rgba(37, 99, 235, 0.22);
        }
        .topbar-time {
            background: var(--bg-base);
            border: 1px solid var(--separator);
            border-radius: 999px;
            padding: 7px 14px;
            color: var(--text-secondary);
        }
        .theme-toggle,
        .topbar-avatar {
            border-radius: 8px;
        }
        .topbar-user {
            border: 1px solid transparent;
        }
        .topbar-user:hover {
            border-color: var(--separator);
        }
        .app-body {
            background: transparent;
        }
        .sidebar {
            width: 72px;
            background: rgba(255, 255, 255, 0.96);
            border-right: 1px solid var(--separator);
            padding-top: 26px;
        }
        .nav-icon {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            color: #64748b;
            position: relative;
        }
        .nav-icon:hover,
        .nav-icon.active {
            background: var(--chip-bg);
            color: var(--brand-blue);
        }
        .nav-icon.active::before {
            content: '';
            position: absolute;
            left: -14px;
            width: 3px;
            height: 24px;
            border-radius: 999px;
            background: var(--brand-blue);
        }
        .detail-pane {
            background: transparent;
        }
        .view-section {
            max-width: 1640px;
            padding: 28px 32px 44px;
        }
        .view-section h1,
        .view-section h2 {
            font-size: 26px !important;
            letter-spacing: -0.02em !important;
        }
        .view-section > .mb-4,
        .view-section > .d-flex:first-child,
        #roster-overview > .d-flex:first-child {
            margin-bottom: 18px !important;
            padding-bottom: 16px;
            border-bottom: 1px solid var(--separator);
        }
        .section-title-icon {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            background: var(--chip-bg);
            color: var(--brand-blue);
        }
        .curriculum-category {
            color: #475569;
            font-size: 11px;
            letter-spacing: 0.08em;
            margin: 22px 0 10px 2px;
        }
        .segmented-control {
            background: #f8fafc;
            border-color: #dbe3ef;
            border-radius: 10px;
            padding: 4px;
            gap: 4px;
            box-shadow: none;
        }
        .segment-btn {
            border-radius: 8px !important;
            padding: 8px 13px;
            font-size: 12px;
            font-weight: 700;
        }
        .segment-btn.active {
            background: #fff;
            color: var(--brand-blue);
            border: 1px solid #dbeafe;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.06);
        }
        .btn-premium,
        .btn-gold {
            border-radius: 10px !important;
            box-shadow: 0 8px 18px rgba(37, 99, 235, 0.16);
        }
        .btn-dark {
            background: #fff !important;
            color: var(--text-primary) !important;
            border-color: var(--separator) !important;
        }

        .dash-grid {
            grid-template-columns: repeat(4, minmax(170px, 1fr));
            gap: 12px;
            margin-bottom: 18px;
        }
        .dash-stat-card,
        .report-panel,
        .student-contact-detail,
        .rollcall-calendar-panel,
        .modal-overview-card,
        .student-profile-panel {
            border-radius: 12px !important;
            border: 1px solid var(--separator);
            background: #fff;
            box-shadow: var(--shadow-sm);
        }
        .dash-stat-card {
            padding: 16px;
            min-height: 112px;
            justify-content: space-between;
        }
        .dash-stat-card h3 {
            font-size: 30px;
        }
        .stat-card-head {
            margin-bottom: 10px;
        }
        .stat-card-head .material-symbols-rounded {
            width: 28px;
            height: 28px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: var(--chip-bg);
            font-size: 18px;
        }

        .student-page-grid {
            grid-template-columns: repeat(auto-fill, minmax(310px, 1fr));
            gap: 12px;
            margin-top: 14px;
        }
        .student-page-card,
        .ios-list-item,
        .course-module-card,
        .report-result-card,
        .student-contact-card,
        .module-student-option,
        .report-person-card {
            border-radius: 10px !important;
            border-color: var(--separator);
            background: #fff;
            box-shadow: var(--shadow-sm);
        }
        .student-page-card {
            padding: 14px;
        }
        .student-page-card:hover,
        .ios-list-item:hover,
        .course-module-card:hover,
        .student-contact-card:hover,
        .report-person-card:hover {
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
            border-color: #bfdbfe;
        }
        .student-avatar,
        .report-photo,
        .student-contact-avatar,
        .marked-student-avatar {
            border-radius: 50% !important;
            background: var(--chip-bg);
            color: var(--brand-blue);
            box-shadow: inset 0 0 0 1px #dbeafe;
        }
        .student-page-card .report-name,
        .report-name,
        .student-contact-name,
        .course-module-title {
            color: #0f172a;
            font-weight: 800;
        }
        .report-meta,
        .course-module-meta,
        .student-contact-meta,
        .rollcall-student-meta {
            color: #64748b;
        }
        .roster-prog-track,
        .profile-progress-track {
            background: #eef2f7;
            border: 0;
            height: 7px;
        }
        .roster-prog-fill-theory,
        .profile-progress-fill {
            background: var(--brand-blue);
        }
        .roster-prog-fill-practical {
            background: var(--brand-purple);
        }

        .report-layout,
        .student-contact-layout {
            grid-template-columns: minmax(320px, 400px) minmax(0, 1fr);
            gap: 16px;
        }
        .rollcall-workspace {
            grid-template-columns: minmax(0, 1fr) 380px;
            gap: 16px;
        }
        .report-panel,
        .student-contact-list,
        .rollcall-list-panel {
            background: #fff;
            border: 1px solid var(--separator);
            border-radius: 12px;
            padding: 12px;
            box-shadow: var(--shadow-sm);
        }
        .student-contact-list,
        .rollcall-list-panel {
            max-height: calc(100vh - 210px);
            overflow: auto;
        }
        .ios-list {
            gap: 8px;
        }
        .ios-list-item {
            padding: 14px 16px;
        }
        .rollcall-row,
        .student-contact-card {
            padding: 12px;
        }
        .rollcall-row.active,
        .student-contact-card.active,
        .report-person-card.active,
        .module-student-option.selected {
            border-color: var(--brand-blue);
            box-shadow: var(--surface-ring), var(--shadow-sm);
        }
        .rollcall-summary-grid {
            min-width: 0;
            grid-template-columns: repeat(4, minmax(118px, 1fr));
        }
        .rollcall-summary-card,
        .rollcall-history-stats div,
        .student-contact-info-grid div,
        .student-contact-progress {
            background: #f8fafc;
            border: 1px solid var(--separator);
        }
        .rollcall-calendar-panel,
        .student-contact-detail {
            top: 16px;
            min-height: 0;
            background: #fff;
        }
        .rollcall-calendar-day {
            background: #f8fafc;
            border-color: #e2e8f0;
        }
        .rollcall-calendar-day.today {
            background: var(--chip-bg);
        }
        .rollcall-chip {
            background: #fff;
            border-color: #dbe3ef;
            color: #475569;
            border-radius: 8px !important;
            min-height: 34px;
        }
        .rollcall-chip:hover {
            background: #f8fafc;
            border-color: #bfdbfe;
        }
        .contact-status,
        .student-status-badge,
        .modal-rollcall-chip,
        .marked-student-chip,
        .date-badge,
        .trainer-badge,
        .report-status {
            background: #f8fafc;
            border-color: #dbe3ef;
            border-radius: 999px;
        }
        .contact-status.active,
        .contact-status.finished {
            background: #f0fdf4;
            color: #15803d;
            border-color: #bbf7d0;
        }
        .contact-status.inactive,
        .contact-status.unfinished {
            background: #fffbeb;
            color: #b45309;
            border-color: #fde68a;
        }
        .student-status-badge {
            background: #f0fdf4;
            color: #15803d;
            border: 1px solid #bbf7d0;
        }
        .student-status-badge.inactive {
            background: #fff7ed;
            color: #c2410c;
            border-color: #fed7aa;
        }

        .profile-header-widget,
        .modal-profile-header {
            background: #fff;
            border: 1px solid var(--separator);
            border-radius: 16px;
            padding: 24px;
            box-shadow: var(--shadow-md);
            margin-bottom: 24px;
            margin-top: 12px;
        }
        .profile-header-widget {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-start;
            gap: 24px;
            min-height: 160px;
        }
        .student-profile-identity {
            flex: 1 1 240px;
            display: grid;
            grid-template-columns: 84px minmax(0, 1fr);
            align-items: center;
            gap: 16px;
            min-width: 0;
        }
        .student-profile-identity h2 {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            max-width: 100%;
        }
        .student-profile-id {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-top: 8px;
            color: var(--brand-blue);
            font-size: 13px;
            font-weight: 800;
            white-space: nowrap;
        }
        .student-profile-id .material-symbols-rounded {
            font-size: 18px;
        }
        .profile-header-widget > .student-profile-meta {
            flex: 3 1 340px;
            min-width: 0;
            max-width: 100%;
        }
        .student-profile-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 12px;
            height: auto;
        }
        .student-profile-info-cell {
            display: grid;
            grid-template-columns: 28px minmax(0, 1fr);
            align-items: flex-start;
            gap: 8px;
            min-width: 0;
            background: var(--bg-base);
            border: 1px solid var(--separator);
            border-radius: 10px;
            padding: 10px 12px;
        }
        .student-profile-info-cell .material-symbols-rounded {
            width: 28px;
            height: 28px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: var(--chip-bg);
            color: var(--brand-blue);
            font-size: 18px;
        }
        .student-profile-info-cell small,
        .student-profile-info-cell strong {
            display: block;
            min-width: 0;
            overflow-wrap: anywhere;
        }
        .student-profile-info-cell small {
            color: var(--text-secondary);
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .student-profile-info-cell strong {
            color: var(--text-primary);
            font-size: 12px;
            font-weight: 800;
            margin-top: 2px;
        }
        .student-profile-progress-cell {
            align-items: center;
        }
        .student-profile-progress-track {
            width: 100%;
            height: 6px;
            margin-top: 8px;
            border-radius: 999px;
            background: #e2e8f0;
            overflow: hidden;
        }
        .student-profile-progress-fill {
            height: 100%;
            border-radius: inherit;
            background: var(--brand-blue);
        }
        .student-profile-progress-fill.practical {
            background: var(--brand-purple);
        }
        .profile-large-avatar {
            width: 72px;
            height: 72px;
            border-radius: 50% !important;
            background: var(--brand-blue);
        }
        .student-profile-meta {
            color: var(--text-secondary);
            font-size: 14px;
            margin-top: 0;
            min-width: 0;
            max-width: 100%;
        }
        .student-profile-badge-row {
            display: flex;
            flex-wrap: nowrap;
            gap: 8px;
            align-items: center;
            margin-top: 10px;
            min-height: 32px;
            max-width: 100%;
            overflow-x: auto;
            overflow-y: hidden;
            padding-bottom: 2px;
            scrollbar-gutter: stable;
        }
        .student-profile-badge-row .date-badge {
            flex: 0 0 auto;
            max-width: 220px;
            min-width: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .student-profile-badge-row .date-badge .material-symbols-rounded {
            flex: 0 0 auto;
        }
        .student-profile-badge-row .profile-id-badge {
            max-width: none;
        }
        .student-profile-badge-row .profile-address-badge {
            max-width: min(360px, 42vw);
        }
        .profile-tabs,
        .modal-profile-tabs,
        .course-page-tabs {
            margin-bottom: 16px;
        }
        .profile-back-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            align-self: flex-start;
            border: 1px solid var(--separator);
            background: #fff;
            color: var(--text-primary);
            border-radius: 8px;
            padding: 6px 12px;
            font-size: 12px;
            font-weight: 700;
            margin-bottom: 14px;
            box-shadow: var(--shadow-sm);
        }
        .profile-back-btn:hover {
            border-color: #bfdbfe;
            color: var(--brand-blue);
        }
        .profile-back-btn .material-symbols-rounded {
            font-size: 16px;
        }
        .profile-detail-shell {
            display: grid;
            gap: 14px;
        }
        .profile-detail-panel {
            background: #fff;
            border: 1px solid var(--separator);
            border-radius: 12px;
            padding: 16px;
            box-shadow: var(--shadow-sm);
            min-width: 0;
        }
        .profile-detail-panel-head {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 14px;
            min-width: 0;
        }
        .profile-detail-panel-head .material-symbols-rounded {
            width: 34px;
            height: 34px;
            border-radius: 10px;
            background: var(--chip-bg);
            color: var(--brand-blue);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            flex: 0 0 auto;
        }
        .profile-detail-panel-head h3 {
            font-size: 17px;
            font-weight: 850;
            margin: 0;
            flex: 1 1 auto;
            min-width: 0;
        }
        .profile-detail-panel-head b {
            color: var(--brand-blue);
            background: var(--chip-bg);
            border: 1px solid #bfdbfe;
            border-radius: 999px;
            padding: 5px 9px;
            font-size: 12px;
        }
        .profile-detail-data-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 10px;
        }
        .profile-detail-data-grid div,
        .profile-detail-stats div {
            background: #f8fafc;
            border: 1px solid var(--separator);
            border-radius: 10px;
            padding: 12px;
            min-width: 0;
        }
        .profile-detail-data-grid small,
        .profile-detail-stats span {
            display: block;
            color: var(--text-secondary);
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .profile-detail-data-grid strong {
            display: block;
            color: var(--text-primary);
            font-size: 13px;
            margin-top: 5px;
            overflow-wrap: anywhere;
        }
        .profile-detail-stats {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
            margin-top: 10px;
        }
        .profile-detail-stats strong {
            display: block;
            font-size: 24px;
            line-height: 1;
            margin-bottom: 6px;
        }
        .profile-mark-columns {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
            align-items: stretch;
        }
        .profile-detail-column {
            display: flex;
            flex-direction: column;
            min-height: 0;
        }
        .profile-mark-primary {
            display: flex;
            flex-direction: column;
            min-height: 0;
            max-height: none;
            overflow: visible;
        }
        .profile-mini-mark-list {
            display: flex;
            flex-wrap: wrap;
            gap: 7px;
            max-height: 292px;
            overflow-y: auto;
            overflow: auto;
            padding-right: 4px;
            align-content: flex-start;
            scrollbar-gutter: stable;
        }
        .profile-mini-mark {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #f8fafc;
            border: 1px solid #dbe3ef;
            border-radius: 999px;
            padding: 6px 9px;
            color: #334155;
            font-size: 12px;
            font-weight: 750;
            max-width: 100%;
        }
        .profile-mini-mark .material-symbols-rounded {
            color: var(--system-green);
            font-size: 15px;
            flex: 0 0 auto;
        }
        .profile-mini-empty {
            color: var(--text-secondary);
            background: #f8fafc;
            border: 1px dashed #cbd5e1;
            border-radius: 10px;
            padding: 10px 12px;
            font-size: 13px;
        }
        .profile-repair-section {
            margin-top: auto;
            padding-top: 16px;
            border-top: 1px solid var(--separator);
            flex: 0 0 auto;
            max-height: 440px;
            display: flex;
            flex-direction: column;
            min-height: 0;
        }
        .profile-attendance-section {
            max-height: 440px;
            min-height: 0;
            margin-top: 16px;
        }
        .profile-detail-panel-head.compact {
            margin-bottom: 10px;
        }
        .profile-detail-panel-head.compact .material-symbols-rounded {
            width: 30px;
            height: 30px;
            font-size: 18px;
        }
        .profile-detail-panel-head.compact h3 {
            font-size: 15px;
        }
        .profile-repair-list {
            display: grid;
            gap: 8px;
            flex: 1 1 auto;
            min-height: 0;
            overflow-y: auto;
            overflow-x: hidden;
            padding-right: 4px;
            align-content: flex-start;
            scrollbar-gutter: stable;
        }
        .profile-repair-comment {
            background: #f8fafc;
            border: 1px solid var(--separator);
            border-radius: 10px;
            padding: 11px 12px;
        }
        .profile-repair-title {
            color: var(--text-primary);
            font-size: 13px;
            font-weight: 850;
            margin-bottom: 3px;
        }
        .profile-repair-meta {
            color: var(--text-secondary);
            font-size: 11px;
            font-weight: 700;
            margin-bottom: 7px;
        }
        .profile-repair-comment p {
            margin: 0;
            color: #334155;
            font-size: 12px;
            line-height: 1.45;
        }
        .profile-attendance-calendar {
            background: #f8fafc;
            border: 1px solid var(--separator);
            border-radius: 12px;
            padding: 10px;
            flex: 1 1 auto;
            min-height: 0;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }
        .profile-attendance-month {
            color: var(--text-primary);
            font-size: 12px;
            font-weight: 850;
            margin-bottom: 7px;
        }
        .profile-attendance-weekdays,
        .profile-attendance-grid {
            display: grid;
            grid-template-columns: repeat(7, minmax(0, 1fr));
            gap: 4px;
        }
        .profile-attendance-weekdays {
            margin-bottom: 5px;
            flex: 0 0 auto;
        }
        .profile-attendance-weekdays span {
            color: var(--text-secondary);
            font-size: 8px;
            font-weight: 850;
            text-align: center;
            text-transform: uppercase;
            line-height: 1;
        }
        .profile-attendance-grid {
            grid-template-rows: repeat(6, minmax(34px, 1fr));
            flex: 1 1 auto;
            min-height: 0;
        }
        .profile-attendance-day {
            border: 1px solid #e2e8f0;
            border-radius: 7px;
            background: #fff;
            color: var(--text-secondary);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            font-size: 11px;
            font-weight: 850;
            min-height: 34px;
            line-height: 1;
        }
        .profile-attendance-day.muted {
            background: transparent;
            border-color: transparent;
        }
        .profile-attendance-day.today {
            background: var(--chip-bg);
            color: var(--brand-blue);
            border-color: #bfdbfe;
        }
        .profile-attendance-day.off,
        .rollcall-calendar-day.off {
            background: #f1f5f9;
            color: #94a3b8;
            border-color: #e2e8f0;
        }
        .profile-attendance-day::after {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 9px;
            height: 9px;
            border-top-right-radius: 7px;
            clip-path: polygon(100% 0, 100% 100%, 0 0);
            background: transparent;
        }
        .profile-attendance-day.present::after { background: var(--system-green); }
        .profile-attendance-day.late::after { background: var(--system-orange); }
        .profile-attendance-day.absent::after { background: var(--system-red); }
        .profile-attendance-day.present { border-color: rgba(34, 197, 94, 0.35); }
        .profile-attendance-day.late { border-color: rgba(245, 158, 11, 0.40); }
        .profile-attendance-day.absent { border-color: rgba(255, 61, 0, 0.35); }
        .profile-attendance-legend {
            margin-top: 8px;
            font-size: 9px;
            flex: 0 0 auto;
        }
        .rollcall-window-banner {
            display: flex;
            align-items: center;
            gap: 12px;
            border: 1px solid var(--separator);
            border-radius: 12px;
            background: #fff;
            padding: 13px 16px;
            margin-bottom: 16px;
            box-shadow: var(--shadow-sm);
        }
        .rollcall-window-banner .material-symbols-rounded {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            flex: 0 0 auto;
        }
        .rollcall-window-banner strong,
        .rollcall-window-status strong {
            display: block;
            color: var(--text-primary);
            font-size: 14px;
        }
        .rollcall-window-banner small,
        .rollcall-window-status small {
            display: block;
            color: var(--text-secondary);
            font-size: 12px;
            margin-top: 2px;
        }
        .rollcall-window-banner.open .material-symbols-rounded,
        .rollcall-window-status.open .material-symbols-rounded {
            background: #f0fdf4;
            color: var(--system-green);
        }
        .rollcall-window-banner.closed .material-symbols-rounded,
        .rollcall-window-status.closed .material-symbols-rounded {
            background: #fff7ed;
            color: var(--system-orange);
        }
        .rollcall-chip:disabled {
            opacity: 0.45;
            cursor: not-allowed;
            transform: none !important;
            box-shadow: none !important;
        }
        .admin-settings-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 360px;
            gap: 16px;
            align-items: start;
        }
        .admin-settings-card {
            background: #fff;
            border: 1px solid var(--separator);
            border-radius: 12px;
            padding: 18px;
            box-shadow: var(--shadow-sm);
        }
        .rollcall-day-picker {
            display: grid;
            grid-template-columns: repeat(7, minmax(0, 1fr));
            gap: 8px;
        }
        .rollcall-day-picker label {
            border: 1px solid var(--separator);
            background: #f8fafc;
            border-radius: 10px;
            padding: 10px 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            color: var(--text-primary);
            font-size: 12px;
            font-weight: 850;
            cursor: pointer;
        }
        .rollcall-day-picker input {
            accent-color: var(--brand-blue);
        }
        .rollcall-schedule-set {
            border: 1px solid var(--separator);
            border-radius: 12px;
            padding: 14px;
            background: #f8fafc;
        }
        .admin-curriculum-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            margin-bottom: 16px;
        }
        .curriculum-type-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
            align-items: start;
        }
        .curriculum-type-panel {
            background: #fff;
            border: 1px solid var(--separator);
            border-radius: 12px;
            padding: 14px;
            box-shadow: var(--shadow-sm);
            min-width: 0;
        }
        .curriculum-type-head {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 12px;
        }
        .curriculum-type-head > .material-symbols-rounded {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            background: var(--chip-bg);
            color: var(--brand-blue);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 21px;
        }
        .curriculum-type-head h3 {
            color: var(--text-primary);
            font-size: 17px;
            font-weight: 850;
            margin: 0;
        }
        .curriculum-type-head small {
            color: var(--text-secondary);
            font-size: 12px;
            font-weight: 700;
        }
        .payments-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 18px;
            margin-bottom: 18px;
        }
        .payment-summary-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(92px, 1fr));
            gap: 10px;
            min-width: min(560px, 100%);
        }
        .payment-summary-grid div {
            background: #fff;
            border: 1px solid var(--separator);
            border-radius: 12px;
            padding: 12px;
            box-shadow: var(--shadow-sm);
        }
        .payment-summary-grid .material-symbols-rounded {
            color: var(--brand-blue);
            font-size: 20px;
        }
        .payment-summary-grid strong,
        .payment-summary-grid small {
            display: block;
        }
        .payment-summary-grid strong {
            color: var(--text-primary);
            font-size: 18px;
            line-height: 1.1;
            margin-top: 4px;
        }
        .payment-summary-grid small {
            color: var(--text-secondary);
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            margin-top: 4px;
        }
        .payment-workspace {
            display: grid;
            grid-template-columns: minmax(320px, 410px) minmax(0, 1fr);
            gap: 16px;
            align-items: start;
        }
        .payment-list-panel,
        .payment-detail-panel {
            background: #fff;
            border: 1px solid var(--separator);
            border-radius: 12px;
            box-shadow: var(--shadow-sm);
            min-width: 0;
        }
        .payment-list-panel {
            padding: 14px;
        }
        .payment-detail-panel {
            padding: 18px;
            min-height: 520px;
        }
        .payment-panel-title {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--text-primary);
            margin-bottom: 12px;
        }
        .payment-panel-title .material-symbols-rounded {
            color: var(--brand-blue);
        }
        .payment-student-list {
            display: grid;
            gap: 8px;
            max-height: calc(100vh - 240px);
            overflow: auto;
            padding-right: 3px;
        }
        .payment-student-card {
            border: 1px solid var(--separator);
            border-radius: 10px;
            background: #f8fafc;
            padding: 10px;
            display: grid;
            grid-template-columns: 42px minmax(0, 1fr) auto;
            gap: 10px;
            align-items: center;
            text-align: left;
            width: 100%;
        }
        .payment-student-card.active {
            background: var(--chip-bg);
            border-color: #bfdbfe;
        }
        .payment-student-main strong,
        .payment-student-main small {
            display: block;
        }
        .payment-student-main strong {
            color: var(--text-primary);
            font-size: 14px;
            overflow-wrap: anywhere;
        }
        .payment-student-main small {
            color: var(--text-secondary);
            font-size: 11px;
            margin-top: 2px;
        }
        .payment-mini-track {
            height: 5px;
            background: #e2e8f0;
            border-radius: 999px;
            overflow: hidden;
            margin-top: 7px;
        }
        .payment-mini-track span {
            display: block;
            height: 100%;
            background: linear-gradient(90deg, #2563eb, #16a34a);
            border-radius: inherit;
        }
        .payment-status {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            border-radius: 999px;
            border: 1px solid #cbd5e1;
            background: #fff;
            color: #475569;
            padding: 4px 8px;
            font-size: 10px;
            font-weight: 850;
            white-space: nowrap;
        }
        .payment-status .material-symbols-rounded {
            font-size: 14px;
        }
        .payment-status.paid {
            background: #f0fdf4;
            border-color: #bbf7d0;
            color: #15803d;
        }
        .payment-status.partial {
            background: var(--chip-bg);
            border-color: #bfdbfe;
            color: #1d4ed8;
        }
        .payment-status.unpaid,
        .payment-status.due {
            background: #fff7ed;
            border-color: #fed7aa;
            color: #c2410c;
        }
        .payment-profile-head {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
        }
        .payment-profile-head h3 {
            color: var(--text-primary);
            font-size: 21px;
            font-weight: 850;
            margin: 0;
        }
        .payment-profile-head small {
            color: var(--text-secondary);
            font-size: 12px;
        }
        .payment-profile-head .payment-status {
            margin-left: auto;
        }
        .payment-total-row {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 10px;
            margin-bottom: 16px;
        }
        .payment-total-row div {
            background: #f8fafc;
            border: 1px solid var(--separator);
            border-radius: 10px;
            padding: 12px;
        }
        .payment-total-row small,
        .payment-detail-form label {
            color: var(--text-secondary);
            font-size: 11px;
            font-weight: 850;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .payment-total-row strong {
            display: block;
            color: var(--text-primary);
            font-size: 20px;
            margin-top: 4px;
        }
        .payment-detail-form label {
            display: block;
            margin: 12px 0 7px;
        }
        .payment-installment-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
            margin-top: 12px;
        }
        .payment-installment-grid section {
            border: 1px solid var(--separator);
            background: #f8fafc;
            border-radius: 12px;
            padding: 13px;
            min-width: 0;
        }
        .payment-installment-title {
            display: flex;
            align-items: center;
            gap: 7px;
            color: var(--text-primary);
            font-weight: 850;
            margin-bottom: 8px;
        }
        .payment-installment-title .material-symbols-rounded {
            color: var(--brand-blue);
        }
        .rollcall-time-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
            margin-top: 16px;
        }
        .rollcall-time-grid label span {
            display: block;
            color: var(--text-secondary);
            font-size: 11px;
            font-weight: 850;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            margin-bottom: 7px;
        }
        .settings-save-note {
            color: var(--system-green);
            font-size: 13px;
            font-weight: 800;
        }
        .rollcall-settings-preview {
            display: grid;
            gap: 12px;
        }
        .rollcall-window-status {
            display: flex;
            align-items: center;
            gap: 12px;
            border: 1px solid var(--separator);
            background: #f8fafc;
            border-radius: 12px;
            padding: 14px;
        }
        .rollcall-window-status .material-symbols-rounded {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 21px;
            flex: 0 0 auto;
        }
        .settings-summary-row {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            border-top: 1px solid var(--separator);
            padding-top: 12px;
            color: var(--text-secondary);
            font-size: 13px;
        }
        .settings-summary-row strong {
            color: var(--text-primary);
            text-align: right;
        }
        .course-module-card {
            padding: 16px;
            margin-bottom: 8px;
        }
        .course-module-head {
            gap: 12px;
        }
        .icon-action-btn {
            width: 34px;
            height: 34px;
            border-radius: 8px;
            box-shadow: none;
        }
        .marked-student-grid {
            margin-top: 12px;
            gap: 6px;
        }
        .marked-student-chip {
            padding: 5px 8px 5px 5px;
        }

        .course-page-main-header {
            background: #fff;
            border: 1px solid var(--separator);
            border-radius: 14px;
            padding: 16px 18px !important;
            box-shadow: var(--shadow-sm);
        }
        .course-page-title-block .section-title-row {
            margin-bottom: 8px;
        }
        .course-page-title-block .section-title-icon {
            width: 40px;
            height: 40px;
            font-size: 23px;
        }
        .course-page-title-block h2 {
            font-size: 26px !important;
        }
        .course-page-tabs {
            padding: 6px;
            margin-bottom: 18px;
        }
        .course-page-tabs .segment-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            min-height: 42px;
            font-size: 14px;
            padding: 9px 16px;
        }
        .course-page-tabs .segment-btn .material-symbols-rounded {
            font-size: 19px;
        }
        .course-page-overview {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 14px;
            align-items: center;
            background: #0f172a;
            color: #fff;
            border-radius: 12px;
            padding: 14px 16px;
            margin-bottom: 16px;
            box-shadow: 0 10px 22px rgba(15, 23, 42, 0.13);
            min-height: 0;
        }
        .course-page-overview-main {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
        }
        .course-page-overview-icon {
            width: 42px;
            height: 42px;
            border-radius: 11px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.12);
            color: #93c5fd;
            font-size: 23px;
            flex: 0 0 auto;
        }
        .course-page-overview h3 {
            font-size: 20px;
            font-weight: 850;
            margin: 0;
            letter-spacing: -0.02em;
        }
        .course-page-overview p {
            color: #cbd5e1;
            margin: 4px 0 0;
            font-size: 12px;
            line-height: 1.35;
        }
        .course-page-metrics {
            display: grid;
            grid-template-columns: repeat(3, minmax(78px, 1fr));
            gap: 8px;
        }
        .course-page-metrics div {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 10px;
            padding: 9px 10px;
            min-width: 0;
        }
        .course-page-metrics strong {
            display: block;
            font-size: 19px;
            line-height: 1;
        }
        .course-page-metrics span {
            display: block;
            color: #cbd5e1;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.05em;
            margin-top: 6px;
            text-transform: uppercase;
        }
        .course-category-block {
            margin-bottom: 24px;
        }
        .course-category-header {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 16px;
            padding: 4px 2px 12px;
            border-bottom: 1px solid var(--separator);
            margin-bottom: 10px;
        }
        .course-category-kicker {
            color: var(--brand-blue);
            font-size: 11px;
            font-weight: 850;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }
        .course-category-header h3 {
            font-size: 22px;
            font-weight: 850;
            color: var(--text-primary);
            margin: 4px 0 0;
            letter-spacing: -0.02em;
        }
        .course-category-meta {
            color: var(--text-secondary);
            font-size: 12px;
            font-weight: 800;
            white-space: nowrap;
        }
        .course-module-card {
            border-radius: 12px !important;
            padding: 15px 16px 13px;
        }
        .course-module-head {
            grid-template-columns: minmax(0, 1fr) auto;
            align-items: start;
            min-width: 0;
            gap: 12px;
        }
        .course-module-copy {
            min-width: 0;
            display: grid;
            gap: 4px;
        }
        .course-module-kicker {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
            margin-bottom: 2px;
            color: var(--text-secondary);
            font-size: 11px;
            font-weight: 850;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            min-width: 0;
        }
        .course-module-status {
            border: 1px solid #dbe3ef;
            background: #f8fafc;
            color: #64748b;
            border-radius: 999px;
            padding: 2px 7px;
            text-transform: none;
            letter-spacing: 0;
            white-space: nowrap;
            line-height: 1.1;
            flex: 0 0 auto;
            max-width: 100%;
            position: relative;
            z-index: 1;
            font-size: 9px;
            font-weight: 850;
        }
        .course-module-status.done {
            background: #f0fdf4;
            color: #15803d;
            border-color: #bbf7d0;
        }
        .course-module-status.progress {
            background: var(--chip-bg);
            color: #1d4ed8;
            border-color: #bfdbfe;
        }
        .course-module-status.empty {
            background: #fff7ed;
            color: #c2410c;
            border-color: #fed7aa;
        }
        .course-module-title {
            font-size: 16px;
            line-height: 1.35;
            overflow-wrap: anywhere;
        }
        .course-module-meta {
            font-size: 12px;
            margin-top: 5px;
        }
        .course-module-mark-btn {
            width: auto;
            min-width: 82px;
            padding: 0 11px;
            gap: 7px;
            font-size: 12px;
            font-weight: 800;
            flex: 0 0 auto;
        }
        .course-module-mark-btn span:last-child {
            line-height: 1;
        }
        .course-module-progress {
            height: 6px;
            border-radius: 999px;
            background: #eef2f7;
            overflow: hidden;
            margin-top: 11px;
        }
        .course-module-progress span {
            display: block;
            height: 100%;
            border-radius: inherit;
            background: linear-gradient(90deg, #2563eb, #16a34a);
            min-width: 0;
            transition: width 0.25s ease;
        }
        .course-module-empty-note {
            color: var(--text-secondary);
            background: #f8fafc;
            border: 1px dashed #cbd5e1;
            border-radius: 10px;
            padding: 8px 11px;
            font-size: 13px;
        }
        button:focus-visible,
        .segment-btn:focus-visible,
        .icon-action-btn:focus-visible,
        .student-contact-card:focus-visible,
        .rollcall-chip:focus-visible {
            outline: 3px solid rgba(37, 99, 235, 0.35);
            outline-offset: 2px;
        }

        .student-contact-detail,
        .rollcall-calendar-panel,
        .report-panel {
            padding: 16px;
        }
        .student-contact-info-grid {
            gap: 10px;
        }
        .student-contact-profile-name {
            font-size: 22px;
        }
        .carbon-modal-content {
            border: 1px solid var(--separator);
            background: #fff;
        }

        [data-theme="dark"] {
            --bg-base: #0f172a;
            --bg-surface: #111827;
            --bg-surface-hover: #1f2937;
            --text-primary: #f8fafc;
            --text-secondary: #94a3b8;
            --separator: #243244;
            --chip-bg: rgba(37, 99, 235, 0.18);
        }
        [data-theme="dark"] body {
            background: #0f172a;
        }
        [data-theme="dark"] .rollcall-schedule-set,
        [data-theme="dark"] .rollcall-day-picker label,
        [data-theme="dark"] .rollcall-window-status,
        [data-theme="dark"] .curriculum-type-panel,
        [data-theme="dark"] .payment-summary-grid div,
        [data-theme="dark"] .payment-list-panel,
        [data-theme="dark"] .payment-detail-panel,
        [data-theme="dark"] .student-crm-list-panel,
        [data-theme="dark"] .student-crm-profile-panel,
        [data-theme="dark"] .student-dashboard-head,
        [data-theme="dark"] .student-dashboard-card,
        [data-theme="dark"] .payment-student-card,
        [data-theme="dark"] .payment-total-row div,
        [data-theme="dark"] .payment-installment-grid section {
            background: #0f172a;
            border-color: #243244;
        }
        [data-theme="dark"] .topbar,
        [data-theme="dark"] .sidebar,
        [data-theme="dark"] .student-page-card,
        [data-theme="dark"] .ios-list-item,
        [data-theme="dark"] .course-module-card,
        [data-theme="dark"] .report-panel,
        [data-theme="dark"] .student-contact-detail,
        [data-theme="dark"] .rollcall-calendar-panel,
        [data-theme="dark"] .student-contact-list,
        [data-theme="dark"] .rollcall-list-panel,
        [data-theme="dark"] .student-contact-card,
        [data-theme="dark"] .dash-stat-card,
        [data-theme="dark"] .profile-header-widget,
        [data-theme="dark"] .profile-back-btn,
        [data-theme="dark"] .profile-detail-panel,
        [data-theme="dark"] .modal-profile-header,
        [data-theme="dark"] .carbon-modal-content {
            background: #111827;
            border-color: #243244;
        }
        [data-theme="dark"] .rollcall-summary-card,
        [data-theme="dark"] .rollcall-history-stats div,
        [data-theme="dark"] .student-contact-info-grid div,
        [data-theme="dark"] .student-contact-progress,
        [data-theme="dark"] .profile-detail-data-grid div,
        [data-theme="dark"] .profile-detail-stats div,
        [data-theme="dark"] .student-profile-info-cell,
        [data-theme="dark"] .rollcall-analytics-grid div,
        [data-theme="dark"] .rollcall-exam-card,
        [data-theme="dark"] .profile-mini-mark,
        [data-theme="dark"] .profile-repair-comment,
        [data-theme="dark"] .profile-attendance-calendar,
        [data-theme="dark"] .profile-attendance-day,
        [data-theme="dark"] .rollcall-calendar-day,
        [data-theme="dark"] .btn-dark,
        [data-theme="dark"] .rollcall-chip,
        [data-theme="dark"] .student-dashboard-info,
        [data-theme="dark"] .segmented-control {
            background: #0f172a !important;
            border-color: #243244 !important;
        }
        [data-theme="dark"] .student-page-card.active {
            background: rgba(37, 99, 235, 0.16);
            border-color: rgba(96, 165, 250, 0.62);
        }
        [data-theme="dark"] .student-profile-placeholder {
            background: #0f172a;
            border-color: #243244;
        }
        [data-theme="dark"] .profile-attendance-day.off,
        [data-theme="dark"] .rollcall-calendar-day.off {
            background: #111827 !important;
            color: #64748b;
            border-color: #243244 !important;
        }

        @media (max-width: 1100px) {
            .dash-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
            .report-layout,
            .rollcall-workspace,
            .student-crm-layout,
            .student-contact-layout {
                grid-template-columns: 1fr;
            }
            .student-crm-list-panel {
                position: static;
                height: auto;
                max-height: none;
            }
            .student-crm-profile-panel {
                height: auto;
                min-height: 650px;
            }
            .student-crm-list {
                max-height: 360px;
            }
            .student-contact-list,
            .rollcall-list-panel {
                max-height: none;
            }
            .course-page-overview {
                grid-template-columns: 1fr;
            }
            .course-page-metrics {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
            .student-dashboard-grid {
                grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            }
        }
        @media (max-width: 576px) {
            .profile-header-widget {
                grid-template-columns: 1fr;
                min-height: 0;
            }
            .student-profile-identity {
                border-right: 0;
                border-bottom: 1px solid var(--separator);
                padding-right: 0;
                padding-bottom: 14px;
            }
            .student-profile-info-grid {
                grid-template-columns: 1fr;
            }
            .student-profile-badge-row .profile-address-badge {
                max-width: 260px;
            }
            .view-section {
                padding: 22px 18px 38px;
            }
            .student-view-toggle {
                width: 100%;
            }
            .student-view-toggle .segment-btn {
                flex: 1 1 0;
                justify-content: center;
                padding-inline: 8px;
            }
            .dash-grid,
            .rollcall-summary-grid {
                grid-template-columns: 1fr;
            }
            .student-page-grid {
                grid-template-columns: 1fr;
            }
            .student-dashboard-grid {
                grid-template-columns: 1fr;
            }
            .course-page-main-header {
                padding: 16px !important;
            }
            .course-page-title-block h2 {
                font-size: 25px !important;
            }
            .course-page-overview {
                padding: 12px;
                gap: 10px;
            }
            .course-page-overview-main {
                align-items: flex-start;
            }
            .course-page-overview-icon {
                width: 36px;
                height: 36px;
                font-size: 20px;
                border-radius: 9px;
            }
            .course-page-overview h3 {
                font-size: 18px;
            }
            .course-page-overview p {
                font-size: 11px;
            }
            .course-page-metrics {
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 6px;
            }
            .course-page-metrics div {
                padding: 8px 6px;
            }
            .course-page-metrics strong {
                font-size: 17px;
            }
            .course-page-metrics span {
                font-size: 9px;
                letter-spacing: 0.03em;
            }
            .course-category-header {
                align-items: flex-start;
                flex-direction: column;
                gap: 6px;
            }
            .course-module-head {
                grid-template-columns: 1fr;
                align-items: start;
            }
            .course-module-mark-btn {
                width: 100%;
                justify-content: center;
            }
            .profile-detail-data-grid,
            .profile-mark-columns {
                grid-template-columns: 1fr;
            }
            .profile-detail-stats {
                grid-template-columns: 1fr;
            }
            .admin-settings-grid,
            .curriculum-type-grid,
            .payment-workspace,
            .rollcall-time-grid {
                grid-template-columns: 1fr;
            }
            .payments-head {
                flex-direction: column;
            }
            .payment-summary-grid,
            .payment-total-row,
            .payment-installment-grid {
                grid-template-columns: 1fr;
                width: 100%;
            }
            .payment-student-card {
                grid-template-columns: 42px minmax(0, 1fr);
            }
            .payment-student-card .payment-status {
                grid-column: 1 / -1;
                justify-self: start;
            }
            .admin-curriculum-toolbar {
                align-items: flex-start;
                flex-direction: column;
            }
            .rollcall-day-picker {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        /* Student List final responsive alignment */
        .student-crm-layout {
            grid-template-columns: minmax(300px, min(26vw, 380px)) minmax(0, 1fr) !important;
            gap: 18px !important;
            align-items: stretch !important;
        }
        .student-crm-list-panel,
        .student-crm-profile-panel {
            height: calc(100vh - 112px) !important;
            min-width: 0 !important;
        }
        .student-crm-list {
            max-height: calc(100% - 62px) !important;
            overflow-y: auto !important;
            overflow-x: hidden !important;
        }
        .student-crm-list .student-page-card {
            display: block !important;
            min-width: 0 !important;
        }
        .student-crm-list .student-page-card .d-flex {
            align-items: center !important;
            min-width: 0 !important;
        }
        .student-crm-list .report-photo,
        .student-dashboard-card .report-photo {
            width: 48px !important;
            height: 48px !important;
            flex: 0 0 48px !important;
        }
        .student-crm-list .report-name,
        .student-crm-list .report-meta,
        .student-dashboard-card-head strong,
        .student-dashboard-card-head small {
            display: block !important;
            min-width: 0 !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
            white-space: nowrap !important;
        }
        .student-crm-profile-panel {
            overflow-y: auto !important;
            overflow-x: hidden !important;
        }
        .student-crm-profile-panel #profile-content,
        .student-crm-profile-panel #profile-pane-content {
            min-width: 0 !important;
        }
        .profile-header-widget {
            display: grid !important;
            grid-template-columns: minmax(240px, 320px) minmax(0, 1fr) !important;
            align-items: stretch !important;
            gap: 16px !important;
            min-height: 0 !important;
            margin-top: 0 !important;
            padding: 18px !important;
        }
        .student-profile-identity {
            display: grid !important;
            grid-template-columns: 72px minmax(0, 1fr) !important;
            align-items: center !important;
            gap: 14px !important;
            min-width: 0 !important;
            padding-right: 16px !important;
            border-right: 1px solid var(--separator) !important;
        }
        .student-profile-identity h2 {
            font-size: clamp(20px, 2vw, 26px) !important;
            line-height: 1.15 !important;
        }
        .student-profile-info-grid {
            display: grid !important;
            grid-template-columns: repeat(auto-fit, minmax(132px, 1fr)) !important;
            gap: 6px !important;
            align-content: stretch !important;
        }
        .student-profile-info-cell {
            display: grid !important;
            grid-template-columns: 24px minmax(0, 1fr) !important;
            gap: 7px !important;
            min-height: 44px !important;
            align-items: center !important;
            overflow: hidden !important;
            padding: 8px 10px !important;
        }
        .student-profile-info-cell .material-symbols-rounded {
            width: 24px !important;
            height: 24px !important;
            border-radius: 7px !important;
            font-size: 15px !important;
        }
        .student-profile-info-cell small {
            font-size: 9px !important;
            line-height: 1 !important;
        }
        .student-profile-info-cell strong {
            font-size: 11px !important;
            line-height: 1.2 !important;
        }
        .student-profile-info-cell strong {
            overflow: hidden !important;
            text-overflow: ellipsis !important;
            white-space: nowrap !important;
            overflow-wrap: normal !important;
        }
        .student-profile-progress-strip {
            display: grid !important;
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            gap: 8px !important;
            margin-top: 8px !important;
            min-width: 0 !important;
        }
        .student-profile-progress-card {
            display: grid !important;
            grid-template-columns: 28px minmax(0, 1fr) !important;
            align-items: center !important;
            gap: 8px !important;
            min-width: 0 !important;
            background: var(--bg-base) !important;
            border: 1px solid var(--separator) !important;
            border-radius: 10px !important;
            padding: 8px 10px !important;
            overflow: hidden !important;
        }
        .student-profile-progress-card > .material-symbols-rounded {
            width: 28px !important;
            height: 28px !important;
            border-radius: 8px !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            background: var(--chip-bg) !important;
            color: var(--brand-blue) !important;
            font-size: 17px !important;
        }
        .student-profile-progress-card > div {
            min-width: 0 !important;
            width: 100% !important;
            overflow: hidden !important;
        }
        .student-profile-progress-card small,
        .student-profile-progress-card strong {
            display: block !important;
            min-width: 0 !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
            white-space: nowrap !important;
        }
        .student-profile-progress-card small {
            color: var(--text-secondary) !important;
            font-size: 9px !important;
            font-weight: 800 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.04em !important;
            line-height: 1 !important;
        }
        .student-profile-progress-card strong {
            display: block !important;
            width: 100% !important;
            color: var(--text-primary) !important;
            font-size: 11px !important;
            font-weight: 850 !important;
            margin-bottom: 0 !important;
        }
        .student-profile-progress-track {
            display: block !important;
            width: 100% !important;
            max-width: 100% !important;
            min-width: 0 !important;
            height: 6px !important;
            margin-top: 6px !important;
            border-radius: 999px !important;
        }
        .student-profile-progress-fill {
            display: block !important;
            max-width: 100% !important;
        }
        .profile-detail-shell,
        .profile-mark-columns,
        .profile-detail-panel,
        .profile-detail-column {
            min-width: 0 !important;
        }
        .profile-mark-columns {
            grid-template-columns: repeat(2, minmax(300px, 1fr)) !important;
            align-items: stretch !important;
        }
        .profile-detail-panel-head {
            display: grid !important;
            grid-template-columns: auto minmax(0, 1fr) auto !important;
            align-items: center !important;
        }
        .profile-detail-panel-head h3 {
            overflow: hidden !important;
            text-overflow: ellipsis !important;
            white-space: nowrap !important;
        }
        .student-dashboard-grid {
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)) !important;
        }
        .student-dashboard-card {
            min-width: 0 !important;
        }
        .student-dashboard-info span {
            align-items: center !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
            white-space: nowrap !important;
        }

        @media (max-width: 1180px) {
            .student-crm-layout {
                grid-template-columns: 1fr !important;
            }
            .student-crm-list-panel,
            .student-crm-profile-panel {
                height: auto !important;
            }
            .student-crm-list-panel {
                position: static !important;
            }
            .student-crm-list {
                max-height: 420px !important;
            }
            .student-crm-profile-panel {
                min-height: 0 !important;
            }
            .profile-mark-columns {
                grid-template-columns: 1fr !important;
            }
            .profile-detail-column {
                min-height: 0 !important;
            }
            .profile-header-widget {
                grid-template-columns: 1fr !important;
            }
            .student-profile-identity {
                border-right: 0 !important;
                border-bottom: 1px solid var(--separator) !important;
                padding-right: 0 !important;
                padding-bottom: 14px !important;
            }
            .student-profile-info-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            }
        }

        @media (max-width: 760px) {
            .student-dashboard-head,
            #roster-overview > .d-flex:first-child {
                align-items: stretch !important;
                flex-direction: column !important;
            }
            .profile-header-widget {
                grid-template-columns: 1fr !important;
            }
            .student-profile-identity {
                border-right: 0 !important;
                border-bottom: 1px solid var(--separator) !important;
                padding-right: 0 !important;
                padding-bottom: 14px !important;
            }
            .student-view-toggle {
                width: 100% !important;
            }
            .student-view-toggle .segment-btn {
                flex: 1 1 0 !important;
                justify-content: center !important;
            }
        }

        @media (max-width: 520px) {
            .student-crm-profile-panel,
            .student-crm-list-panel {
                padding: 12px !important;
            }
            .student-crm-list .student-page-card {
                padding: 10px !important;
            }
            .student-profile-identity {
                grid-template-columns: 64px minmax(0, 1fr) !important;
            }
            .profile-large-avatar {
                width: 64px !important;
                height: 64px !important;
            }
            .student-profile-info-grid,
            .student-profile-progress-strip,
            .student-dashboard-grid {
                grid-template-columns: 1fr !important;
            }
            .profile-detail-panel {
                padding: 12px !important;
            }
            .profile-detail-panel-head {
                gap: 8px !important;
            }
            .profile-detail-panel-head b {
                font-size: 10px !important;
                padding: 4px 7px !important;
            }
        }

        /* Live Roll Call dashboard refinement */
        .rollcall-hero {
            background: var(--bg-surface) !important;
            border: 1px solid var(--separator) !important;
            border-radius: 14px !important;
            box-shadow: var(--shadow-sm) !important;
            padding: 18px !important;
            margin-bottom: 16px !important;
        }
        .rollcall-window-banner {
            margin-bottom: 16px !important;
        }
        .rollcall-workspace {
            grid-template-columns: minmax(0, 1fr) minmax(420px, 480px) !important;
            gap: 16px !important;
            align-items: start !important;
        }
        .rollcall-list-panel,
        .rollcall-calendar-panel {
            border-radius: 14px !important;
            box-shadow: var(--shadow-sm) !important;
        }
        .rollcall-list-panel {
            max-height: calc(100vh - 230px) !important;
            overflow-y: auto !important;
            padding: 12px !important;
        }
        .rollcall-row {
            display: grid !important;
            grid-template-columns: minmax(0, 1fr) auto !important;
            gap: 14px !important;
            align-items: center !important;
            padding: 14px !important;
            border-radius: 12px !important;
        }
        .rollcall-student {
            min-width: 0 !important;
        }
        .rollcall-student > div:last-child {
            min-width: 0 !important;
        }
        .rollcall-student-name {
            overflow: hidden !important;
            text-overflow: ellipsis !important;
            white-space: nowrap !important;
        }
        @media (min-width: 1281px) {
            .rollcall-options {
                display: grid !important;
                grid-template-columns: repeat(3, minmax(88px, 1fr)) !important;
                gap: 8px !important;
                min-width: 300px !important;
            }
        }
        .rollcall-chip {
            min-height: 36px !important;
            padding: 8px 10px !important;
            border-radius: 9px !important;
            font-size: 12px !important;
        }
        .rollcall-calendar-panel {
            padding: 14px !important;
            background: var(--bg-surface) !important;
        }
        .rollcall-calendar-content {
            min-width: 0 !important;
        }
        .rollcall-calendar-head,
        .rollcall-history-stats,
        .rollcall-analytics-grid,
        .rollcall-exam-card,
        .rollcall-calendar-grid,
        .rollcall-calendar-weekdays,
        .rollcall-calendar-legend {
            min-width: 0 !important;
        }
        .rollcall-calendar-head {
            background: var(--bg-base) !important;
            border: 1px solid var(--separator) !important;
            border-radius: 12px !important;
            padding: 12px !important;
            margin-bottom: 12px !important;
        }
        .rollcall-history-stats,
        .rollcall-analytics-grid {
            gap: 8px !important;
            margin: 0 0 10px !important;
        }
        .rollcall-history-stats div,
        .rollcall-analytics-grid div {
            padding: 9px !important;
            border-radius: 11px !important;
        }
        .rollcall-history-stats strong,
        .rollcall-analytics-grid strong {
            font-size: 17px !important;
        }
        .rollcall-exam-card {
            margin: 10px 0 12px !important;
            background: #f8fafc !important;
        }
        .rollcall-calendar-month {
            background: var(--bg-base) !important;
            border: 1px solid var(--separator) !important;
            border-radius: 10px !important;
            padding: 9px 11px !important;
            margin: 10px 0 !important;
        }
        .rollcall-calendar-grid {
            gap: 5px !important;
        }
        .rollcall-calendar-day {
            min-height: 38px !important;
            border-radius: 8px !important;
        }

        @media (max-width: 1280px) {
            .rollcall-workspace {
                grid-template-columns: 1fr !important;
            }
            .rollcall-calendar-panel {
                position: static !important;
            }
            .rollcall-list-panel {
                max-height: none !important;
            }
        }

        @media (max-width: 760px) {
            .rollcall-hero {
                padding: 14px !important;
            }
            .rollcall-summary-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
                min-width: 0 !important;
            }
            .rollcall-row {
                grid-template-columns: 1fr !important;
            }
            .rollcall-options {
                grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
                min-width: 0 !important;
            }
            .rollcall-chip {
                padding-inline: 6px !important;
            }
            .rollcall-chip .material-symbols-rounded {
                display: none !important;
            }
        }

        @media (max-width: 520px) {
            .rollcall-summary-grid,
            .rollcall-history-stats,
            .rollcall-analytics-grid {
                grid-template-columns: 1fr !important;
            }
            .rollcall-exam-score-grid,
            .rollcall-exam-result {
                grid-template-columns: 1fr !important;
            }
            .rollcall-calendar-panel {
                padding: 12px !important;
            }
            .rollcall-calendar-day {
                min-height: 34px !important;
                font-size: 10px !important;
            }
        }

        /* Profile tabs: no overlap in List View or Dashboard profile */
        .student-crm-profile-panel #profile-content {
            display: grid;
            grid-auto-rows: auto !important;
            align-content: start !important;
            row-gap: 18px !important;
            column-gap: 0 !important;
            min-width: 0 !important;
            width: 100% !important;
            height: auto !important;
            min-height: 0 !important;
            overflow: visible !important;
        }
        .student-crm-profile-panel .profile-tabs {
            grid-row: auto !important;
            position: static !important;
            top: auto !important;
            z-index: 1 !important;
            display: grid !important;
            grid-template-columns: repeat(6, minmax(0, 1fr)) !important;
            gap: 6px !important;
            width: 100% !important;
            margin: 0 !important;
            padding: 5px !important;
            overflow: visible !important;
            background: transparent !important;
            border: none !important;
            border-radius: 12px !important;
            box-shadow: none !important;
        }
        .student-crm-profile-panel .profile-tabs .segment-btn {
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            width: 100% !important;
            min-width: 0 !important;
            min-height: 38px !important;
            padding: 7px 8px !important;
            white-space: normal !important;
            overflow-wrap: anywhere !important;
            text-align: center !important;
            line-height: 1.15 !important;
            font-size: 11px !important;
            font-weight: 800 !important;
            border-radius: 9px !important;
        }
        .student-crm-profile-panel #profile-pane-content {
            grid-row: auto !important;
            display: block !important;
            position: relative !important;
            z-index: 0 !important;
            clear: both !important;
            margin-top: 0 !important;
            padding-top: 0 !important;
            min-width: 0 !important;
            width: 100% !important;
            overflow: visible !important;
        }
        .student-crm-profile-panel .profile-tabs + #profile-pane-content {
            margin-top: 0 !important;
        }
        [data-theme="dark"] .student-crm-profile-panel .profile-tabs {
            background: transparent !important;
            border-color: transparent !important;
        }

        @media (max-width: 1180px) {
            .student-crm-profile-panel .profile-tabs {
                grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
            }
        }

        @media (max-width: 520px) {
            .student-crm-profile-panel .profile-tabs {
                grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
                gap: 5px !important;
            }
            .student-crm-profile-panel .profile-tabs .segment-btn {
                min-height: 40px !important;
                padding: 6px !important;
                font-size: 10px !important;
            }
        }

        /* ============ COMPACT UI PASS ============ */
        .view-section { padding: 18px 22px 26px !important; }
        .mb-4 { margin-bottom: 0.85rem !important; }
        .mb-3 { margin-bottom: 0.6rem !important; }
        .mb-2 { margin-bottom: 0.4rem !important; }
        .mt-4 { margin-top: 0.85rem !important; }
        .mt-3 { margin-top: 0.6rem !important; }
        .gap-3 { gap: 0.7rem !important; }
        .gap-2 { gap: 0.45rem !important; }
        .student-crm-layout { gap: 14px !important; }
        .student-crm-list-panel, .student-crm-profile-panel { height: calc(100vh - 96px) !important; }
        .student-crm-panel-title { padding-bottom: 8px !important; }
        .dash-stat-card { padding: 18px 20px !important; }
        .ios-list-item { padding: 12px 16px !important; }
        .course-module-card { padding: 14px !important; }
        .report-panel { padding: 16px !important; }
        .rollcall-summary-card { padding: 10px 12px !important; }
        .admin-settings-card { padding: 14px !important; }
        .profile-header-widget { gap: 16px !important; margin-bottom: 16px !important; }
        .segment-btn { padding: 7px 12px !important; }
        .segmented-control { padding: 3px !important; }
        .btn-premium, .btn-gold { padding: 8px 14px !important; font-size: 12px !important; }
        .apple-input { min-height: 38px !important; }
        .modal-body { padding: 1.1rem !important; }
        .rollcall-calendar-panel { padding: 12px !important; }
        .student-contact-detail { padding: 18px !important; }
        .profile-tabs-wrapper { padding: 10px 14px 8px !important; margin-bottom: 12px !important; }
        .master-header { padding: 16px 18px 12px !important; }
        .section-title-row { margin-bottom: 6px !important; }

        /* ============ COMPACT UI PASS 2 (tighter) ============ */
        .topbar { height: 48px !important; padding: 0 16px !important; }
        .app-body { height: calc(100vh - 48px) !important; }
        .topbar-brand { font-size: 13px !important; }
        .topbar-time { font-size: 12px !important; }
        .topbar-user span { font-size: 12.5px !important; }
        .view-section { padding: 14px 18px 20px !important; }
        .nav-icon { width: 40px !important; height: 40px !important; }
        .nav-icon .material-symbols-rounded { font-size: 22px !important; }
        .section-title-row h1, .section-title-row h2, .section-title-row .fw-bold { font-size: 22px !important; }
        .section-title-icon { font-size: 20px !important; width: 30px !important; height: 30px !important; }
        .profile-large-avatar { width: 56px !important; height: 56px !important; font-size: 22px !important; }
        .student-avatar { width: 38px !important; height: 38px !important; font-size: 15px !important; }
        .student-info h4 { font-size: 13.5px !important; margin-bottom: 4px !important; }
        .student-profile-placeholder { min-height: 300px !important; }
        .rollcall-calendar-empty { min-height: 260px !important; }
        .rollcall-calendar-panel { min-height: 300px !important; }
        .student-contact-empty { min-height: 300px !important; }
        .student-contact-detail { min-height: 360px !important; }
        .ios-list-item { padding: 10px 13px !important; }
        .dash-stat-card { padding: 13px 15px !important; }
        .report-panel { padding: 12px !important; }
        .course-module-card { padding: 12px !important; margin-bottom: 8px !important; }
        .rollcall-summary-card { padding: 8px 10px !important; }
        .admin-settings-card { padding: 12px !important; }
        .profile-header-widget { gap: 12px !important; margin-bottom: 12px !important; }
        .segment-btn { padding: 6px 11px !important; font-size: 12px !important; }
        .segmented-control { padding: 3px !important; }
        .btn-premium, .btn-gold { padding: 7px 12px !important; font-size: 11.5px !important; }
        .apple-input { min-height: 34px !important; font-size: 13px !important; }
        .modal-body { padding: 1rem !important; }
        .modal-dialog { margin: 1rem auto !important; }
        .rollcall-chip { min-height: 30px !important; padding: 5px 8px !important; font-size: 11px !important; }
        .mb-4 { margin-bottom: 0.7rem !important; }
        .mb-3 { margin-bottom: 0.5rem !important; }
        .mt-4 { margin-top: 0.7rem !important; }
        .mt-3 { margin-top: 0.5rem !important; }
        .gap-3 { gap: 0.6rem !important; }
        .gap-2 { gap: 0.4rem !important; }
        .student-crm-layout { gap: 12px !important; }
        .master-pane { width: 300px !important; }
        .master-title { font-size: 16px !important; margin-bottom: 12px !important; }
        .profile-tabs-wrapper { padding: 8px 12px 6px !important; margin-bottom: 10px !important; }
        .profile-tabs .segment-btn { font-size: 11.5px !important; padding: 5px 10px !important; }
        .curriculum-category { font-size: 13px !important; }
        .course-page-tabs .segment-btn { padding: 6px 12px !important; font-size: 12px !important; }

        /* ============ COMPACT UI PASS 3 (Theory Operations cards) ============ */
        .course-category-block { margin-bottom: 14px !important; }
        .course-category-header { padding: 2px 2px 6px !important; margin-bottom: 4px !important; gap: 10px !important; }
        .course-category-header h3 { font-size: 15px !important; margin: 2px 0 0 !important; }
        .course-category-kicker { font-size: 10px !important; }
        .course-category-meta { font-size: 11px !important; }
        .course-module-card { padding: 10px 12px !important; }
        .course-module-title { font-size: 13.5px !important; line-height: 1.3 !important; }
        .course-module-meta { font-size: 11px !important; margin-top: 3px !important; }
        .course-module-kicker { font-size: 10px !important; margin-bottom: 1px !important; }
        .course-module-status { font-size: 8.5px !important; padding: 1px 6px !important; }
        .course-module-mark-btn { min-width: 68px !important; padding: 0 8px !important; font-size: 11px !important; gap: 5px !important; }
        .icon-action-btn { width: 28px !important; height: 28px !important; }
        .icon-action-btn .material-symbols-rounded { font-size: 16px !important; }
        .course-module-progress { height: 4px !important; }
        .marked-student-grid { margin-top: 8px !important; gap: 6px !important; }
        .marked-student-chip { padding: 4px 8px !important; font-size: 11px !important; gap: 5px !important; }
        .course-category-block { display: grid !important; grid-template-columns: repeat(2, minmax(0, 1fr)) !important; gap: 8px 12px !important; }
        .course-category-header { grid-column: 1 / -1 !important; }
        .course-module-card { margin-bottom: 0 !important; }
        @media (max-width: 1100px) {
            .course-category-block { grid-template-columns: 1fr !important; }
        }

        /* ============ NEW BUTTON SYSTEM (iOS-style) ============ */
        .btn, .btn-premium, .btn-gold, .btn-dark, .btn-outline-danger {
            border-radius: 12px !important;
            font-weight: 600 !important;
            transition: all 0.18s ease !important;
        }
        .btn-premium, .btn-gold {
            background: linear-gradient(135deg, #0a84ff 0%, #2f6bff 100%) !important;
            color: #fff !important;
            border: none !important;
            padding: 8px 16px !important;
            font-size: 12.5px !important;
            letter-spacing: 0.1px !important;
            box-shadow: 0 4px 14px rgba(10, 132, 255, 0.28), inset 0 1px 0 rgba(255, 255, 255, 0.22) !important;
        }
        .btn-premium:hover, .btn-gold:hover {
            background: linear-gradient(135deg, #0a7bff 0%, #1f5dff 100%) !important;
            transform: translateY(-1px) !important;
            box-shadow: 0 6px 18px rgba(10, 132, 255, 0.36), inset 0 1px 0 rgba(255, 255, 255, 0.22) !important;
        }
        .btn-premium:active, .btn-gold:active { transform: translateY(0) scale(0.97) !important; }
        .btn-dark {
            background: var(--bg-surface-hover) !important;
            color: var(--text-primary) !important;
            border: 1px solid var(--separator) !important;
            padding: 8px 16px !important;
            font-size: 12.5px !important;
            box-shadow: none !important;
        }
        .btn-dark:hover { background: var(--bg-surface) !important; border-color: var(--text-secondary) !important; }
        .btn-dark:active { transform: scale(0.97) !important; }
        .btn-outline-danger {
            background: rgba(220, 38, 38, 0.06) !important;
            color: #dc2626 !important;
            border: 1px solid rgba(220, 38, 38, 0.35) !important;
            padding: 8px 16px !important;
            font-size: 12.5px !important;
        }
        .btn-outline-danger:hover { background: rgba(220, 38, 38, 0.12) !important; }
        .segmented-control { background: transparent !important; border: none !important; padding: 2px !important; }
        .segment-btn { border-radius: 999px !important; font-weight: 600 !important; background: transparent !important; }
        .segment-btn:hover { background: var(--bg-surface-hover) !important; }
        .segment-btn.active {
            background: linear-gradient(135deg, #0a84ff, #2f6bff) !important;
            color: #fff !important;
            box-shadow: 0 2px 8px rgba(10, 132, 255, 0.32) !important;
        }
        .icon-action-btn {
            border-radius: 10px !important;
            background: var(--chip-bg) !important;
            color: var(--brand-blue) !important;
            box-shadow: none !important;
        }
        .icon-action-btn:hover { background: rgba(37, 99, 235, 0.22) !important; transform: translateY(-1px) !important; }
        .icon-action-btn.danger { background: rgba(220, 38, 38, 0.08) !important; color: #dc2626 !important; box-shadow: none !important; }
        .icon-action-btn.danger:hover { background: #dc2626 !important; color: #fff !important; transform: translateY(-1px) !important; }
        .btn-success-soft {
            background: linear-gradient(135deg, #22c55e, #16a34a) !important;
            color: #fff !important;
            border: none !important;
            padding: 8px 16px !important;
            font-size: 12.5px !important;
            font-weight: 600 !important;
            border-radius: 12px !important;
            box-shadow: 0 4px 14px rgba(22, 163, 74, 0.28) !important;
        }
        .btn-success-soft:hover { transform: translateY(-1px) !important; }
        .btn-danger-soft {
            background: linear-gradient(135deg, #ef4444, #dc2626) !important;
            color: #fff !important;
            border: none !important;
            padding: 8px 16px !important;
            font-size: 12.5px !important;
            font-weight: 600 !important;
            border-radius: 12px !important;
            box-shadow: 0 4px 14px rgba(220, 38, 38, 0.28) !important;
        }
        .btn-danger-soft:hover { transform: translateY(-1px) !important; }
        .btn:focus-visible, .btn-premium:focus-visible, .btn-dark:focus-visible, .segment-btn:focus-visible,
        .icon-action-btn:focus-visible, .btn-success-soft:focus-visible, .btn-danger-soft:focus-visible {
            outline: 2px solid var(--brand-blue) !important;
            outline-offset: 2px;
        }

        /* ===== EQUAL-WIDTH BUTTONS ===== */
        .modal-body .d-flex.justify-content-end > button,
        .modal-body .d-flex.ms-auto > button {
            flex: 1 1 0 !important;
            min-width: 0 !important;
            border-width: 1px !important;
            border-style: solid !important;
        }
        #view-calendar .btn-outline-secondary,
        #view-calendar .btn-premium {
            min-width: 92px !important;
            justify-content: center;
        }
        #view-today .btn-outline-secondary {
            min-width: 44px !important;
            justify-content: center;
            padding-inline: 10px !important;
        }

        /* =======================================================================
           BASIC UI MODE - completely stripped, plain, functional UI
           ======================================================================= */
        [data-ui="basic"] {
            --bg-base: #ffffff !important;
            --bg-surface: #ffffff !important;
            --bg-surface-hover: #f5f5f5 !important;
            --text-primary: #111111 !important;
            --text-secondary: #444444 !important;
            --separator: #999999 !important;
            --brand-blue: #0056d6 !important;
            --brand-blue-hover: #003d99 !important;
            --brand-blue-light: #e6efff !important;
            --system-blue: #0056d6 !important;
            --system-green: #1a7f37 !important;
            --system-red: #d1242f !important;
            --system-orange: #b45309 !important;
            --brand-green: #1a7f37 !important;
            --brand-red: #d1242f !important;
            --brand-purple: #6b21a8 !important;
        }

        /* Kill all decorations: orbs, blur, gradients, shadows, rounded corners */
        [data-ui="basic"] body::before,
        [data-ui="basic"] body::after,
        [data-ui="basic"] .global-bg-orb-3 {
            display: none !important;
        }
        [data-ui="basic"] body {
            background-color: #ffffff !important;
            color: #111111 !important;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif !important;
        }
        [data-ui="basic"] .topbar,
        [data-ui="basic"] .sidebar,
        [data-ui="basic"] .master-pane,
        [data-ui="basic"] .detail-pane {
            background: #ffffff !important;
            backdrop-filter: none !important;
            -webkit-backdrop-filter: none !important;
            box-shadow: none !important;
        }
        [data-ui="basic"] .topbar {
            height: 44px !important;
            padding: 0 16px !important;
            border-bottom: 1px solid #999999 !important;
        }
        [data-ui="basic"] .detail-pane {
            background: #f7f7f7 !important;
        }
        [data-ui="basic"] .sidebar {
            width: 56px !important;
            padding-top: 8px !important;
            gap: 4px !important;
            border-right: 1px solid #999999 !important;
        }
        [data-ui="basic"] .nav-icon {
            width: 36px !important;
            height: 36px !important;
            border-radius: 0 !important;
            color: #333333 !important;
            box-shadow: none !important;
            transform: none !important;
        }
        [data-ui="basic"] .nav-icon span { font-size: 20px !important; }
        [data-ui="basic"] .nav-icon:hover {
            background: #eeeeee !important;
            transform: none !important;
        }
        [data-ui="basic"] .nav-icon.active {
            background: #111111 !important;
            color: #ffffff !important;
            box-shadow: none !important;
        }
        [data-ui="basic"] .master-pane {
            width: 300px !important;
            border-right: 1px solid #999999 !important;
        }
        [data-ui="basic"] .master-header { padding: 12px 14px 10px !important; }
        [data-ui="basic"] .master-title { font-size: 15px !important; margin-bottom: 10px !important; }
        [data-ui="basic"] .student-list-container { padding: 6px !important; background: #ffffff !important; }
        [data-ui="basic"] .view-section { padding: 16px 20px 40px !important; max-width: 1400px !important; }

        /* Plain components: no radius, no shadow, thin borders */
        [data-ui="basic"] .btn,
        [data-ui="basic"] .btn-premium,
        [data-ui="basic"] .btn-gold,
        [data-ui="basic"] .btn-dark,
        [data-ui="basic"] .btn-success-soft,
        [data-ui="basic"] .btn-danger-soft,
        [data-ui="basic"] .segment-btn,
        [data-ui="basic"] .icon-action-btn,
        [data-ui="basic"] .apple-input,
        [data-ui="basic"] .student-card,
        [data-ui="basic"] .student-page-card,
        [data-ui="basic"] .student-crm-list-panel,
        [data-ui="basic"] .student-crm-profile-panel,
        [data-ui="basic"] .ios-list,
        [data-ui="basic"] .ios-list-item,
        [data-ui="basic"] .rollcall-chip,
        [data-ui="basic"] .dash-stat-card,
        [data-ui="basic"] .modal-content,
        [data-ui="basic"] .admin-settings-card,
        [data-ui="basic"] .report-person-card,
        [data-ui="basic"] .category-block,
        [data-ui="basic"] .calendar-day,
        [data-ui="basic"] .today-card,
        [data-ui="basic"] .profile-detail-panel,
        [data-ui="basic"] .profile-header-widget {
            border-radius: 0 !important;
            box-shadow: none !important;
            border: 1px solid #bbbbbb !important;
        }
        [data-ui="basic"] .student-card:hover,
        [data-ui="basic"] .student-page-card:hover {
            transform: none !important;
            box-shadow: none !important;
        }
        [data-ui="basic"] .student-page-card {
            padding: 10px 12px !important;
        }
        [data-ui="basic"] .btn-premium,
        [data-ui="basic"] .btn-gold,
        [data-ui="basic"] .btn-success-soft,
        [data-ui="basic"] .btn-danger-soft {
            background: #0056d6 !important;
            border: none !important;
            padding: 6px 14px !important;
            font-size: 13px !important;
            transform: none !important;
        }
        [data-ui="basic"] .btn-success-soft { background: #1a7f37 !important; }
        [data-ui="basic"] .btn-danger-soft { background: #d1242f !important; }
        [data-ui="basic"] .btn-dark {
            background: #ffffff !important;
            color: #111111 !important;
            border: 1px solid #999999 !important;
        }
        [data-ui="basic"] .btn-dark:hover { background: #f0f0f0 !important; }
        [data-ui="basic"] .apple-input {
            background: #ffffff !important;
            color: #111111 !important;
            border: 1px solid #999999 !important;
            padding: 6px 10px !important;
            font-size: 13px !important;
            box-shadow: none !important;
        }
        [data-ui="basic"] .apple-input:focus {
            border: 1px solid #0056d6 !important;
            box-shadow: none !important;
        }
        [data-ui="basic"] .segment-btn {
            border: 1px solid #999999 !important;
            background: #ffffff !important;
            color: #111111 !important;
        }
        [data-ui="basic"] .segment-btn.active {
            background: #111111 !important;
            color: #ffffff !important;
            border-color: #111111 !important;
        }
        [data-ui="basic"] .student-crm-profile-panel .profile-tabs .segment-btn,
        [data-ui="basic"] .profile-tabs .segment-btn {
            border-radius: 0 !important;
        }

        /* Sweep remaining decorative rounding/shadows/gradients in Basic mode */
        [data-ui="basic"] * {
            box-shadow: none !important;
            backdrop-filter: none !important;
            -webkit-backdrop-filter: none !important;
            text-shadow: none !important;
        }
        [data-ui="basic"] .material-symbols-rounded,
        [data-ui="basic"] .section-title-icon,
        [data-ui="basic"] .d-inline-flex.align-items-center,
        [data-ui="basic"] .profile-attendance-day,
        [data-ui="basic"] .profile-upload-preview,
        [data-ui="basic"] .rollcall-window-banner,
        [data-ui="basic"] .rollcall-calendar-month,
        [data-ui="basic"] .student-contact-list,
        [data-ui="basic"] .segmented-control,
        [data-ui="basic"] .student-profile-info-cell,
        [data-ui="basic"] .profile-mini-mark,
        [data-ui="basic"] .profile-repair-comment,
        [data-ui="basic"] .profile-attendance-calendar,
        [data-ui="basic"] .profile-upload-row,
        [data-ui="basic"] .profile-status-toggle,
        [data-ui="basic"] .modal-profile-header,
        [data-ui="basic"] .modal-profile-tabs,
        [data-ui="basic"] .rollcall-hero,
        [data-ui="basic"] .rollcall-summary-card,
        [data-ui="basic"] .rollcall-calendar-panel,
        [data-ui="basic"] .rollcall-calendar-head,
        [data-ui="basic"] .rollcall-calendar,
        [data-ui="basic"] .rollcall-schedule-card,
        [data-ui="basic"] .rollcall-day {
            border-radius: 0 !important;
            box-shadow: none !important;
        }
        [data-ui="basic"] .rollcall-hero > div,
        [data-ui="basic"] .rollcall-summary-card > div {
            border-radius: 0 !important;
        }
        [data-ui="basic"] .rollcall-history-stats > div,
        [data-ui="basic"] .rollcall-analytics-grid > div,
        [data-ui="basic"] .student-contact-info-grid > div {
            border-radius: 0 !important;
            box-shadow: none !important;
        }
        [data-ui="basic"] .student-contact-detail,
        [data-ui="basic"] .contact-status,
        [data-ui="basic"] .student-contact-progress,
        [data-ui="basic"] .student-contact-list-item,
        [data-ui="basic"] .course-page-main-header,
        [data-ui="basic"] .exam-card,
        [data-ui="basic"] .form-select {
            border-radius: 0 !important;
            box-shadow: none !important;
        }
        [data-ui="basic"] .contact-status {
            border: 1px solid #999999 !important;
            background: transparent !important;
        }
        [data-ui="basic"] .profile-mini-mark {
            border: 1px solid #999999 !important;
            background: transparent !important;
        }
        /* Kill the gradient wash on body/detail pane in Basic mode */
        [data-ui="basic"] body,
        [data-ui="basic"] .detail-pane {
            background: #f2f2f2 !important;
            background-image: none !important;
        }
        /* Progress bars: flat, no gradient */
        [data-ui="basic"] .progress,
        [data-ui="basic"] .progress-bar {
            border-radius: 0 !important;
            background-image: none !important;
        }
        [data-ui="basic"] .progress-bar {
            background-color: #0056d6 !important;
        }
        /* Pills/badges: square */
        [data-ui="basic"] .badge,
        [data-ui="basic"] .tag,
        [data-ui="basic"] .status-badge,
        [data-ui="basic"] .completion-tag,
        [data-ui="basic"] .rollcall-chip,
        [data-ui="basic"] .trainee-chip,
        [data-ui="basic"] span[style*="border-radius"],
        [data-ui="basic"] div[style*="border-radius"],
        [data-ui="basic"] button[style*="border-radius"],
        [data-ui="basic"] a[style*="border-radius"],
        [data-ui="basic"] label[style*="border-radius"],
        [data-ui="basic"] select[style*="border-radius"],
        [data-ui="basic"] input[style*="border-radius"],
        [data-ui="basic"] li[style*="border-radius"],
        [data-ui="basic"] td[style*="border-radius"],
        [data-ui="basic"] th[style*="border-radius"] {
            border-radius: 0 !important;
        }
        /* Student/report avatars: keep round photos but drop shadows */
        [data-ui="basic"] .student-avatar,
        [data-ui="basic"] .report-photo,
        [data-ui="basic"] .profile-large-avatar {
            box-shadow: none !important;
        }
        [data-ui="basic"] .profile-detail-panel-head b {
            border-radius: 0 !important;
            background: transparent !important;
            color: var(--text-primary) !important;
            border: 1px solid #999999 !important;
        }
        [data-ui="basic"] .profile-header-widget {
            background: #ffffff !important;
        }
        [data-ui="basic"] .profile-detail-panel-head {
            background: #ffffff !important;
            border-radius: 0 !important;
        }
        [data-ui="basic"] .student-card {
            background: #ffffff !important;
            border: 1px solid #cccccc !important;
            padding: 8px 10px !important;
            margin-bottom: 4px !important;
        }
        [data-ui="basic"] .student-card.active {
            border: 2px solid #0056d6 !important;
            box-shadow: none !important;
        }
        [data-ui="basic"] .student-card:hover { background: #f5f5f5 !important; }

        /* Hide the fancy profile dropdown in topbar (Basic mode = no profile) */
        [data-ui="basic"] .topbar-user,
        [data-ui="basic"] .topbar-user + .dropdown-menu {
            display: none !important;
        }

        /* Basic mode: classic admin text-nav sidebar (stepped/nested layout feel) */
        .nav-label { display: none; }
        [data-ui="basic"] .sidebar {
            width: 168px !important;
            align-items: stretch !important;
            padding: 8px 6px !important;
            gap: 2px !important;
        }
        [data-ui="basic"] .sidebar .nav-icon {
            width: 100% !important;
            height: 34px !important;
            display: flex !important;
            justify-content: flex-start !important;
            align-items: center !important;
            gap: 8px !important;
            padding: 0 10px !important;
            border-radius: 0 !important;
            color: #222222 !important;
            font-size: 13px !important;
            font-weight: 500 !important;
            box-shadow: none !important;
        }
        [data-ui="basic"] .sidebar .nav-icon span.material-symbols-rounded {
            font-size: 18px !important;
        }
        [data-ui="basic"] .sidebar .nav-icon .nav-label {
            display: inline !important;
            white-space: nowrap;
        }
        [data-ui="basic"] .sidebar .nav-icon:hover {
            background: #eeeeee !important;
            transform: none !important;
        }
        [data-ui="basic"] .sidebar .nav-icon.active {
            background: #111111 !important;
            color: #ffffff !important;
            box-shadow: none !important;
        }

        /* Plain text/typography */
        [data-ui="basic"] .topbar-brand { font-size: 13px !important; }
        [data-ui="basic"] .topbar-time {
            font-size: 12px !important;
            color: #333333 !important;
            background: transparent !important;
            border: none !important;
            padding: 0 !important;
            border-radius: 0 !important;
        }
        [data-ui="basic"] h1, [data-ui="basic"] h2, [data-ui="basic"] h3,
        [data-ui="basic"] .section-title, [data-ui="basic"] .profile-tabs .segment-btn {
            letter-spacing: 0 !important;
            text-transform: none !important;
        }
        [data-ui="basic"] .ios-list-item { padding: 6px 10px !important; }
        [data-ui="basic"] .calendar-day { min-height: 70px !important; }
        [data-ui="basic"] .dash-stat-card { padding: 10px 14px !important; }

        /* Compact tables */
        [data-ui="basic"] table { font-size: 12.5px !important; }
        [data-ui="basic"] th, [data-ui="basic"] td { padding: 4px 8px !important; }
    </style>
</head>
<body>
<div class="global-bg-orb-3"></div>
