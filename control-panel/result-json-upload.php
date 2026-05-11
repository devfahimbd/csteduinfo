<?php
/**
 * Result JSON Upload - Admin Panel (v4 - Bulk Upload Edition)
 * 
 * Two upload modes, each with two input methods:
 *   Mode 1: Fill regulation/exam year/semester manually + upload JSON file OR paste JSON code
 *   Mode 2: Upload complete JSON file OR paste complete JSON code (metadata inside)
 * 
 * NEW: Client-side validation, preview, and chunked bulk upload for 55K+ records.
 */
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/result-parser.php';
requireAdmin();

$parser = new ResultPdfParser($pdo);
$flash = getFlash();
$adminName = $_SESSION['admin_name'] ?? 'Admin';
$adminInitial = strtoupper(mb_substr($adminName, 0, 1));

// Handle delete action
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $parser->deleteBatch((int)$_GET['id']);
    setFlash('success', 'Batch deleted successfully.');
    header('Location: result-json-upload.php');
    exit;
}

// Get current batch if specified
$currentBatch = null;
if (isset($_GET['batch_id'])) {
    $currentBatch = $parser->getBatch((int)$_GET['batch_id']);
}

// Get all batches
$allBatches = $parser->getAllBatches();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Result — CST Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        /* ═══════════════════════════════════════════════════════════
           EXISTING STYLES (from original)
           ═══════════════════════════════════════════════════════════ */
        .mode-tabs {
            display: flex;
            gap: 0;
            margin-bottom: 24px;
            background: #F1F5F9;
            border-radius: 10px;
            padding: 4px;
        }
        .mode-tab {
            flex: 1;
            padding: 12px 16px;
            text-align: center;
            font-size: 13px;
            font-weight: 600;
            color: #64748B;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
            background: transparent;
        }
        .mode-tab:hover { color: #334155; }
        .mode-tab.active {
            background: #fff;
            color: #2563EB;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        }
        .mode-panel { display: none; }
        .mode-panel.active { display: block; }

        .input-method-tabs {
            display: flex;
            gap: 0;
            margin-bottom: 16px;
            background: #F8FAFC;
            border-radius: 8px;
            padding: 3px;
            border: 1px solid #E2E8F0;
        }
        .input-method-tab {
            flex: 1;
            padding: 8px 14px;
            text-align: center;
            font-size: 12px;
            font-weight: 600;
            color: #94A3B8;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
            background: transparent;
        }
        .input-method-tab:hover { color: #64748B; }
        .input-method-tab.active {
            background: #fff;
            color: #2563EB;
            box-shadow: 0 1px 2px rgba(0,0,0,0.06);
        }
        .input-method-panel { display: none; }
        .input-method-panel.active { display: block; }

        .form-row-3 {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
        }
        .form-row-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .form-field label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #1E293B;
            margin-bottom: 6px;
        }
        .form-field label .req {
            color: #EF4444;
        }
        .form-field input,
        .form-field select {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid #E2E8F0;
            border-radius: 8px;
            font-size: 14px;
            font-family: 'Inter', sans-serif;
            color: #1E293B;
            background: #fff;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .form-field input:focus,
        .form-field select:focus {
            border-color: #2563EB;
            box-shadow: 0 0 0 3px rgba(37,99,235,0.1);
        }
        .form-field .hint {
            font-size: 12px;
            color: #94A3B8;
            margin-top: 4px;
        }

        .drop-zone {
            border: 2px dashed #CBD5E1;
            border-radius: 12px;
            padding: 36px 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
            background: #F8FAFC;
            margin-top: 20px;
        }
        .drop-zone:hover,
        .drop-zone.dragover {
            border-color: #2563EB;
            background: #EFF6FF;
        }
        .drop-zone svg {
            margin: 0 auto 10px;
            display: block;
        }
        .drop-zone .dz-title {
            font-size: 14px;
            font-weight: 500;
            color: #334155;
            margin-bottom: 4px;
        }
        .drop-zone .dz-sub {
            font-size: 12px;
            color: #94A3B8;
        }
        .drop-zone .dz-file {
            display: none;
            margin-top: 10px;
            padding: 10px 16px;
            background: #EFF6FF;
            border: 1px solid #BFDBFE;
            border-radius: 8px;
            font-size: 13px;
            color: #1E40AF;
            font-weight: 500;
        }
        .drop-zone .dz-file .dz-size {
            color: #3B82F6;
            font-weight: 400;
        }
        .drop-zone .dz-file .dz-remove {
            color: #EF4444;
            cursor: pointer;
            margin-left: 12px;
            font-weight: 600;
        }

        .json-code-area {
            margin-top: 20px;
        }
        .json-code-area textarea {
            width: 100%;
            min-height: 260px;
            max-height: 500px;
            padding: 16px;
            border: 2px solid #E2E8F0;
            border-radius: 12px;
            font-size: 13px;
            font-family: 'Courier New', Courier, monospace;
            color: #1E293B;
            background: #F8FAFC;
            outline: none;
            resize: vertical;
            line-height: 1.6;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .json-code-area textarea:focus {
            border-color: #2563EB;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(37,99,235,0.1);
        }
        .json-code-area textarea::placeholder {
            color: #94A3B8;
        }
        .json-code-area .code-hint {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 8px;
            font-size: 12px;
            color: #94A3B8;
        }
        .json-code-area .code-hint .char-count {
            font-weight: 600;
            color: #64748B;
        }
        .json-code-area .code-hint .format-btn {
            background: #EFF6FF;
            color: #2563EB;
            border: 1px solid #BFDBFE;
            padding: 4px 12px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 11px;
            font-weight: 600;
            transition: all 0.2s;
        }
        .json-code-area .code-hint .format-btn:hover {
            background: #DBEAFE;
        }
        .json-code-area .code-hint .clean-btn {
            background: #FFF7ED;
            color: #EA580C;
            border: 1px solid #FED7AA;
            padding: 4px 12px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 11px;
            font-weight: 600;
            transition: all 0.2s;
        }
        .json-code-area .code-hint .clean-btn:hover {
            background: #FFEDD5;
        }
        .json-code-area .code-hint .sample-btn {
            background: #F0FDF4;
            color: #16A34A;
            border: 1px solid #BBF7D0;
            padding: 4px 12px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 11px;
            font-weight: 600;
            transition: all 0.2s;
        }
        .json-code-area .code-hint .sample-btn:hover {
            background: #DCFCE7;
        }

        .upload-progress-old {
            display: none;
            margin-top: 20px;
            padding: 16px 20px;
            border-radius: 10px;
            background: #F0F9FF;
            border: 1px solid #BAE6FD;
        }
        .upload-progress-old.active { display: flex; align-items: center; gap: 10px; }
        .spinner {
            width: 18px; height: 18px;
            border: 2px solid #BAE6FD;
            border-top-color: #2563EB;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            flex-shrink: 0;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        .upload-progress-old span {
            font-size: 13px;
            color: #0369A1;
            font-weight: 500;
        }

        .result-toast {
            display: none;
            position: fixed;
            top: 24px;
            right: 24px;
            z-index: 9999;
            padding: 16px 24px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 500;
            max-width: 460px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
            animation: slideIn 0.3s ease;
        }
        .result-toast.active { display: flex; align-items: flex-start; gap: 10px; }
        .result-toast.success { background: #DCFCE7; color: #166534; border: 1px solid #BBF7D0; }
        .result-toast.error { background: #FEE2E2; color: #991B1B; border: 1px solid #FECACA; }
        .result-toast.warning { background: #FFFBEB; color: #92400E; border: 1px solid #FDE68A; }
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        @media (max-width: 640px) {
            .form-row-3 { grid-template-columns: 1fr; }
            .form-row-2 { grid-template-columns: 1fr; }
        }

        /* ═══════════════════════════════════════════════════════════
           NEW STYLES - Validation Preview Panel
           ═══════════════════════════════════════════════════════════ */
        .validation-panel {
            background: #F8FAFC;
            border: 1px solid #E2E8F0;
            border-radius: 12px;
            overflow: hidden;
        }
        .validation-header {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 16px 20px;
            border-bottom: 1px solid #E2E8F0;
        }
        .validation-header.success-header {
            background: #F0FDF4;
            border-color: #BBF7D0;
        }
        .validation-header.error-header {
            background: #FEF2F2;
            border-color: #FECACA;
        }
        .validation-header h4 {
            font-size: 15px;
            font-weight: 700;
            color: #1E293B;
            margin: 0;
        }
        .validation-header.success-header h4 { color: #166534; }
        .validation-header.error-header h4 { color: #991B1B; }
        .validation-body {
            padding: 20px;
        }

        .validation-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 12px;
            margin-bottom: 20px;
        }
        .v-stat {
            background: #fff;
            border: 1px solid #E2E8F0;
            border-radius: 8px;
            padding: 12px 16px;
            text-align: center;
        }
        .v-stat-label {
            font-size: 11px;
            font-weight: 600;
            color: #94A3B8;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }
        .v-stat-value {
            font-size: 20px;
            font-weight: 700;
            color: #1E293B;
        }
        .v-stat-value.highlight { color: #2563EB; }

        .v-info-row {
            display: flex;
            align-items: flex-start;
            gap: 8px;
            font-size: 13px;
            color: #475569;
            margin-bottom: 6px;
            line-height: 1.5;
        }
        .v-info-row svg { flex-shrink: 0; margin-top: 2px; }
        .v-info-label { font-weight: 600; color: #334155; }

        .v-fields {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-top: 4px;
        }
        .v-field-tag {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            background: #EFF6FF;
            color: #2563EB;
            border: 1px solid #BFDBFE;
        }

        /* Sample preview table */
        .v-preview-title {
            font-size: 13px;
            font-weight: 700;
            color: #334155;
            margin: 16px 0 10px;
        }
        .v-preview-table-wrap {
            overflow-x: auto;
            border: 1px solid #E2E8F0;
            border-radius: 8px;
        }
        .v-preview-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }
        .v-preview-table th {
            background: #F1F5F9;
            padding: 8px 12px;
            text-align: left;
            font-weight: 600;
            color: #475569;
            white-space: nowrap;
            border-bottom: 1px solid #E2E8F0;
        }
        .v-preview-table td {
            padding: 8px 12px;
            color: #334155;
            border-bottom: 1px solid #F1F5F9;
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .v-preview-table tr:last-child td { border-bottom: none; }
        .v-status-passed { color: #16A34A; font-weight: 600; }
        .v-status-referred { color: #DC2626; font-weight: 600; }
        .v-status-other { color: #F59E0B; font-weight: 600; }

        /* Validation errors */
        .v-errors {
            margin-top: 16px;
            border-top: 1px solid #E2E8F0;
            padding-top: 16px;
        }
        .v-errors-title {
            font-size: 13px;
            font-weight: 700;
            color: #DC2626;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .v-error-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .v-error-item {
            padding: 6px 12px;
            font-size: 12px;
            color: #7F1D1D;
            background: #FEF2F2;
            border-radius: 6px;
            margin-bottom: 4px;
            border-left: 3px solid #EF4444;
        }
        .v-error-more {
            font-size: 12px;
            color: #94A3B8;
            font-style: italic;
            margin-top: 6px;
        }

        /* Validation actions */
        .v-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
            flex-wrap: wrap;
            align-items: center;
        }
        .btn-validate {
            padding: 10px 24px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
            font-family: 'Inter', sans-serif;
        }
        .btn-validate-primary {
            background: #2563EB;
            color: #fff;
        }
        .btn-validate-primary:hover { background: #1D4ED8; }
        .btn-validate-primary:disabled {
            background: #94A3B8;
            cursor: not-allowed;
        }
        .btn-validate-success {
            background: #16A34A;
            color: #fff;
        }
        .btn-validate-success:hover { background: #15803D; }
        .btn-validate-success:disabled {
            background: #94A3B8;
            cursor: not-allowed;
        }
        .btn-validate-danger {
            background: #EF4444;
            color: #fff;
        }
        .btn-validate-danger:hover { background: #DC2626; }
        .btn-validate-outline {
            background: #fff;
            color: #64748B;
            border: 1px solid #E2E8F0;
        }
        .btn-validate-outline:hover { background: #F8FAFC; border-color: #CBD5E1; }

        .simple-upload-link {
            font-size: 12px;
            color: #94A3B8;
            text-decoration: underline;
            cursor: pointer;
            margin-left: auto;
            transition: color 0.2s;
        }
        .simple-upload-link:hover { color: #64748B; }

        .v-warning-box {
            display: flex;
            align-items: flex-start;
            gap: 8px;
            padding: 12px 16px;
            background: #FFFBEB;
            border: 1px solid #FDE68A;
            border-radius: 8px;
            font-size: 13px;
            color: #92400E;
            margin-top: 16px;
            line-height: 1.5;
        }
        .v-warning-box svg { flex-shrink: 0; margin-top: 1px; }

        /* ═══════════════════════════════════════════════════════════
           NEW STYLES - Bulk Upload Progress Panel
           ═══════════════════════════════════════════════════════════ */
        .bulk-progress-panel {
            padding: 24px;
        }
        .bulk-progress-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
        }
        .bulk-progress-header h4 {
            font-size: 15px;
            font-weight: 700;
            color: #1E293B;
            margin: 0;
        }

        .bulk-progress-bar-wrap {
            position: relative;
            height: 28px;
            background: #E2E8F0;
            border-radius: 14px;
            overflow: hidden;
            margin-bottom: 12px;
        }
        .bulk-progress-bar {
            height: 100%;
            border-radius: 14px;
            background: linear-gradient(90deg, #22C55E, #16A34A);
            transition: width 0.4s ease;
            position: relative;
            min-width: 0;
        }
        .bulk-progress-bar.aborted {
            background: linear-gradient(90deg, #EF4444, #DC2626);
        }
        .bulk-progress-bar.failed {
            background: linear-gradient(90deg, #EF4444, #DC2626);
        }
        .bulk-progress-text {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 12px;
            font-weight: 700;
            color: #1E293B;
            white-space: nowrap;
            z-index: 1;
        }
        .bulk-progress-bar-wrap:has(.bulk-progress-bar[style*="width: 0"]),
        .bulk-progress-bar-wrap:has(.bulk-progress-bar:not([style])) {
            background: #E2E8F0;
        }

        .bulk-progress-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 12px;
            margin-bottom: 16px;
        }
        .bp-stat {
            background: #F8FAFC;
            border: 1px solid #E2E8F0;
            border-radius: 8px;
            padding: 10px 14px;
        }
        .bp-stat-label {
            font-size: 11px;
            font-weight: 600;
            color: #94A3B8;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .bp-stat-value {
            font-size: 16px;
            font-weight: 700;
            color: #1E293B;
            margin-top: 2px;
        }

        .bulk-progress-log {
            max-height: 150px;
            overflow-y: auto;
            background: #1E293B;
            border-radius: 8px;
            padding: 12px;
            font-family: 'Courier New', monospace;
            font-size: 11px;
            line-height: 1.7;
            color: #94A3B8;
        }
        .bulk-progress-log .log-ok { color: #4ADE80; }
        .bulk-progress-log .log-err { color: #FCA5A5; }
        .bulk-progress-log .log-warn { color: #FDE68A; }
        .bulk-progress-log .log-info { color: #93C5FD; }

        /* ═══════════════════════════════════════════════════════════
           NEW STYLES - Upload Result Panel
           ═══════════════════════════════════════════════════════════ */
        .result-summary {
            padding: 24px;
        }
        .result-summary-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 20px;
        }
        .result-icon {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .result-icon.success-icon {
            background: #DCFCE7;
            color: #16A34A;
        }
        .result-icon.error-icon {
            background: #FEE2E2;
            color: #DC2626;
        }
        .result-summary-header h4 {
            font-size: 18px;
            font-weight: 700;
            color: #1E293B;
            margin: 0;
        }
        .result-summary-header p {
            font-size: 13px;
            color: #64748B;
            margin: 2px 0 0;
        }

        .result-stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
            gap: 12px;
            margin-bottom: 20px;
        }
        .rs-stat {
            background: #F8FAFC;
            border: 1px solid #E2E8F0;
            border-radius: 10px;
            padding: 16px;
            text-align: center;
        }
        .rs-stat-value {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 2px;
        }
        .rs-stat-value.rs-green { color: #16A34A; }
        .rs-stat-value.rs-red { color: #DC2626; }
        .rs-stat-value.rs-blue { color: #2563EB; }
        .rs-stat-value.rs-amber { color: #F59E0B; }
        .rs-stat-label {
            font-size: 12px;
            color: #94A3B8;
            font-weight: 600;
        }

        .result-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
    </style>
</head>
<body class="admin-layout">

    <!-- Toast -->
    <div class="result-toast" id="toast"></div>

    <!-- SIDEBAR -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#2563EB" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect><line x1="8" y1="21" x2="16" y2="21"></line><line x1="12" y1="17" x2="12" y2="21"></line></svg>
            <span>CST Admin</span>
        </div>
        <nav class="sidebar-nav">
            <div class="nav-section"><div class="nav-section-title">MAIN</div>
                <a href="index.php"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>Dashboard</a>
                <a href="../index.php" target="_blank"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>Website</a>
            </div>
            <div class="nav-section"><div class="nav-section-title">RESULTS</div>
                <a href="result-json-upload.php" class="active"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>Upload Result</a>
                <a href="result-data.php"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>Result Data</a>
                <a href="result-subjects.php"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>Subject Codes</a>
            </div>
            <div class="nav-section"><div class="nav-section-title">CONTENT</div>
                <a href="notices.php"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>Notices</a>
                <a href="teachers.php"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle></svg>Faculty</a>
                <a href="gallery.php"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>Gallery</a>
                <a href="resources.php"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>Resources</a>
            </div>
            <div class="nav-section"><div class="nav-section-title">MANAGEMENT</div>
                <a href="categories.php"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path></svg>Categories</a>
                <a href="sponsors.php"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="7"></circle><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"></polyline></svg>Sponsors</a>
                <a href="credits.php"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>Credits</a>
            </div>
            <div class="nav-section"><div class="nav-section-title">SETTINGS</div>
                <a href="settings.php"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>Settings</a>
            </div>
        </nav>
        <div class="sidebar-footer"><a href="logout.php"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>Logout</a></div>
    </aside>

    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <main class="admin-main">
        <header class="admin-topbar">
            <div style="display:flex;align-items:center;gap:12px;">
                <button class="mobile-toggle" id="mobileToggle"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg></button>
                <div class="topbar-title"><h2>Upload Result</h2></div>
            </div>
            <div class="topbar-actions">
                <div class="admin-user">
                    <div class="avatar"><?php echo htmlspecialchars($adminInitial); ?></div>
                    <span><?php echo htmlspecialchars($adminName); ?></span>
                </div>
            </div>
        </header>

        <div class="admin-content">
            <?php if (!empty($flash['type']) && !empty($flash['message'])): ?>
                <div class="alert alert-<?php echo $flash['type'] === 'success' ? 'success' : 'error'; ?>">
                    <?php echo htmlspecialchars($flash['message']); ?>
                </div>
            <?php endif; ?>

            <!-- Upload Card -->
            <div class="admin-card">
                <div class="admin-card-header">
                    <h3>Bulk Import Student Results</h3>
                    <span style="color:#64748B;font-size:13px;">Supports 55,000+ students &bull; Chunked Upload</span>
                </div>
                <div class="admin-card-body">

                    <!-- Mode Tabs -->
                    <div class="mode-tabs">
                        <button class="mode-tab active" onclick="switchMode('manual')" id="tabManual">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:middle;margin-right:4px;"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                            Manual Entry + JSON
                        </button>
                        <button class="mode-tab" onclick="switchMode('auto')" id="tabAuto">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:middle;margin-right:4;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
                            Complete JSON
                        </button>
                    </div>

                    <!-- ============================================ -->
                    <!-- MODE 1: Manual Fields + JSON (File or Code) -->
                    <!-- ============================================ -->
                    <div class="mode-panel active" id="panelManual">
                        <div class="form-row-3" style="margin-bottom:16px;">
                            <div class="form-field">
                                <label>Regulation Year <span class="req">*</span></label>
                                <select name="regulation_year" id="manualRegYear" required>
                                    <option value="">-- Select --</option>
                                    <?php for ($y = 2016; $y <= 2030; $y++): ?>
                                    <option value="<?php echo $y; ?>"><?php echo $y; ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="form-field">
                                <label>Exam Year <span class="req">*</span></label>
                                <select name="exam_year" id="manualExamYear" required>
                                    <option value="">-- Select --</option>
                                    <?php for ($y = 2015; $y <= 2030; $y++): ?>
                                    <option value="<?php echo $y; ?>"><?php echo $y; ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="form-field">
                                <label>Semester <span class="req">*</span></label>
                                <select name="semester" id="manualSemester" required>
                                    <option value="">-- Select --</option>
                                    <option value="1st Semester">1st Semester</option>
                                    <option value="2nd Semester">2nd Semester</option>
                                    <option value="3rd Semester">3rd Semester</option>
                                    <option value="4th Semester">4th Semester</option>
                                    <option value="5th Semester">5th Semester</option>
                                    <option value="6th Semester">6th Semester</option>
                                    <option value="7th Semester">7th Semester</option>
                                    <option value="8th Semester">8th Semester</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-row-2" style="margin-bottom:0;">
                            <div class="form-field">
                                <label>Program</label>
                                <input type="text" name="program" id="manualProgram" value="Diploma In Engineering" placeholder="e.g. Diploma In Engineering">
                            </div>
                            <div class="form-field" style="display:flex;align-items:flex-end;">
                                <div class="hint" style="line-height:1.5;">
                                    Your JSON should contain student data with roll, institute, institute_code, status, cgpa, and failed_subjects fields. Also supports college_name, college_code, result_type, gpa format.
                                </div>
                            </div>
                        </div>

                        <!-- Input Method Tabs: File Upload vs Paste Code -->
                        <div class="input-method-tabs" style="margin-top:20px;">
                            <button type="button" class="input-method-tab active" onclick="switchInputMethod('manual', 'file')" id="manualTabFile">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:middle;margin-right:3px;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                                Upload JSON File
                            </button>
                            <button type="button" class="input-method-tab" onclick="switchInputMethod('manual', 'code')" id="manualTabCode">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:middle;margin-right:3px;"><polyline points="16 18 22 12 16 6"></polyline><polyline points="8 6 2 12 8 18"></polyline></svg>
                                Paste JSON Code
                            </button>
                        </div>

                        <!-- Manual Mode: File Upload Panel -->
                        <div class="input-method-panel active" id="manualPanelFile">
                            <div class="drop-zone" id="dropZone1" onclick="document.getElementById('file1').click();">
                                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#94A3B8" stroke-width="1.5">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                    <polyline points="17 8 12 3 7 8"></polyline>
                                    <line x1="12" y1="3" x2="12" y2="15"></line>
                                </svg>
                                <p class="dz-title">Click to select or drag & drop JSON file</p>
                                <p class="dz-sub">Only .json files &bull; Max 50MB</p>
                                <input type="file" name="json_file" id="file1" accept=".json" style="display:none;">
                                <div class="dz-file" id="fileInfo1"></div>
                            </div>
                        </div>

                        <!-- Manual Mode: Paste Code Panel -->
                        <div class="input-method-panel" id="manualPanelCode">
                            <div class="json-code-area">
                                <textarea name="json_code" id="jsonCode1" placeholder='Paste your JSON student data here...

Example (object format with roll as key):
{
  "200013": {
    "roll": "200013",
    "institute": "Thakurgaon Polytechnic Institute, Thakurgaon",
    "institute_code": "12053",
    "status": "referred",
    "cgpa": null,
    "failed_subjects": [
      "25913(T)",
      "26711(T)"
    ],
    "total_failed": 2
  },
  "200010": {
    "roll": "200010",
    "institute": "Dhaka Polytechnic Institute, Dhaka",
    "institute_code": "49166",
    "status": "passed",
    "cgpa": 3.52,
    "failed_subjects": [],
    "total_failed": 0
  }
}

Or (with "students" array):
{
  "students": [
    {
      "roll": "200010",
      "institute": "Dhaka Polytechnic Institute",
      "institute_code": "49166",
      "status": "passed",
      "cgpa": 3.52,
      "failed_subjects": [],
      "total_failed": 0
    }
  ]
}'></textarea>
                                <div class="code-hint">
                                    <span class="char-count" id="charCount1">0 characters</span>
                                    <div style="display:flex;gap:6px;">
                                        <button type="button" class="sample-btn" onclick="loadSampleManual()">Load Sample</button>
                                        <button type="button" class="clean-btn" onclick="cleanAndFixCode('jsonCode1')">Clean & Fix</button>
                                        <button type="button" class="format-btn" onclick="formatCode('jsonCode1')">Format JSON</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="v-actions" style="margin-top:20px;">
                            <button type="button" class="btn-validate btn-validate-primary" id="validateBtn1" onclick="handleValidate('manual')">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4"></path><circle cx="12" cy="12" r="10"></circle></svg>
                                Validate &amp; Preview
                            </button>
                            <span class="simple-upload-link" onclick="handleSimpleUpload('manual')">Use simple upload (for small files)</span>
                        </div>

                        <div class="upload-progress-old" id="progress1">
                            <div class="spinner"></div>
                            <span>Importing data... please wait for large files.</span>
                        </div>
                    </div>

                    <!-- ================================================ -->
                    <!-- MODE 2: Complete JSON (File or Code) -->
                    <!-- ================================================ -->
                    <div class="mode-panel" id="panelAuto">
                        <div style="background:#F8FAFC;border:1px solid #E2E8F0;border-radius:10px;padding:16px 20px;margin-bottom:20px;">
                            <p style="font-size:13px;color:#475569;line-height:1.6;">
                                Upload or paste a complete JSON file that already contains <strong>exam_year</strong>, <strong>regulation_year</strong>, <strong>semester</strong>, and <strong>students</strong> array. The system will read everything automatically.
                            </p>
                        </div>

                        <!-- Input Method Tabs: File Upload vs Paste Code -->
                        <div class="input-method-tabs">
                            <button type="button" class="input-method-tab active" onclick="switchInputMethod('auto', 'file')" id="autoTabFile">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:middle;margin-right:3px;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                                Upload JSON File
                            </button>
                            <button type="button" class="input-method-tab" onclick="switchInputMethod('auto', 'code')" id="autoTabCode">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:middle;margin-right:3px;"><polyline points="16 18 22 12 16 6"></polyline><polyline points="8 6 2 12 8 18"></polyline></svg>
                                Paste JSON Code
                            </button>
                        </div>

                        <!-- Auto Mode: File Upload Panel -->
                        <div class="input-method-panel active" id="autoPanelFile">
                            <div class="drop-zone" id="dropZone2" onclick="document.getElementById('file2').click();">
                                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#94A3B8" stroke-width="1.5">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                    <polyline points="14 2 14 8 20 8"></polyline>
                                </svg>
                                <p class="dz-title">Click to select or drag & drop complete JSON file</p>
                                <p class="dz-sub">Must contain exam_year, regulation_year, semester, students[]</p>
                                <input type="file" name="json_file" id="file2" accept=".json" style="display:none;">
                                <div class="dz-file" id="fileInfo2"></div>
                            </div>
                        </div>

                        <!-- Auto Mode: Paste Code Panel -->
                        <div class="input-method-panel" id="autoPanelCode">
                            <div class="json-code-area">
                                <textarea name="json_code" id="jsonCode2" placeholder='Paste your complete JSON data here...

Must include exam_year, regulation_year, semester, and students array:

{
  "exam_year": "2022",
  "regulation_year": "2016",
  "semester": "1st Semester",
  "program": "Diploma In Engineering",
  "students": [
    {
      "roll": "200010",
      "institute_code": "49166",
      "institute": "Dhaka Polytechnic Institute, Dhaka",
      "cgpa": 3.52,
      "status": "passed",
      "failed_subjects": [],
      "total_failed": 0
    },
    {
      "roll": "200013",
      "institute_code": "12053",
      "institute": "Thakurgaon Polytechnic Institute, Thakurgaon",
      "cgpa": null,
      "status": "referred",
      "failed_subjects": [
        "25913(T)",
        "26711(T)"
      ],
      "total_failed": 2
    }
  ]
}'></textarea>
                                <div class="code-hint">
                                    <span class="char-count" id="charCount2">0 characters</span>
                                    <div style="display:flex;gap:6px;">
                                        <button type="button" class="sample-btn" onclick="loadSampleAuto()">Load Sample</button>
                                        <button type="button" class="clean-btn" onclick="cleanAndFixCode('jsonCode2')">Clean & Fix</button>
                                        <button type="button" class="format-btn" onclick="formatCode('jsonCode2')">Format JSON</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="v-actions" style="margin-top:20px;">
                            <button type="button" class="btn-validate btn-validate-primary" id="validateBtn2" onclick="handleValidate('auto')">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4"></path><circle cx="12" cy="12" r="10"></circle></svg>
                                Validate &amp; Preview
                            </button>
                            <span class="simple-upload-link" onclick="handleSimpleUpload('auto')">Use simple upload (for small files)</span>
                        </div>

                        <div class="upload-progress-old" id="progress2">
                            <div class="spinner"></div>
                            <span>Importing data... please wait for large files.</span>
                        </div>
                    </div>

                </div>
            </div>

            <!-- ═══════════════════════════════════════════════════ -->
            <!-- VALIDATION PREVIEW PANEL (hidden by default) -->
            <!-- ═══════════════════════════════════════════════════ -->
            <div class="admin-card" id="validationPanel" style="display:none;margin-top:20px;">
                <div class="validation-panel" id="validationContent">
                    <!-- Populated by JS -->
                </div>
            </div>

            <!-- ═══════════════════════════════════════════════════ -->
            <!-- BULK UPLOAD PROGRESS PANEL (hidden by default) -->
            <!-- ═══════════════════════════════════════════════════ -->
            <div class="admin-card" id="progressPanel" style="display:none;margin-top:20px;">
                <div class="bulk-progress-panel">
                    <div class="bulk-progress-header">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#2563EB" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                        <h4 id="progressTitle">Bulk Upload Progress</h4>
                    </div>

                    <div class="bulk-progress-bar-wrap">
                        <div class="bulk-progress-bar" id="bulkProgressBar" style="width: 0%;"></div>
                        <span class="bulk-progress-text" id="bulkProgressText">0%</span>
                    </div>

                    <div class="bulk-progress-stats">
                        <div class="bp-stat">
                            <div class="bp-stat-label">Chunk</div>
                            <div class="bp-stat-value" id="bpChunk">0 / 0</div>
                        </div>
                        <div class="bp-stat">
                            <div class="bp-stat-label">Students</div>
                            <div class="bp-stat-value" id="bpStudents">0 / 0</div>
                        </div>
                        <div class="bp-stat">
                            <div class="bp-stat-label">Errors</div>
                            <div class="bp-stat-value" id="bpErrors" style="color:#DC2626;">0</div>
                        </div>
                        <div class="bp-stat">
                            <div class="bp-stat-label">Elapsed</div>
                            <div class="bp-stat-value" id="bpElapsed">0s</div>
                        </div>
                        <div class="bp-stat">
                            <div class="bp-stat-label">ETA</div>
                            <div class="bp-stat-value" id="bpEta">--</div>
                        </div>
                    </div>

                    <div class="bulk-progress-log" id="progressLog"></div>

                    <div style="margin-top:16px;display:flex;gap:10px;">
                        <button type="button" class="btn-validate btn-validate-danger" id="abortBtn" onclick="handleAbort()" style="display:none;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect></svg>
                            Abort Upload
                        </button>
                        <button type="button" class="btn-validate btn-validate-outline" id="resetBtn" onclick="resetAll()" style="display:none;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="1 4 1 10 7 10"></polyline><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"></path></svg>
                            Upload New File
                        </button>
                    </div>
                </div>
            </div>

            <!-- ═══════════════════════════════════════════════════ -->
            <!-- UPLOAD RESULT PANEL (hidden by default) -->
            <!-- ═══════════════════════════════════════════════════ -->
            <div class="admin-card" id="resultPanel" style="display:none;margin-top:20px;">
                <div class="result-summary" id="resultContent">
                    <!-- Populated by JS -->
                </div>
            </div>

            <!-- Batch Stats (if viewing a batch) -->
            <?php if ($currentBatch): ?>
            <div class="stats-grid" style="margin-top:20px;">
                <div class="stat-card">
                    <div class="stat-icon" style="color:#2563EB;background:#EFF6FF;"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle></svg></div>
                    <div class="stat-info"><h3><?php echo number_format($currentBatch['total_students']); ?></h3><p>Total Students</p></div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="color:#16A34A;background:#F0FDF4;"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg></div>
                    <div class="stat-info"><h3><?php echo number_format($currentBatch['total_passed']); ?></h3><p>Passed</p></div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="color:#DC2626;background:#FEF2F2;"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg></div>
                    <div class="stat-info"><h3><?php echo number_format($currentBatch['total_failed']); ?></h3><p>Failed/Referred</p></div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="color:#9333EA;background:#FAF5FF;"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg></div>
                    <div class="stat-info"><h3><?php echo htmlspecialchars($currentBatch['semester']); ?></h3><p><?php echo htmlspecialchars($currentBatch['exam_year']); ?> / Reg: <?php echo htmlspecialchars($currentBatch['regulation_year']); ?></p></div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Upload History -->
            <div class="admin-card" style="margin-top:20px;">
                <div class="admin-card-header">
                    <h3>Upload History</h3>
                    <span style="color:#6B7280;font-size:13px;"><?php echo count($allBatches); ?> batch(es)</span>
                </div>
                <div class="admin-card-body" style="padding:0;">
                    <?php if (empty($allBatches)): ?>
                        <div style="padding:48px 24px;text-align:center;">
                            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#CBD5E1" stroke-width="1.5" style="margin:0 auto 12px;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                            <p style="color:#94A3B8;font-size:14px;">No data uploaded yet. Upload your first JSON file above.</p>
                        </div>
                    <?php else: ?>
                    <div style="overflow-x:auto;">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Regulation</th>
                                    <th>Exam Year</th>
                                    <th>Semester</th>
                                    <th>Students</th>
                                    <th>Passed</th>
                                    <th>Failed</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th class="table-actions">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($allBatches as $batch): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($batch['regulation_year']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($batch['exam_year']); ?></td>
                                    <td><?php echo htmlspecialchars($batch['semester']); ?></td>
                                    <td><?php echo number_format($batch['total_students']); ?></td>
                                    <td><span style="color:#16A34A;font-weight:600;"><?php echo number_format($batch['total_passed']); ?></span></td>
                                    <td><span style="color:#DC2626;font-weight:600;"><?php echo number_format($batch['total_failed']); ?></span></td>
                                    <td>
                                        <?php if ($batch['status'] === 'completed'): ?>
                                            <span class="badge badge-success">Completed</span>
                                        <?php else: ?>
                                            <span class="badge badge-danger">Failed</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($batch['created_at'])); ?></td>
                                    <td class="table-actions">
                                        <a href="result-data.php?batch_id=<?php echo (int)$batch['id']; ?>" class="btn btn-sm btn-edit">View</a>
                                        <a href="result-json-upload.php?action=delete&id=<?php echo (int)$batch['id']; ?>" class="btn btn-sm btn-delete" onclick="return confirm('Delete this batch and all student data?');">Delete</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <script>
    (function() {
        'use strict';

        // ═══════════════════════════════════════════════════════════
        // GLOBAL STATE
        // ═══════════════════════════════════════════════════════════
        var currentInputMethod = { manual: 'file', auto: 'file' };
        var validatedStudents = null;   // Array of normalized students
        var validatedMetadata = null;  // { exam_year, regulation_year, semester, program }
        var validatedMode = null;      // 'manual' or 'auto'
        var uploadAbortController = null;
        var uploadState = { batchId: null, aborted: false };

        // ═══════════════════════════════════════════════════════════
        // SIDEBAR
        // ═══════════════════════════════════════════════════════════
        var sidebar = document.getElementById('sidebar');
        var overlay = document.getElementById('sidebarOverlay');
        var toggle = document.getElementById('mobileToggle');
        function openSidebar() { sidebar.classList.add('open'); overlay.classList.add('active'); }
        function closeSidebar() { sidebar.classList.remove('open'); overlay.classList.remove('active'); }
        if (toggle) toggle.addEventListener('click', function(e) { e.stopPropagation(); sidebar.classList.contains('open') ? closeSidebar() : openSidebar(); });
        if (overlay) overlay.addEventListener('click', closeSidebar);

        // ═══════════════════════════════════════════════════════════
        // MODE SWITCH
        // ═══════════════════════════════════════════════════════════
        window.switchMode = function(mode) {
            document.getElementById('tabManual').classList.toggle('active', mode === 'manual');
            document.getElementById('tabAuto').classList.toggle('active', mode === 'auto');
            document.getElementById('panelManual').classList.toggle('active', mode === 'manual');
            document.getElementById('panelAuto').classList.toggle('active', mode === 'auto');
        };

        // ═══════════════════════════════════════════════════════════
        // INPUT METHOD SWITCH (File vs Code)
        // ═══════════════════════════════════════════════════════════
        window.switchInputMethod = function(mode, method) {
            currentInputMethod[mode] = method;
            var prefix = mode;
            document.getElementById(prefix + 'TabFile').classList.toggle('active', method === 'file');
            document.getElementById(prefix + 'TabCode').classList.toggle('active', method === 'code');
            document.getElementById(prefix + 'PanelFile').classList.toggle('active', method === 'file');
            document.getElementById(prefix + 'PanelCode').classList.toggle('active', method === 'code');
        };

        // ═══════════════════════════════════════════════════════════
        // FILE DROP ZONES
        // ═══════════════════════════════════════════════════════════
        setupDropZone('dropZone1', 'file1', 'fileInfo1');
        setupDropZone('dropZone2', 'file2', 'fileInfo2');

        function setupDropZone(zoneId, inputId, infoId) {
            var zone = document.getElementById(zoneId);
            var input = document.getElementById(inputId);
            var info = document.getElementById(infoId);
            if (!zone || !input) return;

            zone.addEventListener('click', function(e) {
                if (e.target.closest('.dz-remove')) return;
                input.click();
            });
            zone.addEventListener('dragover', function(e) { e.preventDefault(); zone.classList.add('dragover'); });
            zone.addEventListener('dragleave', function() { zone.classList.remove('dragover'); });
            zone.addEventListener('drop', function(e) {
                e.preventDefault();
                zone.classList.remove('dragover');
                if (e.dataTransfer.files.length) {
                    input.files = e.dataTransfer.files;
                    showFileInfo(e.dataTransfer.files[0], info, zone);
                }
            });
            input.addEventListener('change', function() {
                if (input.files.length) showFileInfo(input.files[0], info, zone);
            });
        }

        function showFileInfo(file, infoEl, zone) {
            var sizeMB = (file.size / (1024 * 1024)).toFixed(1);
            infoEl.innerHTML = file.name + ' <span class="dz-size">(' + sizeMB + ' MB)</span> <span class="dz-remove" onclick="clearFile(event, \'' + zone.id + '\', \'' + infoEl.id + '\')">&times; Remove</span>';
            infoEl.style.display = 'block';
            zone.style.borderColor = '#2563EB';
            zone.style.background = '#EFF6FF';
        }

        window.clearFile = function(e, zoneId, infoId) {
            e.stopPropagation();
            var zone = document.getElementById(zoneId);
            var info = document.getElementById(infoId);
            var input = zone.querySelector('input[type="file"]');
            if (input) input.value = '';
            info.style.display = 'none';
            info.innerHTML = '';
            zone.style.borderColor = '#CBD5E1';
            zone.style.background = '#F8FAFC';
        };

        // ═══════════════════════════════════════════════════════════
        // CHARACTER COUNTER FOR TEXTAREAS
        // ═══════════════════════════════════════════════════════════
        var code1 = document.getElementById('jsonCode1');
        var code2 = document.getElementById('jsonCode2');
        var count1 = document.getElementById('charCount1');
        var count2 = document.getElementById('charCount2');

        if (code1) code1.addEventListener('input', function() {
            count1.textContent = this.value.length.toLocaleString() + ' characters';
        });
        if (code2) code2.addEventListener('input', function() {
            count2.textContent = this.value.length.toLocaleString() + ' characters';
        });

        // ═══════════════════════════════════════════════════════════
        // JSON CLEANUP FUNCTION
        // ═══════════════════════════════════════════════════════════
        function cleanupJsonString(str) {
            // Remove BOM
            if (str.charCodeAt(0) === 0xFEFF) str = str.substring(1);
            str = str.replace(/\uFEFF/g, '');
            // Remove single-line comments
            str = str.replace(/^\s*\/\/.*$/gm, '');
            // Remove multi-line comments
            str = str.replace(/\/\*[\s\S]*?\*\//g, '');
            // Remove trailing commas before } or ]
            str = str.replace(/,\s*([\]}])/g, '$1');
            // Remove trailing commas before }/] with newlines
            str = str.replace(/,\s*\n\s*([\]}])/g, '$1');
            // Remove non-printable chars (keep normal whitespace)
            str = str.replace(/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/g, '');
            // Normalize line endings
            str = str.replace(/\r\n/g, '\n').replace(/\r/g, '\n');
            return str.trim();
        }

        // ═══════════════════════════════════════════════════════════
        // CLEAN & FIX JSON BUTTON
        // ═══════════════════════════════════════════════════════════
        window.cleanAndFixCode = function(textareaId) {
            var ta = document.getElementById(textareaId);
            if (!ta || !ta.value.trim()) { showToast('Paste JSON code first.', 'error'); return; }
            var cleaned = cleanupJsonString(ta.value);
            try {
                var parsed = JSON.parse(cleaned);
                ta.value = JSON.stringify(parsed, null, 2);
                var countEl = textareaId === 'jsonCode1' ? count1 : count2;
                if (countEl) countEl.textContent = ta.value.length.toLocaleString() + ' characters';
                showToast('JSON cleaned and formatted successfully!', 'success');
            } catch(err) {
                ta.value = cleaned;
                var countEl = textareaId === 'jsonCode1' ? count1 : count2;
                if (countEl) countEl.textContent = ta.value.length.toLocaleString() + ' characters';
                showToast('Auto-cleaned but JSON still has issues. Check line ' + err.message.split('line ')[1] + '.', 'error');
            }
        };

        // ═══════════════════════════════════════════════════════════
        // FORMAT JSON BUTTON
        // ═══════════════════════════════════════════════════════════
        window.formatCode = function(textareaId) {
            var ta = document.getElementById(textareaId);
            if (!ta || !ta.value.trim()) { showToast('Paste JSON code first.', 'error'); return; }
            var cleaned = cleanupJsonString(ta.value);
            try {
                var parsed = JSON.parse(cleaned);
                ta.value = JSON.stringify(parsed, null, 2);
                var countEl = textareaId === 'jsonCode1' ? count1 : count2;
                if (countEl) countEl.textContent = ta.value.length.toLocaleString() + ' characters';
                showToast('JSON formatted successfully.', 'success');
            } catch(err) {
                showToast('Invalid JSON: ' + err.message, 'error');
            }
        };

        // ═══════════════════════════════════════════════════════════
        // LOAD SAMPLE BUTTONS
        // ═══════════════════════════════════════════════════════════
        window.loadSampleManual = function() {
            var sample = {
                "200010": {
                    "roll": "200010",
                    "institute": "Dhaka Polytechnic Institute, Dhaka",
                    "institute_code": "49166",
                    "status": "passed",
                    "cgpa": 3.52,
                    "failed_subjects": [],
                    "total_failed": 0
                },
                "200013": {
                    "roll": "200013",
                    "institute": "Thakurgaon Polytechnic Institute, Thakurgaon",
                    "institute_code": "12053",
                    "status": "referred",
                    "cgpa": null,
                    "failed_subjects": ["25913(T)", "26711(T)"],
                    "total_failed": 2
                },
                "200015": {
                    "roll": "200015",
                    "institute": "Dhaka Polytechnic Institute, Dhaka",
                    "institute_code": "49166",
                    "status": "passed",
                    "cgpa": 2.81,
                    "failed_subjects": [],
                    "total_failed": 0
                }
            };
            document.getElementById('jsonCode1').value = JSON.stringify(sample, null, 2);
            count1.textContent = document.getElementById('jsonCode1').value.length.toLocaleString() + ' characters';
            showToast('Sample data loaded. Fill the fields above and click Validate & Preview.', 'success');
        };

        window.loadSampleAuto = function() {
            var sample = {
                "exam_year": "2022",
                "regulation_year": "2016",
                "semester": "1st Semester",
                "program": "Diploma In Engineering",
                "students": [
                    {
                        "roll": "200010",
                        "institute_code": "49166",
                        "institute": "Dhaka Polytechnic Institute, Dhaka",
                        "cgpa": 3.52,
                        "status": "passed",
                        "failed_subjects": [],
                        "total_failed": 0
                    },
                    {
                        "roll": "200013",
                        "institute_code": "12053",
                        "institute": "Thakurgaon Polytechnic Institute, Thakurgaon",
                        "cgpa": null,
                        "status": "referred",
                        "failed_subjects": ["25913(T)", "26711(T)"],
                        "total_failed": 2
                    },
                    {
                        "roll": "200020",
                        "institute_code": "49265",
                        "institute": "Chittagong Polytechnic Institute, Chittagong",
                        "cgpa": 3.98,
                        "status": "passed",
                        "failed_subjects": [],
                        "total_failed": 0
                    }
                ]
            };
            document.getElementById('jsonCode2').value = JSON.stringify(sample, null, 2);
            count2.textContent = document.getElementById('jsonCode2').value.length.toLocaleString() + ' characters';
            showToast('Sample complete JSON loaded. Click Validate & Preview.', 'success');
        };

        // ═══════════════════════════════════════════════════════════
        // NORMALIZE STUDENT DATA
        // Maps various field names to standard format
        // ═══════════════════════════════════════════════════════════
        function normalizeStudent(s) {
            var out = {};

            // Roll
            out.roll = String(s.roll || s.Roll || s.roll_number || '').trim();

            // College name: institute / college_name
            out.college_name = String(
                s.college_name || s.institute || s.Institute || s.college || s.institute_name || ''
            ).trim();

            // College code: institute_code / college_code / code
            out.college_code = String(
                s.college_code || s.institute_code || s.code || s.Code || s.instituteCode || ''
            ).trim();

            // GPA: gpa / cgpa
            var gpaRaw = (s.gpa !== undefined && s.gpa !== null) ? s.gpa :
                         (s.cgpa !== undefined && s.cgpa !== null) ? s.cgpa : null;
            out.gpa = (gpaRaw !== null && gpaRaw !== '' && String(gpaRaw).trim() !== '') ? parseFloat(gpaRaw) : null;
            if (isNaN(out.gpa)) out.gpa = null;

            // Result type: result_type / status
            var statusRaw = String(s.result_type || s.status || s.Status || s.result || '').trim().toLowerCase();
            if (statusRaw === 'passed' || statusRaw === 'pass') {
                out.result_type = 'passed';
            } else if (statusRaw === 'referred' || statusRaw === 'refer' || statusRaw === 're-appear' || statusRaw === 'reappear') {
                out.result_type = 'referred';
            } else if (statusRaw === 'failed_4plus' || statusRaw === '4+ failed') {
                out.result_type = 'failed_4plus';
            } else if (statusRaw === 'failed' || statusRaw === 'fail') {
                out.result_type = 'referred'; // treat as referred
            } else if (statusRaw === 'promoted' || statusRaw === 'promotion') {
                out.result_type = 'passed'; // promoted = passed
            } else {
                out.result_type = out.gpa !== null ? 'passed' : 'passed'; // default
            }

            // Failed subjects
            out.failed_subjects = [];
            var rawFailed = s.failed_subjects || s.fail_subjects || s.failedSubjects || [];

            if (Array.isArray(rawFailed)) {
                for (var i = 0; i < rawFailed.length; i++) {
                    var fs = rawFailed[i];
                    if (typeof fs === 'string') {
                        // Parse "25913(T)" format
                        var match = fs.match(/^(\d{5})\s*\((\w+)\)$/);
                        if (match) {
                            out.failed_subjects.push({ code: match[1], fail_type: match[2] });
                        } else {
                            // Try "25913-T" or just a code
                            var match2 = fs.match(/^(\d{5})\s*[-/]?\s*(\w+)?$/);
                            if (match2) {
                                out.failed_subjects.push({ code: match2[1], fail_type: (match2[2] || 'T') });
                            } else {
                                out.failed_subjects.push({ code: String(fs).trim(), fail_type: 'T' });
                            }
                        }
                    } else if (typeof fs === 'object' && fs !== null) {
                        out.failed_subjects.push({
                            code: String(fs.code || fs.subject_code || '').trim(),
                            fail_type: String(fs.fail_type || fs.type || 'T').trim()
                        });
                    }
                }
            }

            return out;
        }

        // ═══════════════════════════════════════════════════════════
        // VALIDATE JSON DATA (CLIENT-SIDE)
        // ═══════════════════════════════════════════════════════════
        function validateJsonData(jsonData, mode) {
            var result = {
                valid: false,
                totalStudents: 0,
                students: [],
                errors: [],
                samplePreview: [],
                format: '',
                fieldsFound: [],
                metadata: null
            };

            // Auto mode: expect object with metadata + students
            if (mode === 'auto') {
                if (!jsonData || typeof jsonData !== 'object' || Array.isArray(jsonData)) {
                    result.errors.push('Expected a JSON object with exam_year, regulation_year, semester, students[]');
                    return result;
                }

                result.metadata = {
                    exam_year: jsonData.exam_year || jsonData.ExamYear || jsonData.examYear || '',
                    regulation_year: jsonData.regulation_year || jsonData.RegulationYear || jsonData.regulationYear || '',
                    semester: jsonData.semester || jsonData.Semester || '',
                    program: jsonData.program || jsonData.Program || 'Diploma In Engineering'
                };

                if (!result.metadata.exam_year) result.errors.push('Missing exam_year in JSON');
                if (!result.metadata.regulation_year) result.errors.push('Missing regulation_year in JSON');
                if (!result.metadata.semester) result.errors.push('Missing semester in JSON');

                var studentsArr = jsonData.students || jsonData.Students || jsonData.data || null;
                if (!studentsArr || !Array.isArray(studentsArr)) {
                    result.errors.push('Missing or invalid "students" array in JSON');
                    return result;
                }

                result.format = 'Complete JSON (object with metadata)';
                result.students = studentsArr;
            }

            // Manual mode: various formats
            if (mode === 'manual') {
                if (!jsonData || typeof jsonData !== 'object') {
                    result.errors.push('Invalid JSON data');
                    return result;
                }

                // Format 1: Object with "students" array
                if (jsonData.students && Array.isArray(jsonData.students)) {
                    result.format = 'Object with students[] array';
                    result.students = jsonData.students;
                }
                // Format 2: Array of student objects
                else if (Array.isArray(jsonData)) {
                    result.format = 'Array of student objects';
                    result.students = jsonData;
                }
                // Format 3: Object with roll numbers as keys
                else {
                    var keys = Object.keys(jsonData);
                    var allValuesAreObjects = keys.every(function(k) {
                        return jsonData[k] && typeof jsonData[k] === 'object' && !Array.isArray(jsonData[k]);
                    });
                    if (allValuesAreObjects && keys.length > 0) {
                        result.format = 'Object (roll as key)';
                        result.students = keys.map(function(k) {
                            var s = jsonData[k];
                            // Merge the key as roll if not present
                            if (!s.roll) s.roll = k;
                            return s;
                        });
                    } else {
                        result.errors.push('Could not detect student data format. Expected: students[] array, plain array, or {roll: data} object.');
                        return result;
                    }
                }
            }

            // Normalize students
            result.totalStudents = result.students.length;
            if (result.totalStudents === 0) {
                result.errors.push('No student records found in JSON.');
                return result;
            }

            var fieldSet = {};
            var previewCount = 0;
            var normalizedList = [];

            for (var i = 0; i < result.students.length; i++) {
                var raw = result.students[i];
                var norm = normalizeStudent(raw);
                normalizedList.push(norm);

                // Collect field names from first record
                if (i === 0) {
                    for (var key in raw) {
                        if (raw.hasOwnProperty(key)) {
                            fieldSet[key] = true;
                        }
                    }
                }

                // Validate each student
                if (!norm.roll) {
                    result.errors.push({
                        type: 'missing_roll',
                        index: i,
                        msg: 'Record #' + (i + 1) + ': Missing roll number'
                    });
                    continue;
                }

                if (!norm.college_name && !norm.college_code) {
                    result.errors.push({
                        type: 'missing_college',
                        index: i,
                        roll: norm.roll,
                        msg: 'Roll ' + norm.roll + ': Missing institute name and code'
                    });
                }

                var validStatuses = ['passed', 'referred', 'failed_4plus'];
                if (raw.status !== undefined) {
                    var rawStatus = String(raw.status).trim().toLowerCase();
                    if (!['passed', 'pass', 'referred', 'refer', 're-appear', 'reappear', 'failed', 'fail', 'failed_4plus', '4+ failed', 'promoted', 'promotion'].includes(rawStatus)) {
                        result.errors.push({
                            type: 'invalid_status',
                            index: i,
                            roll: norm.roll,
                            msg: 'Roll ' + norm.roll + ': Unrecognized status "' + raw.status + '"'
                        });
                    }
                }

                // Collect sample preview (first 5 valid)
                if (previewCount < 5) {
                    result.samplePreview.push(norm);
                    previewCount++;
                }
            }

            result.fieldsFound = Object.keys(fieldSet);
            result.students = normalizedList; // Replace with normalized version

            // Separate critical errors from warnings
            var criticalErrors = result.errors.filter(function(e) { return typeof e === 'string'; });
            var warnings = result.errors.filter(function(e) { return typeof e === 'object'; });
            result.errors = warnings;

            result.valid = criticalErrors.length === 0 && result.totalStudents > 0;

            return result;
        }

        // ═══════════════════════════════════════════════════════════
        // HANDLE VALIDATE BUTTON
        // ═══════════════════════════════════════════════════════════
        window.handleValidate = function(mode) {
            validatedStudents = null;
            validatedMetadata = null;
            validatedMode = mode;

            var btnId = mode === 'manual' ? 'validateBtn1' : 'validateBtn2';
            var btn = document.getElementById(btnId);
            btn.disabled = true;
            btn.textContent = 'Validating...';

            // Step 1: Get JSON string
            var method = currentInputMethod[mode];
            var jsonStr = '';

            if (method === 'file') {
                var fileInput = document.getElementById(mode === 'manual' ? 'file1' : 'file2');
                if (!fileInput || !fileInput.files.length) {
                    showToast('Please select a JSON file first.', 'error');
                    btn.disabled = false;
                    btn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4"></path><circle cx="12" cy="12" r="10"></circle></svg> Validate &amp; Preview';
                    return;
                }
                // Read file
                var reader = new FileReader();
                reader.onload = function(e) {
                    processValidation(e.target.result, mode, btn);
                };
                reader.onerror = function() {
                    showToast('Failed to read file. Please try again.', 'error');
                    btn.disabled = false;
                    btn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4"></path><circle cx="12" cy="12" r="10"></circle></svg> Validate &amp; Preview';
                };
                reader.readAsText(fileInput.files[0]);
            } else {
                var textarea = document.getElementById(mode === 'manual' ? 'jsonCode1' : 'jsonCode2');
                jsonStr = textarea.value.trim();
                if (!jsonStr) {
                    showToast('Please paste JSON code first.', 'error');
                    btn.disabled = false;
                    btn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4"></path><circle cx="12" cy="12" r="10"></circle></svg> Validate &amp; Preview';
                    return;
                }
                processValidation(jsonStr, mode, btn);
            }
        };

        function processValidation(jsonStr, mode, btn) {
            // Clean up
            var cleaned = cleanupJsonString(jsonStr);

            // Parse
            var jsonData;
            try {
                jsonData = JSON.parse(cleaned);
            } catch(err) {
                showToast('Invalid JSON: ' + err.message + '. Try clicking "Clean & Fix" first.', 'error');
                btn.disabled = false;
                btn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4"></path><circle cx="12" cy="12" r="10"></circle></svg> Validate &amp; Preview';
                return;
            }

            // Validate
            var result = validateJsonData(jsonData, mode);

            if (!result.valid) {
                renderValidationErrors(result, mode);
                btn.disabled = false;
                btn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4"></path><circle cx="12" cy="12" r="10"></circle></svg> Validate &amp; Preview';
                return;
            }

            // For manual mode, get metadata from form
            if (mode === 'manual') {
                result.metadata = {
                    exam_year: document.getElementById('manualExamYear').value,
                    regulation_year: document.getElementById('manualRegYear').value,
                    semester: document.getElementById('manualSemester').value,
                    program: document.getElementById('manualProgram').value || 'Diploma In Engineering'
                };

                if (!result.metadata.exam_year || !result.metadata.regulation_year || !result.metadata.semester) {
                    showToast('Please fill in Regulation Year, Exam Year, and Semester above.', 'error');
                    btn.disabled = false;
                    btn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4"></path><circle cx="12" cy="12" r="10"></circle></svg> Validate &amp; Preview';
                    return;
                }
            }

            // Store validated data
            validatedStudents = result.students;
            validatedMetadata = result.metadata;
            validatedMode = mode;

            // Render preview
            renderValidationSuccess(result, mode);

            btn.disabled = false;
            btn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4"></path><circle cx="12" cy="12" r="10"></circle></svg> Re-validate';
        }

        // ═══════════════════════════════════════════════════════════
        // RENDER VALIDATION SUCCESS
        // ═══════════════════════════════════════════════════════════
        function renderValidationSuccess(result, mode) {
            var panel = document.getElementById('validationPanel');
            var content = document.getElementById('validationContent');

            var totalChunks = Math.ceil(result.totalStudents / 2000);

            var fieldsHtml = '';
            for (var i = 0; i < result.fieldsFound.length; i++) {
                fieldsHtml += '<span class="v-field-tag">' + escHtml(result.fieldsFound[i]) + '</span>';
            }

            var previewHtml = '';
            for (var i = 0; i < result.samplePreview.length; i++) {
                var s = result.samplePreview[i];
                var statusClass = 'v-status-passed';
                if (s.result_type === 'referred') statusClass = 'v-status-referred';
                else if (s.result_type === 'failed_4plus') statusClass = 'v-status-other';
                previewHtml += '<tr>' +
                    '<td>' + escHtml(s.roll) + '</td>' +
                    '<td>' + escHtml(truncate(s.college_name, 30)) + '</td>' +
                    '<td class="' + statusClass + '">' + escHtml(capitalize(s.result_type)) + '</td>' +
                    '<td>' + (s.gpa !== null ? s.gpa.toFixed(2) : '-') + '</td>' +
                    '</tr>';
            }

            var errorsHtml = '';
            if (result.errors.length > 0) {
                errorsHtml = '<div class="v-errors">' +
                    '<div class="v-errors-title">' +
                    '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>' +
                    'Found ' + result.errors.length + ' record(s) with issues:</div>' +
                    '<ul class="v-error-list">';

                var showCount = Math.min(result.errors.length, 10);
                for (var i = 0; i < showCount; i++) {
                    errorsHtml += '<li class="v-error-item">' + escHtml(result.errors[i].msg) + '</li>';
                }
                if (result.errors.length > 10) {
                    errorsHtml += '<div class="v-error-more">... and ' + (result.errors.length - 10) + ' more</div>';
                }
                errorsHtml += '</ul></div>';
            }

            var warningHtml = '';
            if (result.totalStudents >= 1000) {
                warningHtml = '<div class="v-warning-box">' +
                    '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>' +
                    '<span><strong>' + result.totalStudents.toLocaleString() + '</strong> students will be uploaded in <strong>' + totalChunks + ' chunks</strong> of 2,000 students each. This may take a few minutes. Do not close this page.</span>' +
                    '</div>';
            }

            content.innerHTML =
                '<div class="validation-header success-header">' +
                    '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#16A34A" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>' +
                    '<h4>JSON Validated Successfully</h4>' +
                '</div>' +
                '<div class="validation-body">' +
                    '<div class="validation-stats">' +
                        '<div class="v-stat"><div class="v-stat-label">Total Students</div><div class="v-stat-value highlight">' + result.totalStudents.toLocaleString() + '</div></div>' +
                        '<div class="v-stat"><div class="v-stat-label">Format</div><div class="v-stat-value" style="font-size:13px;">' + escHtml(result.format) + '</div></div>' +
                        (mode === 'auto' ?
                        '<div class="v-stat"><div class="v-stat-label">Exam Year</div><div class="v-stat-value">' + escHtml(result.metadata.exam_year) + '</div></div>' +
                        '<div class="v-stat"><div class="v-stat-label">Semester</div><div class="v-stat-value">' + escHtml(result.metadata.semester) + '</div></div>' : '') +
                    '</div>' +
                    '<div class="v-info-row">' +
                        '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#2563EB" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>' +
                        '<span><span class="v-info-label">Fields found:</span></span>' +
                    '</div>' +
                    '<div class="v-fields">' + fieldsHtml + '</div>' +

                    '<div class="v-preview-title">Sample Preview (first ' + result.samplePreview.length + '):</div>' +
                    '<div class="v-preview-table-wrap">' +
                        '<table class="v-preview-table">' +
                            '<thead><tr><th>Roll</th><th>Institute</th><th>Status</th><th>GPA</th></tr></thead>' +
                            '<tbody>' + previewHtml + '</tbody>' +
                        '</table>' +
                    '</div>' +

                    errorsHtml +
                    warningHtml +

                    '<div class="v-actions">' +
                        '<button type="button" class="btn-validate btn-validate-success" onclick="startBulkUpload()">' +
                            '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>' +
                            'Start Bulk Upload (' + result.totalStudents.toLocaleString() + ' students)' +
                        '</button>' +
                        '<button type="button" class="btn-validate btn-validate-outline" onclick="document.getElementById(\'validationPanel\').style.display=\'none\'">Cancel</button>' +
                    '</div>' +
                '</div>';

            panel.style.display = 'block';
            panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        // ═══════════════════════════════════════════════════════════
        // RENDER VALIDATION ERRORS
        // ═══════════════════════════════════════════════════════════
        function renderValidationErrors(result, mode) {
            var panel = document.getElementById('validationPanel');
            var content = document.getElementById('validationContent');

            var errorsHtml = '<ul class="v-error-list">';
            for (var i = 0; i < result.errors.length; i++) {
                var msg = typeof result.errors[i] === 'string' ? result.errors[i] : result.errors[i].msg;
                errorsHtml += '<li class="v-error-item">' + escHtml(msg) + '</li>';
            }
            errorsHtml += '</ul>';

            content.innerHTML =
                '<div class="validation-header error-header">' +
                    '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#DC2626" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>' +
                    '<h4>Validation Failed</h4>' +
                '</div>' +
                '<div class="validation-body">' +
                    '<p style="font-size:13px;color:#7F1D1D;margin-bottom:12px;">Please fix the following issues and try again:</p>' +
                    errorsHtml +
                    '<div class="v-actions">' +
                        '<button type="button" class="btn-validate btn-validate-outline" onclick="document.getElementById(\'validationPanel\').style.display=\'none\'">Dismiss</button>' +
                    '</div>' +
                '</div>';

            panel.style.display = 'block';
            panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        // ═══════════════════════════════════════════════════════════
        // CHUNKED BULK UPLOAD
        // ═══════════════════════════════════════════════════════════
        var CHUNK_SIZE = 2000;

        window.startBulkUpload = async function() {
            if (!validatedStudents || !validatedMetadata) {
                showToast('No validated data. Please validate first.', 'error');
                return;
            }

            // Reset state
            uploadState = { batchId: null, aborted: false };

            var totalStudents = validatedStudents.length;
            var totalChunks = Math.ceil(totalStudents / CHUNK_SIZE);
            var totalInserted = 0;
            var allErrors = [];
            var startTime = Date.now();

            // Show progress panel
            document.getElementById('validationPanel').style.display = 'none';
            document.getElementById('resultPanel').style.display = 'none';
            var progressPanel = document.getElementById('progressPanel');
            progressPanel.style.display = 'block';
            progressPanel.scrollIntoView({ behavior: 'smooth', block: 'start' });

            // Reset progress UI
            document.getElementById('progressTitle').textContent = 'Bulk Upload Progress';
            document.getElementById('bulkProgressBar').style.width = '0%';
            document.getElementById('bulkProgressBar').className = 'bulk-progress-bar';
            document.getElementById('bulkProgressText').textContent = '0%';
            document.getElementById('bpChunk').textContent = '0 / ' + totalChunks;
            document.getElementById('bpStudents').textContent = '0 / ' + totalStudents.toLocaleString();
            document.getElementById('bpErrors').textContent = '0';
            document.getElementById('bpElapsed').textContent = '0s';
            document.getElementById('bpEta').textContent = '--';
            document.getElementById('progressLog').innerHTML = '';
            document.getElementById('abortBtn').style.display = 'inline-flex';
            document.getElementById('resetBtn').style.display = 'none';

            // Disable validate buttons
            document.getElementById('validateBtn1').disabled = true;
            document.getElementById('validateBtn2').disabled = true;

            addLog('info', 'Starting bulk upload for ' + totalStudents.toLocaleString() + ' students in ' + totalChunks + ' chunks...');

            try {
                // ─── Step 1: INIT ───
                addLog('info', 'Creating batch (init)...');

                var initRes = await fetch('../api/upload-bulk.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({
                        action: 'init',
                        exam_year: validatedMetadata.exam_year,
                        regulation_year: validatedMetadata.regulation_year,
                        semester: validatedMetadata.semester,
                        program: validatedMetadata.program || 'Diploma In Engineering',
                        total_students: totalStudents
                    })
                });

                var initData = await initRes.json();
                if (!initData.success) {
                    throw new Error(initData.error || 'Failed to create batch');
                }

                uploadState.batchId = initData.batch_id;
                addLog('ok', 'Batch #' + initData.batch_id + ' created successfully.');

                // ─── Step 2: UPLOAD CHUNKS ───
                for (var i = 0; i < totalChunks; i++) {
                    if (uploadState.aborted) {
                        addLog('warn', 'Upload aborted by user at chunk ' + (i + 1) + '.');
                        break;
                    }

                    updateBulkProgress(i, totalChunks, totalStudents, totalInserted, allErrors.length, startTime);

                    var start = i * CHUNK_SIZE;
                    var end = Math.min(start + CHUNK_SIZE, totalStudents);
                    var chunk = validatedStudents.slice(start, end);

                    addLog('info', 'Uploading chunk ' + (i + 1) + '/' + totalChunks + ' (' + start + '-' + end + ')...');

                    var chunkRes = null;
                    var chunkData = null;

                    // Try once, retry once on failure
                    for (var attempt = 0; attempt < 2; attempt++) {
                        try {
                            chunkRes = await fetch('../api/upload-bulk.php', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                                body: new URLSearchParams({
                                    action: 'chunk',
                                    batch_id: uploadState.batchId,
                                    chunk_index: i,
                                    students: JSON.stringify(chunk)
                                })
                            });
                            chunkData = await chunkRes.json();

                            if (chunkData.success) {
                                totalInserted += (chunkData.inserted || 0);
                                if (chunkData.errors && chunkData.errors.length > 0) {
                                    allErrors = allErrors.concat(chunkData.errors);
                                }
                                addLog('ok', 'Chunk ' + (i + 1) + '/' + totalChunks + ' uploaded. Inserted: ' + (chunkData.inserted || 0));
                                break; // Success, exit retry loop
                            } else {
                                if (attempt === 0) {
                                    addLog('warn', 'Chunk ' + (i + 1) + ' failed. Retrying...');
                                    await sleep(1000);
                                } else {
                                    addLog('err', 'Chunk ' + (i + 1) + ' failed after retry: ' + (chunkData.error || 'Unknown error'));
                                    allErrors.push({ chunk: i + 1, error: chunkData.error || 'Unknown error' });
                                }
                            }
                        } catch(fetchErr) {
                            if (attempt === 0) {
                                addLog('warn', 'Chunk ' + (i + 1) + ' network error. Retrying...');
                                await sleep(2000);
                            } else {
                                addLog('err', 'Chunk ' + (i + 1) + ' network error after retry: ' + fetchErr.message);
                                allErrors.push({ chunk: i + 1, error: fetchErr.message });
                            }
                        }
                    }
                }

                // ─── Step 3: FINISH ───
                if (!uploadState.aborted && uploadState.batchId) {
                    addLog('info', 'Finalizing batch...');
                    updateBulkProgress(totalChunks, totalChunks, totalStudents, totalInserted, allErrors.length, startTime);

                    try {
                        var finishRes = await fetch('../api/upload-bulk.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: new URLSearchParams({
                                action: 'finish',
                                batch_id: uploadState.batchId
                            })
                        });
                        var finishData = await finishRes.json();

                        if (finishData.success) {
                            addLog('ok', 'Upload complete! Total: ' + finishData.total_students + ', Passed: ' + finishData.total_passed + ', Failed: ' + finishData.total_failed);
                            showResultPanel(true, finishData, allErrors);
                        } else {
                            addLog('err', 'Failed to finalize: ' + (finishData.error || 'Unknown error'));
                            showResultPanel(false, finishData, allErrors);
                        }
                    } catch(err) {
                        addLog('err', 'Failed to finalize: ' + err.message);
                        showResultPanel(false, { error: err.message }, allErrors);
                    }
                } else if (uploadState.aborted) {
                    // Abort the batch
                    if (uploadState.batchId) {
                        try {
                            await fetch('../api/upload-bulk.php', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                                body: new URLSearchParams({
                                    action: 'abort',
                                    batch_id: uploadState.batchId
                                })
                            });
                            addLog('warn', 'Batch #' + uploadState.batchId + ' has been deleted.');
                        } catch(err) { /* ignore */ }
                    }
                    document.getElementById('bulkProgressBar').className = 'bulk-progress-bar aborted';
                    document.getElementById('progressTitle').textContent = 'Upload Aborted';
                    addLog('warn', 'Upload was aborted. Batch deleted.');
                    document.getElementById('abortBtn').style.display = 'none';
                    document.getElementById('resetBtn').style.display = 'inline-flex';
                }

            } catch(err) {
                addLog('err', 'Fatal error: ' + err.message);
                showResultPanel(false, { error: err.message }, allErrors);
            }

            // Re-enable validate buttons
            document.getElementById('validateBtn1').disabled = false;
            document.getElementById('validateBtn2').disabled = false;
        };

        // ═══════════════════════════════════════════════════════════
        // UPDATE BULK PROGRESS UI
        // ═══════════════════════════════════════════════════════════
        function updateBulkProgress(currentChunk, totalChunks, totalStudents, inserted, errorCount, startTime) {
            var pct = totalChunks > 0 ? Math.round((currentChunk / totalChunks) * 100) : 0;
            var elapsed = Math.round((Date.now() - startTime) / 1000);

            document.getElementById('bulkProgressBar').style.width = pct + '%';
            document.getElementById('bulkProgressText').textContent = pct + '%';
            document.getElementById('bpChunk').textContent = currentChunk + ' / ' + totalChunks;
            document.getElementById('bpStudents').textContent = inserted.toLocaleString() + ' / ' + totalStudents.toLocaleString();
            document.getElementById('bpErrors').textContent = errorCount;
            document.getElementById('bpElapsed').textContent = formatTime(elapsed);

            // ETA
            if (currentChunk > 0 && inserted > 0) {
                var rate = inserted / elapsed;
                var remaining = totalStudents - inserted;
                var etaSeconds = rate > 0 ? Math.round(remaining / rate) : 0;
                document.getElementById('bpEta').textContent = formatTime(etaSeconds);
            }
        }

        // ═══════════════════════════════════════════════════════════
        // SHOW RESULT PANEL
        // ═══════════════════════════════════════════════════════════
        function showResultPanel(success, stats, errors) {
            var panel = document.getElementById('resultPanel');
            var content = document.getElementById('resultContent');

            document.getElementById('abortBtn').style.display = 'none';
            document.getElementById('resetBtn').style.display = 'inline-flex';

            if (success) {
                var elapsed = document.getElementById('bpElapsed').textContent;
                content.innerHTML =
                    '<div class="result-summary-header">' +
                        '<div class="result-icon success-icon">' +
                            '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>' +
                        '</div>' +
                        '<div>' +
                            '<h4>Upload Complete!</h4>' +
                            '<p>Batch #' + (stats.batch_id || uploadState.batchId) + ' &bull; ' + elapsed + '</p>' +
                        '</div>' +
                    '</div>' +
                    '<div class="result-stats-grid">' +
                        '<div class="rs-stat"><div class="rs-stat-value rs-blue">' + (stats.total_students || 0).toLocaleString() + '</div><div class="rs-stat-label">Total Students</div></div>' +
                        '<div class="rs-stat"><div class="rs-stat-value rs-green">' + (stats.total_passed || 0).toLocaleString() + '</div><div class="rs-stat-label">Passed</div></div>' +
                        '<div class="rs-stat"><div class="rs-stat-value rs-red">' + (stats.total_failed || 0).toLocaleString() + '</div><div class="rs-stat-label">Failed/Referred</div></div>' +
                        '<div class="rs-stat"><div class="rs-stat-value' + (errors.length > 0 ? ' rs-amber' : ' rs-green') + '">' + errors.length + '</div><div class="rs-stat-label">Record Errors</div></div>' +
                    '</div>' +
                    '<div class="result-actions">' +
                        '<a href="result-data.php?batch_id=' + (stats.batch_id || uploadState.batchId) + '" class="btn-validate btn-validate-primary" style="text-decoration:none;">' +
                            '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>' +
                            'View Data' +
                        '</a>' +
                        '<a href="result-json-upload.php?batch_id=' + (stats.batch_id || uploadState.batchId) + '" class="btn-validate btn-validate-outline" style="text-decoration:none;">' +
                            '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>' +
                            'View Stats' +
                        '</a>' +
                    '</div>';
            } else {
                content.innerHTML =
                    '<div class="result-summary-header">' +
                        '<div class="result-icon error-icon">' +
                            '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>' +
                        '</div>' +
                        '<div>' +
                            '<h4>Upload Failed</h4>' +
                            '<p>' + escHtml(stats.error || 'An unknown error occurred.') + '</p>' +
                        '</div>' +
                    '</div>' +
                    '<div class="result-actions">' +
                        '<button type="button" class="btn-validate btn-validate-primary" onclick="resetAll()">Try Again</button>' +
                    '</div>';
            }

            panel.style.display = 'block';
            panel.scrollIntoView({ behavior: 'smooth', block: 'start' });

            if (success) {
                showToast('Upload completed successfully!', 'success');
                // Auto redirect after 3s
                setTimeout(function() {
                    // Only redirect if still on page
                    if (document.getElementById('resultPanel').style.display !== 'none') {
                        // Don't auto-redirect, let user choose
                    }
                }, 3000);
            }
        }

        // ═══════════════════════════════════════════════════════════
        // ABORT UPLOAD
        // ═══════════════════════════════════════════════════════════
        window.handleAbort = function() {
            if (confirm('Are you sure you want to abort the upload? The batch will be deleted and no data will be saved.')) {
                uploadState.aborted = true;
            }
        };

        // ═══════════════════════════════════════════════════════════
        // RESET ALL
        // ═══════════════════════════════════════════════════════════
        window.resetAll = function() {
            validatedStudents = null;
            validatedMetadata = null;
            validatedMode = null;
            uploadState = { batchId: null, aborted: false };

            document.getElementById('validationPanel').style.display = 'none';
            document.getElementById('progressPanel').style.display = 'none';
            document.getElementById('resultPanel').style.display = 'none';

            document.getElementById('validateBtn1').disabled = false;
            document.getElementById('validateBtn2').disabled = false;

            // Scroll to top
            window.scrollTo({ top: 0, behavior: 'smooth' });
        };

        // ═══════════════════════════════════════════════════════════
        // SIMPLE UPLOAD (OLD FLOW FALLBACK)
        // ═══════════════════════════════════════════════════════════
        window.handleSimpleUpload = function(mode) {
            var method = currentInputMethod[mode];
            var progressId = mode === 'manual' ? 'progress1' : 'progress2';
            var progress = document.getElementById(progressId);

            if (mode === 'manual') {
                var regYear = document.getElementById('manualRegYear').value;
                var examYear = document.getElementById('manualExamYear').value;
                var semester = document.getElementById('manualSemester').value;
                if (!regYear || !examYear || !semester) {
                    showToast('Please fill in Regulation Year, Exam Year, and Semester.', 'error');
                    return;
                }

                if (method === 'file') {
                    var input = document.getElementById('file1');
                    if (!input.files.length) { showToast('Please select a JSON file or switch to Paste Code tab.', 'error'); return; }
                    var fd = new FormData();
                    fd.append('regulation_year', regYear);
                    fd.append('exam_year', examYear);
                    fd.append('semester', semester);
                    fd.append('program', document.getElementById('manualProgram').value || 'Diploma In Engineering');
                    fd.append('json_file', input.files[0]);
                    uploadFormData(fd, progressId);
                } else {
                    var rawCode = document.getElementById('jsonCode1').value.trim();
                    if (!rawCode) { showToast('Please paste JSON code or switch to Upload File tab.', 'error'); return; }
                    var cleanedCode = cleanupJsonString(rawCode);
                    try { JSON.parse(cleanedCode); } catch(err) {
                        showToast('JSON has syntax errors. Click "Clean & Fix" button to auto-fix.', 'error');
                        return;
                    }
                    document.getElementById('jsonCode1').value = cleanedCode;
                    var fd = new FormData();
                    fd.append('regulation_year', regYear);
                    fd.append('exam_year', examYear);
                    fd.append('semester', semester);
                    fd.append('program', document.getElementById('manualProgram').value || 'Diploma In Engineering');
                    fd.append('json_code', cleanedCode);
                    uploadFormData(fd, progressId);
                }
            } else {
                // Auto mode
                if (method === 'file') {
                    var input = document.getElementById('file2');
                    if (!input.files.length) { showToast('Please select a JSON file or switch to Paste Code tab.', 'error'); return; }
                    var fd = new FormData();
                    fd.append('json_file', input.files[0]);
                    uploadFormData(fd, progressId);
                } else {
                    var rawCode = document.getElementById('jsonCode2').value.trim();
                    if (!rawCode) { showToast('Please paste JSON code or switch to Upload File tab.', 'error'); return; }
                    var cleanedCode = cleanupJsonString(rawCode);
                    try { JSON.parse(cleanedCode); } catch(err) {
                        showToast('JSON has syntax errors. Click "Clean & Fix" button to auto-fix.', 'error');
                        return;
                    }
                    document.getElementById('jsonCode2').value = cleanedCode;
                    var fd = new FormData();
                    fd.append('json_code', cleanedCode);
                    uploadFormData(fd, progressId);
                }
            }
        };

        // Upload FormData (old single-request upload)
        function uploadFormData(fd, progressId) {
            var progress = document.getElementById(progressId);
            progress.classList.add('active');

            var xhr = new XMLHttpRequest();
            xhr.open('POST', '../api/upload-json.php', true);
            xhr.timeout = 600000; // 10 min timeout

            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4) {
                    progress.classList.remove('active');
                    try {
                        var res = JSON.parse(xhr.responseText);
                        if (res.success) {
                            showToast(res.message, 'success');
                            setTimeout(function() { window.location.href = 'result-json-upload.php?batch_id=' + res.batch_id; }, 1500);
                        } else {
                            showToast('Error: ' + (res.error || 'Unknown error'), 'error');
                        }
                    } catch(err) {
                        if (xhr.status === 0) {
                            showToast('Network error or data too large. Try bulk upload for files over 1000 students.', 'error');
                        } else {
                            showToast('Server error. Please try again.', 'error');
                        }
                    }
                }
            };
            xhr.onerror = function() {
                progress.classList.remove('active');
                showToast('Network error. Please check your connection.', 'error');
            };
            xhr.ontimeout = function() {
                progress.classList.remove('active');
                showToast('Upload timed out. Data may be too large. Try bulk upload instead.', 'error');
            };
            xhr.send(fd);
        }

        // ═══════════════════════════════════════════════════════════
        // PROGRESS LOG
        // ═══════════════════════════════════════════════════════════
        function addLog(type, msg) {
            var log = document.getElementById('progressLog');
            var time = new Date().toLocaleTimeString();
            var cls = type === 'ok' ? 'log-ok' : type === 'err' ? 'log-err' : type === 'warn' ? 'log-warn' : 'log-info';
            var prefix = type === 'ok' ? '[OK]' : type === 'err' ? '[ERR]' : type === 'warn' ? '[WARN]' : '[INFO]';
            log.innerHTML += '<div class="' + cls + '">[' + time + '] ' + prefix + ' ' + escHtml(msg) + '</div>';
            log.scrollTop = log.scrollHeight;
        }

        // ═══════════════════════════════════════════════════════════
        // TOAST NOTIFICATIONS
        // ═══════════════════════════════════════════════════════════
        var toastTimer = null;
        function showToast(msg, type) {
            clearTimeout(toastTimer);
            var toast = document.getElementById('toast');
            toast.className = 'result-toast ' + type + ' active';
            var icon;
            if (type === 'success') {
                icon = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>';
            } else if (type === 'warning') {
                icon = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>';
            } else {
                icon = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>';
            }
            toast.innerHTML = icon + '<span>' + msg + '</span>';
            toastTimer = setTimeout(function() { toast.classList.remove('active'); }, 6000);
        }
        window.showToast = showToast;

        // ═══════════════════════════════════════════════════════════
        // HELPER FUNCTIONS
        // ═══════════════════════════════════════════════════════════
        function escHtml(str) {
            if (str === null || str === undefined) return '';
            return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        }

        function truncate(str, max) {
            if (!str) return '';
            return str.length > max ? str.substring(0, max) + '...' : str;
        }

        function capitalize(str) {
            if (!str) return '';
            return str.charAt(0).toUpperCase() + str.slice(1);
        }

        function formatTime(seconds) {
            if (seconds < 60) return seconds + 's';
            var m = Math.floor(seconds / 60);
            var s = seconds % 60;
            return m + 'm ' + s + 's';
        }

        function sleep(ms) {
            return new Promise(function(resolve) { setTimeout(resolve, ms); });
        }

    })();
    </script>
</body>
</html>
