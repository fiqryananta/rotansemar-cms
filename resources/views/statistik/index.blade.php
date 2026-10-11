@php
    $title = 'Statistik';
@endphp
@extends('layouts.admin-blade')

@section('content')
    <div class="rounded-2xl border border-gray-200/70 bg-gray-50 shadow-sm">
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="mb-8 flex items-start gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-teal-50 text-teal-600">
                    <i class="ti ti-chart-pie" style="font-size:1.5rem;" aria-hidden="true"></i>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Statistik</h1>
                </div>
            </div>

            <div class="space-y-4">
                <div class="rounded-lg border border-gray-200 bg-white shadow-sm">
                    <div class="flex flex-col gap-3 border-b border-gray-200 p-4 sm:flex-row sm:items-center sm:justify-between">
                        <h2 class="text-lg font-semibold text-gray-900">Jumlah Kebutuhan per OPD</h2>

                        <div class="flex items-center gap-2">
                            <label for="status-filter" class="text-sm text-gray-600">Jenis Proses</label>
                            <select id="status-filter" class="h-10 rounded-md border border-gray-300 px-3 text-sm text-gray-700 focus:border-teal-500 focus:outline-none">
                                <option value="">Semua Status</option>
                                @foreach ($statusLabels as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="p-4">
                        {{-- Grafik selalu digambar, termasuk saat datanya nol: sumbu dan
                             labelnya tetap tampil dengan nilai 0, bukan paragraf kosong. --}}
                        <div class="h-80" role="img" aria-label="Grafik batang jumlah kebutuhan per OPD">
                            <canvas id="opd-chart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="grid gap-4 lg:grid-cols-2">
                    <div class="rounded-lg border border-gray-200 bg-white shadow-sm">
                        <div class="border-b border-gray-200 p-4">
                            <h2 class="text-lg font-semibold text-gray-900">Tren Bulanan per Jenis Proses</h2>
                        </div>

                        <div class="p-4">
                            <div class="h-80" role="img" aria-label="Grafik garis bulanan menurut jenis proses">
                                <canvas id="status-monthly-chart"></canvas>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-lg border border-gray-200 bg-white shadow-sm">
                        <div class="border-b border-gray-200 p-4">
                            <h2 class="text-lg font-semibold text-gray-900">Tren Bulanan per Jenis Kebutuhan</h2>
                        </div>

                        <div class="p-4">
                            <div class="h-80" role="img" aria-label="Grafik garis bulanan menurut jenis kebutuhan">
                                <canvas id="kebutuhan-monthly-chart"></canvas>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-lg border border-gray-200 bg-white shadow-sm">
                        <div class="border-b border-gray-200 p-4">
                            <h2 class="text-lg font-semibold text-gray-900">Total per Jenis Kebutuhan</h2>
                        </div>

                        <div class="p-4">
                            <div class="h-80" role="img" aria-label="Grafik pie total kebutuhan per jenis kebutuhan">
                                <canvas id="kebutuhan-pie-chart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
(function () {
    if (typeof Chart === 'undefined') return;

    var OPD_CHART = @json($opdChart);

    /*
     * Palet seri grafik, sengaja hex dan bukan token tema (--chart-1 s/d --chart-5)
     * meski hue-nya sengaja disamakan dengan token tersebut (cyan, emerald, amber,
     * lime, merah). Alasannya token tema bernilai oklch(), sedangkan parser warna
     * Chart.js hanya paham hex/rgb/hsl sehingga seri jadi tidak terwarnai.
     */
    var COLORS = ['#0891b2', '#10b981', '#f59e0b', '#84cc16', '#ef4444', '#6366f1', '#8b5cf6', '#ec4899', '#0ea5e9', '#14b8a6'];

    function colorAt(index) {
        return COLORS[index % COLORS.length];
    }

    var COUNT_AXIS = {
        beginAtZero: true,
        ticks: { precision: 0 },
    };

    /*
     * Ketiga helper di bawah sengaja tidak lagi menolak payload kosong: grafiknya
     * tetap digambar walau seluruh nilainya 0, karena server sudah mengirim label
     * dan seri bernilai nol. Yang dijaga hanya keberadaan canvas-nya.
     */
    function lineChart(canvasId, payload) {
        var canvas = document.getElementById(canvasId);
        if (!canvas) return;

        new Chart(canvas, {
            type: 'line',
            data: {
                labels: payload.labels,
                datasets: payload.datasets.map(function (dataset, index) {
                    return {
                        label: dataset.label,
                        data: dataset.data,
                        borderColor: colorAt(index),
                        backgroundColor: colorAt(index),
                        borderWidth: 2,
                        pointRadius: 3,
                        tension: 0.3,
                    };
                }),
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                scales: { y: COUNT_AXIS },
            },
        });
    }

    function pieChart(canvasId, payload) {
        var canvas = document.getElementById(canvasId);
        if (!canvas) return;

        new Chart(canvas, {
            type: 'pie',
            data: {
                labels: payload.labels,
                datasets: [{
                    data: payload.data,
                    backgroundColor: payload.data.map(function (value, index) {
                        return colorAt(index);
                    }),
                    borderColor: '#ffffff',
                    borderWidth: 2,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } },
            },
        });
    }

    // Seluruh seri status sudah dikirim server, jadi dropdown "jenis proses"
    // cukup menukar dataset tanpa reload halaman maupun endpoint tambahan.
    var opdCanvas = document.getElementById('opd-chart');
    var opdChart = null;

    // Sengaja tidak lagi menuntut OPD_CHART.labels terisi: kalau memang belum ada
    // data, batangnya digambar rata 0 supaya kerangka grafiknya tetap kelihatan.
    if (opdCanvas) {
        opdChart = new Chart(opdCanvas, {
            type: 'bar',
            data: {
                labels: OPD_CHART.labels,
                datasets: [{
                    label: 'Semua Status',
                    data: OPD_CHART.series.total,
                    backgroundColor: colorAt(0),
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: COUNT_AXIS },
            },
        });
    }

    var statusFilter = document.getElementById('status-filter');

    if (opdChart && statusFilter) {
        // Select2 di layout tetap memicu event 'change' native, jadi listener
        // vanilla ini jalan baik sebelum maupun sesudah Select2 aktif.
        statusFilter.addEventListener('change', function () {
            var key = this.value === '' ? 'total' : this.value;
            var option = this.options[this.selectedIndex];

            opdChart.data.datasets[0].data = OPD_CHART.series[key] || [];
            opdChart.data.datasets[0].label = option ? option.textContent : key;
            opdChart.update();
        });
    }

    lineChart('status-monthly-chart', @json($statusMonthly));
    lineChart('kebutuhan-monthly-chart', @json($kebutuhanMonthly));
    pieChart('kebutuhan-pie-chart', @json($kebutuhanPie));
})();
</script>
@endpush
