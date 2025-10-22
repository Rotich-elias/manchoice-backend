<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>@yield('title') - MAN's Choice Enterprise</title>
    <style>
        @page {
            margin: 100px 50px 80px 50px;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11pt;
            color: #333;
            line-height: 1.4;
        }

        header {
            position: fixed;
            top: -80px;
            left: 0;
            right: 0;
            height: 80px;
            text-align: center;
            border-bottom: 3px solid #2563eb;
            padding-bottom: 10px;
        }

        .logo-section {
            margin-bottom: 5px;
        }

        .company-name {
            font-size: 22pt;
            font-weight: bold;
            color: #1e40af;
            margin: 0;
            padding: 0;
        }

        .company-tagline {
            font-size: 9pt;
            color: #6b7280;
            font-style: italic;
            margin: 0;
            padding: 0;
        }

        .company-info {
            font-size: 8pt;
            color: #6b7280;
            margin-top: 5px;
        }

        footer {
            position: fixed;
            bottom: -60px;
            left: 0;
            right: 0;
            height: 50px;
            border-top: 2px solid #e5e7eb;
            padding-top: 10px;
            text-align: center;
            font-size: 8pt;
            color: #6b7280;
        }

        .page-number:before {
            content: "Page " counter(page);
        }

        .report-title {
            font-size: 18pt;
            font-weight: bold;
            color: #1f2937;
            margin: 20px 0 10px 0;
            text-align: center;
        }

        .report-meta {
            text-align: center;
            font-size: 9pt;
            color: #6b7280;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e5e7eb;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            font-size: 9pt;
        }

        table thead {
            background-color: #2563eb;
            color: white;
        }

        table thead th {
            padding: 10px 8px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #1e40af;
        }

        table tbody td {
            padding: 8px;
            border: 1px solid #e5e7eb;
        }

        table tbody tr:nth-child(even) {
            background-color: #f9fafb;
        }

        table tbody tr:hover {
            background-color: #f3f4f6;
        }

        .summary-box {
            background-color: #eff6ff;
            border-left: 4px solid #2563eb;
            padding: 15px;
            margin: 20px 0;
        }

        .summary-box h3 {
            margin: 0 0 10px 0;
            color: #1e40af;
            font-size: 12pt;
        }

        .summary-grid {
            display: table;
            width: 100%;
        }

        .summary-item {
            display: table-cell;
            padding: 5px 10px;
            width: 25%;
        }

        .summary-label {
            font-size: 8pt;
            color: #6b7280;
            text-transform: uppercase;
        }

        .summary-value {
            font-size: 14pt;
            font-weight: bold;
            color: #1f2937;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .font-bold {
            font-weight: bold;
        }

        .text-green {
            color: #059669;
        }

        .text-red {
            color: #dc2626;
        }

        .text-blue {
            color: #2563eb;
        }

        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 8pt;
            font-weight: bold;
        }

        .status-pending {
            background-color: #fef3c7;
            color: #92400e;
        }

        .status-approved {
            background-color: #d1fae5;
            color: #065f46;
        }

        .status-active {
            background-color: #dbeafe;
            color: #1e40af;
        }

        .status-completed {
            background-color: #e5e7eb;
            color: #374151;
        }

        .status-rejected {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 80pt;
            color: rgba(0, 0, 0, 0.05);
            z-index: -1;
        }
    </style>
</head>
<body>
    <header>
        <div class="logo-section">
            <h1 class="company-name">MAN's CHOICE ENTERPRISE</h1>
            <p class="company-tagline">Your Trusted Partner in Motorcycle Financing</p>
        </div>
        <div class="company-info">
            Phone: +254 700 000 000 | Email: info@manschoice.co.ke | Nairobi, Kenya
        </div>
    </header>

    <footer>
        <div>
            <strong>MAN's Choice Enterprise</strong> - Motorcycle Loan & Product Services
        </div>
        <div class="page-number"></div>
        <div>Generated on {{ date('F d, Y \a\t h:i A') }}</div>
    </footer>

    <main>
        @yield('content')
    </main>
</body>
</html>
