document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll("[data-reload-chart]").forEach((button) => {
        button.addEventListener("click", function (event) {
            event.preventDefault();

            const serviceAlias = this.getAttribute("data-service-alias");
            const sanitizedServiceAlias = serviceAlias.replace(/[ _-]/g, "");

            const reloadUrl = `/cms/api/dashboard/widget/${serviceAlias}/getStatsDataAsJson`;

            fetch(reloadUrl, {
                method: "GET",
                headers: {
                    "Content-Type": "application/json"
                }
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP-Error! Status: ${response.status}`);
                    }
                    return response.json(); // Direkt JSON parsen lassen
                })
                .then(parsedData => {
                    const chart = Chart.getChart(`chart${sanitizedServiceAlias}`);

                    // Update the labels and datasets
                    chart.data.labels = parsedData['labels'];
                    chart.data.datasets = parsedData['datasets'];

                    // Update the chart
                    chart.update();

                    // Update the timestamp in the footer
                    const footerElement = document.querySelector(`#widget-${serviceAlias} .card-footer .widget-timestamp`);
                    if (footerElement) {
                        footerElement.textContent = parsedData['dateTime'];
                    }
                })
                .catch(error => {
                    console.error("Error loading the widget data:", error);
                });
        });
    });
});