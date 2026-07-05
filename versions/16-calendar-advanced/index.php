<?php
declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';
require_logged_in_for_page();

$csrfToken = get_csrf_token();
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e('HaushaltsPilot – Teil 16 Kalender vertieft') ?></title>
    <meta name="csrf-token" content="<?= e($csrfToken) ?>">
    <style>
        :root {
            --sidebar:#343a40;
            --sidebar-dark:#2f353a;
            --sidebar-soft:#495057;
            --content:#f4f6f9;
            --card:#ffffff;
            --text:#1f2937;
            --muted:#6b7280;
            --border:#d7dde5;
            --soft:#f8fafc;
            --primary:#007bff;
            --primary-dark:#0069d9;
            --success:#28a745;
            --danger:#dc3545;
            --danger-dark:#bd2130;
            --warning:#ffc107;
            --info:#17a2b8;
            --purple:#6f42c1;
            --done:#eef2f7;
            --shadow:0 10px 24px rgba(15,23,42,.08);
            --radius:12px;
        }
        * { box-sizing:border-box; }
        html { min-height:100%; }
        body { margin:0; min-height:100vh; font-family:Arial,Helvetica,sans-serif; background:var(--content); color:var(--text); }
        button,input,select,textarea { font-family:inherit; }
        .app-shell { min-height:100vh; display:grid; grid-template-columns:260px minmax(0,1fr); }
        .sidebar { background:var(--sidebar); color:#c2c7d0; display:flex; flex-direction:column; min-height:100vh; box-shadow:4px 0 16px rgba(0,0,0,.08); position:sticky; top:0; }
        .brand { display:flex; align-items:center; gap:10px; padding:18px 18px; border-bottom:1px solid rgba(255,255,255,.08); color:#fff; font-size:1.15rem; font-weight:800; letter-spacing:.01em; }
        .brand-icon { width:34px; height:34px; border-radius:9px; display:grid; place-items:center; background:var(--primary); box-shadow:0 8px 20px rgba(0,123,255,.25); }
        .sidebar-user { padding:16px 18px; border-bottom:1px solid rgba(255,255,255,.08); }
        .sidebar-user strong { display:block; color:#fff; margin-bottom:4px; }
        .sidebar-user small { color:#adb5bd; }
        .sidebar-nav { padding:12px; display:grid; gap:4px; }
        .app-nav-btn { width:100%; display:flex; align-items:center; justify-content:space-between; gap:10px; color:#c2c7d0; background:transparent; border:0; border-radius:8px; padding:11px 12px; text-align:left; cursor:pointer; font-weight:700; }
        .app-nav-btn:hover { background:var(--sidebar-soft); color:#fff; }
        .app-nav-btn.active { background:var(--primary); color:#fff; box-shadow:0 8px 16px rgba(0,123,255,.18); }
        .nav-left { display:flex; align-items:center; gap:10px; min-width:0; }
        .nav-icon { width:22px; text-align:center; opacity:.95; }
        .nav-badge,.unread-badge,.thread-unread,.admin-menu-btn small,.admin-count-pill { border-radius:999px; font-size:.78rem; font-weight:800; line-height:1; }
        .nav-badge { min-width:24px; padding:5px 7px; text-align:center; background:#dc3545; color:white; }
        .nav-badge.empty { background:#6c757d; color:white; }
        .sidebar-footer { margin-top:auto; padding:14px 18px; border-top:1px solid rgba(255,255,255,.08); font-size:.78rem; color:#adb5bd; line-height:1.45; }
        .main-shell { min-width:0; display:flex; flex-direction:column; }
        .main-header { height:64px; background:#fff; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; padding:0 22px; position:sticky; top:0; z-index:10; box-shadow:0 2px 8px rgba(15,23,42,.04); }
        .page-heading { min-width:0; }
        .page-heading h1 { margin:0; font-size:1.25rem; line-height:1.2; }
        .page-heading small { color:var(--muted); }
        .top-userbox { display:flex; align-items:center; gap:14px; color:var(--muted); font-size:.9rem; white-space:nowrap; }
        .top-userbox strong { display:block; color:var(--text); text-align:right; }
        .logout { border:0; border-radius:8px; background:var(--danger); color:white; padding:9px 12px; font-weight:bold; cursor:pointer; }
        .logout:hover { background:var(--danger-dark); }
        .content { padding:22px; max-width:1540px; width:100%; }
        .module-page { display:none; }
        .module-page.active { display:block; }
        .module-page:not(.active) { display:none !important; }
        .panel { background:var(--card); border:1px solid var(--border); border-radius:var(--radius); padding:18px; box-shadow:var(--shadow); margin-bottom:18px; }
        .panel h2,.panel h3 { color:#111827; }
        .panel h2 { margin:0 0 12px; font-size:1.25rem; }
        .panel h3 { margin:0 0 10px; font-size:1.05rem; }
        p { color:var(--muted); line-height:1.5; margin:0; }
        .note { margin:0 0 18px; background:#e8f4ff; border:1px solid #b8daff; color:#004085; border-radius:10px; padding:12px 14px; font-size:.9rem; line-height:1.45; }
        .dashboard-hero { display:grid; grid-template-columns:minmax(0,1fr) 340px; gap:18px; align-items:stretch; }
        .hero-card { min-height:190px; background:linear-gradient(135deg,#007bff,#0d6efd 55%,#0056b3); color:white; border-radius:var(--radius); padding:24px; box-shadow:var(--shadow); overflow:hidden; position:relative; }
        .hero-card::after { content:""; position:absolute; width:210px; height:210px; border-radius:50%; right:-70px; bottom:-95px; background:rgba(255,255,255,.14); }
        .hero-card h2 { color:#fff; margin:0 0 10px; font-size:1.7rem; }
        .hero-card p { color:rgba(255,255,255,.9); max-width:720px; }
        .hero-actions { display:flex; flex-wrap:wrap; gap:10px; margin-top:22px; position:relative; z-index:1; }
        .dashboard-action { border:0; border-radius:8px; padding:10px 12px; font-weight:800; cursor:pointer; color:#fff; background:rgba(255,255,255,.18); }
        .dashboard-action:hover { background:rgba(255,255,255,.28); }
        .status-card { background:#fff; border:1px solid var(--border); border-radius:var(--radius); padding:18px; box-shadow:var(--shadow); }
        .status-card strong { display:block; margin-bottom:8px; }
        .status-list { display:grid; gap:10px; margin-top:12px; }
        .status-row { display:flex; justify-content:space-between; gap:12px; padding:10px; border:1px solid var(--border); border-radius:9px; background:var(--soft); }
        .small-boxes { display:grid; grid-template-columns:repeat(4,1fr); gap:14px; margin:18px 0; }
        .small-box { position:relative; min-height:118px; border-radius:var(--radius); color:#fff; padding:16px; overflow:hidden; box-shadow:var(--shadow); }
        .small-box strong { display:block; font-size:2rem; line-height:1; margin-bottom:8px; }
        .small-box span { font-weight:700; opacity:.96; }
        .small-box small { display:block; margin-top:7px; opacity:.86; }
        .small-box .big-icon { position:absolute; right:14px; bottom:10px; font-size:3.2rem; opacity:.23; }
        .box-blue { background:#007bff; }
        .box-green { background:#28a745; }
        .box-yellow { background:#ffc107; color:#1f2937; }
        .box-red { background:#dc3545; }
        .box-purple { background:#6f42c1; }
        .box-cyan { background:#17a2b8; }
        .dashboard-grid { display:grid; grid-template-columns:2fr 1fr; gap:18px; }
        .dashboard-card-grid { display:grid; grid-template-columns:repeat(2,1fr); gap:12px; }
        .dashboard-card { background:white; border:1px solid var(--border); border-radius:12px; padding:14px; }
        .dashboard-card strong { display:block; margin-bottom:5px; }
        .dashboard-card span { color:var(--muted); font-size:.88rem; }
        .layout { display:grid; grid-template-columns:330px 1fr; gap:18px; align-items:start; }
        .form { display:grid; gap:10px; margin-bottom:14px; }
        .item-form { display:grid; grid-template-columns:1.5fr 1fr 1fr auto; gap:10px; margin-bottom:12px; }
        input,select,textarea { width:100%; border:1px solid var(--border); border-radius:9px; padding:11px 12px; font-size:1rem; outline:none; background:white; }
        textarea { min-height:82px; resize:vertical; }
        input:focus,select:focus,textarea:focus { border-color:var(--primary); box-shadow:0 0 0 3px rgba(0,123,255,.15); }
        button { border:0; border-radius:9px; padding:11px 13px; font-size:.94rem; cursor:pointer; transition:.18s ease; }
        button:disabled { background:#d1d5db !important; color:#6b7280 !important; cursor:not-allowed; }
        .add-btn,.small-btn { background:var(--primary); color:white; font-weight:bold; }
        .add-btn:hover,.small-btn:hover { background:var(--primary-dark); }
        .delete-list-btn,.danger { background:var(--danger); color:white; font-weight:bold; }
        .delete-list-btn:hover,.danger:hover { background:var(--danger-dark); }
        .small-btn,.danger,.warning { padding:7px 10px; border-radius:8px; font-size:.84rem; }
        .warning { background:#f0ad4e; color:white; font-weight:bold; }
        .scope-row { display:flex; gap:8px; align-items:center; color:var(--muted); font-size:.9rem; }
        .scope-row input { width:auto; }
        .admin-only { display:none; }
        .admin-only.visible { display:block; }
        .sidebar-nav .admin-only.visible { display:flex; }
        .list-group { margin-top:14px; }
        .list-group-title { display:flex; justify-content:space-between; align-items:center; gap:8px; color:var(--muted); font-size:.78rem; font-weight:bold; text-transform:uppercase; letter-spacing:.06em; margin:12px 0 8px; }
        .list-buttons { display:grid; gap:8px; }
        .list-button { display:grid; grid-template-columns:1fr auto; gap:8px; width:100%; background:white; border:1px solid var(--border); color:var(--text); text-align:left; }
        .list-button:hover { border-color:var(--primary); }
        .list-button.active { background:var(--primary); border-color:var(--primary); color:white; }
        .list-button span { min-width:0; overflow:hidden; text-overflow:ellipsis; }
        .list-button small { opacity:.85; white-space:nowrap; }
        .current-title,.section-title-with-badge { display:flex; justify-content:space-between; align-items:center; gap:12px; margin-bottom:14px; }
        .current-title h2,.section-title-with-badge h2 { margin:0; }
        .current-title span { color:var(--muted); font-size:.9rem; }
        .status-message { min-height:22px; margin-bottom:12px; font-size:.88rem; color:var(--muted); text-align:center; }
        .status-message.error { color:var(--danger); font-weight:bold; }
        .stats { display:grid; grid-template-columns:repeat(3,1fr); gap:10px; margin-bottom:16px; }
        .stat-card { background:white; border:1px solid var(--border); border-radius:12px; padding:12px; text-align:center; }
        .stat-card span { display:block; color:var(--muted); font-size:.82rem; margin-bottom:4px; }
        .stat-card strong { font-size:1.3rem; }
        .list-settings { display:none; background:white; border:1px solid var(--border); border-radius:12px; padding:12px; margin-bottom:14px; }
        .list-settings.visible { display:block; }
        .settings-header { display:flex; justify-content:space-between; gap:10px; align-items:flex-start; margin-bottom:10px; }
        .settings-header strong { display:block; margin-bottom:4px; }
        .settings-header span { color:var(--muted); font-size:.84rem; }
        .settings-actions { display:flex; flex-wrap:wrap; gap:8px; align-items:center; }
        .owner-select-row { display:none; grid-template-columns:1fr auto; gap:8px; margin-top:10px; }
        .owner-select-row.visible { display:grid; }
        .inline-field { display:grid; grid-template-columns:1fr auto; gap:8px; align-items:center; margin-top:10px; }
        .shopping-list,.todo-list { list-style:none; padding:0; margin:0; }
        .shopping-item { display:grid; grid-template-columns:1fr auto; gap:12px; align-items:center; padding:14px; border:1px solid var(--border); border-radius:12px; margin-bottom:10px; background:white; }
        .shopping-item.done { background:var(--done); opacity:.75; }
        .shopping-item.done .item-name { text-decoration:line-through; color:var(--muted); }
        .item-name,.todo-title { display:block; font-weight:bold; word-break:break-word; margin-bottom:5px; }
        .item-meta { display:flex; flex-wrap:wrap; gap:6px; }
        .badge { display:inline-block; border-radius:999px; padding:4px 8px; font-size:.78rem; background:var(--soft); border:1px solid var(--border); color:var(--muted); }
        .item-actions { display:flex; gap:8px; align-items:center; }
        .toggle-btn { background:var(--success); color:white; padding:8px 10px; font-size:.85rem; white-space:nowrap; }
        .delete-btn { background:var(--danger); color:white; padding:8px 10px; font-size:.85rem; white-space:nowrap; }
        .empty-message { text-align:center; color:var(--muted); background:white; border:1px dashed var(--border); border-radius:10px; padding:18px; margin-top:12px; }
        .todo-form { display:grid; grid-template-columns:2fr 145px 145px 210px; gap:10px; align-items:center; margin-bottom:14px; }
        .todo-dashboard { display:grid; grid-template-columns:repeat(3,1fr); gap:10px; margin:0 0 16px; }
        .todo-board { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
        .todo-column h3 { margin:0 0 10px; font-size:1rem; }
        .todo-list { display:grid; gap:12px; }
        .todo-item { display:grid; grid-template-columns:1fr auto; gap:10px; align-items:start; background:white; border:1px solid var(--border); border-radius:12px; padding:12px; }
        .todo-item.done { background:var(--done); opacity:.78; }
        .todo-item.done .todo-title { text-decoration:line-through; color:var(--muted); }
        .todo-actions { display:flex; flex-wrap:wrap; gap:8px; justify-content:flex-end; }
        .todo-edit-form { grid-column:1 / -1; display:grid; grid-template-columns:2fr repeat(3,1fr); gap:8px; border-top:1px solid var(--border); padding-top:10px; }
        .todo-edit-form .wide { grid-column:1 / -1; }
        .comment-box { grid-column:1 / -1; border-top:1px solid var(--border); padding-top:10px; }
        .comment-list { display:grid; gap:6px; margin:0 0 8px; }
        .comment-item { background:var(--soft); border:1px solid var(--border); border-radius:10px; padding:8px; font-size:.86rem; }
        .comment-item small { display:block; color:var(--muted); margin-top:4px; }
        .comment-form { display:grid; grid-template-columns:1fr auto; gap:8px; }
        .message-form { display:grid; gap:10px; margin:14px 0 16px; }
        .message-layout { display:grid; grid-template-columns:340px minmax(0,1fr); gap:16px; align-items:start; }
        .thread-list { display:grid; gap:8px; }
        .thread-button { position:relative; width:100%; display:grid; grid-template-columns:1fr auto; gap:8px; background:#fff; border:1px solid var(--border); border-radius:10px; text-align:left; color:var(--text); }
        .thread-button.active { border-color:var(--primary); box-shadow:0 0 0 3px rgba(0,123,255,.12); }
        .thread-button strong { display:block; margin-bottom:5px; }
        .thread-button small { display:block; color:var(--muted); line-height:1.35; }
        .thread-unread { align-self:center; min-width:26px; padding:6px 8px; text-align:center; color:#fff; background:var(--danger); }
        .message-view { background:#fff; border:1px solid var(--border); border-radius:12px; padding:14px; min-height:240px; }
        .message-list { display:grid; gap:10px; margin:12px 0; }
        .message-bubble { max-width:78%; justify-self:start; background:var(--soft); border:1px solid var(--border); border-radius:14px 14px 14px 4px; padding:10px 12px; }
        .message-bubble.own { justify-self:end; background:#e8f4ff; border-color:#b8daff; border-radius:14px 14px 4px 14px; }
        .message-bubble p { color:var(--text); white-space:pre-wrap; overflow-wrap:anywhere; }
        .message-bubble small { display:block; color:var(--muted); margin-top:7px; font-size:.76rem; }
        .message-state { display:inline-flex; gap:4px; align-items:center; justify-content:flex-end; color:var(--muted); font-size:.74rem; font-weight:700; margin-top:5px; }
        .message-bubble.own .message-state,.chat-bubble.own .message-state { color:rgba(255,255,255,.86); }
        .typing-indicator { justify-self:start; display:inline-flex; align-items:center; gap:7px; color:var(--muted); background:#fff; border:1px solid var(--border); border-radius:999px; padding:7px 10px; font-size:.84rem; box-shadow:0 3px 8px rgba(15,23,42,.05); }
        .typing-dots { display:inline-flex; gap:3px; }
        .typing-dots span { width:5px; height:5px; border-radius:50%; background:var(--muted); opacity:.55; animation:typingPulse 1.2s infinite ease-in-out; }
        .typing-dots span:nth-child(2) { animation-delay:.16s; }
        .typing-dots span:nth-child(3) { animation-delay:.32s; }
        @keyframes typingPulse { 0%,80%,100%{transform:translateY(0);opacity:.35;} 40%{transform:translateY(-3px);opacity:.9;} }
        .reply-form { display:none; grid-template-columns:1fr auto; gap:8px; margin-top:12px; }
        .reply-form.visible { display:grid; }
        .unread-badge { display:inline-grid; place-items:center; min-width:32px; height:28px; padding:0 9px; color:#fff; background:var(--danger); }
        .unread-badge.empty { background:#6c757d; }

        .chat-page-grid { display:grid; grid-template-columns:330px minmax(0,1fr); gap:16px; align-items:start; }
        .chat-directory { display:grid; gap:10px; }
        .chat-contact-form { display:grid; gap:10px; margin-bottom:14px; }
        .chat-thread-list { display:grid; gap:8px; max-height:560px; overflow:auto; padding-right:3px; }
        .chat-thread-card { width:100%; display:grid; grid-template-columns:1fr auto; gap:8px; text-align:left; background:white; border:1px solid var(--border); border-radius:12px; padding:12px; color:var(--text); box-shadow:none; }
        .chat-thread-card:hover { border-color:var(--primary); background:#f8fbff; }
        .chat-thread-card.active { border-color:var(--primary); box-shadow:0 0 0 3px rgba(0,123,255,.12); }
        .chat-thread-card strong { display:block; margin-bottom:4px; }
        .chat-thread-card small { display:block; color:var(--muted); line-height:1.35; }
        .chat-main { min-height:600px; display:flex; flex-direction:column; overflow:hidden; }
        .chat-main-header { display:flex; justify-content:space-between; align-items:flex-start; gap:12px; padding:14px; border-bottom:1px solid var(--border); background:linear-gradient(135deg,#ffffff,#f8fbff); }
        .chat-main-header h3 { margin:0 0 4px; }
        .chat-main-header small { color:var(--muted); }
        .chat-main-body { flex:1; min-height:360px; max-height:520px; overflow:auto; display:grid; align-content:start; gap:10px; padding:14px; background:#f8fafc; }
        .chat-bubble { max-width:76%; justify-self:start; background:white; border:1px solid var(--border); border-radius:16px 16px 16px 5px; padding:10px 12px; box-shadow:0 3px 8px rgba(15,23,42,.05); }
        .chat-bubble.own { justify-self:end; color:white; background:var(--primary); border-color:var(--primary); border-radius:16px 16px 5px 16px; }
        .chat-bubble p { margin:0; white-space:pre-wrap; overflow-wrap:anywhere; }
        .chat-bubble small { display:block; margin-top:6px; color:var(--muted); font-size:.74rem; }
        .chat-bubble.own small { color:rgba(255,255,255,.78); }
        .chat-composer { display:grid; grid-template-columns:1fr auto; gap:10px; padding:12px; border-top:1px solid var(--border); background:white; }
        .chat-composer textarea { min-height:48px; max-height:130px; resize:vertical; }
        .emoji-bar { grid-column:1 / -1; display:flex; flex-wrap:wrap; gap:6px; }
        .emoji-btn { width:34px; height:34px; display:grid; place-items:center; padding:0; border-radius:10px; background:var(--soft); border:1px solid var(--border); font-size:1.05rem; }
        .emoji-btn:hover { border-color:var(--primary); background:#e8f4ff; }

        .calendar-layout { display:grid; grid-template-columns:320px minmax(0,1fr); gap:18px; align-items:start; }
        .calendar-toolbar { display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:14px; flex-wrap:wrap; }
        .calendar-toolbar h2 { margin:0; }
        .calendar-actions { display:flex; gap:8px; flex-wrap:wrap; }
        .calendar-grid { display:grid; grid-template-columns:repeat(7, minmax(0,1fr)); border:1px solid var(--border); border-radius:14px; overflow:hidden; background:white; }
        .calendar-weekday { padding:10px; background:var(--soft); border-right:1px solid var(--border); border-bottom:1px solid var(--border); color:var(--muted); font-size:.78rem; font-weight:800; text-transform:uppercase; letter-spacing:.04em; }
        .calendar-weekday:nth-child(7n) { border-right:0; }
        .calendar-day { min-height:128px; padding:9px; border-right:1px solid var(--border); border-bottom:1px solid var(--border); background:white; cursor:pointer; display:flex; flex-direction:column; gap:6px; }
        .calendar-day:nth-child(7n) { border-right:0; }
        .calendar-day.outside { background:#f8fafc; color:#9ca3af; }
        .calendar-day.today { box-shadow:inset 0 0 0 2px rgba(0,123,255,.45); }
        .calendar-day:hover { background:#f8fbff; }
        .calendar-day-number { font-size:.82rem; font-weight:800; display:flex; justify-content:space-between; gap:8px; }
        .calendar-event-chip { width:100%; display:block; text-align:left; border:0; border-radius:7px; padding:5px 7px; background:#e8f4ff; color:#004085; font-size:.78rem; font-weight:800; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .calendar-event-chip.family { background:#f3e8ff; color:#4c1d95; }
        .calendar-event-chip:hover { filter:brightness(.96); }
        .calendar-side-list { display:grid; gap:8px; max-height:680px; overflow:auto; padding-right:3px; }
        .calendar-side-card { border:1px solid var(--border); border-radius:12px; padding:10px; background:white; cursor:pointer; }
        .calendar-side-card:hover { border-color:var(--primary); background:#f8fbff; }
        .calendar-side-card strong { display:block; margin-bottom:4px; }
        .calendar-side-card small { display:block; color:var(--muted); line-height:1.35; }
        .calendar-legend { display:flex; flex-wrap:wrap; gap:8px; margin-top:12px; }
        .calendar-legend span { display:inline-flex; align-items:center; gap:6px; color:var(--muted); font-size:.84rem; }
        .calendar-dot { width:10px; height:10px; border-radius:50%; background:#e8f4ff; border:1px solid #b8daff; }
        .calendar-dot.family { background:#f3e8ff; border-color:#d8b4fe; }
        .calendar-dot.recurring { background:#ecfccb; border-color:#bef264; }
        .calendar-dot.reminder { background:#fef3c7; border-color:#f59e0b; }
        .calendar-filter-bar { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:8px; margin:12px 0; }
        .calendar-filter-bar input,.calendar-filter-bar select { width:100%; }
        .calendar-mode-tabs { display:flex; gap:8px; flex-wrap:wrap; margin-top:8px; }
        .calendar-mode-btn.active { background:var(--primary); color:white; border-color:var(--primary); }
        .calendar-event-chip.recurring::before { content:'↻ '; }
        .calendar-event-chip.linked::after { content:' · Todo'; font-weight:700; opacity:.75; }
        .calendar-event-chip.reminder { box-shadow:inset 0 0 0 2px rgba(245,158,11,.2); }
        .calendar-week-grid { display:grid; grid-template-columns:repeat(7,minmax(0,1fr)); gap:10px; }
        .calendar-week-column { min-height:460px; border:1px solid var(--border); border-radius:14px; background:white; padding:9px; display:flex; flex-direction:column; gap:8px; }
        .calendar-week-column.today { box-shadow:inset 0 0 0 2px rgba(0,123,255,.45); }
        .calendar-week-head { font-weight:900; color:var(--text); border-bottom:1px solid var(--border); padding-bottom:8px; }
        .calendar-reminder-list { display:grid; gap:8px; margin-top:8px; }
        .calendar-reminder-card { border:1px solid #fde68a; background:#fffbeb; border-radius:12px; padding:10px; }
        .calendar-reminder-card strong { display:block; margin-bottom:4px; }
        .calendar-meta-pill { display:inline-flex; align-items:center; border-radius:999px; border:1px solid var(--border); padding:3px 8px; font-size:.72rem; color:var(--muted); background:#fff; margin:2px 4px 2px 0; }
        .modal-backdrop { display:none; position:fixed; inset:0; background:rgba(15,23,42,.58); z-index:60; align-items:center; justify-content:center; padding:18px; }
        .modal-backdrop.open { display:flex; }
        .modal-card { width:min(620px, 100%); max-height:calc(100vh - 36px); overflow:auto; background:white; border-radius:18px; box-shadow:0 28px 70px rgba(15,23,42,.35); border:1px solid rgba(255,255,255,.4); }
        .modal-header { display:flex; justify-content:space-between; gap:14px; align-items:flex-start; padding:16px 18px; border-bottom:1px solid var(--border); background:linear-gradient(135deg,#ffffff,#f8fbff); }
        .modal-header h3 { margin:0 0 3px; }
        .modal-body { padding:18px; display:grid; gap:11px; }
        .modal-actions { display:flex; flex-wrap:wrap; justify-content:space-between; gap:10px; padding:0 18px 18px; }
        .modal-actions .right { display:flex; flex-wrap:wrap; gap:10px; margin-left:auto; }
        .close-modal { background:var(--soft); border:1px solid var(--border); color:var(--text); }

        .floating-chat { position:fixed; right:22px; bottom:22px; z-index:40; }
        .floating-chat-toggle { min-width:62px; height:54px; border-radius:999px; display:flex; align-items:center; justify-content:center; gap:8px; box-shadow:0 18px 36px rgba(0,123,255,.28); font-size:1.4rem; }
        .floating-chat-toggle .float-count { min-width:24px; height:24px; display:grid; place-items:center; padding:0 7px; border-radius:999px; background:var(--danger); color:white; font-size:.78rem; font-weight:800; }
        .floating-chat-panel { display:none; position:absolute; right:0; bottom:66px; width:min(410px, calc(100vw - 32px)); height:min(650px, calc(100vh - 100px)); background:white; border:1px solid var(--border); border-radius:18px; box-shadow:0 24px 60px rgba(15,23,42,.24); overflow:hidden; }
        .floating-chat-panel.open { display:grid; grid-template-rows:auto 150px 1fr auto; }
        .floating-chat-header { display:flex; align-items:center; justify-content:space-between; gap:10px; padding:12px 14px; color:white; background:var(--sidebar); }
        .floating-chat-header strong { display:block; }
        .floating-chat-header small { display:block; color:#c2c7d0; }
        .floating-chat-close { background:rgba(255,255,255,.12); border-color:rgba(255,255,255,.25); color:white; }
        .floating-chat-threads { overflow:auto; padding:10px; border-bottom:1px solid var(--border); background:#f8fafc; display:grid; gap:7px; }
        .floating-thread-btn { width:100%; text-align:left; background:white; border:1px solid var(--border); border-radius:10px; padding:9px; color:var(--text); }
        .floating-thread-btn.active { border-color:var(--primary); box-shadow:0 0 0 3px rgba(0,123,255,.10); }
        .floating-thread-btn strong { display:block; }
        .floating-thread-btn small { color:var(--muted); display:block; margin-top:3px; }
        .floating-chat-messages { overflow:auto; padding:12px; background:#f8fafc; display:grid; align-content:start; gap:8px; }
        .floating-chat-form { display:grid; grid-template-columns:1fr auto; gap:8px; padding:10px; border-top:1px solid var(--border); background:white; }
        .floating-chat-form textarea { min-height:44px; resize:vertical; }
        .floating-chat-form .emoji-bar { grid-column:1 / -1; gap:4px; }
        .floating-chat-form .emoji-btn { width:30px; height:30px; font-size:.98rem; }

        .admin-panel { display:none; }
        .admin-panel.visible { display:block; }
        .admin-shell { display:grid; grid-template-columns:230px minmax(0,1fr); gap:16px; margin-top:16px; align-items:start; }
        .admin-menu { position:sticky; top:82px; display:grid; gap:8px; background:white; border:1px solid var(--border); border-radius:12px; padding:10px; }
        .admin-menu-btn { width:100%; display:flex; justify-content:space-between; align-items:center; gap:10px; background:var(--soft); border:1px solid var(--border); color:var(--text); font-weight:bold; text-align:left; }
        .admin-menu-btn:hover { border-color:var(--primary); color:var(--primary); }
        .admin-menu-btn.active { background:var(--primary); border-color:var(--primary); color:white; box-shadow:0 10px 20px rgba(0,123,255,.18); }
        .admin-menu-btn small { min-width:30px; text-align:center; padding:5px 7px; background:white; border:1px solid var(--border); color:var(--muted); }
        .admin-menu-btn.active small { background:rgba(255,255,255,.18); border-color:rgba(255,255,255,.35); color:white; }
        .admin-content { min-width:0; }
        .admin-section { display:none; background:white; border:1px solid var(--border); border-radius:12px; padding:14px; }
        .admin-section.active { display:block; }
        .admin-section-header { display:flex; justify-content:space-between; align-items:flex-start; gap:14px; margin-bottom:12px; }
        .admin-section-header h3 { margin:0 0 4px; font-size:1.05rem; }
        .admin-section-header p { font-size:.86rem; }
        .admin-toolbar { display:flex; flex-wrap:wrap; gap:10px; align-items:center; margin:0 0 12px; }
        .admin-toolbar select, .admin-toolbar input { max-width:280px; }
        .admin-count-pill { flex:0 0 auto; padding:7px 10px; background:#e8f4ff; border:1px solid #b8daff; color:#004085; }
        .table-wrap { overflow-x:auto; background:white; border:1px solid var(--border); border-radius:12px; }
        table { width:100%; border-collapse:collapse; font-size:.88rem; }
        th,td { padding:10px; border-bottom:1px solid var(--border); text-align:left; vertical-align:top; }
        th { background:var(--soft); color:var(--muted); font-size:.78rem; text-transform:uppercase; letter-spacing:.04em; }
        tr:last-child td { border-bottom:0; }
        .inline-actions { display:flex; flex-wrap:wrap; gap:6px; }
        .footer-note { margin-top:22px; font-size:.82rem; color:var(--muted); text-align:center; }
        @media(max-width:1100px){ .small-boxes{grid-template-columns:repeat(2,1fr);} .dashboard-hero,.dashboard-grid,.layout,.message-layout,.todo-board,.chat-page-grid,.calendar-layout{grid-template-columns:1fr;} .todo-form,.todo-edit-form,.todo-dashboard{grid-template-columns:1fr;} }
        @media(max-width:860px){ .app-shell{grid-template-columns:1fr;} .sidebar{position:relative; min-height:auto;} .sidebar-nav{grid-template-columns:repeat(2,1fr);} .main-header{position:static; height:auto; align-items:flex-start; gap:12px; padding:14px; flex-direction:column;} .top-userbox{width:100%; justify-content:space-between;} .content{padding:14px;} .admin-shell{grid-template-columns:1fr;} .admin-menu{position:static; grid-template-columns:repeat(2,1fr);} .admin-section-header{display:block;} .admin-toolbar select,.admin-toolbar input{max-width:none;} .item-form{grid-template-columns:1fr;} }
        @media(max-width:560px){ .sidebar-nav,.small-boxes,.dashboard-card-grid,.stats,.admin-menu{grid-template-columns:1fr;} .panel,.content{padding:12px;} .shopping-item,.todo-item{grid-template-columns:1fr;} .item-actions{width:100%;} .toggle-btn,.delete-btn{flex:1;} .current-title,.section-title-with-badge{align-items:flex-start;} .message-bubble{max-width:100%;} }
    </style>
</head>
<body>
    <div class="app-shell">
        <aside class="sidebar">
            <div class="brand"><span class="brand-icon">⌂</span><span>HaushaltsPilot</span></div>
            <div class="sidebar-user">
                <strong id="sidebarUserLabel">Lade Benutzer...</strong>
                <small id="sidebarFamilyLabel">Haushalt: wird geladen</small>
            </div>
            <nav class="sidebar-nav" aria-label="Hauptnavigation">
                <button class="app-nav-btn active" type="button" data-view="dashboard"><span class="nav-left"><span class="nav-icon">▦</span><span>Dashboard</span></span></button>
                <button class="app-nav-btn" type="button" data-view="lists"><span class="nav-left"><span class="nav-icon">🛒</span><span>Listen</span></span><span class="nav-badge empty" id="navListBadge">0</span></button>
                <button class="app-nav-btn" type="button" data-view="todos"><span class="nav-left"><span class="nav-icon">✓</span><span>Todos</span></span><span class="nav-badge empty" id="navTodoBadge">0</span></button>
                <button class="app-nav-btn" type="button" data-view="messages"><span class="nav-left"><span class="nav-icon">✉</span><span>Nachrichten</span></span><span class="nav-badge empty" id="navUnreadBadge">0</span></button>
                <button class="app-nav-btn" type="button" data-view="chats"><span class="nav-left"><span class="nav-icon">💬</span><span>Chats</span></span><span class="nav-badge empty" id="navChatBadge">0</span></button>
                <button class="app-nav-btn" type="button" data-view="calendar"><span class="nav-left"><span class="nav-icon">📅</span><span>Kalender</span></span><span class="nav-badge empty" id="navCalendarBadge">0</span></button>
                <button class="app-nav-btn" type="button" data-view="familychat"><span class="nav-left"><span class="nav-icon">👨‍👩‍👧‍👦</span><span>Familienchat</span></span><span class="nav-badge empty" id="navFamilyChatBadge">0</span></button>
                <button class="app-nav-btn admin-only" id="navAdminItem" type="button" data-view="admin"><span class="nav-left"><span class="nav-icon">⚙</span><span>Administration</span></span><span class="nav-badge empty" id="navAdminBadge">0</span></button>
            </nav>
            <div class="sidebar-footer">Teil 16 · Kalender vertieft: Wiederholungen, Erinnerungen, Todos, Filter und Wochenansicht.</div>
        </aside>

        <div class="main-shell">
            <header class="main-header">
                <div class="page-heading">
                    <h1 id="topbarPageTitle">Dashboard</h1>
                    <small id="topbarPageSubtitle">Übersicht über Listen, Todos, Nachrichten und Administration</small>
                </div>
                <div class="top-userbox">
                    <div>
                        <strong id="currentUserLabel">Lade Benutzer...</strong>
                        <span id="currentFamilyLabel">Haushalt: wird geladen</span>
                    </div>
                    <form id="logoutForm" method="post" action="auth.php" autocomplete="off">
                        <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                        <input type="hidden" name="action" value="logout">
                        <button class="logout" id="logoutButton" type="submit">Logout</button>
                    </form>
                </div>
            </header>

            <main class="content">
                <section class="module-page active" id="dashboardPage">
                    <div class="dashboard-hero">
                        <section class="hero-card">
                            <h2>HaushaltsPilot</h2>
                            <p>Teil 16 vertieft den Kalender mit Wiederholungen, Erinnerungen, Todo-Verknüpfung, Filtern und Wochenansicht.</p>
                            <div class="hero-actions">
                                <button class="dashboard-action" type="button" data-view="lists">Listen öffnen</button>
                                <button class="dashboard-action" type="button" data-view="todos">Todos öffnen</button>
                                <button class="dashboard-action" type="button" data-view="messages">Nachrichten öffnen</button>
                                <button class="dashboard-action" type="button" data-view="chats">Chats öffnen</button>
                                <button class="dashboard-action" type="button" data-view="calendar">Kalender öffnen</button>
                                <button class="dashboard-action" type="button" data-view="familychat">Familienchat öffnen</button>
                                <button class="dashboard-action admin-only" id="dashboardAdminAction" type="button" data-view="admin">Administration öffnen</button>
                            </div>
                        </section>
                        <section class="status-card">
                            <strong>Systemstatus</strong>
                            <p>Die Sicherheitsbasis bleibt erhalten: Sessions, CSRF, Rollenprüfung, serverseitige Validierung, sichere Textausgabe und PDO Prepared Statements bei SQLite/MySQL.</p>
                            <div class="status-list">
                                <div class="status-row"><span>Aktueller Benutzer</span><strong id="dashCurrentUser">–</strong></div>
                                <div class="status-row"><span>Haushalt</span><strong id="dashCurrentFamily">–</strong></div>
                                <div class="status-row"><span>Rolle</span><strong id="dashCurrentRole">–</strong></div>
                            </div>
                        </section>
                    </div>

                    <section class="small-boxes" aria-label="Kennzahlen">
                        <div class="small-box box-blue"><strong id="dashListCount">0</strong><span>Listen</span><small id="dashListDetail">0 Artikel insgesamt</small><span class="big-icon">🛒</span></div>
                        <div class="small-box box-green"><strong id="dashTodoOpenCount">0</strong><span>Offene Todos</span><small id="dashTodoDetail">0 erledigt</small><span class="big-icon">✓</span></div>
                        <div class="small-box box-yellow"><strong id="dashUnreadCount">0</strong><span>Ungelesene Nachrichten</span><small id="dashMessageDetail">Messages · Chats · Familie</small><span class="big-icon">✉</span></div>
                        <div class="small-box box-cyan"><strong id="dashCalendarCount">0</strong><span>Termine</span><small id="dashCalendarDetail">eigene und Familie</small><span class="big-icon">📅</span></div>
                        <div class="small-box box-red"><strong id="dashAdminCount">0</strong><span>Admin-Objekte</span><small id="dashAdminDetail">nur für Admins relevant</small><span class="big-icon">⚙</span></div>
                    </section>

                    <section class="dashboard-grid">
                        <div class="panel">
                            <h2>Modulübersicht</h2>
                            <div class="dashboard-card-grid">
                                <article class="dashboard-card"><strong>Einkaufs- und Haushaltslisten</strong><span id="dashListSummary">Noch keine Daten geladen.</span></article>
                                <article class="dashboard-card"><strong>Aufgaben</strong><span id="dashTodoSummary">Noch keine Daten geladen.</span></article>
                                <article class="dashboard-card"><strong>Nachrichten</strong><span id="dashMessageSummary">Noch keine Daten geladen.</span></article>
                                <article class="dashboard-card"><strong>Kalender</strong><span id="dashCalendarSummary">Noch keine Daten geladen.</span></article>
                            </div>
                        </div>
                        <div class="panel">
                            <h2>Schnellzugriff</h2>
                            <div class="status-list">
                                <button class="dashboard-action box-blue" type="button" data-view="lists">Listen verwalten</button>
                                <button class="dashboard-action box-green" type="button" data-view="todos">Aufgaben bearbeiten</button>
                                <button class="dashboard-action box-cyan" type="button" data-view="messages">Private Nachrichten</button>
                                <button class="dashboard-action box-purple" type="button" data-view="chats">1:1-Chats</button>
                                <button class="dashboard-action box-cyan" type="button" data-view="calendar">Kalender öffnen</button>
                                <button class="dashboard-action box-purple admin-only" id="dashboardAdminQuick" type="button" data-view="admin">Adminbereich</button>
                            </div>
                        </div>
                    </section>
                </section>

                <section class="module-page" id="listsPage">
                    <div class="note">Listen bleiben der fachliche Ursprung des Projekts. Die Ansicht ist jetzt ein eigenes Modul statt Teil einer langen Gesamtseite.</div>
                    <section class="layout">
                        <aside class="panel">
                            <h2>Listen</h2>
                            <form class="form" id="listForm">
                                <input type="text" id="listNameInput" placeholder="Neue Liste, z. B. Einkauf" autocomplete="off" maxlength="40">
                                <select id="listTypeInput" aria-label="Listentyp wählen">
                                    <option value="shopping">Einkaufsliste</option>
                                    <option value="household">Haushaltsliste</option>
                                    <option value="other">Sonstige Liste</option>
                                </select>
                                <label class="scope-row"><input type="checkbox" id="listSharedInput"> als Haushaltsliste freigeben</label>
                                <div class="admin-only" id="adminCreateOwnerBox"><select id="listOwnerInput" aria-label="Besitzer wählen"></select></div>
                                <button class="add-btn" type="submit">Liste erstellen</button>
                            </form>
                            <div class="list-group"><div class="list-group-title"><span>Meine Listen</span><small id="myListCount">0</small></div><div class="list-buttons" id="myListButtons"></div></div>
                            <div class="list-group"><div class="list-group-title"><span>Gemeinsame Einkaufslisten</span><small id="sharedShoppingListCount">0</small></div><div class="list-buttons" id="sharedShoppingListButtons"></div></div>
                            <div class="list-group"><div class="list-group-title"><span>Gemeinsame Haushaltslisten</span><small id="sharedHouseholdListCount">0</small></div><div class="list-buttons" id="sharedHouseholdListButtons"></div></div>
                            <div class="list-group"><div class="list-group-title"><span>Sonstige Gemeinschaftslisten</span><small id="sharedOtherListCount">0</small></div><div class="list-buttons" id="sharedOtherListButtons"></div></div>
                            <div class="list-group admin-only" id="adminOtherListsGroup"><div class="list-group-title"><span>Andere Nutzerlisten</span><small id="otherListCount">0</small></div><div class="list-buttons" id="otherListButtons"></div></div>
                        </aside>

                        <section class="panel">
                            <div class="current-title"><h2 id="currentListTitle">Aktive Liste</h2><span id="currentListInfo">0 Artikel</span></div>
                            <div class="list-settings" id="listSettings">
                                <div class="settings-header"><div><strong id="listSettingsTitle">Listeneinstellungen</strong><span id="listSettingsInfo">Besitzer / Haushalt / Sichtbarkeit</span></div></div>
                                <div class="settings-actions">
                                    <button class="small-btn" id="toggleVisibilityButton" type="button">Sichtbarkeit ändern</button>
                                    <button class="danger" id="deleteListButton" type="button">Liste löschen</button>
                                </div>
                                <div class="inline-field" id="listTypeRow">
                                    <select id="activeListTypeSelect" aria-label="Listentyp ändern"><option value="shopping">Einkaufsliste</option><option value="household">Haushaltsliste</option><option value="other">Sonstige Liste</option></select>
                                    <button class="small-btn" id="updateListTypeButton" type="button">Typ speichern</button>
                                </div>
                                <div class="owner-select-row" id="ownerSelectRow"><select id="activeListOwnerSelect"></select><button class="small-btn" id="updateOwnerButton" type="button">Besitzer ändern</button></div>
                            </div>
                            <form class="item-form" id="itemForm">
                                <input type="text" id="itemNameInput" placeholder="Artikel, z. B. Milch" autocomplete="off" maxlength="80">
                                <input type="text" id="itemAmountInput" placeholder="Menge, z. B. 2 Liter" autocomplete="off" maxlength="40">
                                <select id="itemCategoryInput"><option value="Lebensmittel">Lebensmittel</option><option value="Getränke">Getränke</option><option value="Haushalt">Haushalt</option><option value="Drogerie">Drogerie</option><option value="Schule">Schule</option><option value="Medikamente">Medikamente</option><option value="Sonstiges">Sonstiges</option></select>
                                <button class="add-btn" type="submit">Hinzufügen</button>
                            </form>
                            <div class="status-message" id="statusMessage"></div>
                            <section class="stats"><div class="stat-card"><span>Gesamt</span><strong id="totalCount">0</strong></div><div class="stat-card"><span>Offen</span><strong id="openCount">0</strong></div><div class="stat-card"><span>Erledigt</span><strong id="doneCount">0</strong></div></section>
                            <ul class="shopping-list" id="shoppingList"></ul>
                            <div class="empty-message" id="emptyMessage">Diese Liste ist leer.</div>
                        </section>
                    </section>
                </section>

                <section class="module-page panel todo-panel" id="todosPage">
                    <h2>Todos</h2>
                    <p>Aufgaben sind seit Teil 11 ein vertieftes Modul: privat oder gemeinsam, mit Priorität, Zuständigkeit, Kommentaren, Fälligkeit, Erinnerung und Kalenderbezug.</p>
                    <form class="todo-form" id="todoForm">
                        <input type="text" id="todoTitleInput" placeholder="Neue Aufgabe, z. B. Müll rausbringen" autocomplete="off" maxlength="120">
                        <input type="date" id="todoDueInput" aria-label="Fälligkeitsdatum">
                        <select id="todoPriorityInput" aria-label="Priorität wählen"><option value="low">Niedrig</option><option value="normal" selected>Normal</option><option value="high">Hoch</option><option value="urgent">Dringend</option></select>
                        <select id="todoAssignedInput" aria-label="Zuständige Person"></select>
                        <label class="scope-row"><input type="checkbox" id="todoFamilyInput"> Familienaufgabe</label>
                        <input type="date" id="todoReminderInput" aria-label="Erinnerungsdatum">
                        <input type="date" id="todoCalendarInput" aria-label="Kalenderdatum">
                        <button class="add-btn" type="submit">Aufgabe erstellen</button>
                    </form>
                    <section class="stats"><div class="stat-card"><span>Aufgaben</span><strong id="todoTotalCount">0</strong></div><div class="stat-card"><span>Offen</span><strong id="todoOpenCount">0</strong></div><div class="stat-card"><span>Erledigt</span><strong id="todoDoneCount">0</strong></div></section>
                    <section class="todo-dashboard" id="todoDashboard"></section>
                    <div class="todo-board"><div class="todo-column"><h3>Meine Aufgaben</h3><ul class="todo-list" id="privateTodoList"></ul><div class="empty-message" id="privateTodoEmpty">Keine privaten Aufgaben vorhanden.</div></div><div class="todo-column"><h3>Familienaufgaben</h3><ul class="todo-list" id="familyTodoList"></ul><div class="empty-message" id="familyTodoEmpty">Keine Familienaufgaben vorhanden.</div></div></div>
                </section>

                <section class="module-page panel message-panel" id="messagesPage">
                    <div class="section-title-with-badge"><h2>Personal Messages</h2><span class="unread-badge empty" id="unreadBadge">0</span></div>
                    <p>Teil 12 bleibt als Personal-Message-Modul erhalten, Teil 13 als direkter 1:1-Chat. Teil 14 ergänzt zusätzlich den gemeinsamen Familienchat pro Haushalt.</p>
                    <form class="message-form" id="messageForm">
                        <select id="messageRecipientInput" aria-label="Empfänger wählen"></select>
                        <textarea id="messageBodyInput" maxlength="1200" placeholder="Private Nachricht schreiben ..."></textarea>
                        <button class="add-btn" type="submit">Senden</button>
                    </form>
                    <div class="message-layout">
                        <div><h3>Verläufe <span id="messageBadgeText"></span></h3><div class="thread-list" id="threadList"></div><div class="empty-message" id="threadEmpty">Noch keine privaten Nachrichten vorhanden.</div></div>
                        <div class="message-view" id="messageView"><h3 id="activeThreadTitle">Kein Verlauf ausgewählt</h3><div class="message-list" id="messageList"></div><form class="reply-form" id="replyForm"><textarea id="replyInput" maxlength="1200" placeholder="Antwort schreiben ..."></textarea><button class="small-btn" type="submit">Antworten</button></form></div>
                    </div>
                </section>


                <section class="module-page panel chat-panel" id="chatsPage">
                    <div class="section-title-with-badge"><h2>Einzelchat</h2><span class="unread-badge empty" id="chatUnreadBadge">0</span></div>
                    <p>Teil 13 lieferte den direkten 1:1-Chat. In Teil 14 bleibt dieser Bereich bewusst getrennt vom Familienchat, damit private und gruppenbezogene Kommunikation nicht vermischt werden.</p>
                    <div class="chat-page-grid">
                        <aside class="chat-directory">
                            <form class="chat-contact-form" id="chatStartForm">
                                <label for="chatRecipientInput"><strong>Neuen Chat starten</strong></label>
                                <select id="chatRecipientInput" aria-label="Chatpartner wählen"></select>
                                <textarea id="chatStartInput" maxlength="1200" placeholder="Erste Chatnachricht schreiben ..."></textarea>
                                <div class="emoji-bar" id="chatStartEmojiBar" aria-label="Emoji-Auswahl für neue Chatnachricht"></div>
                                <button class="add-btn" type="submit">Chat starten</button>
                            </form>
                            <h3>Meine Chats <span id="chatListBadgeText"></span></h3>
                            <div class="chat-thread-list" id="chatThreadList"></div>
                            <div class="empty-message" id="chatThreadEmpty">Noch keine 1:1-Chats vorhanden.</div>
                        </aside>
                        <section class="message-view chat-main">
                            <header class="chat-main-header">
                                <div><h3 id="chatActiveTitle">Kein Chat ausgewählt</h3><small id="chatActiveMeta">Wähle links einen Chat oder starte einen neuen Verlauf.</small></div>
                                <button class="small-btn" type="button" id="chatOpenFloatingButton">Als Mini-Chat öffnen</button>
                            </header>
                            <div class="chat-main-body" id="chatMessageList"></div>
                            <form class="chat-composer" id="chatReplyForm">
                                <div class="emoji-bar" id="chatReplyEmojiBar" aria-label="Emoji-Auswahl für Chatantwort"></div>
                                <textarea id="chatReplyInput" maxlength="1200" placeholder="Chatnachricht schreiben ..."></textarea>
                                <button class="small-btn" type="submit">Senden</button>
                            </form>
                        </section>
                    </div>
                </section>


                <section class="module-page" id="calendarPage">
                    <div class="note">Teil 16 vertieft den Kalender: Wiederholungstermine, einfache Erinnerungen, Todo-Verknüpfung, Filter und eine eigene Wochenansicht im FullCalendar-Stil.</div>
                    <section class="calendar-layout">
                        <aside class="panel">
                            <h2>Termin erstellen</h2>
                            <form class="form" id="calendarForm">
                                <input id="calendarTitleInput" maxlength="120" placeholder="Titel, z. B. Arzttermin">
                                <select id="calendarScopeInput" aria-label="Terminbereich wählen">
                                    <option value="private">Eigener Termin</option>
                                    <option value="family">Familientermin</option>
                                </select>
                                <label>Von <input id="calendarStartInput" type="datetime-local"></label>
                                <label>Bis <input id="calendarEndInput" type="datetime-local"></label>
                                <input id="calendarLocationInput" maxlength="120" placeholder="Ort, z. B. Zuhause, Schule, Praxis">
                                <select id="calendarTodoInput" aria-label="Todo mit Termin verknüpfen"></select>
                                <select id="calendarRecurrenceInput" aria-label="Wiederholung wählen">
                                    <option value="none">Keine Wiederholung</option>
                                    <option value="daily">Täglich</option>
                                    <option value="weekly">Wöchentlich</option>
                                    <option value="monthly">Monatlich</option>
                                </select>
                                <label>Wiederholen bis <input id="calendarRecurrenceUntilInput" type="date"></label>
                                <label>Erinnerung <input id="calendarReminderInput" type="datetime-local"></label>
                                <textarea id="calendarDescriptionInput" maxlength="1200" placeholder="Beschreibung"></textarea>
                                <button class="add-btn" type="submit">Termin eintragen</button>
                            </form>
                            <div class="calendar-legend">
                                <span><i class="calendar-dot"></i> eigener Termin</span>
                                <span><i class="calendar-dot family"></i> Familientermin</span>
                                <span><i class="calendar-dot recurring"></i> Wiederholung</span>
                                <span><i class="calendar-dot reminder"></i> Erinnerung</span>
                            </div>
                            <div class="calendar-filter-bar">
                                <select id="calendarScopeFilter" aria-label="Kalenderbereich filtern">
                                    <option value="all">Alle Bereiche</option>
                                    <option value="private">Nur eigene Termine</option>
                                    <option value="family">Nur Familientermine</option>
                                </select>
                                <select id="calendarSpecialFilter" aria-label="Kalendereigenschaften filtern">
                                    <option value="all">Alle Termine</option>
                                    <option value="recurring">Nur Wiederholungen</option>
                                    <option value="reminders">Nur Erinnerungen</option>
                                    <option value="linked">Nur Todo-Verknüpfungen</option>
                                </select>
                                <input id="calendarSearchFilter" placeholder="Titel, Ort, Beschreibung suchen">
                            </div>
                            <h3 style="margin-top:18px">Erinnerungen</h3>
                            <div class="calendar-reminder-list" id="calendarReminderList"></div>
                            <div class="empty-message" id="calendarReminderEmpty">Keine fälligen Erinnerungen vorhanden.</div>
                            <h3 style="margin-top:18px">Anstehende Termine</h3>
                            <div class="calendar-side-list" id="calendarUpcomingList"></div>
                            <div class="empty-message" id="calendarUpcomingEmpty">Keine anstehenden Termine vorhanden.</div>
                        </aside>
                        <section class="panel">
                            <div class="calendar-toolbar">
                                <div>
                                    <h2 id="calendarMonthLabel">Kalender</h2>
                                    <p>Monatsansicht mit eigenen Terminen und gemeinsamen Familienterminen.</p>
                                </div>
                                <div class="calendar-actions">
                                    <button class="small-btn" type="button" id="calendarPrevMonth">‹ Zurück</button>
                                    <button class="small-btn" type="button" id="calendarTodayButton">Heute</button>
                                    <button class="small-btn" type="button" id="calendarNextMonth">Weiter ›</button>
                                </div>
                                <div class="calendar-mode-tabs">
                                    <button class="small-btn calendar-mode-btn active" type="button" id="calendarMonthMode">Monat</button>
                                    <button class="small-btn calendar-mode-btn" type="button" id="calendarWeekMode">Woche</button>
                                </div>
                            </div>
                            <div class="calendar-grid" id="calendarGrid" aria-label="Kalender Monatsansicht"></div>
                        </section>
                    </section>
                </section>

                <section class="module-page panel chat-panel" id="familyChatPage">
                    <div class="section-title-with-badge"><h2>Familienchat</h2><span class="unread-badge empty" id="familyChatUnreadBadge">0</span></div>
                    <p>Teil 14 ergänzt einen gemeinsamen Gruppenchat pro Haushalt. Die Berechtigung entsteht nicht über manuelle Empfängerlisten, sondern über die Haushaltszuordnung der aktiven Nutzer.</p>
                    <div class="chat-page-grid">
                        <aside class="chat-directory">
                            <h3>Meine Familienchats <span id="familyChatListBadgeText"></span></h3>
                            <p>Pro Haushalt gibt es genau einen Familienchat. Neue Mitglieder des Haushalts werden automatisch als Teilnehmer berücksichtigt.</p>
                            <div class="chat-thread-list" id="familyChatThreadList"></div>
                            <div class="empty-message" id="familyChatThreadEmpty">Noch kein Familienchat verfügbar. Dein Haushalt benötigt mindestens zwei aktive Mitglieder.</div>
                        </aside>
                        <section class="message-view chat-main">
                            <header class="chat-main-header">
                                <div><h3 id="familyChatActiveTitle">Kein Familienchat ausgewählt</h3><small id="familyChatActiveMeta">Wähle links den Familienchat deines Haushalts.</small></div>
                            </header>
                            <div class="chat-main-body" id="familyChatMessageList"></div>
                            <form class="chat-composer" id="familyChatReplyForm">
                                <div class="emoji-bar" id="familyChatEmojiBar" aria-label="Emoji-Auswahl für Familienchat"></div>
                                <textarea id="familyChatReplyInput" maxlength="1200" placeholder="Nachricht an den Haushalt schreiben ..."></textarea>
                                <button class="small-btn" type="submit">An Familie senden</button>
                            </form>
                        </section>
                    </div>
                </section>


                <section class="module-page panel admin-panel" id="adminPanel">
                    <h2>Administration</h2>
                    <p>Die Verwaltung ist jetzt ein eigener Arbeitsbereich im Dashboard-Stil: links ein festes Admin-Menü, rechts genau ein aktiver Verwaltungsbereich.</p>
                    <div class="admin-shell">
                        <nav class="admin-menu" role="tablist" aria-label="Adminbereiche">
                            <button class="admin-menu-btn active" type="button" data-admin-tab="families" aria-controls="adminTabFamilies"><span>Haushalte</span><small id="adminFamiliesCount">0</small></button>
                            <button class="admin-menu-btn" type="button" data-admin-tab="users" aria-controls="adminTabUsers"><span>Benutzer</span><small id="adminUsersCount">0</small></button>
                            <button class="admin-menu-btn" type="button" data-admin-tab="lists" aria-controls="adminTabLists"><span>Listen</span><small id="adminListsCount">0</small></button>
                            <button class="admin-menu-btn" type="button" data-admin-tab="todos" aria-controls="adminTabTodos"><span>Todos</span><small id="adminTodosCount">0</small></button>
                            <button class="admin-menu-btn" type="button" data-admin-tab="messages" aria-controls="adminTabMessages"><span>Nachrichten</span><small id="adminMessagesCount">0</small></button>
                        </nav>
                        <div class="admin-content">
                            <section class="admin-section active" id="adminTabFamilies" role="tabpanel"><div class="admin-section-header"><div><h3>Haushalte</h3><p>Haushaltsgruppen anlegen, prüfen und verwalten.</p></div><span class="admin-count-pill" id="adminFamiliesPill">0 Haushalte</span></div><form class="form" id="familyForm"><input id="familyNameInput" maxlength="60" placeholder="Neuer Haushalt, z. B. Familie Müller"><button class="add-btn" type="submit">Haushalt erstellen</button></form><div class="table-wrap"><table><thead><tr><th>Haushalt</th><th>Mitglieder</th><th>Listen</th><th>Aktionen</th></tr></thead><tbody id="familyTableBody"></tbody></table></div></section>
                            <section class="admin-section" id="adminTabUsers" role="tabpanel"><div class="admin-section-header"><div><h3>Benutzer</h3><p>Rollen, Aktivstatus und Haushaltszuordnung verwalten.</p></div><span class="admin-count-pill" id="adminUsersPill">0 Benutzer</span></div><div class="table-wrap"><table><thead><tr><th>Benutzer</th><th>Rolle</th><th>Status</th><th>Haushalt</th><th>Aktionen</th></tr></thead><tbody id="userTableBody"></tbody></table></div></section>
                            <section class="admin-section" id="adminTabLists" role="tabpanel"><div class="admin-section-header"><div><h3>Listenübersicht</h3><p>Listen nach Typ, Besitzer und Sichtbarkeit prüfen.</p></div><span class="admin-count-pill" id="adminListsPill">0 Listen</span></div><div class="admin-toolbar"><select id="adminListFilter"><option value="all">Alle Listen</option><option value="own">Meine Listen</option><option value="shared">Gemeinschaftslisten</option><option value="shopping">Einkaufslisten</option><option value="household">Haushaltslisten</option><option value="private">Private Listen</option></select></div><div class="table-wrap"><table><thead><tr><th>Liste</th><th>Typ</th><th>Besitzer</th><th>Haushalt</th><th>Status</th><th>Aktionen</th></tr></thead><tbody id="adminListTableBody"></tbody></table></div></section>
                            <section class="admin-section" id="adminTabTodos" role="tabpanel"><div class="admin-section-header"><div><h3>Todo-Übersicht</h3><p>Private Aufgaben, Familienaufgaben, Prioritäten, Zuweisungen und Kommentare kontrollieren.</p></div><span class="admin-count-pill" id="adminTodosPill">0 Todos</span></div><div class="admin-toolbar"><select id="adminTodoFilter"><option value="all">Alle Aufgaben</option><option value="private">Private Aufgaben</option><option value="family">Familienaufgaben</option><option value="open">Offen</option><option value="done">Erledigt</option></select></div><div class="table-wrap"><table><thead><tr><th>Aufgabe</th><th>Bereich</th><th>Priorität</th><th>Zuweisung</th><th>Fällig</th><th>Erinnerung</th><th>Status</th><th>Kommentare</th><th>Aktionen</th></tr></thead><tbody id="adminTodoTableBody"></tbody></table></div></section>
                            <section class="admin-section" id="adminTabMessages" role="tabpanel"><div class="admin-section-header"><div><h3>Nachrichtenübersicht</h3><p>Private Nachrichten getrennt vom Listen- und Todo-Bereich prüfen.</p></div><span class="admin-count-pill" id="adminMessagesPill">0 Nachrichten</span></div><div class="admin-toolbar"><select id="adminMessageFilter"><option value="all">Alle Nachrichten</option><option value="own">Von mir gesendet</option><option value="received">An mich gesendet</option></select></div><div class="table-wrap"><table><thead><tr><th>Zeit</th><th>Von</th><th>An</th><th>Nachricht</th><th>Aktionen</th></tr></thead><tbody id="adminMessageTableBody"></tbody></table></div></section>
                        </div>
                    </div>
                </section>

                <p class="footer-note">Teil 16 erweitert HaushaltsPilot um einen vertieften Kalender mit Wiederholungen, Erinnerungen, Todo-Verknüpfung, Filtern und Wochenansicht.</p>
            </main>
        </div>
    </div>



    <div class="modal-backdrop" id="calendarModal" aria-hidden="true">
        <section class="modal-card" role="dialog" aria-modal="true" aria-labelledby="calendarModalTitle">
            <header class="modal-header">
                <div>
                    <h3 id="calendarModalTitle">Termin bearbeiten</h3>
                    <p id="calendarModalMeta">Persönlicher Termin oder Familientermin</p>
                </div>
                <button class="close-modal" id="calendarModalClose" type="button">×</button>
            </header>
            <div class="modal-body">
                <input id="calendarModalTitleInput" maxlength="120" placeholder="Titel">
                <select id="calendarModalScopeInput">
                    <option value="private">Eigener Termin</option>
                    <option value="family">Familientermin</option>
                </select>
                <label>Von <input id="calendarModalStartInput" type="datetime-local"></label>
                <label>Bis <input id="calendarModalEndInput" type="datetime-local"></label>
                <input id="calendarModalLocationInput" maxlength="120" placeholder="Ort">
                <select id="calendarModalTodoInput" aria-label="Todo verknüpfen"></select>
                <select id="calendarModalRecurrenceInput" aria-label="Wiederholung wählen">
                    <option value="none">Keine Wiederholung</option>
                    <option value="daily">Täglich</option>
                    <option value="weekly">Wöchentlich</option>
                    <option value="monthly">Monatlich</option>
                </select>
                <label>Wiederholen bis <input id="calendarModalRecurrenceUntilInput" type="date"></label>
                <label>Erinnerung <input id="calendarModalReminderInput" type="datetime-local"></label>
                <textarea id="calendarModalDescriptionInput" maxlength="1200" placeholder="Beschreibung"></textarea>
            </div>
            <div class="modal-actions">
                <button class="danger" id="calendarModalDelete" type="button">Termin löschen</button>
                <div class="right">
                    <button class="close-modal" id="calendarModalCancel" type="button">Abbrechen</button>
                    <button class="add-btn" id="calendarModalSave" type="button">Speichern</button>
                </div>
            </div>
        </section>
    </div>

    <div class="floating-chat" aria-live="polite">
        <button class="add-btn floating-chat-toggle" id="floatingChatToggle" type="button" aria-expanded="false" aria-controls="floatingChatPanel"><span>💬</span><span class="float-count" id="floatingChatCount">0</span></button>
        <section class="floating-chat-panel" id="floatingChatPanel" aria-label="Schnellchat">
            <header class="floating-chat-header">
                <div><strong id="floatingChatTitle">Schnellchat</strong><small id="floatingChatSubtitle">Meine 1:1-Chats</small></div>
                <button class="floating-chat-close" id="floatingChatClose" type="button">×</button>
            </header>
            <div class="floating-chat-threads" id="floatingChatThreads"></div>
            <div class="floating-chat-messages" id="floatingChatMessages"></div>
            <form class="floating-chat-form" id="floatingChatForm">
                <div class="emoji-bar" id="floatingEmojiBar" aria-label="Emoji-Auswahl für Schnellchat"></div>
                <textarea id="floatingChatInput" maxlength="1200" placeholder="Schnellantwort ..."></textarea>
                <button class="small-btn" type="submit">Senden</button>
            </form>
        </section>
    </div>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const byId = (id) => document.getElementById(id);
        const appNavButtons = document.querySelectorAll('[data-view]');
        const modulePages = { dashboard:byId('dashboardPage'), lists:byId('listsPage'), todos:byId('todosPage'), messages:byId('messagesPage'), chats:byId('chatsPage'), calendar:byId('calendarPage'), familychat:byId('familyChatPage'), admin:byId('adminPanel') };
        const topbarPageTitle = byId('topbarPageTitle');
        const topbarPageSubtitle = byId('topbarPageSubtitle');
        const sidebarUserLabel = byId('sidebarUserLabel');
        const sidebarFamilyLabel = byId('sidebarFamilyLabel');
        const navListBadge = byId('navListBadge');
        const navTodoBadge = byId('navTodoBadge');
        const navUnreadBadge = byId('navUnreadBadge');
        const navChatBadge = byId('navChatBadge');
        const navCalendarBadge = byId('navCalendarBadge');
        const navFamilyChatBadge = byId('navFamilyChatBadge');
        const navAdminBadge = byId('navAdminBadge');
        const navAdminItem = byId('navAdminItem');
        const dashboardAdminAction = byId('dashboardAdminAction');
        const dashboardAdminQuick = byId('dashboardAdminQuick');
        const dashCurrentUser = byId('dashCurrentUser');
        const dashCurrentFamily = byId('dashCurrentFamily');
        const dashCurrentRole = byId('dashCurrentRole');
        const dashListCount = byId('dashListCount');
        const dashListDetail = byId('dashListDetail');
        const dashTodoOpenCount = byId('dashTodoOpenCount');
        const dashTodoDetail = byId('dashTodoDetail');
        const dashUnreadCount = byId('dashUnreadCount');
        const dashCalendarCount = byId('dashCalendarCount');
        const dashCalendarDetail = byId('dashCalendarDetail');
        const dashMessageDetail = byId('dashMessageDetail');
        const dashAdminCount = byId('dashAdminCount');
        const dashAdminDetail = byId('dashAdminDetail');
        const dashListSummary = byId('dashListSummary');
        const dashTodoSummary = byId('dashTodoSummary');
        const dashMessageSummary = byId('dashMessageSummary');
        const dashCalendarSummary = byId('dashCalendarSummary');

        const currentUserLabel = byId('currentUserLabel');
        const currentFamilyLabel = byId('currentFamilyLabel');
        const logoutForm = byId('logoutForm');
        const logoutButton = byId('logoutButton');
        const listForm = byId('listForm');
        const listNameInput = byId('listNameInput');
        const listTypeInput = byId('listTypeInput');
        const listSharedInput = byId('listSharedInput');
        const listOwnerInput = byId('listOwnerInput');
        const adminCreateOwnerBox = byId('adminCreateOwnerBox');
        const myListButtons = byId('myListButtons');
        const sharedShoppingListButtons = byId('sharedShoppingListButtons');
        const sharedHouseholdListButtons = byId('sharedHouseholdListButtons');
        const sharedOtherListButtons = byId('sharedOtherListButtons');
        const otherListButtons = byId('otherListButtons');
        const adminOtherListsGroup = byId('adminOtherListsGroup');
        const myListCount = byId('myListCount');
        const sharedShoppingListCount = byId('sharedShoppingListCount');
        const sharedHouseholdListCount = byId('sharedHouseholdListCount');
        const sharedOtherListCount = byId('sharedOtherListCount');
        const otherListCount = byId('otherListCount');
        const currentListTitle = byId('currentListTitle');
        const currentListInfo = byId('currentListInfo');
        const listSettings = byId('listSettings');
        const listSettingsTitle = byId('listSettingsTitle');
        const listSettingsInfo = byId('listSettingsInfo');
        const toggleVisibilityButton = byId('toggleVisibilityButton');
        const activeListTypeSelect = byId('activeListTypeSelect');
        const updateListTypeButton = byId('updateListTypeButton');
        const deleteListButton = byId('deleteListButton');
        const ownerSelectRow = byId('ownerSelectRow');
        const activeListOwnerSelect = byId('activeListOwnerSelect');
        const updateOwnerButton = byId('updateOwnerButton');
        const itemForm = byId('itemForm');
        const itemNameInput = byId('itemNameInput');
        const itemAmountInput = byId('itemAmountInput');
        const itemCategoryInput = byId('itemCategoryInput');
        const statusMessage = byId('statusMessage');
        const totalCount = byId('totalCount');
        const openCount = byId('openCount');
        const doneCount = byId('doneCount');
        const shoppingList = byId('shoppingList');
        const emptyMessage = byId('emptyMessage');
        const todoForm = byId('todoForm');
        const todoTitleInput = byId('todoTitleInput');
        const todoDueInput = byId('todoDueInput');
        const todoPriorityInput = byId('todoPriorityInput');
        const todoAssignedInput = byId('todoAssignedInput');
        const todoFamilyInput = byId('todoFamilyInput');
        const todoReminderInput = byId('todoReminderInput');
        const todoCalendarInput = byId('todoCalendarInput');
        const privateTodoList = byId('privateTodoList');
        const familyTodoList = byId('familyTodoList');
        const privateTodoEmpty = byId('privateTodoEmpty');
        const familyTodoEmpty = byId('familyTodoEmpty');
        const todoTotalCount = byId('todoTotalCount');
        const todoOpenCount = byId('todoOpenCount');
        const todoDoneCount = byId('todoDoneCount');
        const todoDashboard = byId('todoDashboard');
        const messageForm = byId('messageForm');
        const messageRecipientInput = byId('messageRecipientInput');
        const messageBodyInput = byId('messageBodyInput');
        const unreadBadge = byId('unreadBadge');
        const messageBadgeText = byId('messageBadgeText');
        const threadList = byId('threadList');
        const threadEmpty = byId('threadEmpty');
        const activeThreadTitle = byId('activeThreadTitle');
        const messageList = byId('messageList');
        const replyForm = byId('replyForm');
        const replyInput = byId('replyInput');
        const chatUnreadBadge = byId('chatUnreadBadge');
        const chatStartForm = byId('chatStartForm');
        const chatRecipientInput = byId('chatRecipientInput');
        const chatStartInput = byId('chatStartInput');
        const chatStartEmojiBar = byId('chatStartEmojiBar');
        const chatListBadgeText = byId('chatListBadgeText');
        const chatThreadList = byId('chatThreadList');
        const chatThreadEmpty = byId('chatThreadEmpty');
        const chatActiveTitle = byId('chatActiveTitle');
        const chatActiveMeta = byId('chatActiveMeta');
        const chatMessageList = byId('chatMessageList');
        const chatReplyForm = byId('chatReplyForm');
        const chatReplyInput = byId('chatReplyInput');
        const chatReplyEmojiBar = byId('chatReplyEmojiBar');
        const chatOpenFloatingButton = byId('chatOpenFloatingButton');
        const familyChatUnreadBadge = byId('familyChatUnreadBadge');
        const familyChatListBadgeText = byId('familyChatListBadgeText');
        const familyChatThreadList = byId('familyChatThreadList');
        const familyChatThreadEmpty = byId('familyChatThreadEmpty');
        const familyChatActiveTitle = byId('familyChatActiveTitle');
        const familyChatActiveMeta = byId('familyChatActiveMeta');
        const familyChatMessageList = byId('familyChatMessageList');
        const familyChatReplyForm = byId('familyChatReplyForm');
        const familyChatReplyInput = byId('familyChatReplyInput');
        const familyChatEmojiBar = byId('familyChatEmojiBar');
        const floatingChatToggle = byId('floatingChatToggle');
        const floatingChatPanel = byId('floatingChatPanel');
        const floatingChatClose = byId('floatingChatClose');
        const floatingChatCount = byId('floatingChatCount');
        const floatingChatTitle = byId('floatingChatTitle');
        const floatingChatSubtitle = byId('floatingChatSubtitle');
        const floatingChatThreads = byId('floatingChatThreads');
        const floatingChatMessages = byId('floatingChatMessages');
        const floatingChatForm = byId('floatingChatForm');
        const floatingChatInput = byId('floatingChatInput');
        const floatingEmojiBar = byId('floatingEmojiBar');
        const adminPanel = byId('adminPanel');
        const familyForm = byId('familyForm');
        const familyNameInput = byId('familyNameInput');
        const familyTableBody = byId('familyTableBody');
        const userTableBody = byId('userTableBody');
        const adminListFilter = byId('adminListFilter');
        const adminListTableBody = byId('adminListTableBody');
        const adminTodoFilter = byId('adminTodoFilter');
        const adminTodoTableBody = byId('adminTodoTableBody');
        const adminMessageFilter = byId('adminMessageFilter');
        const adminMessageTableBody = byId('adminMessageTableBody');
        const adminTabButtons = document.querySelectorAll('[data-admin-tab]');
        const adminTabFamilies = byId('adminTabFamilies');
        const adminTabUsers = byId('adminTabUsers');
        const adminTabLists = byId('adminTabLists');
        const adminTabTodos = byId('adminTabTodos');
        const adminTabMessages = byId('adminTabMessages');
        const adminFamiliesCount = byId('adminFamiliesCount');
        const adminUsersCount = byId('adminUsersCount');
        const adminListsCount = byId('adminListsCount');
        const adminTodosCount = byId('adminTodosCount');
        const adminMessagesCount = byId('adminMessagesCount');
        const adminFamiliesPill = byId('adminFamiliesPill');
        const adminUsersPill = byId('adminUsersPill');
        const adminListsPill = byId('adminListsPill');
        const adminTodosPill = byId('adminTodosPill');
        const adminMessagesPill = byId('adminMessagesPill');

        let appData = { currentUser:null, currentFamily:null, lists:[], activeListId:null, todos:[], adminTodos:[], calendarEvents:[], adminCalendarEvents:[], messageThreads:[], chatThreads:[], familyChatThreads:[], messages:[], typingIndicators:[], totalUnreadMessages:0, totalUnreadChats:0, totalUnreadFamilyChats:0, adminMessages:[], users:[], activeUsers:[], families:[], isAdmin:false };
        let activeMessageThreadId = null;
        let activeChatThreadId = null;
        let activeFamilyChatThreadId = null;
        let floatingChatOpen = false;
        let activeCalendarEventId = null;
        let calendarViewMode = 'month';
        let calendarCursor = new Date();
        calendarCursor.setDate(1);
        let activeModule = 'dashboard';
        let backgroundRefreshBusy = false;
        let isLoggingOut = false;
        let appRefreshTimer = null;
        const typingTimers = {};
        const typingLastSent = {};
        const chatEmojis = ['😀','🙂','😂','👍','🙏','❤️','🔥','✅','⚠️','🎉','🙈','😅'];

        async function apiGet(action) {
            if (isLoggingOut) throw new Error('Logout läuft.');
            const response = await fetch('api.php?action=' + encodeURIComponent(action), { method:'GET', credentials:'same-origin', headers:{ Accept:'application/json' } });
            return handleApiResponse(response);
        }
        async function apiPost(action, payload) {
            if (isLoggingOut) throw new Error('Logout läuft.');
            const response = await fetch('api.php?action=' + encodeURIComponent(action), { method:'POST', credentials:'same-origin', headers:{ Accept:'application/json', 'Content-Type':'application/json', 'X-CSRF-Token':csrfToken }, body:JSON.stringify(payload || {}) });
            return handleApiResponse(response);
        }
        async function apiPostQuiet(action, payload) {
            if (isLoggingOut) throw new Error('Logout läuft.');
            const response = await fetch('api.php?action=' + encodeURIComponent(action), { method:'POST', credentials:'same-origin', headers:{ Accept:'application/json', 'Content-Type':'application/json', 'X-CSRF-Token':csrfToken }, body:JSON.stringify(payload || {}) });
            return handleApiResponse(response);
        }
        async function handleApiResponse(response) {
            let result;
            try { result = await response.json(); } catch (error) { throw new Error('Serverantwort war kein gültiges JSON.'); }
            if (response.status === 401 && !isLoggingOut) {
                window.location.href = 'login.php';
            }
            if (!response.ok || result.success !== true) throw new Error(result.message || 'Unbekannter Serverfehler.');
            return result;
        }
        async function loadData(silent) {
            if (isLoggingOut) return;
            try { const result = await apiGet('load'); if (!isLoggingOut) { appData = result.data; renderApp(); } }
            catch (error) { if (silent !== true && !isLoggingOut) showStatus(error.message, true); }
        }
        function beginLogout() {
            isLoggingOut = true;
            if (appRefreshTimer !== null) {
                window.clearInterval(appRefreshTimer);
                appRefreshTimer = null;
            }
            Object.keys(typingTimers).forEach(function(key){
                window.clearTimeout(typingTimers[key]);
                delete typingTimers[key];
            });
            if (logoutButton) {
                logoutButton.disabled = true;
                logoutButton.textContent = 'Logout ...';
            }
        }
        function threadHasUnread(threadId, collectionName) {
            const thread = ((appData[collectionName] || [])).find((item) => item.id === threadId);
            return thread && (thread.unreadCount || 0) > 0;
        }
        async function markVisibleThreadsRead() {
            const jobs = [];
            if (activeModule === 'messages' && activeMessageThreadId && threadHasUnread(activeMessageThreadId, 'messageThreads')) {
                jobs.push(apiPostQuiet('mark_thread_read', { threadId:activeMessageThreadId }));
            }
            if ((activeModule === 'chats' || floatingChatOpen) && activeChatThreadId && threadHasUnread(activeChatThreadId, 'chatThreads')) {
                jobs.push(apiPostQuiet('mark_thread_read', { threadId:activeChatThreadId }));
            }
            if (activeModule === 'familychat' && activeFamilyChatThreadId && threadHasUnread(activeFamilyChatThreadId, 'familyChatThreads')) {
                jobs.push(apiPostQuiet('mark_thread_read', { threadId:activeFamilyChatThreadId }));
            }
            if (jobs.length === 0) return;
            const results = await Promise.allSettled(jobs);
            const lastSuccess = results.map((result) => result.status === 'fulfilled' ? result.value : null).filter(Boolean).pop();
            if (lastSuccess && lastSuccess.data) { appData = lastSuccess.data; renderApp(); }
        }
        async function refreshDataInBackground() {
            if (isLoggingOut || backgroundRefreshBusy || document.hidden) return;
            backgroundRefreshBusy = true;
            const previousMessageThreadId = activeMessageThreadId;
            const previousChatThreadId = activeChatThreadId;
            const previousFamilyChatThreadId = activeFamilyChatThreadId;
            try {
                const result = await apiGet('load');
                if (isLoggingOut) return;
                appData = result.data;
                activeMessageThreadId = previousMessageThreadId;
                activeChatThreadId = previousChatThreadId;
                activeFamilyChatThreadId = previousFamilyChatThreadId;
                renderApp();
                await markVisibleThreadsRead();
            } catch (error) {
                // Hintergrund-Aktualisierung bleibt still, damit die Oberfläche nicht nervt.
            } finally {
                backgroundRefreshBusy = false;
            }
        }
        function getActiveList() { return appData.lists.find((list) => list.id === appData.activeListId); }
        function getUser(userId) { return appData.users.find((user) => user.id === userId) || null; }
        function getUserName(userId) { if (!userId) return 'nicht zugewiesen'; const user = getUser(userId); return user ? user.displayName : 'Unbekannt'; }
        function getFamily(familyId) { return appData.families.find((family) => family.id === familyId) || null; }
        function getFamilyName(familyId) { if (!familyId) return 'kein Haushalt'; const family = getFamily(familyId); return family ? family.name : 'unbekannter Haushalt'; }
        function listTypeLabel(type) { if (type === 'household') return 'Haushaltsliste'; if (type === 'other') return 'Sonstige Liste'; return 'Einkaufsliste'; }
        function todoScopeLabel(scope) { return scope === 'family' ? 'Familienaufgabe' : 'Private Aufgabe'; }
        function todoStatusLabel(status) { return status === 'done' ? 'erledigt' : 'offen'; }
        function priorityLabel(priority) { return { low:'niedrig', normal:'normal', high:'hoch', urgent:'dringend' }[priority] || 'normal'; }
        function formatDate(value) { return value ? value : 'kein Datum'; }
        function pad2(value) { return String(value).padStart(2, '0'); }
        function dateKeyFromDate(date) { return date.getFullYear() + '-' + pad2(date.getMonth()+1) + '-' + pad2(date.getDate()); }
        function dateTimeToInput(value) { if (!value) return ''; return value.replace(' ', 'T').slice(0, 16); }
        function inputToApiDateTime(value) { return value ? value : ''; }
        function eventDateKey(event) { return (event.startsAt || '').slice(0, 10); }
        function eventScopeLabel(scope) { return scope === 'family' ? 'Familientermin' : 'Eigener Termin'; }
        function eventTimeLabel(event) { const start=(event.startsAt || '').slice(11,16); const end=(event.endsAt || '').slice(11,16); return start + (end ? '–' + end : ''); }
        function recurrenceLabel(value) { return { none:'keine Wiederholung', daily:'täglich', weekly:'wöchentlich', monthly:'monatlich' }[value || 'none'] || 'keine Wiederholung'; }
        function parseApiDateTime(value) { if (!value) return null; const normalized = String(value).replace(' ', 'T'); const date = new Date(normalized); return Number.isNaN(date.getTime()) ? null : date; }
        function addDays(date, amount) { const copy = new Date(date); copy.setDate(copy.getDate() + amount); return copy; }
        function addMonths(date, amount) { const copy = new Date(date); copy.setMonth(copy.getMonth() + amount); return copy; }
        function getTodo(todoId) { return (appData.todos || []).find((todo) => todo.id === todoId) || null; }
        function todoLinkLabel(todoId) { const todo = getTodo(todoId); return todo ? todo.title : ''; }
        function canUseFamilyScope() { return appData.currentUser && appData.currentUser.familyId !== ''; }
        function formatMessagePreview(value) { if (!value) return 'Noch keine Nachricht'; return value.length > 72 ? value.slice(0, 72) + '…' : value; }
        function getMessageThread(threadId) { return (appData.messageThreads || []).find((thread) => thread.id === threadId) || null; }
        function getChatThread(threadId) { return (appData.chatThreads || []).find((thread) => thread.id === threadId) || null; }
        function getFamilyChatThread(threadId) { return (appData.familyChatThreads || []).find((thread) => thread.id === threadId) || null; }
        function getThread(threadId) { return getMessageThread(threadId) || getChatThread(threadId) || getFamilyChatThread(threadId); }
        function getMessagesForThread(threadId) { return (appData.messages || []).filter((message) => message.threadId === threadId); }
        function getOtherParticipant(thread) { if (!thread || !appData.currentUser) return null; const id = thread.participantIds.find((participantId) => participantId !== appData.currentUser.id) || thread.otherParticipantId || ''; return getUser(id); }
        function getTypingUsers(channel, threadId, recipientId) {
            return (appData.typingIndicators || []).filter(function(indicator){
                if (indicator.channel !== channel) return false;
                if (threadId && indicator.threadId === threadId) return true;
                return !threadId && recipientId && indicator.recipientId === recipientId;
            });
        }
        function appendTypingIndicator(container, users) {
            if (!users || users.length === 0) return;
            const wrap=document.createElement('div'); wrap.className='typing-indicator';
            const dots=document.createElement('span'); dots.className='typing-dots';
            dots.innerHTML='<span></span><span></span><span></span>';
            const label=document.createElement('span');
            label.textContent = users.map((user)=>user.displayName).join(', ') + (users.length === 1 ? ' schreibt …' : ' schreiben …');
            wrap.appendChild(dots); wrap.appendChild(label); container.appendChild(wrap);
        }
        function deliveryText(message) {
            if (!appData.currentUser || message.senderId !== appData.currentUser.id || !message.deliveryLabel) return '';
            return (message.deliveryIcon || '') + ' ' + message.deliveryLabel;
        }
        function canManageListSettings(list) { return appData.isAdmin === true || (appData.currentUser && list.ownerId === appData.currentUser.id); }
        function todayString() { return new Date().toISOString().slice(0, 10); }
        function isOverdue(todo) { return todo.status !== 'done' && todo.dueAt && todo.dueAt < todayString(); }
        function isReminderDue(todo) { return todo.status !== 'done' && todo.reminderAt && todo.reminderAt <= todayString(); }

        function fillUserSelect(select, selectedValue, scope, includeEmpty) {
            select.textContent = '';
            if (includeEmpty) {
                const empty = document.createElement('option');
                empty.value = '';
                empty.textContent = 'nicht zugewiesen';
                select.appendChild(empty);
            }
            const currentFamilyId = appData.currentUser ? appData.currentUser.familyId : '';
            const users = (appData.activeUsers || []).filter((user) => scope === 'family' ? user.familyId === currentFamilyId && currentFamilyId !== '' : appData.currentUser && user.id === appData.currentUser.id);
            users.forEach((user) => {
                const option = document.createElement('option');
                option.value = user.id;
                option.textContent = user.displayName + ' (@' + user.username + ')';
                if (user.id === selectedValue) option.selected = true;
                select.appendChild(option);
            });
        }
        function refreshCreateTodoAssignees() { fillUserSelect(todoAssignedInput, appData.currentUser ? appData.currentUser.id : '', todoFamilyInput.checked ? 'family' : 'private', true); }

        async function createList() {
            const name = listNameInput.value.trim();
            if (name === '') { showStatus('Bitte gib einen Listennamen ein.', true); listNameInput.focus(); return; }
            const payload = { name:name, listType:listTypeInput.value, isShared:listSharedInput.checked };
            if (appData.isAdmin === true && listOwnerInput.value !== '') payload.ownerId = listOwnerInput.value;
            try { const result = await apiPost('create_list', payload); appData = result.data; listNameInput.value=''; listTypeInput.value='shopping'; listSharedInput.checked=false; renderApp(); showStatus(result.message); }
            catch (error) { showStatus(error.message, true); }
        }
        async function selectList(listId) { try { const result = await apiPost('set_active_list', { listId:listId }); appData = result.data; renderApp(); } catch(error){ showStatus(error.message, true); } }
        async function deleteActiveList() { const list = getActiveList(); if (!list) return; if (!confirm('Liste "' + list.name + '" wirklich löschen?')) return; try { const result = await apiPost('delete_list', { listId:list.id }); appData = result.data; renderApp(); showStatus(result.message); } catch(error){ showStatus(error.message, true); } }
        async function updateActiveListVisibility() { const list = getActiveList(); if (!list) return; try { const result = await apiPost('update_list_visibility', { listId:list.id, isShared:!list.isShared }); appData = result.data; renderApp(); showStatus(result.message); } catch(error){ showStatus(error.message, true); } }
        async function updateActiveListType() { const list = getActiveList(); if (!list) return; try { const result = await apiPost('update_list_type', { listId:list.id, listType:activeListTypeSelect.value }); appData = result.data; renderApp(); showStatus(result.message); } catch(error){ showStatus(error.message, true); } }
        async function updateActiveListOwner() { const list = getActiveList(); if (!list || appData.isAdmin !== true) return; try { const result = await apiPost('admin_update_list_owner', { listId:list.id, ownerId:activeListOwnerSelect.value }); appData = result.data; renderApp(); showStatus(result.message); } catch(error){ showStatus(error.message, true); } }
        async function addItem() { const list = getActiveList(); if (!list) return; const name = itemNameInput.value.trim(); if (name === '') { showStatus('Bitte gib einen Artikelnamen ein.', true); itemNameInput.focus(); return; } try { const result = await apiPost('add_item', { listId:list.id, name:name, amount:itemAmountInput.value.trim(), category:itemCategoryInput.value }); appData = result.data; itemNameInput.value=''; itemAmountInput.value=''; itemCategoryInput.value='Lebensmittel'; renderApp(); showStatus(result.message); } catch(error){ showStatus(error.message, true); } }
        async function toggleItemDone(itemId) { const list = getActiveList(); if (!list) return; try { const result = await apiPost('toggle_item', { listId:list.id, itemId:itemId }); appData = result.data; renderApp(); showStatus(result.message); } catch(error){ showStatus(error.message, true); } }
        async function deleteItem(itemId) { const list = getActiveList(); if (!list) return; try { const result = await apiPost('delete_item', { listId:list.id, itemId:itemId }); appData = result.data; renderApp(); showStatus(result.message); } catch(error){ showStatus(error.message, true); } }

        async function createTodo() {
            const title = todoTitleInput.value.trim();
            if (title === '') { showStatus('Bitte gib eine Aufgabe ein.', true); todoTitleInput.focus(); return; }
            const scope = todoFamilyInput.checked ? 'family' : 'private';
            const payload = { title, scope, priority:todoPriorityInput.value, assignedTo:todoAssignedInput.value, dueAt:todoDueInput.value, reminderAt:todoReminderInput.value, calendarDate:todoCalendarInput.value };
            try {
                const result = await apiPost('create_todo', payload);
                appData = result.data;
                todoTitleInput.value = '';
                todoDueInput.value = '';
                todoReminderInput.value = '';
                todoCalendarInput.value = '';
                todoPriorityInput.value = 'normal';
                todoFamilyInput.checked = false;
                renderApp();
                showStatus(result.message);
            } catch(error) { showStatus(error.message, true); }
        }
        async function updateTodo(todoId, payload) { try { const result = await apiPost('update_todo', Object.assign({ todoId }, payload)); appData = result.data; renderApp(); showStatus(result.message); } catch(error){ showStatus(error.message, true); } }
        async function toggleTodoDone(todoId) { try { const result = await apiPost('toggle_todo', { todoId }); appData = result.data; renderApp(); showStatus(result.message); } catch(error){ showStatus(error.message, true); } }
        async function deleteTodo(todoId) { if (!confirm('Aufgabe wirklich löschen?')) return; try { const result = await apiPost('delete_todo', { todoId }); appData = result.data; renderApp(); showStatus(result.message); } catch(error){ showStatus(error.message, true); } }
        async function addTodoComment(todoId, input) { const body = input.value.trim(); if (body === '') { showStatus('Bitte gib einen Kommentar ein.', true); return; } try { const result = await apiPost('add_todo_comment', { todoId, body }); appData = result.data; renderApp(); showStatus(result.message); } catch(error){ showStatus(error.message, true); } }
        async function deleteTodoComment(todoId, commentId) { try { const result = await apiPost('delete_todo_comment', { todoId, commentId }); appData = result.data; renderApp(); showStatus(result.message); } catch(error){ showStatus(error.message, true); } }

        function findMessageThreadByOtherUser(userId) { return (appData.messageThreads || []).find((thread) => thread.participantIds.includes(userId)); }
        function findChatThreadByOtherUser(userId) { return (appData.chatThreads || []).find((thread) => thread.participantIds.includes(userId)); }
        async function sendPersonalMessage(action, recipientId, body) {
            if (!recipientId) { showStatus('Bitte wähle einen Empfänger aus.', true); return; }
            if (body.trim() === '') { showStatus('Bitte gib eine Nachricht ein.', true); return; }
            try {
                const result = await apiPost(action, { recipientId, body:body.trim() });
                appData = result.data;
                const thread = action === 'send_chat_message' ? findChatThreadByOtherUser(recipientId) : findMessageThreadByOtherUser(recipientId);
                if (thread && action === 'send_chat_message') activeChatThreadId = thread.id;
                if (thread && action !== 'send_chat_message') activeMessageThreadId = thread.id;
                renderApp();
                showStatus(result.message);
            } catch(error) { showStatus(error.message, true); }
        }
        async function openMessageThread(threadId) {
            activeMessageThreadId = threadId;
            try {
                const result = await apiPost('mark_thread_read', { threadId });
                appData = result.data;
                activeMessageThreadId = threadId;
                renderApp();
            } catch(error) { renderApp(); showStatus(error.message, true); }
        }
        async function openChatThread(threadId) {
            activeChatThreadId = threadId;
            try {
                const result = await apiPost('mark_thread_read', { threadId });
                appData = result.data;
                activeChatThreadId = threadId;
                renderApp();
            } catch(error) { renderApp(); showStatus(error.message, true); }
        }
        async function openFamilyChatThread(threadId) {
            activeFamilyChatThreadId = threadId;
            try {
                const result = await apiPost('mark_thread_read', { threadId });
                appData = result.data;
                activeFamilyChatThreadId = threadId;
                renderApp();
            } catch(error) { renderApp(); showStatus(error.message, true); }
        }
        async function sendFamilyChatMessage(body) {
            if (body.trim() === '') { showStatus('Bitte gib eine Familienchat-Nachricht ein.', true); return; }
            try {
                const result = await apiPost('send_family_chat_message', { body:body.trim() });
                appData = result.data;
                const thread = (appData.familyChatThreads || [])[0] || null;
                if (thread) activeFamilyChatThreadId = thread.id;
                renderApp();
                showStatus(result.message);
            } catch(error) { showStatus(error.message, true); }
        }
        async function deleteMessage(messageId) {
            if (!confirm('Nachricht wirklich löschen?')) return;
            try { const result = await apiPost('delete_message', { messageId }); appData = result.data; renderApp(); showStatus(result.message); }
            catch(error){ showStatus(error.message, true); }
        }
        function typingKey(channel, target) { return channel + ':' + (target.threadId || '') + ':' + (target.recipientId || ''); }
        async function sendTypingState(channel, target, isTyping) {
            if (!target || (!target.threadId && !target.recipientId)) return;
            try {
                await apiPostQuiet('set_typing', { channel:channel, threadId:target.threadId || '', recipientId:target.recipientId || '', isTyping:isTyping });
            } catch (error) {
                // Tippstatus ist eine Komfortfunktion und darf den eigentlichen Chat nicht blockieren.
            }
        }
        function registerTypingInput(input, channel, targetFactory) {
            if (!input) return;
            input.addEventListener('input', function(){
                const target = targetFactory();
                const key = typingKey(channel, target || {});
                window.clearTimeout(typingTimers[key]);
                if (!target || (!target.threadId && !target.recipientId)) return;
                if (input.value.trim() === '') { sendTypingState(channel, target, false); return; }
                const now = Date.now();
                if (!typingLastSent[key] || now - typingLastSent[key] > 2500) {
                    typingLastSent[key] = now;
                    sendTypingState(channel, target, true);
                }
                typingTimers[key] = window.setTimeout(function(){ sendTypingState(channel, target, false); }, 4200);
            });
            input.addEventListener('blur', function(){
                const target = targetFactory();
                if (target && (target.threadId || target.recipientId)) sendTypingState(channel, target, false);
            });
        }


        function fillCalendarTodoSelect(select, selectedValue) {
            if (!select) return;
            const current = selectedValue || '';
            select.textContent = '';
            const empty = document.createElement('option');
            empty.value = '';
            empty.textContent = 'Keine Todo-Verknüpfung';
            select.appendChild(empty);
            (appData.todos || []).slice().sort((a,b)=>String(a.title).localeCompare(String(b.title))).forEach(function(todo){
                const option = document.createElement('option');
                option.value = todo.id;
                option.textContent = todo.title + ' · ' + todoScopeLabel(todo.scope) + ' · ' + todoStatusLabel(todo.status);
                select.appendChild(option);
            });
            select.value = current;
        }
        function calendarPayloadFromInputs(prefix) {
            const source = prefix === 'modal' ? {
                title:calendarModalTitleInput, scope:calendarModalScopeInput, start:calendarModalStartInput, end:calendarModalEndInput, location:calendarModalLocationInput, description:calendarModalDescriptionInput, recurrence:calendarModalRecurrenceInput, recurrenceUntil:calendarModalRecurrenceUntilInput, reminder:calendarModalReminderInput, todo:calendarModalTodoInput
            } : {
                title:calendarTitleInput, scope:calendarScopeInput, start:calendarStartInput, end:calendarEndInput, location:calendarLocationInput, description:calendarDescriptionInput, recurrence:calendarRecurrenceInput, recurrenceUntil:calendarRecurrenceUntilInput, reminder:calendarReminderInput, todo:calendarTodoInput
            };
            return {
                title:source.title.value.trim(),
                scope:source.scope.value,
                startsAt:inputToApiDateTime(source.start.value),
                endsAt:inputToApiDateTime(source.end.value),
                location:source.location.value.trim(),
                description:source.description.value.trim(),
                recurrence:source.recurrence.value,
                recurrenceUntil:source.recurrenceUntil.value,
                reminderAt:inputToApiDateTime(source.reminder.value),
                todoId:source.todo.value
            };
        }
        async function createCalendarEventFromForm() {
            const payload = calendarPayloadFromInputs('form');
            if (payload.title === '') { showStatus('Bitte gib einen Termintitel ein.', true); return; }
            try {
                const result = await apiPost('create_calendar_event', payload);
                appData = result.data;
                calendarTitleInput.value=''; calendarLocationInput.value=''; calendarDescriptionInput.value=''; calendarEndInput.value=''; calendarRecurrenceInput.value='none'; calendarRecurrenceUntilInput.value=''; calendarReminderInput.value=''; calendarTodoInput.value='';
                renderApp(); showStatus(result.message);
            } catch(error) { showStatus(error.message, true); }
        }
        async function saveCalendarModal() {
            const payload = calendarPayloadFromInputs('modal');
            if (payload.title === '') { showStatus('Bitte gib einen Termintitel ein.', true); return; }
            try {
                const action = activeCalendarEventId ? 'update_calendar_event' : 'create_calendar_event';
                if (activeCalendarEventId) payload.eventId = activeCalendarEventId;
                const result = await apiPost(action, payload);
                appData = result.data;
                closeCalendarModal(); renderApp(); showStatus(result.message);
            } catch(error) { showStatus(error.message, true); }
        }
        async function deleteCalendarEvent(eventId) {
            const event = getCalendarEvent(eventId);
            if (!event) return;
            if (!confirm('Termin "' + event.title + '" wirklich löschen?')) return;
            try {
                const result = await apiPost('delete_calendar_event', { eventId:eventId });
                appData = result.data;
                closeCalendarModal(); renderApp(); showStatus(result.message);
            } catch(error) { showStatus(error.message, true); }
        }

        async function adminCreateFamily() { const name = familyNameInput.value.trim(); if (name === '') { showStatus('Bitte gib einen Haushaltsnamen ein.', true); return; } try { const result = await apiPost('admin_create_family', { name:name }); appData = result.data; familyNameInput.value=''; renderApp(); showStatus(result.message); } catch(error){ showStatus(error.message, true); } }
        async function adminRenameFamily(familyId) { const family = getFamily(familyId); if (!family) return; const name = prompt('Neuer Name für Haushalt:', family.name); if (name === null) return; try { const result = await apiPost('admin_update_family_name', { familyId:familyId, name:name.trim() }); appData = result.data; renderApp(); showStatus(result.message); } catch(error){ showStatus(error.message, true); } }
        async function adminDeleteFamily(familyId) { const family = getFamily(familyId); if (!family) return; if (!confirm('Haushalt "' + family.name + '" wirklich löschen? Zugeordnete Nutzer werden ohne Haushalt gesetzt und Haushaltslisten werden privat.')) return; try { const result = await apiPost('admin_delete_family', { familyId:familyId }); appData = result.data; renderApp(); showStatus(result.message); } catch(error){ showStatus(error.message, true); } }
        async function adminUpdateUserFamily(userId, familyId) { try { const result = await apiPost('admin_update_user_family', { userId:userId, familyId:familyId }); appData = result.data; renderApp(); showStatus(result.message); } catch(error){ showStatus(error.message, true); } }
        async function adminUpdateRole(userId, role) { try { const result = await apiPost('admin_update_user_role', { userId:userId, role:role }); appData = result.data; renderApp(); showStatus(result.message); } catch(error){ showStatus(error.message, true); } }
        async function adminToggleActive(userId) { try { const result = await apiPost('admin_toggle_user_active', { userId:userId }); appData = result.data; renderApp(); showStatus(result.message); } catch(error){ showStatus(error.message, true); } }
        async function adminDeleteUser(userId) { if (!confirm('Benutzer wirklich löschen? Eigene Listen dieses Benutzers werden entfernt.')) return; try { const result = await apiPost('admin_delete_user', { userId:userId }); appData = result.data; renderApp(); showStatus(result.message); } catch(error){ showStatus(error.message, true); } }
        async function adminUpdateListOwner(listId, ownerId) { try { const result = await apiPost('admin_update_list_owner', { listId:listId, ownerId:ownerId }); appData = result.data; renderApp(); showStatus(result.message); } catch(error){ showStatus(error.message, true); } }
        async function adminUpdateListType(listId, listType) { try { const result = await apiPost('update_list_type', { listId:listId, listType:listType }); appData = result.data; renderApp(); showStatus(result.message); } catch(error){ showStatus(error.message, true); } }
        async function adminToggleListVisibility(list) { try { const result = await apiPost('update_list_visibility', { listId:list.id, isShared:!list.isShared }); appData = result.data; renderApp(); showStatus(result.message); } catch(error){ showStatus(error.message, true); } }

        function renderApp() { renderCurrentUser(); renderUserSelects(); renderListGroups(); renderActiveList(); renderTodos(); renderMessages(); renderChats(); renderCalendar(); renderFamilyChat(); renderFloatingChat(); renderAdminPanel(); renderDashboardShell(); }
        function renderCurrentUser() {
            if (!appData.currentUser) return;
            const userText = appData.currentUser.displayName + ' (' + appData.currentUser.role + ')';
            const familyText = 'Haushalt: ' + getFamilyName(appData.currentUser.familyId);
            currentUserLabel.textContent = userText;
            currentFamilyLabel.textContent = familyText;
            sidebarUserLabel.textContent = appData.currentUser.displayName;
            sidebarFamilyLabel.textContent = familyText;
        }
        function setBadge(element, value) {
            if (!element) return;
            element.textContent = String(value);
            element.classList.toggle('empty', Number(value) === 0);
        }

        function getCalendarEvent(eventId) { return (appData.calendarEvents || []).find((event) => event.id === eventId) || null; }
        function openCalendarModal(eventOrDate) {
            let event = null;
            activeCalendarEventId = null;
            fillCalendarTodoSelect(calendarModalTodoInput, '');
            if (typeof eventOrDate === 'string') {
                event = getCalendarEvent(eventOrDate);
            }
            if (event) {
                activeCalendarEventId = event.id;
                calendarModalTitle.textContent = 'Termin bearbeiten';
                calendarModalMeta.textContent = eventScopeLabel(event.scope) + ' · Besitzer: ' + getUserName(event.ownerId) + (event.recurrence && event.recurrence !== 'none' ? ' · ' + recurrenceLabel(event.recurrence) : '');
                calendarModalTitleInput.value = event.title;
                calendarModalScopeInput.value = event.scope;
                calendarModalStartInput.value = dateTimeToInput(event.startsAt);
                calendarModalEndInput.value = dateTimeToInput(event.endsAt);
                calendarModalLocationInput.value = event.location || '';
                calendarModalTodoInput.value = event.todoId || '';
                calendarModalRecurrenceInput.value = event.recurrence || 'none';
                calendarModalRecurrenceUntilInput.value = event.recurrenceUntil || '';
                calendarModalReminderInput.value = dateTimeToInput(event.reminderAt || '');
                calendarModalDescriptionInput.value = event.description || '';
                calendarModalDelete.style.display = 'inline-block';
            } else {
                activeCalendarEventId = null;
                calendarModalTitle.textContent = 'Termin erstellen';
                calendarModalMeta.textContent = 'Neuer Termin im Kalender';
                calendarModalTitleInput.value = '';
                calendarModalScopeInput.value = 'private';
                const day = eventOrDate instanceof Date ? eventOrDate : new Date();
                calendarModalStartInput.value = dateKeyFromDate(day) + 'T09:00';
                calendarModalEndInput.value = dateKeyFromDate(day) + 'T10:00';
                calendarModalLocationInput.value = '';
                calendarModalTodoInput.value = '';
                calendarModalRecurrenceInput.value = 'none';
                calendarModalRecurrenceUntilInput.value = '';
                calendarModalReminderInput.value = '';
                calendarModalDescriptionInput.value = '';
                calendarModalDelete.style.display = 'none';
            }
            calendarModalScopeInput.querySelector('option[value="family"]').disabled = !canUseFamilyScope();
            calendarModal.classList.add('open');
            calendarModal.setAttribute('aria-hidden', 'false');
        }
        function closeCalendarModal() {
            activeCalendarEventId = null;
            calendarModal.classList.remove('open');
            calendarModal.setAttribute('aria-hidden', 'true');
        }
        function calendarRangeEnd() {
            const base = new Date(calendarCursor);
            if (calendarViewMode === 'week') return addDays(getWeekStart(base), 6);
            return new Date(base.getFullYear(), base.getMonth() + 1, 0);
        }
        function getWeekStart(date) {
            const copy = new Date(date);
            const offset = (copy.getDay() + 6) % 7;
            copy.setDate(copy.getDate() - offset);
            copy.setHours(0,0,0,0);
            return copy;
        }
        function eventOccurrenceOnDate(event, dateKey) {
            return expandCalendarEvents([event], dateKey, dateKey).find(Boolean) || null;
        }
        function expandCalendarEvents(events, startKey, endKey) {
            const expanded = [];
            events.forEach(function(event){
                const start = parseApiDateTime(event.startsAt);
                if (!start) return;
                const end = parseApiDateTime(event.endsAt || '');
                const baseKey = dateKeyFromDate(start);
                const recurrence = event.recurrence || 'none';
                const untilKey = event.recurrenceUntil || baseKey;
                let cursor = new Date(start);
                let guard = 0;
                while (guard < 370) {
                    const key = dateKeyFromDate(cursor);
                    if (key > endKey) break;
                    if (key >= startKey && key <= endKey) {
                        const occurrence = Object.assign({}, event, { occurrenceDate:key, occurrenceStartsAt:key + ' ' + (event.startsAt || '').slice(11,19), isOccurrence: key !== baseKey });
                        if (end) occurrence.occurrenceEndsAt = key + ' ' + (event.endsAt || '').slice(11,19);
                        expanded.push(occurrence);
                    }
                    if (recurrence === 'none' || key >= untilKey) break;
                    if (recurrence === 'daily') cursor = addDays(cursor, 1);
                    else if (recurrence === 'weekly') cursor = addDays(cursor, 7);
                    else if (recurrence === 'monthly') cursor = addMonths(cursor, 1);
                    else break;
                    guard++;
                }
            });
            return expanded.sort((a,b)=>(a.occurrenceStartsAt || a.startsAt || '').localeCompare(b.occurrenceStartsAt || b.startsAt || ''));
        }
        function getFilteredCalendarEvents() {
            let events = (appData.calendarEvents || []).slice();
            const scope = calendarScopeFilter ? calendarScopeFilter.value : 'all';
            const special = calendarSpecialFilter ? calendarSpecialFilter.value : 'all';
            const search = calendarSearchFilter ? calendarSearchFilter.value.trim().toLowerCase() : '';
            if (scope !== 'all') events = events.filter((event)=>event.scope === scope);
            if (special === 'recurring') events = events.filter((event)=>(event.recurrence || 'none') !== 'none');
            if (special === 'reminders') events = events.filter((event)=>event.reminderAt);
            if (special === 'linked') events = events.filter((event)=>event.todoId);
            if (search !== '') events = events.filter(function(event){
                return [event.title, event.location, event.description, todoLinkLabel(event.todoId)].join(' ').toLowerCase().includes(search);
            });
            return events;
        }
        function makeCalendarChip(event) {
            const chip=document.createElement('button');
            chip.type='button';
            chip.className='calendar-event-chip' + (event.scope === 'family' ? ' family' : '') + ((event.recurrence || 'none') !== 'none' ? ' recurring' : '') + (event.todoId ? ' linked' : '') + (event.reminderAt ? ' reminder' : '');
            chip.textContent=eventTimeLabel({ startsAt:event.occurrenceStartsAt || event.startsAt, endsAt:event.occurrenceEndsAt || event.endsAt }) + ' ' + event.title;
            chip.title=event.title + (event.location ? ' · ' + event.location : '') + (event.todoId ? ' · Todo: ' + todoLinkLabel(event.todoId) : '');
            chip.addEventListener('click', function(ev){ ev.stopPropagation(); openCalendarModal(event.id); });
            return chip;
        }
        function renderCalendar() {
            if (!calendarGrid) return;
            fillCalendarTodoSelect(calendarTodoInput, calendarTodoInput.value || '');
            const events = getFilteredCalendarEvents();
            calendarMonthMode.classList.toggle('active', calendarViewMode === 'month');
            calendarWeekMode.classList.toggle('active', calendarViewMode === 'week');
            if (calendarViewMode === 'week') renderCalendarWeek(events); else renderCalendarMonth(events);
            renderCalendarReminderList(events);
            navCalendarBadge.textContent = String(events.length);
            navCalendarBadge.classList.toggle('empty', events.length === 0);
            calendarScopeInput.querySelector('option[value="family"]').disabled = !canUseFamilyScope();
        }
        function renderCalendarMonth(events) {
            const currentYear = calendarCursor.getFullYear();
            const currentMonth = calendarCursor.getMonth();
            const monthNames = ['Januar','Februar','März','April','Mai','Juni','Juli','August','September','Oktober','November','Dezember'];
            calendarMonthLabel.textContent = monthNames[currentMonth] + ' ' + currentYear;
            calendarGrid.className = 'calendar-grid';
            calendarGrid.textContent = '';
            ['Mo','Di','Mi','Do','Fr','Sa','So'].forEach(function(day){ const cell=document.createElement('div'); cell.className='calendar-weekday'; cell.textContent=day; calendarGrid.appendChild(cell); });
            const firstDay = new Date(currentYear, currentMonth, 1);
            const startOffset = (firstDay.getDay() + 6) % 7;
            const startDate = new Date(currentYear, currentMonth, 1 - startOffset);
            const endDate = addDays(startDate, 41);
            const occurrences = expandCalendarEvents(events, dateKeyFromDate(startDate), dateKeyFromDate(endDate));
            const todayKey = dateKeyFromDate(new Date());
            for (let index=0; index<42; index++) {
                const date = addDays(startDate, index);
                const key = dateKeyFromDate(date);
                const cell=document.createElement('div');
                cell.className='calendar-day';
                if (date.getMonth() !== currentMonth) cell.classList.add('outside');
                if (key === todayKey) cell.classList.add('today');
                const head=document.createElement('div'); head.className='calendar-day-number'; head.textContent=String(date.getDate());
                cell.appendChild(head);
                const dayEvents = occurrences.filter((event)=>event.occurrenceDate === key);
                dayEvents.slice(0,4).forEach(function(event){ cell.appendChild(makeCalendarChip(event)); });
                if (dayEvents.length > 4) { const more=document.createElement('small'); more.textContent='+' + (dayEvents.length-4) + ' weitere'; more.style.color='var(--muted)'; cell.appendChild(more); }
                cell.addEventListener('click', function(){ openCalendarModal(date); });
                calendarGrid.appendChild(cell);
            }
            renderCalendarUpcoming(occurrences);
        }
        function renderCalendarWeek(events) {
            const start = getWeekStart(calendarCursor);
            const end = addDays(start, 6);
            calendarMonthLabel.textContent = 'Woche ' + dateKeyFromDate(start) + ' – ' + dateKeyFromDate(end);
            calendarGrid.className = 'calendar-week-grid';
            calendarGrid.textContent = '';
            const occurrences = expandCalendarEvents(events, dateKeyFromDate(start), dateKeyFromDate(end));
            const todayKey = dateKeyFromDate(new Date());
            ['Mo','Di','Mi','Do','Fr','Sa','So'].forEach(function(label, index){
                const date = addDays(start, index);
                const key = dateKeyFromDate(date);
                const column=document.createElement('div'); column.className='calendar-week-column';
                if (key === todayKey) column.classList.add('today');
                const head=document.createElement('div'); head.className='calendar-week-head'; head.textContent=label + ' · ' + key;
                column.appendChild(head);
                occurrences.filter((event)=>event.occurrenceDate === key).forEach(function(event){ column.appendChild(makeCalendarChip(event)); });
                column.addEventListener('click', function(){ openCalendarModal(date); });
                calendarGrid.appendChild(column);
            });
            renderCalendarUpcoming(occurrences);
        }
        function renderCalendarUpcoming(occurrences) {
            calendarUpcomingList.textContent='';
            const todayKey = dateKeyFromDate(new Date());
            const upcoming = occurrences.filter((event)=>event.occurrenceDate >= todayKey).slice(0,12);
            calendarUpcomingEmpty.style.display = upcoming.length === 0 ? 'block' : 'none';
            upcoming.forEach(function(event){
                const card=document.createElement('button'); card.type='button'; card.className='calendar-side-card';
                const title=document.createElement('strong'); title.textContent=event.title;
                const meta=document.createElement('small'); meta.textContent=eventScopeLabel(event.scope) + ' · ' + (event.occurrenceStartsAt || event.startsAt) + (event.endsAt ? ' bis ' + (event.occurrenceEndsAt || event.endsAt) : '');
                const location=document.createElement('small'); location.textContent=event.location ? 'Ort: ' + event.location : 'kein Ort hinterlegt';
                card.appendChild(title); card.appendChild(meta); card.appendChild(location);
                if ((event.recurrence || 'none') !== 'none') { const pill=document.createElement('span'); pill.className='calendar-meta-pill'; pill.textContent=recurrenceLabel(event.recurrence); card.appendChild(pill); }
                if (event.todoId) { const pill=document.createElement('span'); pill.className='calendar-meta-pill'; pill.textContent='Todo: ' + todoLinkLabel(event.todoId); card.appendChild(pill); }
                card.addEventListener('click', function(){ openCalendarModal(event.id); });
                calendarUpcomingList.appendChild(card);
            });
        }
        function renderCalendarReminderList(events) {
            if (!calendarReminderList) return;
            calendarReminderList.textContent='';
            const now = new Date();
            const soon = addDays(now, 7);
            const reminders = events.filter((event)=>event.reminderAt).filter(function(event){ const reminder=parseApiDateTime(event.reminderAt); return reminder && reminder <= soon; }).sort((a,b)=>String(a.reminderAt).localeCompare(String(b.reminderAt))).slice(0,8);
            calendarReminderEmpty.style.display = reminders.length === 0 ? 'block' : 'none';
            reminders.forEach(function(event){
                const card=document.createElement('div'); card.className='calendar-reminder-card';
                const title=document.createElement('strong'); title.textContent=event.title;
                const meta=document.createElement('small'); meta.textContent='Erinnerung: ' + event.reminderAt + ' · Termin: ' + event.startsAt;
                card.appendChild(title); card.appendChild(meta);
                card.addEventListener('click', function(){ openCalendarModal(event.id); });
                calendarReminderList.appendChild(card);
            });
        }

        function renderMessages() {
            const totalUnread = appData.totalUnreadMessages || 0;
            unreadBadge.textContent = String(totalUnread);
            unreadBadge.classList.toggle('empty', totalUnread === 0);
            messageBadgeText.textContent = totalUnread > 0 ? '(' + totalUnread + ' ungelesen)' : '(keine ungelesenen)';
            threadList.textContent = '';
            const threads = appData.messageThreads || [];
            threadEmpty.style.display = threads.length === 0 ? 'block' : 'none';
            if (activeMessageThreadId && !getMessageThread(activeMessageThreadId)) activeMessageThreadId = null;
            threads.forEach(function(thread){ renderThreadButton(thread); });
            renderActiveThread();
        }
        function renderThreadButton(thread) {
            const button=document.createElement('button'); button.className='thread-button'; button.type='button'; if (thread.id===activeMessageThreadId) button.classList.add('active');
            const main=document.createElement('div'); const other=getOtherParticipant(thread); const title=document.createElement('strong'); title.textContent=other ? other.displayName + ' (@' + other.username + ')' : 'Nachrichtenverlauf';
            const small=document.createElement('small'); const last=thread.lastMessage ? getUserName(thread.lastMessage.senderId) + ': ' + formatMessagePreview(thread.lastMessage.body) : 'Noch keine Nachricht'; small.textContent=last;
            const time=document.createElement('small'); time.textContent='Aktualisiert: ' + (thread.updatedAt || thread.createdAt);
            main.appendChild(title); main.appendChild(small); main.appendChild(time); button.appendChild(main);
            if ((thread.unreadCount || 0) > 0) { const badge=document.createElement('span'); badge.className='thread-unread'; badge.textContent=String(thread.unreadCount); button.appendChild(badge); }
            button.addEventListener('click', function(){ openMessageThread(thread.id); }); threadList.appendChild(button);
        }
        function renderActiveThread() {
            messageList.textContent = '';
            const thread = getMessageThread(activeMessageThreadId);
            if (!thread) {
                activeThreadTitle.textContent = 'Kein Verlauf ausgewählt';
                replyForm.style.display = 'none';
                return;
            }
            const other = getOtherParticipant(thread);
            activeThreadTitle.textContent = other ? 'Privater Verlauf mit ' + other.displayName : 'Privater Verlauf';
            replyForm.style.display = other ? 'grid' : 'none';
            const messages = getMessagesForThread(thread.id);
            if (messages.length === 0) {
                const empty=document.createElement('div'); empty.className='empty-message'; empty.textContent='Noch keine Nachrichten in diesem Verlauf.'; messageList.appendChild(empty); return;
            }
            messages.forEach(function(message){
                const bubble=document.createElement('div'); bubble.className='message-bubble'; if (appData.currentUser && message.senderId===appData.currentUser.id) bubble.classList.add('own');
                const body=document.createElement('div'); body.textContent=message.body;
                const meta=document.createElement('small'); meta.textContent=getUserName(message.senderId) + ' → ' + getUserName(message.recipientId) + ' · ' + message.createdAt;
                const stateText = deliveryText(message);
                if (stateText) { const state=document.createElement('span'); state.className='message-state'; state.textContent=stateText; bubble.appendChild(body); bubble.appendChild(meta); bubble.appendChild(state); }
                else { bubble.appendChild(body); bubble.appendChild(meta); }
                const actions=document.createElement('div'); actions.className='inline-actions'; actions.style.marginTop='8px';
                const del=document.createElement('button'); del.className='danger'; del.type='button'; del.textContent='Löschen'; del.addEventListener('click', function(){ deleteMessage(message.id); });
                actions.appendChild(del); bubble.appendChild(actions); messageList.appendChild(bubble);
            });
            appendTypingIndicator(messageList, getTypingUsers('message', thread.id, other ? other.id : ''));
            messageList.scrollTop = messageList.scrollHeight;
        }

        function renderChats() {
            const totalUnread = appData.totalUnreadChats || 0;
            chatUnreadBadge.textContent = String(totalUnread);
            chatUnreadBadge.classList.toggle('empty', totalUnread === 0);
            chatListBadgeText.textContent = totalUnread > 0 ? '(' + totalUnread + ' ungelesen)' : '(keine ungelesenen)';
            renderEmojiBar(chatStartEmojiBar, chatStartInput);
            renderEmojiBar(chatReplyEmojiBar, chatReplyInput);
            chatThreadList.textContent = '';
            const threads = appData.chatThreads || [];
            chatThreadEmpty.style.display = threads.length === 0 ? 'block' : 'none';
            if (activeChatThreadId && !getChatThread(activeChatThreadId)) activeChatThreadId = null;
            threads.forEach(function(thread){ renderChatThreadCard(chatThreadList, thread, 'main'); });
            renderActiveChat();
        }
        function renderChatThreadCard(container, thread, mode) {
            const button=document.createElement('button');
            button.className = mode === 'floating' ? 'floating-thread-btn' : 'chat-thread-card';
            button.type='button';
            if (thread.id===activeChatThreadId) button.classList.add('active');
            const main=document.createElement('div');
            const other=getOtherParticipant(thread);
            const title=document.createElement('strong');
            title.textContent=other ? other.displayName + ' (@' + other.username + ')' : 'Privater Chat';
            const last=document.createElement('small');
            last.textContent=thread.lastMessage ? formatMessagePreview(thread.lastMessage.body) : 'Noch keine Nachricht';
            const time=document.createElement('small');
            time.textContent='Aktualisiert: ' + (thread.updatedAt || thread.createdAt || '–');
            main.appendChild(title); main.appendChild(last); main.appendChild(time); button.appendChild(main);
            if ((thread.unreadCount || 0) > 0) { const badge=document.createElement('span'); badge.className='thread-unread'; badge.textContent=String(thread.unreadCount); button.appendChild(badge); }
            button.addEventListener('click', function(){ openChatThread(thread.id); });
            container.appendChild(button);
        }
        function renderActiveChat() {
            chatMessageList.textContent = '';
            const thread = getChatThread(activeChatThreadId);
            if (!thread) {
                chatActiveTitle.textContent = 'Kein Chat ausgewählt';
                chatActiveMeta.textContent = 'Wähle links einen Chat oder starte einen neuen Verlauf.';
                chatReplyForm.style.display = 'none';
                const empty=document.createElement('div'); empty.className='empty-message'; empty.textContent='Noch kein Chat geöffnet.'; chatMessageList.appendChild(empty);
                return;
            }
            const other = getOtherParticipant(thread);
            chatActiveTitle.textContent = other ? 'Chat mit ' + other.displayName : 'Privater Chat';
            chatActiveMeta.textContent = '1:1-Verlauf · ' + getMessagesForThread(thread.id).length + ' Nachricht(en)';
            chatReplyForm.style.display = other ? 'grid' : 'none';
            renderChatMessages(chatMessageList, thread.id, true);
        }
        function renderChatMessages(container, threadId, withDelete) {
            container.textContent='';
            const messages = getMessagesForThread(threadId);
            if (messages.length === 0) {
                const empty=document.createElement('div'); empty.className='empty-message'; empty.textContent='Noch keine Nachrichten in diesem Chat.'; container.appendChild(empty); return;
            }
            messages.forEach(function(message){
                const bubble=document.createElement('div'); bubble.className='chat-bubble'; if (appData.currentUser && message.senderId===appData.currentUser.id) bubble.classList.add('own');
                const body=document.createElement('p'); body.textContent=message.body;
                const meta=document.createElement('small'); meta.textContent=getUserName(message.senderId) + ' · ' + message.createdAt;
                bubble.appendChild(body); bubble.appendChild(meta);
                const stateText = deliveryText(message);
                if (stateText) { const state=document.createElement('span'); state.className='message-state'; state.textContent=stateText; bubble.appendChild(state); }
                if (withDelete === true) { const actions=document.createElement('div'); actions.className='inline-actions'; actions.style.marginTop='8px'; const del=document.createElement('button'); del.className='danger'; del.type='button'; del.textContent='Löschen'; del.addEventListener('click', function(){ deleteMessage(message.id); }); actions.appendChild(del); bubble.appendChild(actions); }
                container.appendChild(bubble);
            });
            const thread = getChatThread(threadId);
            const other = getOtherParticipant(thread);
            appendTypingIndicator(container, getTypingUsers('chat', threadId, other ? other.id : ''));
            container.scrollTop = container.scrollHeight;
        }
        function renderEmojiBar(container, target) {
            if (!container || container.dataset.ready === '1') return;
            chatEmojis.forEach(function(emoji){ const btn=document.createElement('button'); btn.type='button'; btn.className='emoji-btn'; btn.textContent=emoji; btn.addEventListener('click', function(){ insertAtCursor(target, emoji); }); container.appendChild(btn); });
            container.dataset.ready = '1';
        }
        function insertAtCursor(input, text) {
            if (!input) return;
            const start = input.selectionStart || input.value.length;
            const end = input.selectionEnd || input.value.length;
            input.value = input.value.slice(0, start) + text + input.value.slice(end);
            const next = start + text.length;
            input.focus();
            input.setSelectionRange(next, next);
        }
        function renderFamilyChat() {
            const totalUnread = appData.totalUnreadFamilyChats || 0;
            familyChatUnreadBadge.textContent = String(totalUnread);
            familyChatUnreadBadge.classList.toggle('empty', totalUnread === 0);
            familyChatListBadgeText.textContent = totalUnread > 0 ? '(' + totalUnread + ' ungelesen)' : '(keine ungelesenen)';
            renderEmojiBar(familyChatEmojiBar, familyChatReplyInput);
            familyChatThreadList.textContent = '';
            const threads = appData.familyChatThreads || [];
            familyChatThreadEmpty.style.display = threads.length === 0 ? 'block' : 'none';
            if (activeFamilyChatThreadId && !getFamilyChatThread(activeFamilyChatThreadId)) activeFamilyChatThreadId = null;
            if (!activeFamilyChatThreadId && threads.length > 0) activeFamilyChatThreadId = threads[0].id;
            threads.forEach(function(thread){ renderFamilyChatThreadCard(thread); });
            renderActiveFamilyChat();
        }
        function renderFamilyChatThreadCard(thread) {
            const button=document.createElement('button');
            button.className='chat-thread-card';
            button.type='button';
            if (thread.id===activeFamilyChatThreadId) button.classList.add('active');
            const main=document.createElement('div');
            const title=document.createElement('strong');
            title.textContent = thread.title || ('Familienchat · ' + getFamilyName(thread.familyId));
            const last=document.createElement('small');
            last.textContent=thread.lastMessage ? getUserName(thread.lastMessage.senderId) + ': ' + formatMessagePreview(thread.lastMessage.body) : 'Noch keine Nachricht';
            const time=document.createElement('small');
            time.textContent=(thread.participantCount || thread.participantIds.length) + ' Teilnehmer · aktualisiert: ' + (thread.updatedAt || thread.createdAt || '–');
            main.appendChild(title); main.appendChild(last); main.appendChild(time); button.appendChild(main);
            if ((thread.unreadCount || 0) > 0) { const badge=document.createElement('span'); badge.className='thread-unread'; badge.textContent=String(thread.unreadCount); button.appendChild(badge); }
            button.addEventListener('click', function(){ openFamilyChatThread(thread.id); });
            familyChatThreadList.appendChild(button);
        }
        function renderActiveFamilyChat() {
            familyChatMessageList.textContent = '';
            const thread = getFamilyChatThread(activeFamilyChatThreadId);
            if (!appData.currentUser || !appData.currentUser.familyId) {
                familyChatActiveTitle.textContent = 'Kein Haushalt zugeordnet';
                familyChatActiveMeta.textContent = 'Der Familienchat ist nur für Nutzer mit Haushaltsgruppe sichtbar.';
                familyChatReplyForm.style.display = 'none';
                const empty=document.createElement('div'); empty.className='empty-message'; empty.textContent='Bitte zuerst einem Haushalt beitreten oder von einem Admin zuordnen lassen.'; familyChatMessageList.appendChild(empty);
                return;
            }
            if (!thread) {
                familyChatActiveTitle.textContent = 'Kein Familienchat verfügbar';
                familyChatActiveMeta.textContent = 'Der Haushalt benötigt mindestens zwei aktive Mitglieder.';
                familyChatReplyForm.style.display = 'none';
                const empty=document.createElement('div'); empty.className='empty-message'; empty.textContent='Sobald ein weiteres aktives Haushaltsmitglied vorhanden ist, wird der Familienchat automatisch bereitgestellt.'; familyChatMessageList.appendChild(empty);
                return;
            }
            familyChatActiveTitle.textContent = thread.title || ('Familienchat · ' + getFamilyName(thread.familyId));
            familyChatActiveMeta.textContent = (thread.participantCount || thread.participantIds.length) + ' Teilnehmer · ' + getMessagesForThread(thread.id).length + ' Nachricht(en)';
            familyChatReplyForm.style.display = 'grid';
            const messages = getMessagesForThread(thread.id);
            if (messages.length === 0) {
                const empty=document.createElement('div'); empty.className='empty-message'; empty.textContent='Noch keine Nachrichten im Familienchat.'; familyChatMessageList.appendChild(empty);
            }
            messages.forEach(function(message){
                const bubble=document.createElement('div'); bubble.className='chat-bubble'; if (appData.currentUser && message.senderId===appData.currentUser.id) bubble.classList.add('own');
                const body=document.createElement('p'); body.textContent=message.body;
                const meta=document.createElement('small'); meta.textContent=getUserName(message.senderId) + ' · ' + message.createdAt;
                bubble.appendChild(body); bubble.appendChild(meta);
                const stateText = deliveryText(message);
                if (stateText) { const state=document.createElement('span'); state.className='message-state'; state.textContent=stateText; bubble.appendChild(state); }
                const actions=document.createElement('div'); actions.className='inline-actions'; actions.style.marginTop='8px'; const del=document.createElement('button'); del.className='danger'; del.type='button'; del.textContent='Löschen'; del.addEventListener('click', function(){ deleteMessage(message.id); }); actions.appendChild(del); bubble.appendChild(actions);
                familyChatMessageList.appendChild(bubble);
            });
            appendTypingIndicator(familyChatMessageList, getTypingUsers('family', thread.id, ''));
            familyChatMessageList.scrollTop = familyChatMessageList.scrollHeight;
        }

        function renderFloatingChat() {
            const totalUnread = appData.totalUnreadChats || 0;
            floatingChatCount.textContent = String(totalUnread);
            floatingChatCount.style.display = totalUnread > 0 ? 'grid' : 'none';
            floatingChatPanel.classList.toggle('open', floatingChatOpen);
            floatingChatToggle.setAttribute('aria-expanded', floatingChatOpen ? 'true' : 'false');
            renderEmojiBar(floatingEmojiBar, floatingChatInput);
            floatingChatThreads.textContent='';
            const threads = appData.chatThreads || [];
            if (threads.length === 0) { const empty=document.createElement('div'); empty.className='empty-message'; empty.textContent='Noch keine Chats vorhanden.'; floatingChatThreads.appendChild(empty); }
            threads.forEach(function(thread){ renderChatThreadCard(floatingChatThreads, thread, 'floating'); });
            const thread = getChatThread(activeChatThreadId) || threads[0] || null;
            if (!activeChatThreadId && thread) activeChatThreadId = thread.id;
            if (!thread) {
                floatingChatTitle.textContent='Schnellchat';
                floatingChatSubtitle.textContent='Noch kein Verlauf';
                floatingChatMessages.textContent='';
                const empty=document.createElement('div'); empty.className='empty-message'; empty.textContent='Starte zuerst im Chat-Menü einen 1:1-Chat.'; floatingChatMessages.appendChild(empty);
                floatingChatForm.style.display='none';
                return;
            }
            const other = getOtherParticipant(thread);
            floatingChatTitle.textContent = other ? other.displayName : 'Schnellchat';
            floatingChatSubtitle.textContent = '1:1-Chat · ' + getMessagesForThread(thread.id).length + ' Nachricht(en)';
            floatingChatForm.style.display = other ? 'grid' : 'none';
            renderChatMessages(floatingChatMessages, thread.id, false);
        }

        function renderAdminMessageTable() {
            adminMessageTableBody.textContent='';
            let messages=(appData.adminMessages || []).slice();
            const filter=adminMessageFilter.value;
            if (filter==='own') messages=messages.filter((message)=>appData.currentUser && message.senderId===appData.currentUser.id);
            if (filter==='received') messages=messages.filter((message)=>appData.currentUser && message.recipientId===appData.currentUser.id);
            messages.reverse().forEach(function(message){
                const row=document.createElement('tr');
                [['time',message.createdAt],['from',getUserName(message.senderId)],['to',getUserName(message.recipientId)],['body',formatMessagePreview(message.body)]].forEach(function(pair){ const td=document.createElement('td'); td.textContent=pair[1]; row.appendChild(td); });
                const actions=document.createElement('td'); actions.className='inline-actions'; const del=document.createElement('button'); del.className='danger'; del.textContent='Löschen'; del.addEventListener('click', function(){ deleteMessage(message.id); }); actions.appendChild(del); row.appendChild(actions); adminMessageTableBody.appendChild(row);
            });
        }
        function pluralLabel(count, singular, plural) { return String(count) + ' ' + (count === 1 ? singular : plural); }
        function renderAdminSummary() {
            const familyCount = (appData.families || []).length;
            const userCount = (appData.users || []).length;
            const listCount = (appData.lists || []).length;
            const todoCount = (appData.adminTodos || appData.todos || []).length;
            const messageCount = (appData.adminMessages || []).length;
            adminFamiliesCount.textContent = String(familyCount);
            adminUsersCount.textContent = String(userCount);
            adminListsCount.textContent = String(listCount);
            adminTodosCount.textContent = String(todoCount);
            adminMessagesCount.textContent = String(messageCount);
            adminFamiliesPill.textContent = pluralLabel(familyCount, 'Haushalt', 'Haushalte');
            adminUsersPill.textContent = pluralLabel(userCount, 'Benutzer', 'Benutzer');
            adminListsPill.textContent = pluralLabel(listCount, 'Liste', 'Listen');
            adminTodosPill.textContent = pluralLabel(todoCount, 'Todo', 'Todos');
            adminMessagesPill.textContent = pluralLabel(messageCount, 'Nachricht', 'Nachrichten');
        }
        function renderAdminPanel() { adminPanel.classList.toggle('visible', appData.isAdmin === true); if (appData.isAdmin !== true) return; renderAdminSummary(); renderFamilyTable(); renderUserTable(); renderAdminListTable(); renderAdminTodoTable(); renderAdminMessageTable(); }
        function renderFamilyTable() { familyTableBody.textContent=''; appData.families.forEach(function(family){ const row=document.createElement('tr'); const name=document.createElement('td'); name.textContent=family.name; const members=document.createElement('td'); members.textContent=appData.users.filter(function(user){ return user.familyId === family.id; }).length; const lists=document.createElement('td'); lists.textContent=appData.lists.filter(function(list){ return list.familyId === family.id; }).length; const actions=document.createElement('td'); actions.className='inline-actions'; const rename=document.createElement('button'); rename.className='small-btn'; rename.textContent='Umbenennen'; rename.addEventListener('click', function(){ adminRenameFamily(family.id); }); const del=document.createElement('button'); del.className='danger'; del.textContent='Löschen'; del.addEventListener('click', function(){ adminDeleteFamily(family.id); }); actions.appendChild(rename); actions.appendChild(del); row.appendChild(name); row.appendChild(members); row.appendChild(lists); row.appendChild(actions); familyTableBody.appendChild(row); }); }
        function renderUserTable() { userTableBody.textContent=''; appData.users.forEach(function(user){ const row=document.createElement('tr'); const name=document.createElement('td'); name.textContent=user.displayName + ' (@' + user.username + ')'; const role=document.createElement('td'); role.textContent=user.role; const active=document.createElement('td'); active.textContent=user.active ? 'aktiv' : 'deaktiviert'; const familyCell=document.createElement('td'); const select=document.createElement('select'); const emptyOption=document.createElement('option'); emptyOption.value=''; emptyOption.textContent='kein Haushalt'; select.appendChild(emptyOption); appData.families.forEach(function(family){ const opt=document.createElement('option'); opt.value=family.id; opt.textContent=family.name; if (user.familyId === family.id) opt.selected=true; select.appendChild(opt); }); select.addEventListener('change', function(){ adminUpdateUserFamily(user.id, select.value); }); familyCell.appendChild(select); const actions=document.createElement('td'); actions.className='inline-actions'; const roleBtn=document.createElement('button'); roleBtn.className='small-btn'; roleBtn.textContent=user.role==='admin'?'zu Nutzer':'zu Admin'; roleBtn.addEventListener('click', function(){ adminUpdateRole(user.id, user.role==='admin'?'user':'admin'); }); const activeBtn=document.createElement('button'); activeBtn.className='warning'; activeBtn.textContent=user.active?'Deaktivieren':'Aktivieren'; activeBtn.addEventListener('click', function(){ adminToggleActive(user.id); }); const del=document.createElement('button'); del.className='danger'; del.textContent='Löschen'; del.addEventListener('click', function(){ adminDeleteUser(user.id); }); actions.appendChild(roleBtn); actions.appendChild(activeBtn); actions.appendChild(del); row.appendChild(name); row.appendChild(role); row.appendChild(active); row.appendChild(familyCell); row.appendChild(actions); userTableBody.appendChild(row); }); }
        function renderAdminListTable() { adminListTableBody.textContent=''; let lists=appData.lists.slice(); const filter=adminListFilter.value; if (filter==='own') lists=lists.filter(function(list){ return appData.currentUser && list.ownerId===appData.currentUser.id; }); if (filter==='shared') lists=lists.filter(function(list){ return list.isShared; }); if (filter==='shopping') lists=lists.filter(function(list){ return list.listType==='shopping'; }); if (filter==='household') lists=lists.filter(function(list){ return list.listType==='household'; }); if (filter==='private') lists=lists.filter(function(list){ return !list.isShared; }); lists.forEach(function(list){ const row=document.createElement('tr'); const name=document.createElement('td'); name.textContent=list.name; const type=document.createElement('td'); const typeSelect=document.createElement('select'); [['shopping','Einkaufsliste'],['household','Haushaltsliste'],['other','Sonstige Liste']].forEach(function(pair){ const opt=document.createElement('option'); opt.value=pair[0]; opt.textContent=pair[1]; if ((list.listType || 'shopping')===pair[0]) opt.selected=true; typeSelect.appendChild(opt); }); type.appendChild(typeSelect); const owner=document.createElement('td'); const ownerSelect=document.createElement('select'); appData.activeUsers.forEach(function(user){ const opt=document.createElement('option'); opt.value=user.id; opt.textContent=user.displayName + ' – ' + getFamilyName(user.familyId); if (user.id===list.ownerId) opt.selected=true; ownerSelect.appendChild(opt); }); owner.appendChild(ownerSelect); const family=document.createElement('td'); family.textContent=getFamilyName(list.familyId); const status=document.createElement('td'); status.textContent=list.isShared?'gemeinschaftlich':'privat'; const actions=document.createElement('td'); actions.className='inline-actions'; const typeBtn=document.createElement('button'); typeBtn.className='small-btn'; typeBtn.textContent='Typ speichern'; typeBtn.addEventListener('click', function(){ adminUpdateListType(list.id, typeSelect.value); }); const ownerBtn=document.createElement('button'); ownerBtn.className='small-btn'; ownerBtn.textContent='Besitzer speichern'; ownerBtn.addEventListener('click', function(){ adminUpdateListOwner(list.id, ownerSelect.value); }); const scopeBtn=document.createElement('button'); scopeBtn.className='warning'; scopeBtn.textContent=list.isShared?'Privat':'Teilen'; scopeBtn.addEventListener('click', function(){ adminToggleListVisibility(list); }); actions.appendChild(typeBtn); actions.appendChild(ownerBtn); actions.appendChild(scopeBtn); row.appendChild(name); row.appendChild(type); row.appendChild(owner); row.appendChild(family); row.appendChild(status); row.appendChild(actions); adminListTableBody.appendChild(row); }); }
        function renderAdminTodoTable() { adminTodoTableBody.textContent=''; let todos=(appData.adminTodos || appData.todos || []).slice(); const filter=adminTodoFilter.value; if (filter==='private') todos=todos.filter((todo)=>todo.scope==='private'); if (filter==='family') todos=todos.filter((todo)=>todo.scope==='family'); if (filter==='open') todos=todos.filter((todo)=>todo.status==='open'); if (filter==='done') todos=todos.filter((todo)=>todo.status==='done'); todos.forEach(function(todo){ const row=document.createElement('tr'); [['title',todo.title],['scope',todoScopeLabel(todo.scope)],['priority',priorityLabel(todo.priority)],['assigned',getUserName(todo.assignedTo)],['due',formatDate(todo.dueAt)],['reminder',formatDate(todo.reminderAt)],['status',todoStatusLabel(todo.status)],['comments',String((todo.comments || []).length)]].forEach(function(pair){ const td=document.createElement('td'); td.textContent=pair[1]; row.appendChild(td); }); const actions=document.createElement('td'); actions.className='inline-actions'; const toggle=document.createElement('button'); toggle.className='small-btn'; toggle.textContent=todo.status==='done'?'Öffnen':'Erledigt'; toggle.addEventListener('click', function(){ toggleTodoDone(todo.id); }); const edit=document.createElement('button'); edit.className='small-btn'; edit.textContent='Titel ändern'; edit.addEventListener('click', function(){ const title=prompt('Neuer Aufgabentitel:', todo.title); if (title !== null) updateTodo(todo.id, { title:title.trim(), scope:todo.scope, priority:todo.priority, assignedTo:todo.assignedTo, dueAt:todo.dueAt, reminderAt:todo.reminderAt, calendarDate:todo.calendarDate }); }); const del=document.createElement('button'); del.className='danger'; del.textContent='Löschen'; del.addEventListener('click', function(){ deleteTodo(todo.id); }); actions.appendChild(toggle); actions.appendChild(edit); actions.appendChild(del); row.appendChild(actions); adminTodoTableBody.appendChild(row); }); }
        function switchModule(viewName) {
            if (viewName === 'admin' && appData.isAdmin !== true) viewName = 'dashboard';
            activeModule = viewName;
            const labels = {
                dashboard:['Dashboard','Übersicht über Listen, Todos, Nachrichten und Administration'],
                lists:['Listen','Einkaufs-, Haushalts- und Gemeinschaftslisten'],
                todos:['Todos','Persönliche Aufgaben und Familienaufgaben'],
                messages:['Nachrichten','Private Nachrichten und ungelesene Badges'],
                chats:['Chats','Direkter 1:1-Chat mit Emoji-Funktion und Schnellfenster'],
                calendar:['Kalender','Eigene Termine und Familientermine verwalten'],
                familychat:['Familienchat','Gemeinsamer Gruppenchat für deinen Haushalt'],
                admin:['Administration','Haushalte, Benutzer, Listen, Todos und Nachrichten verwalten']
            };
            Object.keys(modulePages).forEach(function(key){ if (modulePages[key]) modulePages[key].classList.toggle('active', key === viewName); });
            document.querySelectorAll('.app-nav-btn[data-view]').forEach(function(button){ button.classList.toggle('active', button.dataset.view === viewName); });
            topbarPageTitle.textContent = labels[viewName][0];
            topbarPageSubtitle.textContent = labels[viewName][1];
            window.scrollTo({ top:0, behavior:'smooth' });
        }
        function switchAdminTab(tabName) {
            const sections = { families:adminTabFamilies, users:adminTabUsers, lists:adminTabLists, todos:adminTabTodos, messages:adminTabMessages };
            adminTabButtons.forEach(function(button){
                const active = button.dataset.adminTab === tabName;
                button.classList.toggle('active', active);
                button.setAttribute('aria-selected', active ? 'true' : 'false');
            });
            Object.keys(sections).forEach(function(key){
                const active = key === tabName;
                sections[key].classList.toggle('active', active);
                sections[key].setAttribute('aria-hidden', active ? 'false' : 'true');
            });
        }
        function showStatus(message, isError) { statusMessage.textContent=message; statusMessage.classList.toggle('error', isError===true); window.clearTimeout(showStatus.timeoutId); showStatus.timeoutId=window.setTimeout(function(){ statusMessage.textContent=''; statusMessage.classList.remove('error'); }, 3500); }

        listForm.addEventListener('submit', function(event){ event.preventDefault(); createList(); });
        todoForm.addEventListener('submit', function(event){ event.preventDefault(); createTodo(); });
        messageForm.addEventListener('submit', function(event){ event.preventDefault(); const body=messageBodyInput.value; sendPersonalMessage('send_message', messageRecipientInput.value, body).then(function(){ messageBodyInput.value=''; }); });
        chatStartForm.addEventListener('submit', function(event){ event.preventDefault(); const body=chatStartInput.value; sendPersonalMessage('send_chat_message', chatRecipientInput.value, body).then(function(){ chatStartInput.value=''; switchModule('chats'); }); });
        chatReplyForm.addEventListener('submit', function(event){ event.preventDefault(); const thread=getChatThread(activeChatThreadId); const other=getOtherParticipant(thread); if (!thread || !other) { showStatus('Kein Chat ausgewählt.', true); return; } const body=chatReplyInput.value; sendPersonalMessage('send_chat_message', other.id, body).then(function(){ chatReplyInput.value=''; }); });
        calendarForm.addEventListener('submit', function(event){ event.preventDefault(); createCalendarEventFromForm(); });
        calendarPrevMonth.addEventListener('click', function(){ if (calendarViewMode === 'week') calendarCursor = addDays(calendarCursor, -7); else calendarCursor.setMonth(calendarCursor.getMonth()-1); renderCalendar(); });
        calendarNextMonth.addEventListener('click', function(){ if (calendarViewMode === 'week') calendarCursor = addDays(calendarCursor, 7); else calendarCursor.setMonth(calendarCursor.getMonth()+1); renderCalendar(); });
        calendarTodayButton.addEventListener('click', function(){ calendarCursor = new Date(); calendarCursor.setDate(1); renderCalendar(); });
        calendarMonthMode.addEventListener('click', function(){ calendarViewMode='month'; calendarCursor.setDate(1); renderCalendar(); });
        calendarWeekMode.addEventListener('click', function(){ calendarViewMode='week'; renderCalendar(); });
        calendarScopeFilter.addEventListener('change', renderCalendar);
        calendarSpecialFilter.addEventListener('change', renderCalendar);
        calendarSearchFilter.addEventListener('input', renderCalendar);
        calendarModalSave.addEventListener('click', saveCalendarModal);
        calendarModalDelete.addEventListener('click', function(){ if (activeCalendarEventId) deleteCalendarEvent(activeCalendarEventId); });
        calendarModalClose.addEventListener('click', closeCalendarModal);
        calendarModalCancel.addEventListener('click', closeCalendarModal);
        calendarModal.addEventListener('click', function(event){ if (event.target === calendarModal) closeCalendarModal(); });
        familyChatReplyForm.addEventListener('submit', function(event){ event.preventDefault(); const body=familyChatReplyInput.value; sendFamilyChatMessage(body).then(function(){ familyChatReplyInput.value=''; }); });
        floatingChatForm.addEventListener('submit', function(event){ event.preventDefault(); const thread=getChatThread(activeChatThreadId); const other=getOtherParticipant(thread); if (!thread || !other) { showStatus('Kein Schnellchat ausgewählt.', true); return; } const body=floatingChatInput.value; sendPersonalMessage('send_chat_message', other.id, body).then(function(){ floatingChatInput.value=''; }); });
        floatingChatToggle.addEventListener('click', function(){ floatingChatOpen = !floatingChatOpen; renderFloatingChat(); });
        floatingChatClose.addEventListener('click', function(){ floatingChatOpen = false; renderFloatingChat(); });
        chatOpenFloatingButton.addEventListener('click', function(){ floatingChatOpen = true; renderFloatingChat(); });
        replyForm.addEventListener('submit', function(event){ event.preventDefault(); const thread=getMessageThread(activeMessageThreadId); const other=getOtherParticipant(thread); if (!thread || !other) { showStatus('Kein Nachrichtenverlauf ausgewählt.', true); return; } const body=replyInput.value; sendPersonalMessage('send_message', other.id, body).then(function(){ replyInput.value=''; }); });
        todoFamilyInput.addEventListener('change', refreshCreateTodoAssignees);
        itemForm.addEventListener('submit', function(event){ event.preventDefault(); addItem(); });
        toggleVisibilityButton.addEventListener('click', updateActiveListVisibility);
        updateListTypeButton.addEventListener('click', updateActiveListType);
        deleteListButton.addEventListener('click', deleteActiveList);
        updateOwnerButton.addEventListener('click', updateActiveListOwner);
        familyForm.addEventListener('submit', function(event){ event.preventDefault(); adminCreateFamily(); });
        adminListFilter.addEventListener('change', renderAdminListTable);
        adminTodoFilter.addEventListener('change', renderAdminTodoTable);
        adminMessageFilter.addEventListener('change', renderAdminMessageTable);
        adminTabButtons.forEach(function(button){ button.addEventListener('click', function(){ switchAdminTab(button.dataset.adminTab); }); });
        appNavButtons.forEach(function(button){ button.addEventListener('click', function(){ switchModule(button.dataset.view); }); });
        if (logoutForm) { logoutForm.addEventListener('submit', beginLogout); }
        registerTypingInput(messageBodyInput, 'message', function(){ return { recipientId:messageRecipientInput.value || '', threadId:'' }; });
        registerTypingInput(replyInput, 'message', function(){ const thread=getMessageThread(activeMessageThreadId); const other=getOtherParticipant(thread); return { threadId:thread ? thread.id : '', recipientId:other ? other.id : '' }; });
        registerTypingInput(chatStartInput, 'chat', function(){ return { recipientId:chatRecipientInput.value || '', threadId:'' }; });
        registerTypingInput(chatReplyInput, 'chat', function(){ const thread=getChatThread(activeChatThreadId); const other=getOtherParticipant(thread); return { threadId:thread ? thread.id : '', recipientId:other ? other.id : '' }; });
        registerTypingInput(floatingChatInput, 'chat', function(){ const thread=getChatThread(activeChatThreadId); const other=getOtherParticipant(thread); return { threadId:thread ? thread.id : '', recipientId:other ? other.id : '' }; });
        registerTypingInput(familyChatReplyInput, 'family', function(){ const thread=getFamilyChatThread(activeFamilyChatThreadId); return { threadId:thread ? thread.id : '', recipientId:'' }; });
        appRefreshTimer = window.setInterval(refreshDataInBackground, 6500);
        document.addEventListener('visibilitychange', function(){ if (!document.hidden && !isLoggingOut) refreshDataInBackground(); });
        switchAdminTab('families');
        switchModule('dashboard');
        loadData();
    </script>
</body>
</html>
