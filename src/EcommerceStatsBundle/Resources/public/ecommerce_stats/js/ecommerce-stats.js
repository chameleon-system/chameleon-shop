
/** @see https://stackoverflow.com/questions/7171099/how-to-replace-url-parameter-with-javascript-jquery#comment-64443667 */
function replaceUrlParameter(url, paramName, paramValue) {
    return url.replace(new RegExp("(" + encodeURIComponent(paramName) + "=).*?(&|$)"), '$1' + encodeURIComponent(paramValue) + '$2');
}

document.addEventListener('DOMContentLoaded', () => {
    const paramsProducer = document.querySelectorAll('.produce-params');

    paramsProducer.forEach((producerElement) => {
        producerElement.addEventListener('change', () => {
            const paramName = producerElement.getAttribute('data-param');
            const value = producerElement.value;

            if (paramName !== null && value !== null) {
                const paramsConsumer = document.querySelectorAll('.consume-params');
                paramsConsumer.forEach((consumerElement) => {
                    const href = consumerElement.getAttribute('href');
                    if (href) {
                        const hrefChanged = replaceUrlParameter(href, paramName, value);
                        consumerElement.setAttribute('href', hrefChanged);
                    }
                });
            }
        });
    });
});
