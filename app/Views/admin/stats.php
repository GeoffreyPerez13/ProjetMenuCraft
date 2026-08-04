<?php require BASE_PATH . '/app/Views/partials/header.php'; ?>
<?php if (!isset($slug)) $slug = ''; ?>

<style>
/* ─── Stats page ─── */
.stats-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
}
.stats-header h2 { margin: 0; }
.stats-header select { width: auto; }

.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: var(--spacing-md);
    margin-bottom: var(--spacing-lg);
}
.stats-kpi {
    background: var(--color-bg);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-md);
    padding: var(--spacing-lg);
    text-align: center;
    transition: box-shadow 0.2s, transform 0.2s;
}
.stats-kpi:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.06);
    transform: translateY(-2px);
}
.stats-kpi-value {
    font-size: 2rem;
    font-weight: 700;
    color: var(--color-primary);
    line-height: 1.2;
}
.stats-kpi-label {
    font-size: 0.82rem;
    color: var(--color-text-muted);
    margin-top: 4px;
}
.stats-kpi:nth-child(1) .stats-kpi-value { color: #b45309; }
.stats-kpi:nth-child(2) .stats-kpi-value { color: #2563eb; }
.stats-kpi:nth-child(3) .stats-kpi-value { color: #16a34a; }
.stats-kpi:nth-child(4) .stats-kpi-value { color: #7c3aed; }

.stats-chart-main {
    position: relative;
    height: 300px;
    margin-bottom: var(--spacing-lg);
}

.stats-charts-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--spacing-lg);
}
.stats-chart-box {
    min-width: 0;
}
.stats-chart-box h3 {
    font-size: 0.9rem;
    font-weight: 600;
    margin-bottom: 12px;
}
.stats-chart-box h3 i {
    color: var(--color-primary);
    margin-right: 6px;
}
.stats-chart-box canvas {
    max-height: 200px;
    width: 100% !important;
}

/* ─── Responsive ─── */
@media (max-width: 992px) {
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    .stats-kpi { padding: var(--spacing-md); }
    .stats-kpi-value { font-size: 1.6rem; }
    .stats-chart-main { height: 250px; }
}

@media (max-width: 768px) {
    .stats-header {
        flex-direction: column;
        align-items: stretch;
    }
    .stats-header select {
        width: 100%;
    }
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 8px;
    }
    .stats-kpi {
        padding: 12px 10px;
    }
    .stats-kpi-value { font-size: 1.4rem; }
    .stats-kpi-label { font-size: 0.75rem; }

    .stats-chart-main { height: 220px; }

    .stats-charts-row {
        grid-template-columns: 1fr;
        gap: var(--spacing-md);
    }
    .stats-chart-box canvas {
        max-height: 180px;
    }
}

@media (max-width: 480px) {
    .stats-grid {
        grid-template-columns: 1fr 1fr;
        gap: 6px;
    }
    .stats-kpi {
        padding: 10px 8px;
    }
    .stats-kpi-value { font-size: 1.2rem; }
    .stats-kpi-label { font-size: 0.7rem; }

    .stats-chart-main { height: 180px; }

    .stats-chart-box canvas {
        max-height: 160px;
    }
}
</style>

<div class="card">
    <div class="card-header stats-header">
        <h2><i class="fas fa-chart-line"></i> Statistiques de visites</h2>
        <select id="statsPeriod" class="form-control" onchange="loadStats()">
            <option value="7">7 derniers jours</option>
            <option value="30" selected>30 derniers jours</option>
            <option value="90">90 derniers jours</option>
        </select>
    </div>

    <div class="stats-grid" id="statsCards">
        <div class="stats-kpi">
            <div class="stats-kpi-value" id="totalVisits">—</div>
            <div class="stats-kpi-label"><i class="fas fa-eye"></i> Visites totales</div>
        </div>
        <div class="stats-kpi">
            <div class="stats-kpi-value" id="uniqueVisitors">—</div>
            <div class="stats-kpi-label"><i class="fas fa-user"></i> Visiteurs uniques</div>
        </div>
        <div class="stats-kpi">
            <div class="stats-kpi-value" id="mobilePercent">—</div>
            <div class="stats-kpi-label"><i class="fas fa-mobile-alt"></i> Mobile</div>
        </div>
        <div class="stats-kpi">
            <div class="stats-kpi-value" id="desktopPercent">—</div>
            <div class="stats-kpi-label"><i class="fas fa-desktop"></i> Desktop</div>
        </div>
    </div>

    <div class="stats-chart-main">
        <canvas id="visitsChart"></canvas>
    </div>

    <div class="stats-charts-row">
        <div class="stats-chart-box">
            <h3><i class="fas fa-mobile-alt"></i> Appareils</h3>
            <canvas id="devicesChart"></canvas>
        </div>
        <div class="stats-chart-box">
            <h3><i class="fas fa-globe"></i> Navigateurs</h3>
            <canvas id="browsersChart"></canvas>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
let visitsChart, devicesChart, browsersChart;

function loadStats() {
    const days = document.getElementById('statsPeriod').value;
    fetch('<?= APP_URL ?>?page=stats-data&days=' + days)
        .then(r => r.json())
        .then(data => {
            if (data.error) return;

            document.getElementById('totalVisits').textContent = data.total_visits || 0;
            document.getElementById('uniqueVisitors').textContent = data.unique_visitors || 0;

            const devices = data.devices || {};
            const total = Object.values(devices).reduce((a, b) => a + b, 0) || 1;
            document.getElementById('mobilePercent').textContent = Math.round((devices.mobile || 0) / total * 100) + '%';
            document.getElementById('desktopPercent').textContent = Math.round((devices.desktop || 0) / total * 100) + '%';

            // Visits chart
            const daily = data.daily || [];
            if (visitsChart) visitsChart.destroy();
            visitsChart = new Chart(document.getElementById('visitsChart'), {
                type: 'line',
                data: {
                    labels: daily.map(d => d.date),
                    datasets: [{
                        label: 'Visites',
                        data: daily.map(d => d.count),
                        borderColor: '#b45309',
                        backgroundColor: 'rgba(180,83,9,0.1)',
                        fill: true,
                        tension: 0.3,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { ticks: { maxRotation: 45, font: { size: 11 } } },
                        y: { beginAtZero: true, ticks: { font: { size: 11 } } }
                    }
                }
            });

            // Devices chart
            if (devicesChart) devicesChart.destroy();
            devicesChart = new Chart(document.getElementById('devicesChart'), {
                type: 'doughnut',
                data: {
                    labels: Object.keys(devices),
                    datasets: [{ data: Object.values(devices), backgroundColor: ['#b45309', '#2563eb', '#16a34a', '#dc2626'] }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } } }
                }
            });

            // Browsers chart
            const browsers = data.browsers || {};
            if (browsersChart) browsersChart.destroy();
            browsersChart = new Chart(document.getElementById('browsersChart'), {
                type: 'doughnut',
                data: {
                    labels: Object.keys(browsers),
                    datasets: [{ data: Object.values(browsers), backgroundColor: ['#d97706', '#3b82f6', '#22c55e', '#ef4444', '#8b5cf6'] }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } } }
                }
            });
        });
}

loadStats();
</script>

<?php require BASE_PATH . '/app/Views/partials/footer.php'; ?>
