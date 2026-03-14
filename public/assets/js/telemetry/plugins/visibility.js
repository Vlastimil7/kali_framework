// /assets/js/telemetry/plugins/visibility.js
function TelemetryVisibilityPlugin() {
  return function initializeVisibilityTracking(telemetryInstance) {
    window.addEventListener("pagehide", function () {
      telemetryInstance.flushEvents("pageHidden");
    });

    document.addEventListener("visibilitychange", function () {
      if (document.visibilityState === "hidden") {
        telemetryInstance.flushEvents("documentHidden");
      }
    });
  };
}
