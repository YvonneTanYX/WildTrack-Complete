<?php 
require_once __DIR__ . '/check_session.php';
requireVisitorLogin();   
$currentPage = 'visit';   
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WildTrack Ticketing System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="iconify.min.js"></script>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #eef2eb;
            min-height: 100vh;
        }

        /* ── PAGE SWITCHING ── */
        .page {
            display: none;
        }

        .page.active {
            display: block;
        }

        /* ── NAV ── */
        nav {
            background: #F9FBF7;
            border-bottom: 1px solid #e4e9e0;
            padding: 0 40px;
            height: 64px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 1px 6px rgba(0, 0, 0, 0.05);
        }

        .nav-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .nav-brand {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 20px;
            font-weight: 700;
            color: #2D5A27;
        }

        .nav-back {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: #fff;
            border: 1.5px solid #e4e9e0;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06);
            transition: all 0.2s;
            color: #2D5A27;
        }

        .nav-back:hover {
            background: #f0f4ee;
            border-color: #2D5A27;
        }

        /* ── LAYOUT ── */
        .page-wrap {
            max-width: 860px;
            margin: 0 auto;
            padding: 40px 24px 120px;
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        /* ── HERO CARD ── */
        .hero-card {
            background: #F9FBF7;
            border-radius: 24px;
            padding: 28px 32px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
        }

        .hero-card h1 {
            font-size: 26px;
            font-weight: 700;
            color: #2F3640;
            line-height: 1.3;
        }

        .hero-card h1 span {
            color: #2D5A27;
        }

        /* ── DATE SECTION ── */
        .section-card {
            background: #F9FBF7;
            border-radius: 24px;
            padding: 24px 28px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
        }

        .section-card .section-title {
            font-size: 17px;
            font-weight: 600;
            color: #2F3640;
            margin-bottom: 16px;
        }

        .date-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 18px;
        }

        .date-header h2 {
            font-size: 17px;
            font-weight: 600;
            color: #2F3640;
        }

        #currentMonth {
            color: #2D5A27;
            font-weight: 600;
            font-size: 15px;
        }

        .date-row {
            display: flex;
            gap: 10px;
            overflow-x: auto;
            padding-bottom: 4px;
        }

        .date-row::-webkit-scrollbar { display: none; }

        .date-card {
            min-width: 78px;
            height: 96px;
            background: #edf0ea;
            border-radius: 20px;
            text-align: center;
            padding-top: 12px;
            cursor: pointer;
            transition: all 0.2s;
            flex-shrink: 0;
        }

        .date-card p {
            font-size: 12px;
            font-weight: 600;
            color: #888;
        }

        .date-card h3 {
            font-size: 26px;
            font-weight: 700;
            color: #2F3640;
            margin-top: 4px;
        }

        .date-card.active {
            background: #4a7c4e;
            box-shadow: 0 6px 18px rgba(74, 124, 78, 0.3);
        }

        .date-card.active p { color: #c5e0c6; }
        .date-card.active h3 { color: #fff; }

        .date-card:hover:not(.active):not(.disabled) {
            transform: scale(1.04);
            background: #e0e8dc;
        }

        /* REQ 8 — disabled date (today after 3PM) */
        .date-card.disabled {
            opacity: 0.38;
            cursor: not-allowed;
            background: #e0e0e0;
        }

        .date-card.disabled p { color: #aaa; }
        .date-card.disabled h3 { color: #bbb; }

        .date-card.disabled .date-cutoff-label {
            font-size: 9px;
            font-weight: 700;
            color: #E67E22;
            margin-top: 2px;
            display: block;
        }

        /* ── TICKET CARDS ── */
        .ticket-card {
            background: #F9FBF7;
            border-radius: 20px;
            padding: 20px 24px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            border: 2px solid transparent;
            transition: all 0.2s;
        }

        .ticket-card:hover { border-color: rgba(164, 198, 57, 0.35); }

        .ticket-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 16px;
        }

        .ticket-name {
            font-size: 17px;
            font-weight: 600;
            color: #2F3640;
        }

        .ticket-sub {
            font-size: 12px;
            color: #7F8C8D;
            margin-top: 4px;
        }

        .ticket-price {
            font-size: 20px;
            font-weight: 700;
            color: #2D5A27;
        }

        .ticket-bottom {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .badge-orange {
            font-size: 11px;
            font-weight: 600;
            color: #E67E22;
            background: rgba(230, 126, 34, 0.1);
            padding: 4px 10px;
            border-radius: 8px;
        }

        .badge-green {
            font-size: 11px;
            font-weight: 600;
            color: #5a8a2f;
            background: rgba(164, 198, 57, 0.15);
            padding: 4px 10px;
            border-radius: 8px;
        }

        .qty-controls {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .qty-btn {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            border: 1.5px solid #d5d9d2;
            background: transparent;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #2F3640;
            transition: all 0.15s;
        }

        .qty-btn:hover {
            border-color: #2D5A27;
            color: #2D5A27;
        }

        .qty-btn.plus {
            border-color: #2D5A27;
            background: rgba(45, 90, 39, 0.07);
            color: #2D5A27;
        }

        .qty-btn.plus:hover {
            background: #2D5A27;
            color: #fff;
        }

        .qty-num {
            font-size: 18px;
            font-weight: 700;
            color: #2F3640;
            min-width: 24px;
            text-align: center;
        }

        .ticket-card.family {
            background: linear-gradient(135deg, rgba(45, 90, 39, 0.06), rgba(164, 198, 57, 0.12));
            border-color: rgba(164, 198, 57, 0.25);
            position: relative;
            overflow: hidden;
        }

        .save-badge {
            position: absolute;
            top: 0;
            right: 0;
            background: #E67E22;
            color: #fff;
            font-size: 10px;
            font-weight: 700;
            padding: 5px 14px;
            border-bottom-left-radius: 12px;
        }

        .ticket-card.family .ticket-name {
            color: #2D5A27;
            font-weight: 700;
        }

        .family-check {
            font-size: 12px;
            font-weight: 600;
            color: #2D5A27;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .qty-btn.family-plus {
            background: #2D5A27;
            border-color: #2D5A27;
            color: #fff;
            box-shadow: 0 4px 10px rgba(45, 90, 39, 0.25);
        }

        .qty-btn.family-plus:hover { background: #245020; }

        /* ── ADD-ONS ── */
        .addons-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 14px;
            margin-top: 16px;
        }

        .addon-card {
            background: #fff;
            border-radius: 16px;
            padding: 16px;
            border: 1.5px solid transparent;
            transition: all 0.2s;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        }

        .addon-card.has-qty {
            border-color: #2D5A27;
            background: #f5faf3;
        }

        .addon-top {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 12px;
        }

        .addon-icon {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
        }

        .icon-green { background: rgba(164, 198, 57, 0.15); color: #2D5A27; }
        .icon-orange { background: rgba(230, 126, 34, 0.1); color: #E67E22; }

        .addon-info { flex: 1; }
        .addon-name { font-size: 13px; font-weight: 600; color: #2F3640; }
        .addon-price { font-size: 11px; color: #7F8C8D; margin-top: 2px; }

        .addon-bottom {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .addon-subtotal {
            font-size: 13px;
            font-weight: 700;
            color: #2D5A27;
            min-width: 52px;
        }

        .addon-qty-controls { display: flex; align-items: center; gap: 10px; }

        .addon-qty-btn {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            border: 1.5px solid #d5d9d2;
            background: transparent;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #2F3640;
            transition: all 0.15s;
        }

        .addon-qty-btn:hover { border-color: #2D5A27; color: #2D5A27; }

        .addon-qty-btn.plus {
            border-color: #2D5A27;
            background: rgba(45, 90, 39, 0.07);
            color: #2D5A27;
        }

        .addon-qty-btn.plus:hover { background: #2D5A27; color: #fff; }

        .addon-qty-num {
            font-size: 16px;
            font-weight: 700;
            color: #2F3640;
            min-width: 20px;
            text-align: center;
        }

        /* ── BOTTOM BAR ── */
        .bottom-bar {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: #F9FBF7;
            border-top: 1px solid #e4e9e0;
            padding: 16px 40px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.07);
            z-index: 99;
        }

        .bottom-total-label { font-size: 13px; color: #7F8C8D; font-weight: 500; margin-bottom: 2px; }
        .bottom-total-value { font-size: 26px; font-weight: 700; color: #2D5A27; }

        .pay-btn {
            height: 52px;
            padding: 0 40px;
            background: #2D5A27;
            color: #fff;
            border: none;
            border-radius: 16px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 6px 18px rgba(45, 90, 39, 0.28);
            transition: all 0.2s;
        }

        .pay-btn:hover { background: #245020; transform: translateY(-1px); }
        .pay-btn:active { transform: scale(0.98); }

        /* ══ PAGE 2 — ORDER SUMMARY ══ */
        .summary-block {
            background: #F9FBF7;
            border-radius: 20px;
            padding: 22px 26px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .summary-block-title {
            font-size: 13px;
            font-weight: 700;
            color: #7F8C8D;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            margin-bottom: 14px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #f0f4ee;
            font-size: 14px;
        }

        .info-row:last-child { border-bottom: none; padding-bottom: 0; }
        .info-label { color: #7F8C8D; }
        .info-value { font-weight: 600; color: #2F3640; }
        .info-value.green { color: #2D5A27; }

        /* ── TNG PAYMENT METHOD (single card) ── */
        .tng-method-card {
            border: 2px solid #2D5A27;
            border-radius: 16px;
            padding: 18px 20px;
            background: rgba(45, 90, 39, 0.04);
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .tng-logo-circle {
            width: 52px;
            height: 52px;
            border-radius: 50%;
            background: #0066CC;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .tng-logo-text {
            font-size: 11px;
            font-weight: 900;
            color: #fff;
            line-height: 1.1;
            text-align: center;
            letter-spacing: -0.5px;
        }

        .tng-method-info { flex: 1; }
        .tng-method-name { font-size: 15px; font-weight: 700; color: #2F3640; }
        .tng-method-sub { font-size: 12px; color: #7F8C8D; margin-top: 3px; }

        .tng-selected-badge {
            font-size: 11px;
            font-weight: 700;
            color: #2D5A27;
            background: rgba(45,90,39,0.1);
            padding: 4px 10px;
            border-radius: 8px;
        }

        /* Grand total box */
        .grand-box {
            background: linear-gradient(135deg, #2D5A27, #3d7a35);
            border-radius: 20px;
            padding: 24px 28px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 8px 24px rgba(45, 90, 39, 0.25);
        }

        .grand-box-label { color: rgba(255,255,255,0.75); font-size: 14px; margin-bottom: 4px; }
        .grand-box-value { color: #fff; font-size: 32px; font-weight: 700; }
        .grand-box-note { color: rgba(255,255,255,0.6); font-size: 11px; margin-top: 4px; }

        .confirm-btn {
            width: 100%;
            height: 54px;
            background: #2D5A27;
            color: #fff;
            border: none;
            border-radius: 16px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            box-shadow: 0 6px 18px rgba(45, 90, 39, 0.28);
            transition: all 0.2s;
        }

        .confirm-btn:hover { background: #245020; transform: translateY(-1px); }
        .confirm-btn:active { transform: scale(0.98); }

        .ticket-summary-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #f0f4ee;
        }

        .ticket-summary-item:last-child { border-bottom: none; }

        .ticket-summary-left { display: flex; align-items: center; gap: 12px; }

        .ticket-summary-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: rgba(45, 90, 39, 0.08);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #2D5A27;
            font-size: 16px;
        }

        .ticket-summary-name { font-size: 14px; font-weight: 600; color: #2F3640; }
        .ticket-summary-qty { font-size: 12px; color: #7F8C8D; margin-top: 2px; }
        .ticket-summary-price { font-size: 15px; font-weight: 700; color: #2D5A27; }
        .divider { border: none; border-top: 1px solid #e4e9e0; margin: 4px 0; }

        /* ── PAGE TRANSITIONS ── */
        @keyframes slideIn {
            from { opacity: 0; transform: translateX(30px); }
            to   { opacity: 1; transform: translateX(0); }
        }

        @keyframes slideBack {
            from { opacity: 0; transform: translateX(-30px); }
            to   { opacity: 1; transform: translateX(0); }
        }

        .slide-in   { animation: slideIn  0.3s ease both; }
        .slide-back { animation: slideBack 0.3s ease both; }

        /* ══ PAGE 3 — TNG QR PAYMENT ══ */
        .tng-page-header {
            background: linear-gradient(135deg, #0055AA, #0077DD);
            border-radius: 24px;
            padding: 28px 32px;
            text-align: center;
            box-shadow: 0 8px 24px rgba(0,85,170,0.25);
        }

        .tng-page-header h1 {
            color: #fff;
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 6px;
        }

        .tng-page-header p {
            color: rgba(255,255,255,0.75);
            font-size: 13px;
        }

        .tng-qr-card {
            background: #F9FBF7;
            border-radius: 24px;
            padding: 28px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.07);
            text-align: center;
        }

        .tng-receiver-name {
            font-size: 18px;
            font-weight: 700;
            color: #2F3640;
            margin-bottom: 4px;
        }

        .tng-receiver-sub {
            font-size: 12px;
            color: #7F8C8D;
            margin-bottom: 20px;
        }

        .tng-qr-image-wrap {
            display: inline-block;
            border: 3px solid #e4e9e0;
            border-radius: 16px;
            padding: 12px;
            background: #fff;
            margin-bottom: 16px;
        }

        .tng-qr-image-wrap img {
            width: 200px;
            height: 200px;
            display: block;
            border-radius: 8px;
        }

        .tng-qr-hint {
            font-size: 12px;
            color: #7F8C8D;
            line-height: 1.6;
        }

        .tng-amount-box {
            background: #F9FBF7;
            border-radius: 20px;
            padding: 20px 24px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }

        .tng-amount-label {
            font-size: 13px;
            color: #7F8C8D;
            margin-bottom: 6px;
        }

        .tng-amount-value {
            font-size: 36px;
            font-weight: 700;
            color: #2D5A27;
            margin-bottom: 4px;
        }

        .tng-amount-ref {
            font-size: 12px;
            color: #aaa;
        }

        .tng-steps-card {
            background: #F9FBF7;
            border-radius: 20px;
            padding: 20px 24px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }

        .tng-step {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            padding: 10px 0;
            border-bottom: 1px solid #f0f4ee;
        }

        .tng-step:last-child { border-bottom: none; }

        .tng-step-num {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: #0066CC;
            color: #fff;
            font-size: 12px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .tng-step-text { font-size: 13px; color: #2F3640; line-height: 1.5; padding-top: 4px; }

        .ive-paid-btn {
            width: 100%;
            height: 54px;
            background: #0066CC;
            color: #fff;
            border: none;
            border-radius: 16px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            box-shadow: 0 6px 18px rgba(0,102,204,0.3);
            transition: all 0.2s;
            font-family: inherit;
        }

        .ive-paid-btn:hover { background: #0055AA; transform: translateY(-1px); }
        .ive-paid-btn:active { transform: scale(0.98); }

        /* ══ PAGE 4 — UPLOAD PROOF ══ */
        .upload-area {
            border: 2px dashed #b0c4ae;
            border-radius: 20px;
            padding: 40px 24px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
            background: #F9FBF7;
        }

        .upload-area:hover, .upload-area.drag-over {
            border-color: #2D5A27;
            background: #f0f7ee;
        }

        .upload-icon {
            font-size: 48px;
            margin-bottom: 12px;
            display: block;
            color: #2D5A27;
        }

        .upload-title {
            font-size: 16px;
            font-weight: 600;
            color: #2F3640;
            margin-bottom: 6px;
        }

        .upload-sub {
            font-size: 12px;
            color: #7F8C8D;
        }

        .upload-preview {
            display: none;
            margin-top: 16px;
            border-radius: 12px;
            overflow: hidden;
            border: 2px solid #d5e8d4;
        }

        .upload-preview img {
            width: 100%;
            max-height: 300px;
            object-fit: contain;
            background: #f8f8f8;
        }

        .upload-preview-name {
            padding: 10px 14px;
            font-size: 12px;
            color: #7F8C8D;
            background: #f7faf5;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .upload-remove-btn {
            background: none;
            border: none;
            color: #E74C3C;
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
        }

        .submit-proof-btn {
            width: 100%;
            height: 54px;
            background: #2D5A27;
            color: #fff;
            border: none;
            border-radius: 16px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            box-shadow: 0 6px 18px rgba(45,90,39,0.28);
            transition: all 0.2s;
            font-family: inherit;
        }

        .submit-proof-btn:disabled {
            background: #a8c4a5;
            cursor: not-allowed;
            box-shadow: none;
            transform: none;
        }

        .submit-proof-btn:not(:disabled):hover { background: #245020; transform: translateY(-1px); }

        /* ══ PAGE 5 — PENDING BOOKING ══ */
        .pending-hero {
            background: linear-gradient(135deg, #F39C12, #E67E22);
            border-radius: 24px;
            padding: 32px;
            text-align: center;
            box-shadow: 0 8px 24px rgba(243,156,18,0.3);
        }

        .pending-hero h1 {
            color: #fff;
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 6px;
        }

        .pending-hero p { color: rgba(255,255,255,0.85); font-size: 13px; }

        .pending-status-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255,255,255,0.2);
            border-radius: 20px;
            padding: 8px 18px;
            margin-top: 14px;
            color: #fff;
            font-size: 13px;
            font-weight: 600;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50%       { opacity: 0.4; }
        }

        .pulse-dot {
            width: 8px;
            height: 8px;
            background: #fff;
            border-radius: 50%;
            animation: pulse 1.5s ease-in-out infinite;
        }

        .booking-detail-card {
            background: #F9FBF7;
            border-radius: 20px;
            padding: 22px 26px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }

        .booking-ref-display {
            background: #eef6ec;
            border-radius: 12px;
            padding: 14px 18px;
            text-align: center;
            margin-top: 12px;
        }

        .booking-ref-label {
            font-size: 11px;
            font-weight: 700;
            color: #7F8C8D;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-bottom: 4px;
        }

        .booking-ref-value {
            font-size: 22px;
            font-weight: 700;
            color: #2D5A27;
            letter-spacing: 2px;
        }

        .proof-thumb {
            width: 100%;
            max-height: 140px;
            object-fit: cover;
            border-radius: 12px;
            border: 2px solid #e4e9e0;
            margin-top: 12px;
        }

        .what-next-card {
            background: #fff8ec;
            border: 1.5px solid #f5d99a;
            border-radius: 20px;
            padding: 20px 24px;
        }

        .what-next-title {
            font-size: 13px;
            font-weight: 700;
            color: #E67E22;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-bottom: 12px;
        }

        .what-next-item {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 6px 0;
            font-size: 13px;
            color: #2F3640;
        }

        /* ══ PAGE 6 — QR RECEIPT (post-approval) ══ */
        .qr-ticket-card {
            background: #F9FBF7;
            border-radius: 20px;
            padding: 24px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.07);
            text-align: center;
            border: 2px dashed #d5e8d4;
        }

        .qr-ticket-type { font-size: 18px; font-weight: 700; color: #2D5A27; margin-bottom: 4px; }
        .qr-ticket-date { font-size: 13px; color: #7F8C8D; margin-bottom: 16px; }
        .qr-code-box { display: flex; justify-content: center; margin-bottom: 12px; }

        .qr-img {
            width: 160px;
            height: 160px;
            border-radius: 12px;
            border: 3px solid #e4e9e0;
        }

        .qr-code-text { font-size: 11px; color: #aaa; letter-spacing: 0.5px; word-break: break-all; }

        .qr-done-btn {
            width: 100%;
            height: 54px;
            background: #2D5A27;
            color: #fff;
            border: none;
            border-radius: 16px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            box-shadow: 0 6px 18px rgba(45,90,39,0.28);
            transition: all 0.2s;
            font-family: inherit;
        }

        .qr-done-btn:hover { background: #245020; transform: translateY(-1px); }

        /* VOUCHER */
        .voucher-section { margin-top: 16px; }
        .voucher-row { display: flex; gap: 10px; align-items: center; }

        .voucher-input {
            flex: 1;
            height: 44px;
            border: 1.5px solid #d5d9d2;
            border-radius: 12px;
            padding: 0 14px;
            font-size: 14px;
            font-family: inherit;
            color: #2F3640;
            background: #fff;
            outline: none;
            transition: border-color 0.2s;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .voucher-input:focus { border-color: #2D5A27; }
        .voucher-input:disabled { background: #f0f4ee; color: #888; }

        .voucher-apply-btn {
            height: 44px;
            padding: 0 20px;
            background: #2D5A27;
            color: #fff;
            border: none;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            white-space: nowrap;
            font-family: inherit;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .voucher-apply-btn:hover { background: #245020; }
        .voucher-apply-btn:disabled { background: #a8c4a5; cursor: not-allowed; }

        .voucher-remove-btn {
            height: 44px;
            padding: 0 16px;
            background: transparent;
            color: #c0392b;
            border: 1.5px solid #e4b5b0;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            font-family: inherit;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .voucher-remove-btn:hover { background: #fdf0ee; }

        .voucher-feedback { margin-top: 8px; font-size: 13px; font-weight: 500; min-height: 18px; }
        .voucher-feedback.success { color: #2D5A27; }
        .voucher-feedback.error   { color: #c0392b; }

    </style>
</head>

<body>

<!-- ═══════════════════════════════
     PAGE 1 — BOOKING
═══════════════════════════════ -->
<div class="page active" id="page-booking">

    <nav>
        <div class="nav-left">
            <button class="nav-back" onclick="backToHome()">
                <span class="iconify" data-icon="lucide:arrow-left" data-width="18"></span>
            </button>
            <div class="nav-brand">
                <span class="iconify" data-icon="lucide:tent-tree" style="font-size:24px;color:#2D5A27;"></span>
                Ticket Sale
            </div>
        </div>
    </nav>

    <div class="page-wrap">

        <!-- Hero -->
        <div class="hero-card">
            <h1>Discover nature's <br><span>wildest moments</span></h1>
        </div>

        <!-- Date Picker -->
        <div class="section-card">
            <div class="date-header">
                <h2>Select Date</h2>
                <span id="currentMonth"></span>
            </div>
            <div class="date-row" id="dateRow"></div>
        </div>

        <!-- Tickets -->
        <div style="display:flex;flex-direction:column;gap:16px;">

            <!-- Adult -->
            <div class="ticket-card">
                <div class="ticket-top">
                    <div>
                        <div class="ticket-name">Adult Pass</div>
                        <div class="ticket-sub">Age 13-64 • Full access</div>
                    </div>
                    <div class="ticket-price" id="ticket-price-adult">RM20</div>
                </div>
                <div class="ticket-bottom">
                    <span class="badge-orange">Best Seller</span>
                    <div class="qty-controls">
                        <button class="qty-btn" onclick="updateQty('adult', -1)">
                            <span class="iconify" data-icon="lucide:minus" data-width="14"></span>
                        </button>
                        <span class="qty-num" id="qty-adult">1</span>
                        <button class="qty-btn plus" onclick="updateQty('adult', 1)">
                            <span class="iconify" data-icon="lucide:plus" data-width="14"></span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Child -->
            <div class="ticket-card">
                <div class="ticket-top">
                    <div>
                        <div class="ticket-name">Child Pass</div>
                        <div class="ticket-sub">Age 4-12 • Under 4 free</div>
                    </div>
                    <div class="ticket-price" id="ticket-price-child">RM10</div>
                </div>
                <div class="ticket-bottom">
                    <span class="badge-green">Interactive Map Included</span>
                    <div class="qty-controls">
                        <button class="qty-btn" onclick="updateQty('child', -1)">
                            <span class="iconify" data-icon="lucide:minus" data-width="14"></span>
                        </button>
                        <span class="qty-num" id="qty-child">0</span>
                        <button class="qty-btn plus" onclick="updateQty('child', 1)">
                            <span class="iconify" data-icon="lucide:plus" data-width="14"></span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Senior -->
            <div class="ticket-card">
                <div class="ticket-top">
                 <div>
                    <div class="ticket-name">Senior Pass</div>
                    <div class="ticket-sub">Age 65+ • Full access</div>
                </div>
                <div class="ticket-price" id="ticket-price-senior">RM15</div>
            </div>
             <div class="ticket-bottom">
                 <span class="badge-green">Senior Discount</span>
                 <div class="qty-controls">
                    <button class="qty-btn" onclick="updateQty('senior', -1)">
                        <span class="iconify" data-icon="lucide:minus" data-width="14"></span>
                    </button>
                        <span class="qty-num" id="qty-senior">0</span>
                    <button class="qty-btn plus" onclick="updateQty('senior', 1)">
                         <span class="iconify" data-icon="lucide:plus" data-width="14"></span>
                    </button>
                    </div>
                </div>
            </div>

            <!-- Family Bundle -->
            <div class="ticket-card family">
                <div class="save-badge">SAVE 15%</div>
                <div class="ticket-top">
                    <div>
                        <div class="ticket-name">Family Bundle</div>
                            <div class="ticket-sub">2 Adults + 1 Child + 1 Senior</div>
                    </div>
                        <div class="ticket-price" id="ticket-price-family">RM55</div>
                </div>
                <div class="ticket-bottom">
                    <span class="family-check">
                        <span class="iconify" data-icon="lucide:check-circle" data-width="14"></span>
                        Priority Entry
                    </span>
                    <div class="qty-controls">
                        <button class="qty-btn" onclick="updateQty('family', -1)">
                            <span class="iconify" data-icon="lucide:minus" data-width="14"></span>
                        </button>
                        <span class="qty-num" id="qty-family">0</span>
                        <button class="qty-btn plus family-plus" onclick="updateQty('family', 1)">
                            <span class="iconify" data-icon="lucide:plus" data-width="14"></span>
                        </button>
                    </div>
                </div>
            </div>

        </div>

        <!-- Add-ons -->
        <div class="section-card">
            <div class="section-title">Enhance Your Visit</div>
            <div class="addons-grid">

                <!-- Safari Shuttle -->
                <div class="addon-card" id="addon-card-safari">
                    <div class="addon-top">
                        <div class="addon-icon icon-green">
                            <span class="iconify" data-icon="lucide:bus"></span>
                        </div>
                        <div class="addon-info">
                            <div class="addon-name">Safari Shuttle</div>
                            <div class="addon-price" id="addon-label-safari">RM5 / person</div>
                        </div>
                    </div>
                    <div class="addon-bottom">
                        <span class="addon-subtotal" id="addon-subtotal-safari">RM0.00</span>
                        <div class="addon-qty-controls">
                            <button class="addon-qty-btn" onclick="updateAddon('safari', -1)">
                                <span class="iconify" data-icon="lucide:minus" data-width="13"></span>
                            </button>
                            <span class="addon-qty-num" id="addon-qty-safari">0</span>
                            <button class="addon-qty-btn plus" onclick="updateAddon('safari', 1)">
                                <span class="iconify" data-icon="lucide:plus" data-width="13"></span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Feeding Pass -->
                <div class="addon-card" id="addon-card-feeding">
                    <div class="addon-top">
                        <div class="addon-icon icon-orange">
                            <span class="iconify" data-icon="lucide:cookie"></span>
                        </div>
                        <div class="addon-info">
                            <div class="addon-name">Feeding Pass</div>
                            <div class="addon-price" id="addon-label-feeding">RM12 / person</div>
                        </div>
                    </div>
                    <div class="addon-bottom">
                        <span class="addon-subtotal" id="addon-subtotal-feeding">RM0.00</span>
                        <div class="addon-qty-controls">
                            <button class="addon-qty-btn" onclick="updateAddon('feeding', -1)">
                                <span class="iconify" data-icon="lucide:minus" data-width="13"></span>
                            </button>
                            <span class="addon-qty-num" id="addon-qty-feeding">0</span>
                            <button class="addon-qty-btn plus" onclick="updateAddon('feeding', 1)">
                                <span class="iconify" data-icon="lucide:plus" data-width="13"></span>
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>

    <!-- Bottom Bar -->
    <div class="bottom-bar">
        <div>
            <div class="bottom-total-label">Total Price</div>
            <div class="bottom-total-value" id="total-price-bar">RM20.00</div>
        </div>
        <button class="pay-btn" onclick="goToSummary()">
            Pay
            <span class="iconify" data-icon="lucide:arrow-right" data-width="18"></span>
        </button>
    </div>

</div>

<div class="page" id="page-home">
   <a href="mainPage.php"></a>
</div>


<!-- ═══════════════════════════════
     PAGE 2 — ORDER SUMMARY
═══════════════════════════════ -->
<div class="page" id="page-summary">

    <nav>
        <div class="nav-left">
            <button class="nav-back" onclick="goBack()">
                <span class="iconify" data-icon="lucide:arrow-left" data-width="18"></span>
            </button>
            <div class="nav-brand">
                <span class="iconify" data-icon="lucide:tent-tree" style="font-size:22px;color:#2D5A27;"></span>
                Order Summary
            </div>
        </div>
    </nav>

    <div class="page-wrap" style="padding-bottom:40px;">

        <!-- Visit Info -->
        <div class="summary-block">
            <div class="summary-block-title">Visit Details</div>
            <div class="info-row">
                <span class="info-label">📅 Visit Date</span>
                <span class="info-value green" id="s-date">—</span>
            </div>
            <div class="info-row">
                <span class="info-label">🕘 Opening Hours</span>
                <span class="info-value">9:00 AM – 6:00 PM</span>
            </div>
            <div class="info-row">
                <span class="info-label">📍 Location</span>
                <span class="info-value">WildTrack Safari Park</span>
            </div>
            <div class="info-row">
                <span class="info-label">🎟 Booking Ref</span>
                <span class="info-value green" id="s-ref">—</span>
            </div>
        </div>

        <!-- Ticket Summary -->
        <div class="summary-block">
            <div class="summary-block-title">Tickets</div>
            <div id="s-tickets"></div>
            <hr class="divider" style="margin:14px 0;">
            <div style="display:flex;justify-content:space-between;align-items:center;">
                <span style="font-size:13px;color:#7F8C8D;">Total Visitors</span>
                <span style="font-size:14px;font-weight:700;color:#2F3640;" id="s-visitors">—</span>
            </div>
        </div>

        <!-- Price Breakdown -->
        <div class="summary-block">
            <div class="summary-block-title">Price Breakdown</div>
            <div id="s-price-rows"></div>
            <div class="info-row" id="s-voucher-row" style="display:none;">
                <span class="info-label" style="color:#2D5A27;font-weight:600;">🎁 Voucher (<span id="s-voucher-code">—</span>)</span>
                <span class="info-value" id="s-voucher-discount" style="color:#2D5A27;">—</span>
            </div>
            <hr class="divider" style="margin:14px 0;">
            <div class="info-row" style="padding:0;">
                <span class="info-label" style="font-size:15px;font-weight:600;color:#2F3640;">Total</span>
                <span class="info-value green" style="font-size:20px;" id="s-total">—</span>
            </div>
        </div>

        <!-- Payment Method — TNG only -->
        <div class="summary-block">
            <div class="summary-block-title">Payment Method</div>
            <div class="tng-method-card">
                <div class="tng-logo-circle">
                    <div class="tng-logo-text">TNG<br>eWallet</div>
                </div>
                <div class="tng-method-info">
                    <div class="tng-method-name">Touch 'n Go eWallet</div>
                    <div class="tng-method-sub">Scan QR to pay • Instant confirmation</div>
                </div>
                <span class="tng-selected-badge">✓ Selected</span>
            </div>
        </div>

        <!-- Voucher -->
        <div class="summary-block">
            <div class="summary-block-title">Voucher / Promo Code</div>
            <div class="voucher-section">
                <div class="voucher-row">
                    <input type="text" id="voucher-input" class="voucher-input" placeholder="Enter voucher code" maxlength="30" />
                    <button class="voucher-apply-btn" id="voucher-apply-btn" onclick="applyVoucher()">Apply</button>
                    <button class="voucher-remove-btn" id="voucher-remove-btn" onclick="removeVoucher()" style="display:none;">Remove</button>
                </div>
                <div class="voucher-feedback" id="voucher-feedback"></div>
            </div>
        </div>

        <!-- Grand Total -->
        <div class="grand-box">
            <div>
                <div class="grand-box-label">Amount to Pay</div>
                <div class="grand-box-value" id="s-grand">RM0.00</div>
                <div class="grand-box-note">Inclusive of all fees</div>
            </div>
            <span class="iconify" data-icon="lucide:shield-check" style="font-size:48px;color:rgba(255,255,255,0.3);"></span>
        </div>

        <!-- Confirm Button — now goes to TNG page, NOT tickets.php -->
        <button class="confirm-btn" onclick="goToTNGPayment()">
            <span class="iconify" data-icon="lucide:qr-code" data-width="18"></span>
            Proceed to Payment
        </button>

        <p style="text-align:center;font-size:12px;color:#7F8C8D;margin-top:8px;">
            🔒 Secure payment via Touch 'n Go eWallet
        </p>

    </div>
</div>


<!-- ═══════════════════════════════
     PAGE 3 — TNG QR PAYMENT
═══════════════════════════════ -->
<div class="page" id="page-tng">

    <nav>
        <div class="nav-left">
            <button class="nav-back" onclick="backToSummary()">
                <span class="iconify" data-icon="lucide:arrow-left" data-width="18"></span>
            </button>
            <div class="nav-brand">
                <span class="iconify" data-icon="lucide:tent-tree" style="font-size:22px;color:#2D5A27;"></span>
                Payment
            </div>
        </div>
    </nav>

    <div class="page-wrap" style="padding-bottom:40px;">

        <div class="tng-page-header">
            <span class="iconify" data-icon="lucide:smartphone" style="font-size:36px;color:rgba(255,255,255,0.9);margin-bottom:10px;display:block;"></span>
            <h1>Pay via TNG eWallet</h1>
            <p>Open your Touch 'n Go app and scan the QR below</p>
        </div>

        <!-- QR Code Card -->
        <div class="tng-qr-card">
            <div class="tng-receiver-name" id="tng-receiver-name">WildTrack Safari Park</div>
            <div class="tng-receiver-sub">Scan with Touch 'n Go eWallet</div>
            <div class="tng-qr-image-wrap">
                <img id="tng-qr-img" src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=TNG-WILDTRACK-PLACEHOLDER" alt="TNG QR Code" />
            </div>
            <div class="tng-qr-hint">
                This QR code is for WildTrack Safari Park official account.<br>
                Do not pay to any other account.
            </div>
        </div>

        <!-- Amount Box -->
        <div class="tng-amount-box">
            <div style="display:flex;justify-content:space-between;align-items:center;">
                <div>
                    <div class="tng-amount-label">Amount to Transfer</div>
                    <div class="tng-amount-value" id="tng-amount">RM0.00</div>
                    <div class="tng-amount-ref">Ref: <span id="tng-ref">—</span></div>
                </div>
                <div style="text-align:right;">
                    <div style="font-size:11px;color:#7F8C8D;margin-bottom:4px;">Please transfer exactly</div>
                    <div style="font-size:11px;color:#E67E22;font-weight:600;">Include ref in notes</div>
                </div>
            </div>
        </div>

        <!-- Steps -->
        <div class="tng-steps-card">
            <div class="summary-block-title" style="margin-bottom:8px;">How to Pay</div>
            <div class="tng-step">
                <div class="tng-step-num">1</div>
                <div class="tng-step-text">Open your <strong>Touch 'n Go eWallet</strong> app on your phone</div>
            </div>
            <div class="tng-step">
                <div class="tng-step-num">2</div>
                <div class="tng-step-text">Tap <strong>Scan &amp; Pay</strong> and scan the QR code above</div>
            </div>
            <div class="tng-step">
                <div class="tng-step-num">3</div>
                <div class="tng-step-text">Enter the exact amount shown and add the <strong>Booking Ref</strong> in the notes</div>
            </div>
            <div class="tng-step">
                <div class="tng-step-num">4</div>
                <div class="tng-step-text">Confirm payment and <strong>take a screenshot</strong> of the receipt</div>
            </div>
            <div class="tng-step">
                <div class="tng-step-num">5</div>
                <div class="tng-step-text">Tap <em>"I've Paid"</em> below and upload your payment screenshot</div>
            </div>
        </div>

        <!-- I've Paid Button -->
        <button class="ive-paid-btn" onclick="goToUploadProof()">
            <span class="iconify" data-icon="lucide:check" data-width="18"></span>
            I've Paid — Upload Proof
        </button>

        <p style="text-align:center;font-size:12px;color:#7F8C8D;margin-top:8px;">
            Your booking will be confirmed after admin verifies your payment
        </p>

    </div>
</div>


<!-- ═══════════════════════════════
     PAGE 4 — UPLOAD PAYMENT PROOF
═══════════════════════════════ -->
<div class="page" id="page-upload">

    <nav>
        <div class="nav-left">
            <button class="nav-back" onclick="backToTNG()">
                <span class="iconify" data-icon="lucide:arrow-left" data-width="18"></span>
            </button>
            <div class="nav-brand">
                <span class="iconify" data-icon="lucide:tent-tree" style="font-size:22px;color:#2D5A27;"></span>
                Upload Proof
            </div>
        </div>
    </nav>

    <div class="page-wrap" style="padding-bottom:40px;">

        <div class="hero-card" style="text-align:center;padding:28px 32px;">
            <span class="iconify" data-icon="lucide:upload" style="font-size:40px;color:#2D5A27;margin-bottom:10px;display:block;"></span>
            <h1 style="font-size:20px;">Upload Payment <span>Screenshot</span></h1>
            <p style="font-size:13px;color:#7F8C8D;margin-top:6px;">We need proof of your TNG transfer to confirm your booking</p>
        </div>

        <!-- Upload Area -->
        <div class="summary-block">
            <div class="summary-block-title">Payment Screenshot</div>

            <div class="upload-area" id="upload-area" onclick="document.getElementById('proof-file-input').click()"
                 ondragover="handleDragOver(event)" ondragleave="handleDragLeave(event)" ondrop="handleDrop(event)">
                <span class="iconify upload-icon" data-icon="lucide:image-plus" data-width="48"></span>
                <div class="upload-title">Tap to select screenshot</div>
                <div class="upload-sub">JPG, PNG or WEBP • Max 5MB</div>
            </div>

            <input type="file" id="proof-file-input" accept="image/jpeg,image/png,image/webp" style="display:none;" onchange="handleFileSelect(event)" />

            <div class="upload-preview" id="upload-preview">
                <img id="upload-preview-img" src="" alt="Preview" />
                <div class="upload-preview-name">
                    <span id="upload-file-name">—</span>
                    <button class="upload-remove-btn" onclick="removeProofFile()">Remove</button>
                </div>
            </div>
        </div>

        <!-- Booking summary mini -->
        <div class="summary-block">
            <div class="summary-block-title">Booking Summary</div>
            <div class="info-row">
                <span class="info-label">🎟 Booking Ref</span>
                <span class="info-value green" id="upload-ref">—</span>
            </div>
            <div class="info-row">
                <span class="info-label">📅 Visit Date</span>
                <span class="info-value" id="upload-date">—</span>
            </div>
            <div class="info-row">
                <span class="info-label">💰 Amount Paid</span>
                <span class="info-value green" id="upload-amount">—</span>
            </div>
        </div>

        <button class="submit-proof-btn" id="submit-proof-btn" onclick="submitPaymentProof()" disabled>
            <span class="iconify" data-icon="lucide:send" data-width="18"></span>
            Submit for Approval
        </button>

        <p style="text-align:center;font-size:12px;color:#7F8C8D;margin-top:8px;">
            Admin will review your proof and approve within 1–2 hours
        </p>

    </div>
</div>


<!-- ═══════════════════════════════
     PAGE 5 — PENDING BOOKING
═══════════════════════════════ -->
<div class="page" id="page-pending">

    <nav>
        <div class="nav-left">
            <div class="nav-brand">
                <span class="iconify" data-icon="lucide:tent-tree" style="font-size:22px;color:#2D5A27;"></span>
                Booking Status
            </div>
        </div>
    </nav>

    <div class="page-wrap" style="padding-bottom:40px;">

        <div class="pending-hero">
            <span class="iconify" data-icon="lucide:clock" style="font-size:44px;color:rgba(255,255,255,0.9);margin-bottom:10px;display:block;"></span>
            <h1>Booking Submitted!</h1>
            <p>Your payment proof has been sent to our team</p>
            <div class="pending-status-pill">
                <div class="pulse-dot"></div>
                Awaiting Admin Approval
            </div>
        </div>

        <!-- Booking Ref -->
        <div class="booking-detail-card">
            <div class="summary-block-title" style="margin-bottom:8px;">Your Booking Reference</div>
            <div class="booking-ref-display">
                <div class="booking-ref-label">Booking Ref</div>
                <div class="booking-ref-value" id="pending-ref">—</div>
            </div>
        </div>

        <!-- Visit Details -->
        <div class="booking-detail-card">
            <div class="summary-block-title" style="margin-bottom:8px;">Visit Details</div>
            <div class="info-row">
                <span class="info-label">📅 Visit Date</span>
                <span class="info-value green" id="pending-date">—</span>
            </div>
            <div class="info-row">
                <span class="info-label">🕘 Opening Hours</span>
                <span class="info-value">9:00 AM – 6:00 PM</span>
            </div>
            <div class="info-row">
                <span class="info-label">📍 Location</span>
                <span class="info-value">WildTrack Safari Park</span>
            </div>
            <div class="info-row">
                <span class="info-label">💰 Amount Paid</span>
                <span class="info-value green" id="pending-amount">—</span>
            </div>
        </div>

        <!-- Tickets summary -->
        <div class="booking-detail-card">
            <div class="summary-block-title" style="margin-bottom:8px;">Tickets</div>
            <div id="pending-tickets-list"></div>
        </div>

        <!-- Proof thumbnail -->
        <div class="booking-detail-card">
            <div class="summary-block-title" style="margin-bottom:8px;">Payment Proof Submitted</div>
            <img class="proof-thumb" id="pending-proof-thumb" src="" alt="Payment proof" />
            <p style="font-size:12px;color:#7F8C8D;margin-top:8px;">
                ✓ Screenshot uploaded • Pending review
            </p>
        </div>

        <!-- What happens next -->
        <div class="what-next-card">
            <div class="what-next-title">What happens next?</div>
            <div class="what-next-item">
                <span class="iconify" data-icon="lucide:search" style="font-size:16px;color:#E67E22;margin-top:1px;flex-shrink:0;"></span>
                Admin will verify your payment screenshot
            </div>
            <div class="what-next-item">
                <span class="iconify" data-icon="lucide:bell" style="font-size:16px;color:#E67E22;margin-top:1px;flex-shrink:0;"></span>
                You'll receive a notification once approved
            </div>
            <div class="what-next-item">
                <span class="iconify" data-icon="lucide:qr-code" style="font-size:16px;color:#E67E22;margin-top:1px;flex-shrink:0;"></span>
                Your QR ticket will appear in the notification
            </div>
            <div class="what-next-item">
                <span class="iconify" data-icon="lucide:clock" style="font-size:16px;color:#E67E22;margin-top:1px;flex-shrink:0;"></span>
                Approval usually takes 1–2 hours during working hours
            </div>
        </div>

        <button class="qr-done-btn" onclick="window.location.href='mainPage.php'">
            <span class="iconify" data-icon="lucide:house" data-width="18"></span>
            Back to Home
        </button>

    </div>
</div>


<!-- ═══════════════════════════════
     PAGE 6 — QR RECEIPT (post-approval)
═══════════════════════════════ -->
<div class="page" id="page-qr">
    <nav>
        <div class="nav-left">
            <div class="nav-brand">
                <span class="iconify" data-icon="lucide:tent-tree" style="font-size:22px;color:#2D5A27;"></span>
                Your Tickets
            </div>
        </div>
    </nav>
    <div class="page-wrap" style="padding-bottom:40px;">

        <div class="hero-card" style="text-align:center;padding:32px;">
            <span class="iconify" data-icon="lucide:circle-check-big" style="font-size:52px;color:#2D5A27;margin-bottom:10px;display:block;"></span>
            <h1 style="font-size:22px;">Booking <span>Confirmed!</span></h1>
            <p style="font-size:13px;color:#7F8C8D;margin-top:6px;">Show the QR code(s) at the entrance</p>
        </div>

        <div id="qr-tickets-container" style="display:flex;flex-direction:column;gap:16px;"></div>

        <p style="text-align:center;font-size:12px;color:#7F8C8D;">
            Your e-ticket has been saved to your account
        </p>

        <button class="qr-done-btn" onclick="window.location.href='mainPage.php'">
            <span class="iconify" data-icon="lucide:house" data-width="18"></span>
            Back to Home
        </button>

    </div>
</div>


<script>
// ════════════════════════════════════════════════
//  STATE
// ════════════════════════════════════════════════
// Prices loaded dynamically from DB — loadPrices() called on window load
const prices     = { adult: 20, child: 10, senior: 15, family: 55 };
const quantities = { adult: 1,  child: 0,  senior: 0,  family: 0  };
const labels     = { adult: 'Adult Pass', child: 'Child Pass', senior: 'Senior Pass', family: 'Family Bundle' };
const icons      = { adult: 'lucide:user', child: 'lucide:baby', senior: 'lucide:person-standing', family: 'lucide:users' };
const addonPrices = { safari: 5, feeding: 12 };
const addonQty    = { safari: 0, feeding: 0 };
const addonLabels = { safari: 'Safari Shuttle', feeding: 'Feeding Pass' };
const addonIcons  = { safari: 'lucide:bus', feeding: 'lucide:cookie' };
const typeKeyMap = { 'Adult': 'adult', 'Child': 'child', 'Senior': 'senior', 'Group': 'family' };

async function loadPrices() {
    try {
        // ── Load ticket prices from DB ───────────────────────────────────────
        const res  = await fetch('http://localhost/WildTrack/api/tickets.php?action=get_prices');
        const data = await res.json();
        if (data.success) {
            data.prices.forEach(function(row) {
                const key = typeKeyMap[row.ticket_type];
                if (!key) return;
                prices[key] = parseFloat(row.price);
                const priceEl = document.getElementById('ticket-price-' + key);
                if (priceEl) priceEl.textContent = 'RM' + parseFloat(row.price).toFixed(2);
            });
        }
    } catch(e) { /* silently fall back to defaults */ }

    try {
        // ── Load add-on prices from addon_prices.php ─────────────────────────
        const res2  = await fetch('http://localhost/WildTrack/api/addon_prices.php?action=get');
        const data2 = await res2.json();
        if (data2.success) {
            Object.entries(data2.prices).forEach(function([key, row]) {
                addonPrices[key] = parseFloat(row.price);
                // Update the "RMx / person" label visible on the card
                const labelEl = document.getElementById('addon-label-' + key);
                if (labelEl) labelEl.textContent = 'RM' + parseFloat(row.price).toFixed(2) + ' / person';
            });
        }
    } catch(e) { /* silently fall back to defaults */ }
    calculateTotal();
}

let appliedVoucher  = null;
let selectedDate    = new Date();
let selectedDateLabel = '';
let currentBookingRef = '';
let currentFinalTotal = 0;
let proofFile = null;
let pendingTicketIds = [];  // filled after submit_payment succeeds

// ════════════════════════════════════════════════
//  REQ 8 — DATE PICKER with 3PM cutoff
// ════════════════════════════════════════════════
const dateRow   = document.getElementById('dateRow');
const monthDisp = document.getElementById('currentMonth');
const today     = new Date();

function isCutoffPassed() {
    const now = new Date();
    return now.getHours() >= 15; // 3:00 PM
}

function generateWeek(startDate) {
    dateRow.innerHTML = '';
    const isAfter3pm = isCutoffPassed();

    for (let i = 0; i < 7; i++) {
        let date = new Date(startDate);
        date.setDate(startDate.getDate() + i);

        const isToday = date.toDateString() === today.toDateString();
        const isDisabled = isToday && isAfter3pm;

        const dayName   = date.toLocaleDateString('en-US', { weekday: 'short' });
        const dayNumber = date.getDate();

        const card = document.createElement('div');
        card.classList.add('date-card');

        if (isDisabled) {
            card.classList.add('disabled');
            card.innerHTML =
                '<p>' + dayName + '</p>' +
                '<h3>' + dayNumber + '</h3>' +
                '<span class="date-cutoff-label">CLOSED</span>';
        } else {
            if (i === 0 && !isDisabled) {
                card.classList.add('active');
                selectedDate = new Date(date);
                selectedDateLabel = date.toLocaleDateString('en-MY', { weekday:'long', day:'numeric', month:'long', year:'numeric' });
            }
            card.innerHTML = '<p>' + dayName + '</p><h3>' + dayNumber + '</h3>';
            card.addEventListener('click', function () {
                document.querySelectorAll('.date-card').forEach(c => c.classList.remove('active'));
                this.classList.add('active');
                selectedDate = new Date(date);
                monthDisp.textContent = date.toLocaleDateString('en-US', { month:'short', year:'numeric' });
                selectedDateLabel = date.toLocaleDateString('en-MY', { weekday:'long', day:'numeric', month:'long', year:'numeric' });
            });
        }

        dateRow.appendChild(card);
    }

    // If today is disabled, auto-select tomorrow
    if (isAfter3pm) {
        const tomorrow = new Date(today);
        tomorrow.setDate(today.getDate() + 1);
        selectedDate = tomorrow;
        selectedDateLabel = tomorrow.toLocaleDateString('en-MY', { weekday:'long', day:'numeric', month:'long', year:'numeric' });
        const tomorrowCard = dateRow.children[1];
        if (tomorrowCard) tomorrowCard.classList.add('active');
    }

    monthDisp.textContent = startDate.toLocaleDateString('en-US', { month:'short', year:'numeric' });
}
generateWeek(today);

// ════════════════════════════════════════════════
//  TICKET & ADDON LOGIC (unchanged)
// ════════════════════════════════════════════════
function updateAddon(type, change) {
    const newQty = addonQty[type] + change;
    if (newQty >= 0) {
        addonQty[type] = newQty;
        document.getElementById('addon-qty-' + type).innerText = newQty;
        document.getElementById('addon-subtotal-' + type).innerText = 'RM' + (newQty * addonPrices[type]).toFixed(2);
        document.getElementById('addon-card-' + type).classList.toggle('has-qty', newQty > 0);
        calculateTotal();
    }
}
function updateQty(type, change) {
    const newQty = quantities[type] + change;
    if (newQty >= 0) {
        quantities[type] = newQty;
        document.getElementById('qty-' + type).innerText = newQty;
        calculateTotal();
    }
}
function calculateTotal() {
    let total = 0;
    total += quantities.adult  * prices.adult;
    total += quantities.child  * prices.child;
    total += quantities.senior * prices.senior;
    total += quantities.family * prices.family;
    total += addonQty.safari   * addonPrices.safari;
    total += addonQty.feeding  * addonPrices.feeding;
    document.getElementById('total-price-bar').innerText = 'RM' + total.toFixed(2);
    return total;
}

// ════════════════════════════════════════════════
//  PAGE NAVIGATION HELPERS
// ════════════════════════════════════════════════
function showPage(fromId, toId, direction) {
    document.getElementById(fromId).classList.remove('active');
    const toEl = document.getElementById(toId);
    toEl.classList.add('active');
    toEl.classList.add(direction === 'forward' ? 'slide-in' : 'slide-back');
    setTimeout(() => toEl.classList.remove('slide-in', 'slide-back'), 400);
    window.scrollTo(0, 0);
}

function goBack() {
    showPage('page-summary', 'page-booking', 'back');
}

function backToSummary() {
    showPage('page-tng', 'page-summary', 'back');
}

function backToTNG() {
    showPage('page-upload', 'page-tng', 'back');
}

function backToHome() {
    window.location.href = 'mainPage.php';
}

// ════════════════════════════════════════════════
//  PAGE 1 → PAGE 2
// ════════════════════════════════════════════════
function goToSummary() {
    const totalTickets = quantities.adult + quantities.child + quantities.senior + quantities.family;
    if (totalTickets === 0) {
        alert('Please select at least one ticket before proceeding.');
        return;
    }
    const total = calculateTotal();
    const ref   = 'WT-' + Math.random().toString(36).substring(2,7).toUpperCase();
    currentBookingRef = ref;
    currentFinalTotal = appliedVoucher ? appliedVoucher.final_total : total;

    document.getElementById('s-date').textContent = selectedDateLabel;
    document.getElementById('s-ref').textContent  = ref;

    removeVoucher(true);
    document.getElementById('s-grand').textContent = 'RM' + total.toFixed(2);
    document.getElementById('s-total').textContent = 'RM' + total.toFixed(2);

    // Build ticket summary
    const ticketContainer = document.getElementById('s-tickets');
    ticketContainer.innerHTML = '';
    let totalVisitors = 0;
    Object.keys(quantities).forEach(function(type) {
        const qty = quantities[type];
        if (qty === 0) return;
        if (type === 'family') totalVisitors += qty * 4;
        else totalVisitors += qty;
        ticketContainer.innerHTML +=
            '<div class="ticket-summary-item">' +
            '<div class="ticket-summary-left">' +
            '<div class="ticket-summary-icon"><span class="iconify" data-icon="' + icons[type] + '" data-width="18"></span></div>' +
            '<div><div class="ticket-summary-name">' + labels[type] + '</div>' +
            '<div class="ticket-summary-qty">x ' + qty + ' &nbsp;·&nbsp; RM' + prices[type] + ' each</div></div>' +
            '</div>' +
            '<div class="ticket-summary-price">RM' + (qty * prices[type]).toFixed(2) + '</div>' +
            '</div>';
    });
    Object.keys(addonQty).forEach(function(type) {
        const qty = addonQty[type];
        if (qty === 0) return;
        ticketContainer.innerHTML +=
            '<div class="ticket-summary-item">' +
            '<div class="ticket-summary-left">' +
            '<div class="ticket-summary-icon" style="background:rgba(230,126,34,0.1);color:#E67E22;">' +
            '<span class="iconify" data-icon="' + addonIcons[type] + '" data-width="18"></span></div>' +
            '<div><div class="ticket-summary-name">' + addonLabels[type] + '</div>' +
            '<div class="ticket-summary-qty">x ' + qty + ' pax &nbsp;·&nbsp; RM' + addonPrices[type] + ' each</div></div>' +
            '</div>' +
            '<div class="ticket-summary-price">RM' + (qty * addonPrices[type]).toFixed(2) + '</div>' +
            '</div>';
    });
    document.getElementById('s-visitors').textContent = totalVisitors + ' pax';

    // Build price rows
    const priceContainer = document.getElementById('s-price-rows');
    priceContainer.innerHTML = '';
    Object.keys(quantities).forEach(function(type) {
        const qty = quantities[type];
        if (qty === 0) return;
        priceContainer.innerHTML +=
            '<div class="info-row"><span class="info-label">' + labels[type] + ' x ' + qty + '</span>' +
            '<span class="info-value">RM' + (qty * prices[type]).toFixed(2) + '</span></div>';
    });
    Object.keys(addonQty).forEach(function(type) {
        const qty = addonQty[type];
        if (qty === 0) return;
        priceContainer.innerHTML +=
            '<div class="info-row"><span class="info-label">' + addonLabels[type] + ' x ' + qty + ' pax</span>' +
            '<span class="info-value">RM' + (qty * addonPrices[type]).toFixed(2) + '</span></div>';
    });

    showPage('page-booking', 'page-summary', 'forward');
}

// ════════════════════════════════════════════════
//  PAGE 2 → PAGE 3 (TNG QR)
//  REQ 2 — fetch TNG settings from admin, show QR
// ════════════════════════════════════════════════
async function goToTNGPayment() {
    const finalTotal = appliedVoucher ? appliedVoucher.final_total : calculateTotal();
    currentFinalTotal = finalTotal;

    // Update ref display
    document.getElementById('tng-amount').textContent = 'RM' + finalTotal.toFixed(2);
    document.getElementById('tng-ref').textContent = currentBookingRef;

    // Fetch TNG settings from admin (QR image + receiver name)
    try {
        const res = await fetch('http://localhost/WildTrack/api/tickets.php?action=tng_settings', {
            credentials: 'include'
        });
        const data = await res.json();
        if (data.success && data.tng_qr_url) {
            document.getElementById('tng-qr-img').src = data.tng_qr_url;
        }
        if (data.success && data.receiver_name) {
            document.getElementById('tng-receiver-name').textContent = data.receiver_name;
        }
    } catch (e) {}
    showPage('page-summary', 'page-tng', 'forward');
}

// ════════════════════════════════════════════════
//  PAGE 3 → PAGE 4 (Upload Proof)
// ════════════════════════════════════════════════
function goToUploadProof() {
    document.getElementById('upload-ref').textContent    = currentBookingRef;
    document.getElementById('upload-date').textContent   = selectedDateLabel;
    document.getElementById('upload-amount').textContent = 'RM' + currentFinalTotal.toFixed(2);
    showPage('page-tng', 'page-upload', 'forward');
}

// ── File Upload Handling ──
function handleFileSelect(event) {
    const file = event.target.files[0];
    if (file) setProofFile(file);
}

function handleDragOver(event) {
    event.preventDefault();
    document.getElementById('upload-area').classList.add('drag-over');
}

function handleDragLeave(event) {
    document.getElementById('upload-area').classList.remove('drag-over');
}

function handleDrop(event) {
    event.preventDefault();
    document.getElementById('upload-area').classList.remove('drag-over');
    const file = event.dataTransfer.files[0];
    if (file && file.type.startsWith('image/')) setProofFile(file);
}

function setProofFile(file) {
    if (file.size > 5 * 1024 * 1024) {
        showToast('File too large. Max 5MB.');
        return;
    }
    proofFile = file;
    const reader = new FileReader();
    reader.onload = function(e) {
        document.getElementById('upload-preview-img').src = e.target.result;
        document.getElementById('upload-file-name').textContent = file.name;
        document.getElementById('upload-preview').style.display = 'block';
        document.getElementById('upload-area').style.display = 'none';
        document.getElementById('submit-proof-btn').disabled = false;
    };
    reader.readAsDataURL(file);
}

function removeProofFile() {
    proofFile = null;
    document.getElementById('proof-file-input').value = '';
    document.getElementById('upload-preview').style.display = 'none';
    document.getElementById('upload-area').style.display = 'block';
    document.getElementById('submit-proof-btn').disabled = true;
}

// ════════════════════════════════════════════════
//  REQ 3 & 4 — Submit payment proof to backend
// ════════════════════════════════════════════════
async function submitPaymentProof() {
    if (!proofFile) { showToast('Please select a screenshot first.'); return; }

    const btn = document.getElementById('submit-proof-btn');
    btn.disabled = true;
    btn.innerHTML = '<span class="iconify" data-icon="lucide:loader" data-width="18" style="animation:spin 1s linear infinite"></span> Submitting...';

    const visitDate = selectedDate instanceof Date
        ? selectedDate.toISOString().split('T')[0]
        : new Date().toISOString().split('T')[0];

    // Build tickets & addons list
    const ticketsList = [];
    if (quantities.adult  > 0) ticketsList.push({ ticket_type: 'Adult',  price: prices.adult,  quantity: quantities.adult  });
    if (quantities.child  > 0) ticketsList.push({ ticket_type: 'Child',  price: prices.child,  quantity: quantities.child  });
    if (quantities.senior > 0) ticketsList.push({ ticket_type: 'Senior', price: prices.senior, quantity: quantities.senior });
    if (quantities.family > 0) ticketsList.push({ ticket_type: 'Group',  price: prices.family, quantity: quantities.family });

    const addonsList = [];
    if (addonQty.safari  > 0) addonsList.push({ addon_type: 'Safari Shuttle', quantity: addonQty.safari,  price_per: addonPrices.safari  });
    if (addonQty.feeding > 0) addonsList.push({ addon_type: 'Feeding Pass',   quantity: addonQty.feeding, price_per: addonPrices.feeding });

    const formData = new FormData();
    formData.append('proof_image',     proofFile);
    formData.append('visit_date',      visitDate);
    formData.append('booking_ref',     currentBookingRef);
    formData.append('final_total',     currentFinalTotal);
    formData.append('voucher_id',      appliedVoucher ? appliedVoucher.voucher_id : '');
    formData.append('voucher_discount', appliedVoucher ? appliedVoucher.discount_amount : 0);
    formData.append('voucher_code',    appliedVoucher ? appliedVoucher.code : '');
    formData.append('payment_method',  "Touch 'n Go eWallet");
    formData.append('tickets',         JSON.stringify(ticketsList));
    formData.append('addons',          JSON.stringify(addonsList));

    try {
        const res  = await fetch('http://localhost/WildTrack/api/payment_proof.php', {
            method: 'POST',
            credentials: 'include',
            body: formData
        });
        const data = await res.json();

        if (data.success) {
            pendingTicketIds = data.ticket_ids || [];
            showPendingPage(data);
        } else if (res.status === 401) {
            showToast('Please log in first.');
        } else {
            showToast('Error: ' + data.message);
            btn.disabled = false;
            btn.innerHTML = '<span class="iconify" data-icon="lucide:send" data-width="18"></span> Submit for Approval';
        }
    } catch (err) {
        showToast('Connection error. Please try again.');
        btn.disabled = false;
        btn.innerHTML = '<span class="iconify" data-icon="lucide:send" data-width="18"></span> Submit for Approval';
    }
}

// ════════════════════════════════════════════════
//  REQ 5 — Show Pending Booking Page
// ════════════════════════════════════════════════
function showPendingPage(data) {
    document.getElementById('pending-ref').textContent    = currentBookingRef;
    document.getElementById('pending-date').textContent   = selectedDateLabel;
    document.getElementById('pending-amount').textContent = 'RM' + currentFinalTotal.toFixed(2);

    // Show proof thumbnail
    if (proofFile) {
        const reader = new FileReader();
        reader.onload = e => {
            document.getElementById('pending-proof-thumb').src = e.target.result;
        };
        reader.readAsDataURL(proofFile);
    }

    // Build ticket list
    const ticketListEl = document.getElementById('pending-tickets-list');
    ticketListEl.innerHTML = '';
    Object.keys(quantities).forEach(function(type) {
        const qty = quantities[type];
        if (qty === 0) return;
        ticketListEl.innerHTML +=
            '<div class="info-row">' +
            '<span class="info-label">' + labels[type] + '</span>' +
            '<span class="info-value">x ' + qty + ' — RM' + (qty * prices[type]).toFixed(2) + '</span>' +
            '</div>';
    });
    Object.keys(addonQty).forEach(function(type) {
        const qty = addonQty[type];
        if (qty === 0) return;
        ticketListEl.innerHTML +=
            '<div class="info-row">' +
            '<span class="info-label">' + addonLabels[type] + '</span>' +
            '<span class="info-value">x ' + qty + ' — RM' + (qty * addonPrices[type]).toFixed(2) + '</span>' +
            '</div>';
    });

    showPage('page-upload', 'page-pending', 'forward');
    // Start polling for approval
}



// ════════════════════════════════════════════════
//  VOUCHER LOGIC (unchanged from original)
// ════════════════════════════════════════════════
async function applyVoucher() {
    const code  = document.getElementById('voucher-input').value.trim();
    const total = calculateTotal();
    const feedback = document.getElementById('voucher-feedback');
    if (!code) {
        feedback.textContent = 'Please enter a voucher code.';
        feedback.className   = 'voucher-feedback error';
        return;
    }
    const btn = document.getElementById('voucher-apply-btn');
    btn.disabled    = true;
    btn.textContent = 'Checking...';
    feedback.textContent = '';
    feedback.className   = 'voucher-feedback';
    try {
        const res  = await fetch('http://localhost/WildTrack/api/voucher_validate.php', {
            method: 'POST', credentials: 'include',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ code: code, total: total })
        });
        const data = await res.json();
        if (data.success) {
            appliedVoucher = { voucher_id: data.voucher_id, code: data.code, discount_amount: data.discount_amount, final_total: data.final_total };
            feedback.textContent = '✓ ' + data.message;
            feedback.className   = 'voucher-feedback success';
            document.getElementById('s-voucher-row').style.display    = 'flex';
            document.getElementById('s-voucher-code').textContent     = data.code;
            document.getElementById('s-voucher-discount').textContent = '−RM' + data.discount_amount.toFixed(2);
            document.getElementById('s-grand').textContent = 'RM' + data.final_total.toFixed(2);
            document.getElementById('voucher-input').disabled = true;
            btn.style.display = 'none';
            document.getElementById('voucher-remove-btn').style.display = 'inline-flex';
            currentFinalTotal = data.final_total;
        } else {
            feedback.textContent = '✗ ' + data.message;
            feedback.className   = 'voucher-feedback error';
            btn.disabled = false; btn.textContent = 'Apply';
        }
    } catch (err) {
        feedback.textContent = '✗ Could not connect to server. Please try again.';
        feedback.className   = 'voucher-feedback error';
        btn.disabled = false; btn.textContent = 'Apply';
    }
}

function removeVoucher(silent) {
    appliedVoucher = null;
    document.getElementById('voucher-input').value    = '';
    document.getElementById('voucher-input').disabled = false;
    document.getElementById('voucher-feedback').textContent = '';
    document.getElementById('voucher-feedback').className   = 'voucher-feedback';
    document.getElementById('voucher-apply-btn').style.display   = 'inline-flex';
    document.getElementById('voucher-apply-btn').disabled        = false;
    document.getElementById('voucher-apply-btn').textContent     = 'Apply';
    document.getElementById('voucher-remove-btn').style.display  = 'none';
    document.getElementById('s-voucher-row').style.display = 'none';
    const total = calculateTotal();
    document.getElementById('s-grand').textContent = 'RM' + total.toFixed(2);
    currentFinalTotal = total;
}

// ════════════════════════════════════════════════
//  UTILITY
// ════════════════════════════════════════════════

// showToast shim — delegates to the global notification toast in nav.php
function showToast(msg, duration) {
    if (typeof wtShowToast === 'function') {
        wtShowToast(msg, duration);
    } else {
        // fallback
        var t = document.getElementById('wt-toast');
        if (t) { t.textContent = msg; t.classList.add('show'); setTimeout(function(){ t.classList.remove('show'); }, duration || 3500); }
    }
}

function escHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;').replace(/</g, '&lt;')
        .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

// Start polling on load in case user has pending bookings from previous sessions
window.addEventListener('load', function() {
    // Load prices from DB so admin edits reflect immediately
    loadPrices();

    // If redirected from notification bell with ?show=qr, auto-load the QR page
    var params = new URLSearchParams(window.location.search);
    if (params.get('show') === 'qr') {
        var idsRaw = params.get('ticket_ids') || '';
        var ticketIds = [];
        try { ticketIds = JSON.parse(decodeURIComponent(idsRaw)); } catch(e) {}
        if (ticketIds && ticketIds.length > 0) {
            loadApprovedTickets(ticketIds);
        }
    }
});

// ════════════════════════════════════════════════
//  Load approved tickets & show QR page
//  (called from URL param on page load)
// ════════════════════════════════════════════════
async function loadApprovedTickets(ticketIds) {
    if (!ticketIds || ticketIds.length === 0) return;
    try {
        const res  = await fetch('http://localhost/WildTrack/api/tickets.php?action=get_tickets_by_ids', {
            method: 'POST',
            credentials: 'include',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ ticket_ids: ticketIds })
        });
        const data = await res.json();
        if (data.success) {
            showQRPage(data.tickets, currentFinalTotal);
        }
    } catch(e) {
        showToast('Could not load tickets. Please check your account.');
    }
}

function showQRPage(tickets, totalPaid) {
    const container = document.getElementById('qr-tickets-container');
    container.innerHTML = '';
    tickets.forEach(function(t) {
        const div = document.createElement('div');
        div.className = 'qr-ticket-card';
        div.innerHTML =
            '<div class="qr-ticket-type">' + escHtml(t.ticket_type) + ' Pass</div>' +
            '<div class="qr-ticket-date">Visit: ' + escHtml(t.visit_date) + '</div>' +
            '<div class="qr-code-box">' +
            '<img src="https://api.qrserver.com/v1/create-qr-code/?size=160x160&data=' + 
                encodeURIComponent(t.qr_code) + '" alt="QR Code" class="qr-img" />' +
            '</div>' +
            '<div class="qr-code-text">' + escHtml(t.qr_code) + '</div>';
        container.appendChild(div);
    });
    if (totalPaid) {
        document.getElementById('qr-total').textContent = 'RM' + parseFloat(totalPaid).toFixed(2);
    }
    document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
    const qrPage = document.getElementById('page-qr');
    qrPage.classList.add('active');
    window.scrollTo(0, 0);
}
</script>

</body>
</html>
