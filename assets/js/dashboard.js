// ================= Dashboard Charts (sample data) =================

document.addEventListener('DOMContentLoaded', () => {

    const indigo = '#4F46E5';
    const indigoSoft = '#A5B4FC';

    // ---- Student Growth chart ----
    const yearlyData = [420, 610, 480, 720, 860, 980, 690];
    const monthlyData = [140, 165, 152, 190, 175, 210, 198, 225, 240, 260, 235, 250];
    const yearlyLabels = ['2019', '2020', '2021', '2022', '2023', '2024', '2025'];
    const monthlyLabels = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];

    const growthCtx = document.getElementById('growthChart');
    let growthChart;

    function renderGrowthChart(labels, data) {
        const highlightIndex = data.indexOf(Math.max(...data));
        const colors = data.map((_, i) => i === highlightIndex ? indigo : indigoSoft);

        if (growthChart) growthChart.destroy();
        growthChart = new Chart(growthCtx, {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                    data,
                    backgroundColor: colors,
                    borderRadius: 8,
                    maxBarThickness: 46
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { grid: { color: '#EEF0F7' }, ticks: { color: '#9CA3AF' } },
                    x: { grid: { display: false }, ticks: { color: '#9CA3AF' } }
                }
            }
        });
    }

    if (growthCtx) {
        renderGrowthChart(yearlyLabels, yearlyData);

        document.querySelectorAll('.toggle-group [data-range]').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.toggle-group [data-range]').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                if (btn.dataset.range === 'yearly') {
                    renderGrowthChart(yearlyLabels, yearlyData);
                } else {
                    renderGrowthChart(monthlyLabels, monthlyData);
                }
            });
        });
    }

    // ---- Attendance Analytics chart ----
    const attendanceCtx = document.getElementById('attendanceChart');
    if (attendanceCtx) {
        const deptLabels = ['Engineering', 'Business', 'Arts', 'Medicine', 'Law', 'IT', 'Design', 'Science', 'Arts & Science', 'Nursing'];
        const deptData = [88, 97, 80, 90, 92, 70, 74, 91, 82, 89];
        const deptColors = deptData.map(v => v >= 95 || v === Math.max(...deptData) ? indigo : (v === Math.min(...deptData) ? '#312E81' : indigoSoft));

        new Chart(attendanceCtx, {
            type: 'bar',
            data: {
                labels: deptLabels,
                datasets: [{
                    data: deptData,
                    backgroundColor: deptData.map((v, i) => i === 1 ? indigo : (i === deptLabels.length - 4 ? '#312E81' : indigoSoft)),
                    borderRadius: 20,
                    maxBarThickness: 28
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { display: false },
                    x: { grid: { display: false }, ticks: { color: '#9CA3AF', font: { size: 11 } } }
                }
            }
        });
    }
});
