@extends('layouts.app')

@section('title', 'لوحة المراقبة - نظام ERP')
@section('page-title', 'لوحة المراقبة')
@section('page-subtitle')
    {{ now()->hour < 12 ? 'صباح الخير' : 'مساء الخير' }}، {{ auth()->user()->name }} <span class="wave" aria-hidden="true">👋</span>
    <span class="d-none d-sm-inline">— إليك ملخص أعمالك اليوم</span>
@endsection

@section('content')
<!-- بطاقات الإحصائيات -->
<div class="row g-4 mb-4">
    <div class="col-xl-3 col-md-6">
        <x-stat-card label="إجمالي الموظفين" :value="$totalEmployees" icon="fas fa-users" color="primary"
                     :hint="$newEmployees > 0 ? '+' . $newEmployees . ' انضموا هذا الشهر' : 'موظفون نشطون'" />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-stat-card label="إجمالي العملاء" :value="$totalCustomers" icon="fas fa-user-tie" color="success"
                     hint="عملاء نشطون" />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-stat-card label="إجمالي الإيرادات" :value="$totalRevenue" :decimals="2" suffix="ج.م" icon="fas fa-sack-dollar" color="info"
                     :trend="$collectionTrend" :hint="$collectionTrend !== null ? 'في التحصيل عن الشهر الماضي' : 'من الفواتير المدفوعة'" />
    </div>
    <div class="col-xl-3 col-md-6">
        <x-stat-card label="المبالغ المعلقة" :value="$outstandingAmount" :decimals="2" suffix="ج.م" icon="fas fa-hourglass-half" color="warning"
                     :hint="$overdueInvoices > 0 ? $overdueInvoices . ' فاتورة متأخرة' : 'لا توجد فواتير متأخرة'" />
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- المبيعات والتحصيل -->
    <div class="col-lg-8">
        @php
            $salesTotal = $salesChart['sales']->sum();
            $collectedTotal = $salesChart['collected']->sum();
            $collectionRate = $salesTotal > 0 ? min(100, (int) round($collectedTotal / $salesTotal * 100)) : 0;
        @endphp
        <div class="card h-100">
            <div class="card-header d-flex flex-wrap gap-2 align-items-center justify-content-between">
                <h6 class="m-0"><i class="fas fa-chart-column me-2"></i> المبيعات والتحصيل</h6>
                <div class="chart-legend" id="salesLegend">
                    <button type="button" class="legend-chip" data-dataset="0" aria-pressed="true"><span class="dot" style="background:#6366f1"></span>المبيعات</button>
                    <button type="button" class="legend-chip" data-dataset="1" aria-pressed="true"><span class="dot" style="background:#10b981"></span>المحصّل</button>
                </div>
            </div>
            <div class="card-body">
                <div class="chart-summary">
                    <div>
                        <span class="chart-summary-label">مبيعات آخر 6 شهور</span>
                        <strong><span data-count="{{ $salesTotal }}" data-decimals="2">{{ number_format($salesTotal, 2) }}</span> <small>ج.م</small></strong>
                    </div>
                    <div>
                        <span class="chart-summary-label">المحصّل</span>
                        <strong><span data-count="{{ $collectedTotal }}" data-decimals="2">{{ number_format($collectedTotal, 2) }}</span> <small>ج.م</small></strong>
                    </div>
                    <div class="flex-grow-1">
                        <span class="chart-summary-label">نسبة التحصيل</span>
                        <div class="d-flex align-items-center gap-2">
                            <strong><span data-count="{{ $collectionRate }}">{{ $collectionRate }}</span>%</strong>
                            <div class="progress flex-grow-1" style="height: 6px; max-width: 160px" role="progressbar" aria-valuenow="{{ $collectionRate }}" aria-valuemin="0" aria-valuemax="100">
                                <div class="progress-bar progress-bar-animated-in" style="width: {{ $collectionRate }}%; background: linear-gradient(to left, #10b981, #34d399)"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="chart-box"><canvas id="salesChart" aria-label="المبيعات والتحصيل لآخر 6 شهور" role="img"></canvas></div>
            </div>
        </div>
    </div>

    <!-- حالة الفواتير -->
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h6 class="m-0"><i class="fas fa-chart-pie me-2"></i> حالة الفواتير</h6>
                <small class="text-muted">{{ $totalInvoices }} فاتورة</small>
            </div>
            <div class="card-body">
                @if($totalInvoices > 0)
                    <div class="chart-box-sm mb-4">
                        <canvas id="invoiceChart" aria-label="توزيع الفواتير حسب الحالة" role="img"></canvas>
                        <div class="donut-center">
                            <strong data-count="{{ $totalInvoices }}">{{ $totalInvoices }}</strong>
                            <span>فاتورة</span>
                        </div>
                    </div>
                    <ul class="legend-list legend-interactive" id="invoiceLegend">
                        @foreach($invoiceStatus as $i => $item)
                            @php($percent = $totalInvoices > 0 ? round($item['value'] / $totalInvoices * 100) : 0)
                            <li data-index="{{ $i }}" class="{{ $item['value'] == 0 ? 'is-empty' : '' }}">
                                <span class="dot" style="background: {{ $item['color'] }}"></span>
                                <span class="flex-grow-1">
                                    {{ $item['label'] }}
                                    <span class="legend-bar"><span style="width: {{ $percent }}%; background: {{ $item['color'] }}"></span></span>
                                </span>
                                <span class="value">{{ $item['value'] }} <small class="text-muted fw-normal">({{ $percent }}%)</small></span>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="empty-state">
                        <div class="empty-state-icon"><i class="fas fa-file-invoice"></i></div>
                        <p class="text-muted mb-0">لا توجد فواتير حتى الآن</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- آخر الفواتير -->
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h6 class="m-0"><i class="fas fa-file-invoice-dollar me-2"></i> آخر الفواتير</h6>
                @can('invoices.view')
                    <a href="{{ route('invoices.index') }}" class="btn btn-sm btn-soft">عرض الكل <i class="fas fa-arrow-left ms-1"></i></a>
                @endcan
            </div>
            <div class="card-body p-0">
                @if($recentInvoices->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>رقم الفاتورة</th>
                                    <th>العميل</th>
                                    <th>المبلغ</th>
                                    <th>الحالة</th>
                                    <th>التاريخ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentInvoices as $invoice)
                                <tr>
                                    <td class="fw-bold">{{ $invoice->invoice_number }}</td>
                                    <td>{{ $invoice->customer->name }}</td>
                                    <td>{{ number_format($invoice->total_amount, 2) }} ج.م</td>
                                    <td><x-status-badge type="invoice" :status="$invoice->status" /></td>
                                    <td class="text-muted">{{ $invoice->created_at->format('Y-m-d') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="empty-state">
                        <div class="empty-state-icon"><i class="fas fa-file-invoice"></i></div>
                        <p class="text-muted mb-0">لا توجد فواتير حتى الآن</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4 d-flex flex-column gap-4">
        <!-- الحضور اليوم -->
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h6 class="m-0"><i class="fas fa-clock me-2"></i> الحضور اليوم</h6>
                @can('attendance.view')
                    <a href="{{ route('attendance.index') }}" class="small text-decoration-none">التفاصيل</a>
                @endcan
            </div>
            <div class="card-body">
                <div class="d-flex align-items-end justify-content-between mb-3">
                    <div>
                        <div class="big-number"><span data-count="{{ $attendanceRate }}">{{ $attendanceRate }}</span>%</div>
                        <small class="text-muted">نسبة الحضور من {{ $totalEmployees }} موظف</small>
                    </div>
                    <span class="stat-icon" style="--stat-color:#10b981;--stat-soft:rgba(16,185,129,.12)"><i class="fas fa-user-check"></i></span>
                </div>
                @php($attendanceSum = array_sum(array_column($attendanceToday, 'value')))
                @if($attendanceSum > 0)
                    <div class="segmented-bar mb-3">
                        @foreach($attendanceToday as $item)
                            @if($item['value'] > 0)
                                <span style="width: {{ round($item['value'] / $attendanceSum * 100, 2) }}%; background: {{ $item['color'] }}" title="{{ $item['label'] }}: {{ $item['value'] }}"></span>
                            @endif
                        @endforeach
                    </div>
                @endif
                <ul class="legend-list">
                    @foreach($attendanceToday as $item)
                        <li>
                            <span class="dot" style="background: {{ $item['color'] }}"></span>
                            {{ $item['label'] }}
                            <span class="value">{{ $item['value'] }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

        <!-- مخزون منخفض -->
        @can('products.view')
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h6 class="m-0"><i class="fas fa-boxes-stacked me-2"></i> مخزون منخفض</h6>
                <a href="{{ route('products.index', ['low_stock' => 1]) }}" class="small text-decoration-none">عرض الكل</a>
            </div>
            <div class="card-body">
                @if($lowStockProducts->count() > 0)
                    <ul class="mini-list">
                        @foreach($lowStockProducts as $product)
                            <li>
                                <span class="mini-icon {{ $product->stock <= 0 ? 'bg-danger-subtle text-danger-emphasis' : 'bg-warning-subtle text-warning-emphasis' }}">
                                    <i class="fas fa-box"></i>
                                </span>
                                <div class="flex-grow-1 min-w-0">
                                    <a href="{{ route('products.show', $product) }}" class="fw-semibold text-decoration-none text-body d-block text-truncate">{{ $product->name }}</a>
                                    <small class="text-muted">{{ $product->sku }}</small>
                                </div>
                                <span class="badge {{ $product->stock <= 0 ? 'bg-danger' : 'bg-warning' }}">
                                    {{ $product->stock <= 0 ? 'نفد' : (int) $product->stock . ' متبقي' }}
                                </span>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="text-center text-muted py-2">
                        <i class="fas fa-circle-check text-success me-1"></i> كل المنتجات مخزونها كافٍ
                    </div>
                @endif
            </div>
        </div>
        @endcan
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function () {
    if (!window.Chart) return;

    const salesData = @json($salesChart);
    const invoiceData = @json($invoiceStatus);
    const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const money = value => Number(value).toLocaleString('en-US', { maximumFractionDigits: 0 }) + ' ج.م';
    const css = name => getComputedStyle(document.documentElement).getPropertyValue(name).trim();

    Chart.defaults.font.family = "'Cairo', system-ui, sans-serif";
    Chart.defaults.font.size = 12;
    Object.assign(Chart.defaults.plugins.tooltip, {
        rtl: true,
        textDirection: 'rtl',
        padding: 12,
        cornerRadius: 12,
        boxPadding: 6,
        usePointStyle: true,
        titleFont: { weight: '700', size: 13 },
        caretSize: 6,
    });

    // تدرّج رأسي يُحسب من مساحة الرسم الفعلية فيبقى صحيحًا عند تغيير الحجم
    const verticalGradient = (top, bottom) => context => {
        const { ctx, chartArea } = context.chart;
        if (!chartArea) return top;
        const gradient = ctx.createLinearGradient(0, chartArea.bottom, 0, chartArea.top);
        gradient.addColorStop(0, bottom);
        gradient.addColorStop(1, top);
        return gradient;
    };

    // الأعمدة تطلع بالتتابع مرة واحدة عند التحميل فقط
    const staggered = () => {
        let done = false;
        return {
            duration: reduced ? 0 : 750,
            easing: 'easeOutQuart',
            onComplete: () => { done = true; },
            delay: ctx => (!done && ctx.type === 'data' && ctx.mode === 'default') ? ctx.dataIndex * 90 + ctx.datasetIndex * 70 : 0,
        };
    };

    const charts = [];

    const salesCanvas = document.getElementById('salesChart');
    if (salesCanvas) {
        const barStyle = { borderRadius: 7, borderSkipped: 'start', maxBarThickness: 26, categoryPercentage: .6, barPercentage: .85, pointStyle: 'circle' };
        const salesChart = new Chart(salesCanvas, {
            type: 'bar',
            data: {
                labels: salesData.labels,
                datasets: [
                    { ...barStyle, label: 'المبيعات', data: salesData.sales, backgroundColor: verticalGradient('#818cf8', 'rgba(99, 102, 241, .45)'), hoverBackgroundColor: '#6366f1' },
                    { ...barStyle, label: 'المحصّل', data: salesData.collected, backgroundColor: verticalGradient('#34d399', 'rgba(16, 185, 129, .4)'), hoverBackgroundColor: '#10b981' },
                ],
            },
            options: {
                maintainAspectRatio: false,
                animation: staggered(),
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: item => ` ${item.dataset.label}: ${money(item.parsed.y)}`,
                            footer: items => {
                                const sales = salesData.sales[items[0].dataIndex];
                                const collected = salesData.collected[items[0].dataIndex];
                                return sales > 0 ? `نسبة التحصيل: ${Math.round(collected / sales * 100)}%` : '';
                            },
                        },
                    },
                },
                scales: {
                    x: { reverse: true, grid: { display: false }, border: { display: false }, ticks: { font: { weight: '600' } } },
                    y: {
                        position: 'right', beginAtZero: true, grace: '8%',
                        border: { display: false, dash: [4, 4] },
                        grid: { drawTicks: false },
                        ticks: { padding: 10, maxTicksLimit: 5, callback: value => Number(value).toLocaleString('en-US', { notation: 'compact' }) },
                    },
                },
            },
        });
        charts.push(salesChart);

        // مفاتيح الرسم في رأس الكارت: الضغط يخفي/يظهر السلسلة
        document.querySelectorAll('#salesLegend [data-dataset]').forEach(chip => {
            chip.addEventListener('click', () => {
                const index = Number(chip.dataset.dataset);
                const visible = !salesChart.isDatasetVisible(index);
                salesChart.setDatasetVisibility(index, visible);
                chip.classList.toggle('is-off', !visible);
                chip.setAttribute('aria-pressed', String(visible));
                salesChart.update();
            });
        });
    }

    const invoiceCanvas = document.getElementById('invoiceChart');
    if (invoiceCanvas) {
        const total = invoiceData.reduce((sum, item) => sum + item.value, 0);
        const invoiceChart = new Chart(invoiceCanvas, {
            type: 'doughnut',
            data: {
                labels: invoiceData.map(i => i.label),
                datasets: [{
                    data: invoiceData.map(i => i.value),
                    backgroundColor: invoiceData.map(i => i.color),
                    borderWidth: 3, borderRadius: 6, spacing: 2, hoverOffset: 8,
                }],
            },
            options: {
                maintainAspectRatio: false,
                cutout: '76%',
                layout: { padding: 8 },
                animation: { animateRotate: true, animateScale: true, duration: reduced ? 0 : 1100, easing: 'easeOutQuart' },
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: item => ` ${item.label}: ${item.parsed} (${total ? Math.round(item.parsed / total * 100) : 0}%)` } },
                },
            },
        });
        charts.push(invoiceChart);

        // تمرير الماوس على عنصر في القائمة يبرز الجزء المقابل، والضغط يخفيه
        document.querySelectorAll('#invoiceLegend [data-index]').forEach(item => {
            const index = Number(item.dataset.index);
            const highlight = active => {
                const elements = active && invoiceChart.getDataVisibility(index) && invoiceData[index].value > 0 ? [{ datasetIndex: 0, index }] : [];
                invoiceChart.setActiveElements(elements);
                invoiceChart.tooltip.setActiveElements(elements, { x: 0, y: 0 });
                invoiceChart.update();
            };
            item.addEventListener('mouseenter', () => highlight(true));
            item.addEventListener('mouseleave', () => highlight(false));
            item.addEventListener('click', () => {
                invoiceChart.toggleDataVisibility(index);
                item.classList.toggle('is-off', !invoiceChart.getDataVisibility(index));
                invoiceChart.update();
            });
        });
    }

    // ألوان المحاور والحدود والتلميحات تتبع الوضع الفاتح/الداكن
    const applyTheme = () => {
        const dark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
        const muted = css('--muted');
        const border = css('--border');
        const surface = css('--surface');
        charts.forEach(chart => {
            Object.assign(chart.options.plugins.tooltip, {
                backgroundColor: dark ? '#1e293b' : '#1e1b4b',
                borderColor: dark ? border : 'transparent',
                borderWidth: dark ? 1 : 0,
            });
            Object.values(chart.options.scales || {}).forEach(scale => {
                scale.ticks.color = muted;
                scale.grid.color = border;
            });
            if (chart.config.type === 'doughnut') chart.data.datasets[0].borderColor = surface;
            chart.update('none');
        });
    };

    applyTheme();
    window.addEventListener('themechange', applyTheme);
})();
</script>
@endpush
