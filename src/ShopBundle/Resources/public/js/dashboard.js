document.addEventListener("DOMContentLoaded", function () {
    // Attach event listener to the button
    const button = document.querySelector("#lastOrdersDashboardWidgetReload");

    if (button) {
        button.addEventListener("click", function (event) {
            event.preventDefault();

            // URL and target information
            const serviceAlias = this.getAttribute("data-service-alias");
            const reloadUrl = `/cms/api/dashboard/widget/${serviceAlias}/getWidgetHtmlAsJson`;

            // Fetch data
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
                    // Parse the JSON string if needed
                    const parsedData = typeof data === "string" ? JSON.parse(data) : data;
                    const { htmlTable, dateTime } = parsedData;

                    console.log(dateTime); // Verify the content

                    // Update the target container
                    const targetDiv = document.querySelector("#widget-last-orders .card-body");
                    if (targetDiv) {
                        // Animation: hide old HTML
                        targetDiv.style.opacity = 0;

                        setTimeout(() => {
                            // Insert new HTML
                            targetDiv.innerHTML = htmlTable;

                            // Animation: fade in new HTML
                            targetDiv.style.transition = "opacity 0.5s";
                            targetDiv.style.opacity = 1;
                        }, 300);
                    }

                    // Optionally update timestamp
                    const footerElement = document.querySelector(`#widget-last-orders .card-footer .widget-timestamp`);
                    if (footerElement) {
                        footerElement.textContent = dateTime;
                    }
                })
                .catch(error => {
                    console.error("Error loading the widget data:", error);
                });
        });
    }
});
