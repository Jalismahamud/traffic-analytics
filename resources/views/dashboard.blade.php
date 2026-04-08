@extends('admin.layouts.app')
@section('title', 'Traffic Analytics')

@push('css')
<style>
:root {
    --ta-primary: #3C407A;
    --ta-primary-light: #4f54a0;
    --ta-success: #22c55e;
    --ta-warning: #f59e0b;
    --ta-danger: #ef4444;
    --ta-info: #3b82f6;
    --ta-purple: #8b5cf6;
    --ta-card-bg: #fff;
    --ta-border: #e2e8f0;
    --ta-text: #1e293b;
    --ta-muted: #64748b;
    --ta-body-bg: #f0f4f8;
}
[data-theme="dark"] {
    --ta-card-bg: #1e2535;
    --ta-border: #2d3748;
    --ta-text: #e2e8f0;
    --ta-muted: #94a3b8;
    --ta-body-bg: #111827;
}
[data-theme="dark"] .ta-card,
[data-theme="dark"] .ta-table-wrap,
[data-theme="dark"] .ta-logs-wrap { background: var(--ta-card-bg) !important; border-color: var(--ta-border) !important; }
[data-theme="dark"] .ta-label,
[data-theme="dark"] .ta-table th,
[data-theme="dark"] .ta-table td,
[data-theme="dark"] .ta-section-title { color: var(--ta-text) !important; }
[data-theme="dark"] .ta-muted-text { color: var(--ta-muted) !important; }
[data-theme="dark"] .ta-filter-bar { background: var(--ta-card-bg); border-color: var(--ta-border); }
[data-theme="dark"] .ta-filter-bar select,
[data-theme="dark"] .ta-filter-bar input { background: #2d3748; border-color: var(--ta-border); color: var(--ta-text); }
[data-theme="dark"] .ta-table tr:hover { background: rgba(255,255,255,.03); }
[data-theme="dark"] .ta-skeleton { background: linear-gradient(90deg, #2d3748 25%, #3a4556 50%, #2d3748 75%); }

* { box-sizing: border-box; }

.ta-wrapper {
    padding: 24px;
    background: var(--ta-body-bg);
    min-height: 100vh;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
}

.ta-top-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 24px;
    flex-wrap: wrap;
    gap: 12px;
}
.ta-page-title {
    font-size: 22px;
    font-weight: 800;
    color: var(--ta-primary);
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 0;
}
.ta-page-title svg { opacity: .85; }
.ta-top-actions { display: flex; align-items: center; gap: 10px; }

.ta-dark-toggle {
    width: 36px; height: 36px; border-radius: 10px;
    background: var(--ta-card-bg); border: 1.5px solid var(--ta-border);
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; transition: all .2s; color: var(--ta-muted); font-size: 15px;
}
.ta-dark-toggle:hover { border-color: var(--ta-primary); color: var(--ta-primary); }

.ta-export-btn {
    display: flex; align-items: center; gap: 6px;
    padding: 8px 16px; border-radius: 10px;
    background: var(--ta-primary); color: #fff;
    font-size: 13px; font-weight: 600; border: none; cursor: pointer;
    text-decoration: none; transition: background .2s;
}
.ta-export-btn:hover { background: var(--ta-primary-light); color: #fff; }

.ta-filter-bar {
    background: var(--ta-card-bg);
    border: 1.5px solid var(--ta-border);
    border-radius: 14px;
    padding: 14px 20px;
    margin-bottom: 24px;
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}
.ta-filter-bar label { font-size: 12px; font-weight: 700; color: var(--ta-muted); text-transform: uppercase; letter-spacing: .04em; }
.ta-filter-bar select,
.ta-filter-bar input[type="date"] {
    padding: 7px 12px; border: 1.5px solid var(--ta-border); border-radius: 8px;
    font-size: 13px; color: var(--ta-text); outline: none; cursor: pointer;
    background: var(--ta-card-bg); transition: border-color .15s;
}
.ta-filter-bar select:focus,
.ta-filter-bar input[type="date"]:focus { border-color: var(--ta-primary); }
.ta-custom-range { display: none; align-items: center; gap: 8px; }
.ta-custom-range.show { display: flex; }

.ta-apply-btn {
    padding: 7px 18px; border-radius: 8px; background: var(--ta-primary);
    color: #fff; font-size: 13px; font-weight: 600; border: none; cursor: pointer;
    transition: background .15s;
}
.ta-apply-btn:hover { background: var(--ta-primary-light); }

.ta-live-dot {
    width: 8px; height: 8px; border-radius: 50%; background: var(--ta-success);
    display: inline-block; animation: taPulse 1.5s ease-in-out infinite;
}
@keyframes taPulse { 0%, 100% { opacity: 1; } 50% { opacity: .3; } }
.ta-live-badge {
    display: flex; align-items: center; gap: 5px;
    font-size: 11px; font-weight: 600; color: var(--ta-success);
    background: rgba(34,197,94,.1); padding: 4px 10px; border-radius: 999px;
}

.ta-metrics-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 16px;
    margin-bottom: 24px;
}

.ta-card {
    background: var(--ta-card-bg);
    border: 1.5px solid var(--ta-border);
    border-radius: 16px;
    padding: 20px;
    transition: transform .2s, box-shadow .2s;
    position: relative;
    overflow: hidden;
}
.ta-card:hover { transform: translateY(-3px); box-shadow: 0 8px 24px rgba(0,0,0,.08); }

.ta-metric-card {
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.ta-metric-card::before {
    content: '';
    position: absolute;
    top: 0; right: 0;
    width: 80px; height: 80px;
    border-radius: 50%;
    transform: translate(30px, -30px);
    opacity: .12;
}
.ta-card-primary::before   { background: var(--ta-primary); }
.ta-card-success::before   { background: var(--ta-success); }
.ta-card-warning::before   { background: var(--ta-warning); }
.ta-card-danger::before    { background: var(--ta-danger); }
.ta-card-info::before      { background: var(--ta-info); }
.ta-card-purple::before    { background: var(--ta-purple); }

.ta-card-icon {
    width: 42px; height: 42px; border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 18px; flex-shrink: 0;
}
.ta-icon-primary { background: rgba(60,64,122,.12); color: var(--ta-primary); }
.ta-icon-success { background: rgba(34,197,94,.12); color: var(--ta-success); }
.ta-icon-warning { background: rgba(245,158,11,.12); color: var(--ta-warning); }
.ta-icon-danger  { background: rgba(239,68,68,.12);  color: var(--ta-danger); }
.ta-icon-info    { background: rgba(59,130,246,.12); color: var(--ta-info); }
.ta-icon-purple  { background: rgba(139,92,246,.12); color: var(--ta-purple); }

.ta-label {
    font-size: 11px; font-weight: 700; color: var(--ta-muted);
    text-transform: uppercase; letter-spacing: .05em;
}
.ta-value {
    font-size: 28px; font-weight: 800; color: var(--ta-text); line-height: 1;
}
.ta-muted-text { font-size: 12px; color: var(--ta-muted); }

.ta-charts-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 16px;
    margin-bottom: 24px;
}
.ta-charts-grid-3 {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
    margin-bottom: 24px;
}

.ta-section-title {
    font-size: 14px; font-weight: 700; color: var(--ta-text);
    margin-bottom: 16px; display: flex; align-items: center; gap: 8px;
}

.ta-chart-wrap { position: relative; height: 220px; }

.ta-table-wrap {
    background: var(--ta-card-bg);
    border: 1.5px solid var(--ta-border);
    border-radius: 16px;
    overflow: hidden;
    margin-bottom: 24px;
}
.ta-table-head {
    padding: 16px 20px;
    border-bottom: 1px solid var(--ta-border);
    display: flex; align-items: center; justify-content: space-between;
}
.ta-table { width: 100%; border-collapse: collapse; }
.ta-table th {
    padding: 11px 16px; font-size: 11px; font-weight: 700;
    color: var(--ta-muted); text-transform: uppercase; letter-spacing: .04em;
    text-align: left; border-bottom: 1px solid var(--ta-border);
    background: rgba(0,0,0,.015);
}
.ta-table td {
    padding: 11px 16px; font-size: 13px; color: var(--ta-text);
    border-bottom: 1px solid rgba(0,0,0,.04); vertical-align: middle;
}
.ta-table tr:last-child td { border-bottom: none; }
.ta-table tr:hover td { background: rgba(60,64,122,.03); }
.ta-table .url-cell { max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

.ta-badge {
    display: inline-block; padding: 2px 8px; border-radius: 6px;
    font-size: 11px; font-weight: 700;
}
.ta-badge-success { background: rgba(34,197,94,.12);  color: #16a34a; }
.ta-badge-warning { background: rgba(245,158,11,.12); color: #d97706; }
.ta-badge-danger  { background: rgba(239,68,68,.12);  color: #dc2626; }
.ta-badge-info    { background: rgba(59,130,246,.12); color: #2563eb; }
.ta-badge-secondary { background: rgba(100,116,139,.12); color: #475569; }

.ta-logs-wrap {
    background: var(--ta-card-bg);
    border: 1.5px solid var(--ta-border);
    border-radius: 16px;
    overflow: hidden;
}
.ta-logs-body { max-height: 360px; overflow-y: auto; }
.ta-logs-body::-webkit-scrollbar { width: 4px; }
.ta-logs-body::-webkit-scrollbar-thumb { background: var(--ta-border); border-radius: 4px; }

.ta-skeleton {
    border-radius: 8px;
    background: linear-gradient(90deg, #e2e8f0 25%, #f1f5f9 50%, #e2e8f0 75%);
    background-size: 200% 100%;
    animation: taShimmer 1.2s infinite;
}
@keyframes taShimmer { to { background-position: -200% 0; } }
.ta-skeleton-text { height: 14px; margin-bottom: 6px; }
.ta-skeleton-val  { height: 32px; width: 80px; }

.ta-progress-bar {
    height: 6px; background: var(--ta-border); border-radius: 999px; overflow: hidden;
}
.ta-progress-fill {
    height: 100%; border-radius: 999px; transition: width .6s ease;
    background: linear-gradient(90deg, var(--ta-primary), var(--ta-primary-light));
}

.ta-method-chip {
    padding: 2px 8px; border-radius: 5px; font-size: 10px; font-weight: 800;
    letter-spacing: .04em;
}
.method-GET    { background: rgba(34,197,94,.12);  color: #16a34a; }
.method-POST   { background: rgba(59,130,246,.12); color: #2563eb; }
.method-PUT,
.method-PATCH  { background: rgba(245,158,11,.12); color: #d97706; }
.method-DELETE { background: rgba(239,68,68,.12);  color: #dc2626; }

@media (max-width: 900px) {
    .ta-charts-grid   { grid-template-columns: 1fr; }
    .ta-charts-grid-3 { grid-template-columns: 1fr; }
}
@media (max-width: 600px) {
    .ta-metrics-grid  { grid-template-columns: 1fr 1fr; }
    .ta-wrapper       { padding: 14px; }
}
</style>
@endpush

@section('content')
<div class="ta-wrapper" id="taRoot">

    {{-- Top Bar --}}
    <div class="ta-top-bar">
        <h1 class="ta-page-title">
            <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
            Traffic Analytics
        </h1>
        <div class="ta-top-actions">
            <div class="ta-live-badge">
                <span class="ta-live-dot"></span>
                Live
            </div>
            <button class="ta-dark-toggle" id="taDarkToggle" title="Toggle dark mode">
                <i class="fa-solid fa-moon" id="taDarkIcon"></i>
            </button>
            <a href="#" class="ta-export-btn" id="taExportBtn">
                <i class="fa-solid fa-file-csv"></i> Export CSV
            </a>
        </div>
    </div>

    {{-- Filters --}}
    <div class="ta-filter-bar">
        <div>
            <label>Time Range</label><br>
            <select id="taRange">
                <option value="today">Today</option>
                <option value="7" selected>Last 7 Days</option>
                <option value="30">Last 30 Days</option>
                <option value="custom">Custom Range</option>
            </select>
        </div>
        <div class="ta-custom-range" id="taCustomRange">
            <div>
                <label>From</label><br>
                <input type="date" id="taDateFrom" value="{{ now()->subDays(7)->toDateString() }}">
            </div>
            <div>
                <label>To</label><br>
                <input type="date" id="taDateTo" value="{{ now()->toDateString() }}">
            </div>
        </div>
        <div style="margin-top:16px">
            <button class="ta-apply-btn" id="taApplyBtn">
                <i class="fa-solid fa-magnifying-glass me-1"></i> Apply
            </button>
        </div>
        <div style="margin-left:auto; font-size:12px; color:var(--ta-muted)">
            Auto-refresh <strong id="taCountdown">15</strong>s
        </div>
    </div>

    {{-- Metric Cards --}}
    <div class="ta-metrics-grid" id="taMetrics">
        @foreach([
            ['icon'=>'fa-chart-line','color'=>'primary','label'=>'Total Requests','id'=>'mTotal','sub'=>'in selected range'],
            ['icon'=>'fa-users','color'=>'info','label'=>'Unique Visitors','id'=>'mUnique','sub'=>'by IP address'],
            ['icon'=>'fa-clock','color'=>'success','label'=>'Avg Response','id'=>'mAvgTime','sub'=>'milliseconds'],
            ['icon'=>'fa-circle-exclamation','color'=>'danger','label'=>'Error Rate','id'=>'mErrorRate','sub'=>'status ≥ 400'],
            ['icon'=>'fa-calendar-day','color'=>'warning','label'=>'Today Requests','id'=>'mToday','sub'=>'current day'],
            ['icon'=>'fa-user-check','color'=>'purple','label'=>'Today Unique','id'=>'mTodayUnique','sub'=>'current day'],
        ] as $card)
        <div class="ta-card ta-metric-card ta-card-{{ $card['color'] }}">
            <div class="ta-card-icon ta-icon-{{ $card['color'] }}">
                <i class="fa-solid {{ $card['icon'] }}"></i>
            </div>
            <div class="ta-label">{{ $card['label'] }}</div>
            <div class="ta-value ta-skeleton ta-skeleton-val" id="{{ $card['id'] }}">—</div>
            <div class="ta-muted-text">{{ $card['sub'] }}</div>
        </div>
        @endforeach
    </div>

    {{-- Line + Pie Charts --}}
    <div class="ta-charts-grid">
        <div class="ta-card">
            <div class="ta-section-title">
                <i class="fa-solid fa-chart-area" style="color:var(--ta-primary)"></i>
                Traffic Over Time
            </div>
            <div class="ta-chart-wrap">
                <canvas id="taLineChart"></canvas>
            </div>
        </div>
        <div class="ta-card">
            <div class="ta-section-title">
                <i class="fa-solid fa-chart-pie" style="color:var(--ta-warning)"></i>
                Status Distribution
            </div>
            <div class="ta-chart-wrap">
                <canvas id="taPieChart"></canvas>
            </div>
        </div>
    </div>

    {{-- Bar + Method Charts --}}
    <div class="ta-charts-grid-3">
        <div class="ta-card" style="grid-column: span 2">
            <div class="ta-section-title">
                <i class="fa-solid fa-chart-bar" style="color:var(--ta-info)"></i>
                Top Visited URLs
            </div>
            <div class="ta-chart-wrap">
                <canvas id="taBarChart"></canvas>
            </div>
        </div>
        <div class="ta-card">
            <div class="ta-section-title">
                <i class="fa-solid fa-code" style="color:var(--ta-success)"></i>
                HTTP Methods
            </div>
            <div class="ta-chart-wrap">
                <canvas id="taMethodChart"></canvas>
            </div>
        </div>
    </div>

    {{-- Top URLs Table --}}
    <div class="ta-table-wrap">
        <div class="ta-table-head">
            <span class="ta-section-title" style="margin-bottom:0">
                <i class="fa-solid fa-link" style="color:var(--ta-primary)"></i>
                Top URLs
            </span>
        </div>
        <div style="overflow-x:auto">
            <table class="ta-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>URL</th>
                        <th>Hits</th>
                        <th>Avg Time (ms)</th>
                        <th>Popularity</th>
                    </tr>
                </thead>
                <tbody id="taTopUrls">
                    <tr><td colspan="5" class="text-center py-4" style="color:var(--ta-muted)">Loading...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- Top IPs + Recent Logs --}}
    <div class="ta-charts-grid">
        <div class="ta-logs-wrap">
            <div class="ta-table-head">
                <span class="ta-section-title" style="margin-bottom:0">
                    <i class="fa-solid fa-clock-rotate-left" style="color:var(--ta-purple)"></i>
                    Recent Requests
                </span>
            </div>
            <div class="ta-logs-body">
                <table class="ta-table">
                    <thead>
                        <tr>
                            <th>Method</th>
                            <th>URL</th>
                            <th>Status</th>
                            <th>IP</th>
                            <th>Time (ms)</th>
                            <th>When</th>
                        </tr>
                    </thead>
                    <tbody id="taRecentLogs">
                        <tr><td colspan="6" class="text-center py-4" style="color:var(--ta-muted)">Loading...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="ta-card">
            <div class="ta-section-title">
                <i class="fa-solid fa-server" style="color:var(--ta-danger)"></i>
                Top IP Addresses
            </div>
            <div id="taTopIPs" style="display:flex;flex-direction:column;gap:10px">
                <div class="ta-skeleton ta-skeleton-text" style="width:100%"></div>
                <div class="ta-skeleton ta-skeleton-text" style="width:80%"></div>
                <div class="ta-skeleton ta-skeleton-text" style="width:60%"></div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function () {
    'use strict';

    const ROUTES = {
        data:   '{{ route("admin.traffic.data") }}',
        export: '{{ route("admin.traffic.export") }}',
    };

    const COLORS = {
        primary : '#3C407A',
        primaryL: '#6366f1',
        success : '#22c55e',
        warning : '#f59e0b',
        danger  : '#ef4444',
        info    : '#3b82f6',
        purple  : '#8b5cf6',
        muted   : '#94a3b8',
    };

    let charts = {};
    let countdown = 15;
    let refreshTimer = null;
    let isDark = localStorage.getItem('ta_dark') === '1';

    function applyTheme() {
        document.getElementById('taRoot').closest('[data-theme]')?.removeAttribute('data-theme');
        document.getElementById('taRoot').setAttribute('data-theme', isDark ? 'dark' : 'light');
        document.getElementById('taDarkIcon').className = isDark ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
        if (charts.line) { updateChartColors(); }
    }

    document.getElementById('taDarkToggle').addEventListener('click', () => {
        isDark = !isDark;
        localStorage.setItem('ta_dark', isDark ? '1' : '0');
        applyTheme();
    });

    function getFilterParams() {
        const range    = document.getElementById('taRange').value;
        const dateFrom = document.getElementById('taDateFrom').value;
        const dateTo   = document.getElementById('taDateTo').value;
        const params   = new URLSearchParams({ range });
        if (range === 'custom') { params.set('date_from', dateFrom); params.set('date_to', dateTo); }
        return params;
    }

    document.getElementById('taRange').addEventListener('change', function () {
        const show = this.value === 'custom';
        document.getElementById('taCustomRange').classList.toggle('show', show);
    });

    document.getElementById('taApplyBtn').addEventListener('click', loadData);

    document.getElementById('taExportBtn').addEventListener('click', function (e) {
        e.preventDefault();
        const params = getFilterParams();
        window.location.href = ROUTES.export + '?' + params.toString();
    });

    async function loadData() {
        showSkeletons();
        try {
            const res  = await fetch(ROUTES.data + '?' + getFilterParams(), {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            });
            const data = await res.json();
            renderSummary(data.summary);
            renderLineChart(data.over_time);
            renderPieChart(data.status_dist);
            renderBarChart(data.top_urls);
            renderMethodChart(data.methods);
            renderTopUrls(data.top_urls);
            renderRecentLogs(data.recent_logs);
            renderTopIPs(data.top_ips);
        } catch (e) {
            console.error('Traffic data load failed', e);
        }
    }

    function showSkeletons() {
        ['mTotal', 'mUnique', 'mAvgTime', 'mErrorRate', 'mToday', 'mTodayUnique'].forEach(id => {
            const el = document.getElementById(id);
            if (el) { el.className = 'ta-value ta-skeleton ta-skeleton-val'; el.textContent = ''; }
        });
    }

    function renderSummary(s) {
        setVal('mTotal',       fmt(s.total_requests));
        setVal('mUnique',      fmt(s.unique_visitors));
        setVal('mAvgTime',     s.avg_response_time + ' ms');
        setVal('mErrorRate',   s.error_rate + '%');
        setVal('mToday',       fmt(s.today_requests));
        setVal('mTodayUnique', fmt(s.today_unique));
    }

    function setVal(id, val) {
        const el = document.getElementById(id);
        if (!el) return;
        el.className = 'ta-value';
        el.textContent = val;
    }

    function fmt(n) { return new Intl.NumberFormat().format(n); }

    function renderLineChart(data) {
        const ctx    = document.getElementById('taLineChart').getContext('2d');
        const labels = data.map(d => d.period);
        const values = data.map(d => d.count);
        const avgTimes = data.map(d => d.avg_time);

        if (charts.line) charts.line.destroy();
        charts.line = new Chart(ctx, {
            type: 'line',
            data: {
                labels,
                datasets: [
                    {
                        label: 'Requests',
                        data: values,
                        borderColor: COLORS.primary,
                        backgroundColor: hexAlpha(COLORS.primary, .1),
                        tension: .4, fill: true, pointRadius: 3,
                        pointHoverRadius: 5,
                        yAxisID: 'y',
                    },
                    {
                        label: 'Avg Response (ms)',
                        data: avgTimes,
                        borderColor: COLORS.warning,
                        backgroundColor: 'transparent',
                        tension: .4, pointRadius: 3, borderDash: [4, 4],
                        yAxisID: 'y1',
                    },
                ],
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: { legend: { labels: { font: { size: 11 }, color: isDark ? '#e2e8f0' : '#475569' } } },
                scales: {
                    x: { ticks: { color: COLORS.muted, font: { size: 10 } }, grid: { color: isDark ? 'rgba(255,255,255,.05)' : 'rgba(0,0,0,.04)' } },
                    y: { ticks: { color: COLORS.muted, font: { size: 10 } }, grid: { color: isDark ? 'rgba(255,255,255,.05)' : 'rgba(0,0,0,.04)' } },
                    y1: { position: 'right', ticks: { color: COLORS.warning, font: { size: 10 } }, grid: { drawOnChartArea: false } },
                },
            },
        });
    }

    function renderPieChart(dist) {
        const ctx    = document.getElementById('taPieChart').getContext('2d');
        const labels = Object.keys(dist);
        const values = Object.values(dist);
        const colors = [COLORS.success, COLORS.info, COLORS.warning, COLORS.danger];

        if (charts.pie) charts.pie.destroy();
        charts.pie = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels,
                datasets: [{ data: values, backgroundColor: colors, borderWidth: 2, borderColor: isDark ? '#1e2535' : '#fff', hoverOffset: 6 }],
            },
            options: {
                responsive: true, maintainAspectRatio: false, cutout: '65%',
                plugins: { legend: { position: 'bottom', labels: { font: { size: 11 }, color: isDark ? '#e2e8f0' : '#475569', padding: 12 } } },
            },
        });
    }

    function renderBarChart(urls) {
        const ctx    = document.getElementById('taBarChart').getContext('2d');
        const labels = urls.map(u => u.url);
        const values = urls.map(u => u.hits);

        if (charts.bar) charts.bar.destroy();
        charts.bar = new Chart(ctx, {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                    label: 'Hits',
                    data: values,
                    backgroundColor: values.map((_, i) => hexAlpha(COLORS.info, .7 - i * .04)),
                    borderRadius: 6, borderSkipped: false,
                }],
            },
            options: {
                responsive: true, maintainAspectRatio: false, indexAxis: 'y',
                plugins: { legend: { display: false } },
                scales: {
                    x: { ticks: { color: COLORS.muted, font: { size: 10 } }, grid: { color: isDark ? 'rgba(255,255,255,.05)' : 'rgba(0,0,0,.04)' } },
                    y: { ticks: { color: COLORS.muted, font: { size: 10 } }, grid: { display: false } },
                },
            },
        });
    }

    function renderMethodChart(methods) {
        const ctx    = document.getElementById('taMethodChart').getContext('2d');
        const labels = Object.keys(methods);
        const values = Object.values(methods);
        const colors = { GET: COLORS.success, POST: COLORS.info, PUT: COLORS.warning, PATCH: COLORS.warning, DELETE: COLORS.danger };

        if (charts.method) charts.method.destroy();
        charts.method = new Chart(ctx, {
            type: 'pie',
            data: {
                labels,
                datasets: [{
                    data: values,
                    backgroundColor: labels.map(l => colors[l] ?? COLORS.muted),
                    borderWidth: 2, borderColor: isDark ? '#1e2535' : '#fff',
                }],
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom', labels: { font: { size: 11 }, color: isDark ? '#e2e8f0' : '#475569', padding: 10 } } },
            },
        });
    }

    function renderTopUrls(urls) {
        const tbody = document.getElementById('taTopUrls');
        if (!urls.length) { tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4" style="color:var(--ta-muted)">No data</td></tr>'; return; }
        const max = urls[0]?.hits ?? 1;
        tbody.innerHTML = urls.map((u, i) => `
            <tr>
                <td><span style="font-size:12px;color:var(--ta-muted)">${i + 1}</span></td>
                <td class="url-cell" title="${escHtml(u.url)}">${escHtml(u.url)}</td>
                <td><strong>${fmt(u.hits)}</strong></td>
                <td>${u.avg_time} ms</td>
                <td style="min-width:120px">
                    <div class="ta-progress-bar">
                        <div class="ta-progress-fill" style="width:${Math.round((u.hits / max) * 100)}%"></div>
                    </div>
                </td>
            </tr>`).join('');
    }

    function renderRecentLogs(logs) {
        const tbody = document.getElementById('taRecentLogs');
        if (!logs.length) { tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4" style="color:var(--ta-muted)">No data</td></tr>'; return; }
        tbody.innerHTML = logs.map(l => {
            const statusClass = l.status_code >= 500 ? 'danger' : l.status_code >= 400 ? 'warning' : l.status_code >= 300 ? 'info' : 'success';
            const path = (() => { try { return new URL(l.url).pathname; } catch { return l.url; } })();
            const short = path.length > 35 ? path.slice(0, 32) + '…' : path;
            const when  = new Date(l.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
            return `
            <tr>
                <td><span class="ta-method-chip method-${l.method}">${l.method}</span></td>
                <td class="url-cell" title="${escHtml(path)}">${escHtml(short)}</td>
                <td><span class="ta-badge ta-badge-${statusClass}">${l.status_code}</span></td>
                <td style="font-size:12px">${escHtml(l.ip_address)}</td>
                <td style="font-size:12px">${l.response_time} ms</td>
                <td style="font-size:11px;color:var(--ta-muted)">${when}</td>
            </tr>`;
        }).join('');
    }

    function renderTopIPs(ips) {
        const wrap = document.getElementById('taTopIPs');
        if (!ips.length) { wrap.innerHTML = '<p style="color:var(--ta-muted);font-size:13px">No data</p>'; return; }
        const max = ips[0]?.hits ?? 1;
        wrap.innerHTML = ips.map(ip => `
            <div>
                <div style="display:flex;justify-content:space-between;font-size:12px;margin-bottom:4px">
                    <span style="color:var(--ta-text)">${escHtml(ip.ip_address)}</span>
                    <strong style="color:var(--ta-text)">${fmt(ip.hits)}</strong>
                </div>
                <div class="ta-progress-bar">
                    <div class="ta-progress-fill" style="width:${Math.round((ip.hits / max) * 100)}%"></div>
                </div>
            </div>`).join('');
    }

    function updateChartColors() {
        ['line', 'pie', 'bar', 'method'].forEach(k => {
            if (charts[k]) {
                charts[k].options.plugins.legend.labels.color = isDark ? '#e2e8f0' : '#475569';
                charts[k].update();
            }
        });
    }

    function hexAlpha(hex, alpha) {
        const r = parseInt(hex.slice(1, 3), 16);
        const g = parseInt(hex.slice(3, 5), 16);
        const b = parseInt(hex.slice(5, 7), 16);
        return `rgba(${r},${g},${b},${alpha})`;
    }

    function escHtml(s) {
        return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    function startCountdown() {
        clearInterval(refreshTimer);
        countdown = 15;
        refreshTimer = setInterval(() => {
            countdown--;
            const el = document.getElementById('taCountdown');
            if (el) el.textContent = countdown;
            if (countdown <= 0) {
                loadData();
                countdown = 15;
            }
        }, 1000);
    }

    applyTheme();
    loadData();
    startCountdown();
})();
</script>
@endpush
