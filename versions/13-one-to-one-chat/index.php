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
    <title><?= e('HaushaltsPilot – Teil 13 Einzelchat') ?></title>
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
        @media(max-width:1100px){ .small-boxes{grid-template-columns:repeat(2,1fr);} .dashboard-hero,.dashboard-grid,.layout,.message-layout,.todo-board,.chat-page-grid{grid-template-columns:1fr;} .todo-form,.todo-edit-form,.todo-dashboard{grid-template-columns:1fr;} }
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
                <button class="app-nav-btn admin-only" id="navAdminItem" type="button" data-view="admin"><span class="nav-left"><span class="nav-icon">⚙</span><span>Administration</span></span><span class="nav-badge empty" id="navAdminBadge">0</span></button>
            </nav>
            <div class="sidebar-footer">Teil 13 · Direkter 1:1-Chat mit Chatfenster, Emoji-Auswahl, Badges und Chat-Menü.</div>
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
                    <form method="post" action="auth.php">
                        <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                        <input type="hidden" name="action" value="logout">
                        <button class="logout" type="submit">Logout</button>
                    </form>
                </div>
            </header>

            <main class="content">
                <section class="module-page active" id="dashboardPage">
                    <div class="dashboard-hero">
                        <section class="hero-card">
                            <h2>HaushaltsPilot</h2>
                            <p>Teil 13 erweitert das vorhandene Nachrichtenmodell zu einem direkten 1:1-Chat: mit eigener Chat-Ansicht, Chatliste, schwebendem Chatfenster unten rechts, Emoji-Auswahl und Ungelesen-Badges.</p>
                            <div class="hero-actions">
                                <button class="dashboard-action" type="button" data-view="lists">Listen öffnen</button>
                                <button class="dashboard-action" type="button" data-view="todos">Todos öffnen</button>
                                <button class="dashboard-action" type="button" data-view="messages">Nachrichten öffnen</button>
                                <button class="dashboard-action" type="button" data-view="chats">Chats öffnen</button>
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
                        <div class="small-box box-yellow"><strong id="dashUnreadCount">0</strong><span>Ungelesene Nachrichten</span><small id="dashMessageDetail">Chats & Messages</small><span class="big-icon">✉</span></div>
                        <div class="small-box box-red"><strong id="dashAdminCount">0</strong><span>Admin-Objekte</span><small id="dashAdminDetail">nur für Admins relevant</small><span class="big-icon">⚙</span></div>
                    </section>

                    <section class="dashboard-grid">
                        <div class="panel">
                            <h2>Modulübersicht</h2>
                            <div class="dashboard-card-grid">
                                <article class="dashboard-card"><strong>Einkaufs- und Haushaltslisten</strong><span id="dashListSummary">Noch keine Daten geladen.</span></article>
                                <article class="dashboard-card"><strong>Aufgaben</strong><span id="dashTodoSummary">Noch keine Daten geladen.</span></article>
                                <article class="dashboard-card"><strong>Nachrichten</strong><span id="dashMessageSummary">Noch keine Daten geladen.</span></article>
                                <article class="dashboard-card"><strong>Einzelchat</strong><span>Teil 13 nutzt das Nachrichtenmodell jetzt als echten 1:1-Chat. Teil 14 kann darauf mit dem Familienchat aufbauen.</span></article>
                            </div>
                        </div>
                        <div class="panel">
                            <h2>Schnellzugriff</h2>
                            <div class="status-list">
                                <button class="dashboard-action box-blue" type="button" data-view="lists">Listen verwalten</button>
                                <button class="dashboard-action box-green" type="button" data-view="todos">Aufgaben bearbeiten</button>
                                <button class="dashboard-action box-cyan" type="button" data-view="messages">Private Nachrichten</button>
                                <button class="dashboard-action box-purple" type="button" data-view="chats">1:1-Chats</button>
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
                    <p>Teil 12 bleibt als Personal-Message-Modul erhalten. Teil 13 ergänzt daneben eine Chat-Oberfläche für denselben privaten 1:1-Verlauf.</p>
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
                    <p>Teil 13 vertieft die privaten Nachrichten zu einem direkten 1:1-Chat. Die Chats bleiben serverseitig Threads, werden aber wie ein Messenger mit Verlauf, Schnellantwort und Emoji-Auswahl bedient.</p>
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

                <p class="footer-note">Teil 14 kann darauf mit einem gemeinsamen Familienchat aufbauen.</p>
            </main>
        </div>
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
        const modulePages = { dashboard:byId('dashboardPage'), lists:byId('listsPage'), todos:byId('todosPage'), messages:byId('messagesPage'), chats:byId('chatsPage'), admin:byId('adminPanel') };
        const topbarPageTitle = byId('topbarPageTitle');
        const topbarPageSubtitle = byId('topbarPageSubtitle');
        const sidebarUserLabel = byId('sidebarUserLabel');
        const sidebarFamilyLabel = byId('sidebarFamilyLabel');
        const navListBadge = byId('navListBadge');
        const navTodoBadge = byId('navTodoBadge');
        const navUnreadBadge = byId('navUnreadBadge');
        const navChatBadge = byId('navChatBadge');
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
        const dashMessageDetail = byId('dashMessageDetail');
        const dashAdminCount = byId('dashAdminCount');
        const dashAdminDetail = byId('dashAdminDetail');
        const dashListSummary = byId('dashListSummary');
        const dashTodoSummary = byId('dashTodoSummary');
        const dashMessageSummary = byId('dashMessageSummary');

        const currentUserLabel = byId('currentUserLabel');
        const currentFamilyLabel = byId('currentFamilyLabel');
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

        let appData = { currentUser:null, currentFamily:null, lists:[], activeListId:null, todos:[], adminTodos:[], messageThreads:[], chatThreads:[], messages:[], totalUnreadMessages:0, totalUnreadChats:0, adminMessages:[], users:[], activeUsers:[], families:[], isAdmin:false };
        let activeMessageThreadId = null;
        let activeChatThreadId = null;
        let floatingChatOpen = false;
        const chatEmojis = ['😀','🙂','😂','👍','🙏','❤️','🔥','✅','⚠️','🎉','🙈','😅'];

        async function apiGet(action) {
            const response = await fetch('api.php?action=' + encodeURIComponent(action), { method:'GET', credentials:'same-origin', headers:{ Accept:'application/json' } });
            return handleApiResponse(response);
        }
        async function apiPost(action, payload) {
            const response = await fetch('api.php?action=' + encodeURIComponent(action), { method:'POST', credentials:'same-origin', headers:{ Accept:'application/json', 'Content-Type':'application/json', 'X-CSRF-Token':csrfToken }, body:JSON.stringify(payload || {}) });
            return handleApiResponse(response);
        }
        async function handleApiResponse(response) {
            let result;
            try { result = await response.json(); } catch (error) { throw new Error('Serverantwort war kein gültiges JSON.'); }
            if (!response.ok || result.success !== true) throw new Error(result.message || 'Unbekannter Serverfehler.');
            return result;
        }
        async function loadData() {
            try { const result = await apiGet('load'); appData = result.data; renderApp(); }
            catch (error) { showStatus(error.message, true); }
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
        function formatMessagePreview(value) { if (!value) return 'Noch keine Nachricht'; return value.length > 72 ? value.slice(0, 72) + '…' : value; }
        function getMessageThread(threadId) { return (appData.messageThreads || []).find((thread) => thread.id === threadId) || null; }
        function getChatThread(threadId) { return (appData.chatThreads || []).find((thread) => thread.id === threadId) || null; }
        function getThread(threadId) { return getMessageThread(threadId) || getChatThread(threadId); }
        function getMessagesForThread(threadId) { return (appData.messages || []).filter((message) => message.threadId === threadId); }
        function getOtherParticipant(thread) { if (!thread || !appData.currentUser) return null; const id = thread.participantIds.find((participantId) => participantId !== appData.currentUser.id) || thread.otherParticipantId || ''; return getUser(id); }
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
        async function deleteMessage(messageId) {
            if (!confirm('Nachricht wirklich löschen?')) return;
            try { const result = await apiPost('delete_message', { messageId }); appData = result.data; renderApp(); showStatus(result.message); }
            catch(error){ showStatus(error.message, true); }
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

        function renderApp() { renderCurrentUser(); renderUserSelects(); renderListGroups(); renderActiveList(); renderTodos(); renderMessages(); renderChats(); renderFloatingChat(); renderAdminPanel(); renderDashboardShell(); }
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
        function renderDashboardShell() {
            const lists = appData.lists || [];
            const todos = appData.todos || [];
            const totalItems = lists.reduce(function(sum, list){ return sum + ((list.items || []).length); }, 0);
            const openTodos = todos.filter(function(todo){ return todo.status !== 'done'; }).length;
            const doneTodos = todos.length - openTodos;
            const unreadMessages = Number(appData.totalUnreadMessages || 0);
            const unreadChats = Number(appData.totalUnreadChats || 0);
            const unread = unreadMessages + unreadChats;
            const adminObjectCount = (appData.families || []).length + (appData.users || []).length + lists.length + (appData.adminTodos || todos).length + (appData.adminMessages || []).length;
            setBadge(navListBadge, lists.length);
            setBadge(navTodoBadge, openTodos);
            setBadge(navUnreadBadge, unreadMessages);
            setBadge(navChatBadge, unreadChats);
            setBadge(navAdminBadge, adminObjectCount);
            [navAdminItem, dashboardAdminAction, dashboardAdminQuick].forEach(function(element){ if (element) element.classList.toggle('visible', appData.isAdmin === true); });
            dashCurrentUser.textContent = appData.currentUser ? appData.currentUser.displayName : '–';
            dashCurrentFamily.textContent = appData.currentUser ? getFamilyName(appData.currentUser.familyId) : '–';
            dashCurrentRole.textContent = appData.currentUser ? appData.currentUser.role : '–';
            dashListCount.textContent = String(lists.length);
            dashListDetail.textContent = totalItems + ' Artikel insgesamt';
            dashTodoOpenCount.textContent = String(openTodos);
            dashTodoDetail.textContent = doneTodos + ' erledigt';
            dashUnreadCount.textContent = String(unread);
            dashMessageDetail.textContent = unreadMessages + ' Nachrichten · ' + unreadChats + ' Chats';
            dashAdminCount.textContent = String(appData.isAdmin === true ? adminObjectCount : 0);
            dashAdminDetail.textContent = appData.isAdmin === true ? 'Haushalte, Nutzer, Listen, Todos, Nachrichten' : 'nur für Admins sichtbar';
            dashListSummary.textContent = lists.length + ' Listen mit ' + totalItems + ' Artikeln. Aktive Liste: ' + (getActiveList() ? getActiveList().name : 'keine');
            dashTodoSummary.textContent = todos.length + ' Aufgaben, davon ' + openTodos + ' offen und ' + doneTodos + ' erledigt.';
            dashMessageSummary.textContent = unreadMessages + ' ungelesene private Nachricht(en) in ' + (appData.messageThreads || []).length + ' Nachrichtenverlauf/-verläufen und ' + unreadChats + ' ungelesene Chatnachricht(en) in ' + (appData.chatThreads || []).length + ' Chatverlauf/-verläufen.';
        }
        function renderUserSelects() {
            adminCreateOwnerBox.classList.toggle('visible', appData.isAdmin === true);
            listOwnerInput.textContent='';
            activeListOwnerSelect.textContent='';
            messageRecipientInput.textContent='';
            chatRecipientInput.textContent='';
            appData.activeUsers.forEach(function(user){
                const label = user.displayName + ' (@' + user.username + ') – ' + getFamilyName(user.familyId);
                const option = document.createElement('option'); option.value=user.id; option.textContent=label; if (appData.currentUser && user.id === appData.currentUser.id) option.selected=true; listOwnerInput.appendChild(option);
                const option2 = document.createElement('option'); option2.value=user.id; option2.textContent=label; activeListOwnerSelect.appendChild(option2);
                if (!appData.currentUser || user.id !== appData.currentUser.id) {
                    const msgOption = document.createElement('option'); msgOption.value=user.id; msgOption.textContent=label; messageRecipientInput.appendChild(msgOption);
                    const chatOption = document.createElement('option'); chatOption.value=user.id; chatOption.textContent=label; chatRecipientInput.appendChild(chatOption);
                }
            });
            if (messageRecipientInput.options.length === 0) {
                const empty = document.createElement('option'); empty.value=''; empty.textContent='Kein anderer aktiver Nutzer vorhanden'; messageRecipientInput.appendChild(empty);
                const chatEmpty = document.createElement('option'); chatEmpty.value=''; chatEmpty.textContent='Kein anderer aktiver Nutzer vorhanden'; chatRecipientInput.appendChild(chatEmpty);
            }
            refreshCreateTodoAssignees();
        }
        function isSharedList(list) {
            return list.isShared === true || list.isShared === 1 || list.isShared === '1' || list.isShared === 'true';
        }
        function isSameFamilyList(list) {
            return appData.currentUser && list.familyId !== '' && list.familyId === appData.currentUser.familyId;
        }
        function renderListGroups() {
            myListButtons.textContent='';
            sharedShoppingListButtons.textContent='';
            sharedHouseholdListButtons.textContent='';
            sharedOtherListButtons.textContent='';
            otherListButtons.textContent='';

            const mine=[], sharedShopping=[], sharedHousehold=[], sharedOther=[], other=[];

            appData.lists.forEach(function(list){
                const isSharedInMyFamily = isSharedList(list) && isSameFamilyList(list);
                const isOwnedByMe = appData.currentUser && list.ownerId === appData.currentUser.id;

                if (isSharedInMyFamily) {
                    if (list.listType === 'household') sharedHousehold.push(list);
                    else if (list.listType === 'other') sharedOther.push(list);
                    else sharedShopping.push(list);
                    return;
                }

                if (isOwnedByMe) {
                    mine.push(list);
                    return;
                }

                other.push(list);
            });

            renderListButtonGroup(myListButtons, mine);
            renderListButtonGroup(sharedShoppingListButtons, sharedShopping);
            renderListButtonGroup(sharedHouseholdListButtons, sharedHousehold);
            renderListButtonGroup(sharedOtherListButtons, sharedOther);
            renderListButtonGroup(otherListButtons, other);
            myListCount.textContent=mine.length;
            sharedShoppingListCount.textContent=sharedShopping.length;
            sharedHouseholdListCount.textContent=sharedHousehold.length;
            sharedOtherListCount.textContent=sharedOther.length;
            otherListCount.textContent=other.length;
            adminOtherListsGroup.classList.toggle('visible', appData.isAdmin === true);
        }
        function renderListButtonGroup(container, lists) { if (lists.length === 0) { const empty = document.createElement('div'); empty.className='empty-message'; empty.textContent='Keine Listen vorhanden.'; container.appendChild(empty); return; } lists.forEach(function(list){ const button = document.createElement('button'); button.className='list-button'; button.type='button'; if (list.id === appData.activeListId) button.classList.add('active'); const name = document.createElement('span'); name.textContent = list.name; const count = document.createElement('small'); count.textContent = listTypeLabel(list.listType) + ' · ' + list.items.length + ' Artikel'; button.appendChild(name); button.appendChild(count); button.addEventListener('click', function(){ selectList(list.id); }); container.appendChild(button); }); }
        function renderActiveList() { const list = getActiveList(); shoppingList.textContent=''; if (!list) { currentListTitle.textContent='Keine Liste'; currentListInfo.textContent='0 Artikel'; listSettings.classList.remove('visible'); updateStats([]); return; } currentListTitle.textContent=list.name; currentListInfo.textContent=list.items.length + ' Artikel'; renderListSettings(list); list.items.forEach(renderItem); updateStats(list.items); }
        function renderListSettings(list) { const canManage = canManageListSettings(list); listSettings.classList.toggle('visible', canManage); listSettingsTitle.textContent=list.name; listSettingsInfo.textContent='Typ: ' + listTypeLabel(list.listType) + ' | Besitzer: ' + getUserName(list.ownerId) + ' | Haushalt: ' + getFamilyName(list.familyId) + ' | ' + (list.isShared ? 'gemeinschaftlich' : 'privat'); toggleVisibilityButton.textContent = list.isShared ? 'Auf privat setzen' : 'Als Gemeinschaftsliste freigeben'; toggleVisibilityButton.disabled = list.familyId === '' && !list.isShared; activeListTypeSelect.value = list.listType || 'shopping'; updateListTypeButton.disabled = !canManage; deleteListButton.disabled = !canManage; ownerSelectRow.classList.toggle('visible', appData.isAdmin === true); activeListOwnerSelect.value = list.ownerId; }
        function renderItem(item) { const li=document.createElement('li'); li.className='shopping-item'; if (item.done) li.classList.add('done'); const main=document.createElement('div'); const name=document.createElement('span'); name.className='item-name'; name.textContent=item.name; const meta=document.createElement('div'); meta.className='item-meta'; const badges=['Menge: ' + (item.amount || 'keine Angabe'), 'Kategorie: ' + item.category, item.done ? 'Status: erledigt' : 'Status: offen', 'Erstellt von: ' + getUserName(item.createdBy)]; badges.forEach(function(text){ const b=document.createElement('span'); b.className='badge'; b.textContent=text; meta.appendChild(b); }); main.appendChild(name); main.appendChild(meta); const actions=document.createElement('div'); actions.className='item-actions'; const toggle=document.createElement('button'); toggle.className='toggle-btn'; toggle.type='button'; toggle.textContent=item.done?'Öffnen':'Erledigt'; toggle.addEventListener('click', function(){ toggleItemDone(item.id); }); const del=document.createElement('button'); del.className='delete-btn'; del.type='button'; del.textContent='Löschen'; del.addEventListener('click', function(){ deleteItem(item.id); }); actions.appendChild(toggle); actions.appendChild(del); li.appendChild(main); li.appendChild(actions); shoppingList.appendChild(li); }
        function updateStats(items) { const total=items.length; const done=items.filter(function(item){ return item.done === true; }).length; totalCount.textContent=total; doneCount.textContent=done; openCount.textContent=total-done; emptyMessage.style.display=total===0?'block':'none'; }

        function renderTodos() {
            privateTodoList.textContent=''; familyTodoList.textContent=''; todoDashboard.textContent='';
            const todos = appData.todos || [];
            const privateTodos = todos.filter((todo) => todo.scope === 'private');
            const familyTodos = todos.filter((todo) => todo.scope === 'family');
            privateTodos.forEach((todo) => renderTodoItem(privateTodoList, todo));
            familyTodos.forEach((todo) => renderTodoItem(familyTodoList, todo));
            privateTodoEmpty.style.display = privateTodos.length === 0 ? 'block' : 'none';
            familyTodoEmpty.style.display = familyTodos.length === 0 ? 'block' : 'none';
            const done = todos.filter((todo) => todo.status === 'done').length;
            const overdue = todos.filter(isOverdue).length;
            const reminders = todos.filter(isReminderDue).length;
            const assignedToMe = todos.filter((todo) => appData.currentUser && todo.assignedTo === appData.currentUser.id && todo.status !== 'done').length;
            todoTotalCount.textContent = todos.length;
            todoDoneCount.textContent = done;
            todoOpenCount.textContent = todos.length - done;
            renderDashboardCard('Überfällig', overdue + ' Aufgabe(n)', overdue > 0 ? 'Bitte prüfen' : 'Alles im Zeitplan');
            renderDashboardCard('Erinnerungen', reminders + ' aktiv', reminders > 0 ? 'Erinnerungsdatum erreicht' : 'Keine fällige Erinnerung');
            renderDashboardCard('Mir zugewiesen', assignedToMe + ' offen', 'Persönliche Arbeitslast');
        }
        function renderDashboardCard(title, value, detail) { const card=document.createElement('div'); card.className='dashboard-card'; const strong=document.createElement('strong'); strong.textContent=title + ': ' + value; const span=document.createElement('span'); span.textContent=detail; card.appendChild(strong); card.appendChild(span); todoDashboard.appendChild(card); }
        function renderTodoItem(container, todo) {
            const li=document.createElement('li'); li.className='todo-item'; if (todo.status === 'done') li.classList.add('done');
            const main=document.createElement('div'); const title=document.createElement('span'); title.className='todo-title'; title.textContent=todo.title;
            const meta=document.createElement('div'); meta.className='item-meta';
            const badges=[todoScopeLabel(todo.scope), 'Status: ' + todoStatusLabel(todo.status), 'Priorität: ' + priorityLabel(todo.priority), 'Zugewiesen: ' + getUserName(todo.assignedTo), 'Fällig: ' + formatDate(todo.dueAt), 'Erinnerung: ' + formatDate(todo.reminderAt), 'Kalender: ' + formatDate(todo.calendarDate), 'Besitzer: ' + getUserName(todo.ownerId), 'Kommentare: ' + (todo.comments || []).length];
            if (todo.scope === 'family') badges.push('Haushalt: ' + getFamilyName(todo.familyId));
            if (isOverdue(todo)) badges.push('überfällig');
            if (isReminderDue(todo)) badges.push('Erinnerung erreicht');
            badges.forEach(function(text){ const b=document.createElement('span'); b.className='badge'; b.textContent=text; meta.appendChild(b); });
            main.appendChild(title); main.appendChild(meta);
            const actions=document.createElement('div'); actions.className='todo-actions';
            const toggle=document.createElement('button'); toggle.className='toggle-btn'; toggle.type='button'; toggle.textContent=todo.status === 'done' ? 'Öffnen' : 'Erledigt'; toggle.addEventListener('click', function(){ toggleTodoDone(todo.id); });
            const edit=document.createElement('button'); edit.className='small-btn'; edit.type='button'; edit.textContent='Bearbeiten';
            const del=document.createElement('button'); del.className='delete-btn'; del.type='button'; del.textContent='Löschen'; del.addEventListener('click', function(){ deleteTodo(todo.id); });
            actions.appendChild(toggle); actions.appendChild(edit); actions.appendChild(del); li.appendChild(main); li.appendChild(actions);
            const editForm = buildTodoEditForm(todo); editForm.style.display='none'; edit.addEventListener('click', function(){ editForm.style.display = editForm.style.display === 'none' ? 'grid' : 'none'; }); li.appendChild(editForm);
            li.appendChild(buildCommentsBox(todo));
            container.appendChild(li);
        }
        function buildTodoEditForm(todo) {
            const form=document.createElement('form'); form.className='todo-edit-form';
            const title=document.createElement('input'); title.value=todo.title; title.maxLength=120;
            const scope=document.createElement('select'); [['private','Privat'],['family','Familie']].forEach(function(pair){ const opt=document.createElement('option'); opt.value=pair[0]; opt.textContent=pair[1]; if (todo.scope===pair[0]) opt.selected=true; scope.appendChild(opt); });
            const priority=document.createElement('select'); [['low','Niedrig'],['normal','Normal'],['high','Hoch'],['urgent','Dringend']].forEach(function(pair){ const opt=document.createElement('option'); opt.value=pair[0]; opt.textContent=pair[1]; if ((todo.priority || 'normal')===pair[0]) opt.selected=true; priority.appendChild(opt); });
            const assigned=document.createElement('select'); fillUserSelect(assigned, todo.assignedTo || '', todo.scope, true); scope.addEventListener('change', function(){ fillUserSelect(assigned, assigned.value, scope.value, true); });
            const due=document.createElement('input'); due.type='date'; due.value=todo.dueAt || '';
            const reminder=document.createElement('input'); reminder.type='date'; reminder.value=todo.reminderAt || '';
            const calendar=document.createElement('input'); calendar.type='date'; calendar.value=todo.calendarDate || '';
            const save=document.createElement('button'); save.className='small-btn'; save.type='submit'; save.textContent='Speichern';
            form.appendChild(title); form.appendChild(scope); form.appendChild(priority); form.appendChild(assigned); form.appendChild(due); form.appendChild(reminder); form.appendChild(calendar); form.appendChild(save);
            form.addEventListener('submit', function(event){ event.preventDefault(); updateTodo(todo.id, { title:title.value.trim(), scope:scope.value, priority:priority.value, assignedTo:assigned.value, dueAt:due.value, reminderAt:reminder.value, calendarDate:calendar.value }); });
            return form;
        }
        function buildCommentsBox(todo) {
            const box=document.createElement('div'); box.className='comment-box';
            const comments=document.createElement('div'); comments.className='comment-list';
            (todo.comments || []).forEach(function(comment){ const item=document.createElement('div'); item.className='comment-item'; const body=document.createElement('div'); body.textContent=comment.body; const meta=document.createElement('small'); meta.textContent=getUserName(comment.authorId) + ' · ' + comment.createdAt; const del=document.createElement('button'); del.className='danger'; del.type='button'; del.textContent='Kommentar löschen'; del.addEventListener('click', function(){ deleteTodoComment(todo.id, comment.id); }); item.appendChild(body); item.appendChild(meta); item.appendChild(del); comments.appendChild(item); });
            const form=document.createElement('form'); form.className='comment-form'; const input=document.createElement('input'); input.placeholder='Kommentar hinzufügen'; input.maxLength=600; const button=document.createElement('button'); button.className='small-btn'; button.type='submit'; button.textContent='Kommentieren'; form.appendChild(input); form.appendChild(button); form.addEventListener('submit', function(event){ event.preventDefault(); addTodoComment(todo.id, input); });
            box.appendChild(comments); box.appendChild(form); return box;
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
                const actions=document.createElement('div'); actions.className='inline-actions'; actions.style.marginTop='8px';
                const del=document.createElement('button'); del.className='danger'; del.type='button'; del.textContent='Löschen'; del.addEventListener('click', function(){ deleteMessage(message.id); });
                actions.appendChild(del); bubble.appendChild(body); bubble.appendChild(meta); bubble.appendChild(actions); messageList.appendChild(bubble);
            });
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
                if (withDelete === true) { const actions=document.createElement('div'); actions.className='inline-actions'; actions.style.marginTop='8px'; const del=document.createElement('button'); del.className='danger'; del.type='button'; del.textContent='Löschen'; del.addEventListener('click', function(){ deleteMessage(message.id); }); actions.appendChild(del); bubble.appendChild(actions); }
                container.appendChild(bubble);
            });
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
            const labels = {
                dashboard:['Dashboard','Übersicht über Listen, Todos, Nachrichten und Administration'],
                lists:['Listen','Einkaufs-, Haushalts- und Gemeinschaftslisten'],
                todos:['Todos','Persönliche Aufgaben und Familienaufgaben'],
                messages:['Nachrichten','Private Nachrichten und ungelesene Badges'],
                chats:['Chats','Direkter 1:1-Chat mit Emoji-Funktion und Schnellfenster'],
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
        switchAdminTab('families');
        switchModule('dashboard');
        loadData();
    </script>
</body>
</html>
