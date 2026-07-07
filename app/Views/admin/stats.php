<?php require BASE_PATH . '/app/Views/partials/header.php'; ?>

<div class="card">
    <div class="card-header">
        <h2><i class="fas fa-chart-line"></i> Statistiques de visites</h2>
        <select id="statsPeriod" class="form-control" style="width:auto;" onchange="loadStats()">
            <option value="7">7 derniers jours</option>
            <option value="30" selected>30 derniers jours</option>
            <option value="90">90 derniers jours</option>
        </select>
    </div>

    <div class="grid grid-4" id="statsCards" style="margin-bottom:var(--spacing-lg);">
        <div class="stat-card">
            <div class="stat-value" id="totalVisits">—</div>
            <div class="stat-label">Visites totales</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" id="uniqueVisitors">—</div>
            <div class="stat-label">Visiteurs uniques</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" id="mobilePercent">—</div>
            <div class="stat-label">Mobile</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" id="desktopPercent">—</div>
            <div class="stat-label">Desktop</div>
        </div>
    </div>

    <div style="position:relative;height:300px;margin-bottom:var(--spacing-lg);">
        <canvas id="visitsChart"></canvas>
    </div>

    <div class="grid grid-2">
        <div>
            <h3 style="font-size:0.9rem;font-weight:600;margin-bottom:12px;"><i class="fas fa-mobile-alt" style="color:var(--color-primary);"></i> Appareils</h3>
            <canvas id="devicesChart" style="max-height:200px;"></canvas>
        </div>
        <div>
            <h3 style="font-size:0.9rem;font-weight:600;margin-bottom:12px;"><i class="fas fa-globe" style="color:var(--color-primary);"></i> Navigateurs</h3>
            <canvas id="browsersChart" style="max-height:200px;"></canvas>
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
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
            });

            // Devices chart
            if (devicesChart) devicesChart.destroy();
            devicesChart = new Chart(document.getElementById('devicesChart'), {
                type: 'doughnut',
                data: {
                    labels: Object.keys(devices),
                    datasets: [{ data: Object.values(devices), backgroundColor: ['#b45309', '#2563eb', '#16a34a', '#dc2626'] }]
                },
                options: { responsive: true, maintainAspectRatio: false }
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
                options: { responsive: true, maintainAspectRatio: false }
            });
        });
}

loadStats();
</script>

<?php require BASE_PATH . '/app/Views/partials/footer.php'; ?>
