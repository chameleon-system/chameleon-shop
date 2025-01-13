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
                    return response.json();
                })
                .then(data => {
                    const parsedData = JSON.parse(data);
                    console.log(parsedData);
                    const chart = Chart.getChart(`chart${sanitizedServiceAlias}`);

                    chart.data.datasets = parsedData['datasets'];
                    chart.update();

                    const footerElement = document.querySelector(`#widget-${serviceAlias} .card-footer .widget-timestamp`);
                    if (footerElement) {
                        const now = new Date();
                        const formattedTime = `${now.toLocaleDateString()} ${now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}`;
                        footerElement.textContent = `${parsedData['dateTime']}`;
                    }
                })
                .catch(error => {
                    console.error("Error loading the chat data:", error);
                });
        });
    });
});
