if (typeof CHAMELEON === "undefined" || !CHAMELEON) {
    var CHAMELEON = {};
}
CHAMELEON.CORE = CHAMELEON.CORE || {};

CHAMELEON.CORE.Charts = {
    generateChart: function (chartId, labels, datasets, options = {}) {
        const config = {
            type: 'bar',
            data: {
                labels: labels,
                datasets: datasets,
            },
            options: {
                plugins: {
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                    },
                    legend: options.legend || {},
                },
                responsive: true,
                scales: {
                    x: {
                        stacked: true,
                    },
                    y: {
                        stacked: true,
                    },
                },
            },
            plugins: [
                {
                    id: 'stackedSum',
                    beforeDraw: (chart) => {
                        const { ctx, chartArea, scales } = chart;
                        const datasets = chart.data.datasets;
                        const labels = chart.data.labels;

                        labels.forEach((label, index) => {
                            let sum = 0;

                            datasets.forEach((dataset) => {
                                sum += dataset.data[index];
                            });

                            const roundedSum = sum.toFixed(2);

                            // Position and Rotation
                            ctx.save();
                            ctx.font = 'bold 12px Arial';
                            ctx.fillStyle = '#989FA5';
                            ctx.translate(
                                scales.x.getPixelForValue(label),
                                chartArea.top + 45
                            );
                            ctx.rotate(-Math.PI / 4); // rotate 45° to the left
                            ctx.fillText(roundedSum, 0, 0);
                            ctx.restore();
                        });
                    },
                },
            ],
        };

        const chart = new Chart(document.getElementById(chartId), config);

        CHAMELEON.CORE.Charts.increaseYAxisHeight(chart);
    },

    increaseYAxisHeight: function (chart) {
        const maxHeight = chart.scales.y.end;
        const increasedMaxHeight = maxHeight + (maxHeight / 100 * 10);
        chart.config.options.scales.y.suggestedMax = increasedMaxHeight;
        chart.update();
    },
};
