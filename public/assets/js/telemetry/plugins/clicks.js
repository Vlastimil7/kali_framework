function TelemetryClickTrackingPlugin() {
  return function initializeClickTracking(telemetry) {
    document.addEventListener(
      "click",
      function handleClick(event) {
        const clickedElement = event.target.closest("[data-track]");
        if (!clickedElement) {
          return;
        }

        const eventName = clickedElement.getAttribute("data-track");
        const metaDataAttribute =
          clickedElement.getAttribute("data-track-meta");

        let metaData = {};
        if (metaDataAttribute) {
          try {
            metaData = JSON.parse(metaDataAttribute);
          } catch (error) {
            metaData = {};
          }
        }

        metaData.elementTag = clickedElement.tagName.toLowerCase();

        if (clickedElement.id) {
          metaData.elementId = clickedElement.id;
        }

        if (clickedElement.getAttribute("href")) {
          metaData.href = clickedElement.getAttribute("href");
        }

        telemetry.trackEvent(eventName, metaData);

        // Volitelné: pošli hned (jen když chceš)
        telemetry.flushEvents("clickImmediate");
      },
      { capture: true },
    );
  };
}
