// /assets/js/telemetry/plugins/scroll.js
function TelemetryScrollDepthPlugin(options = {}) {
  const scrollThresholds = options.thresholds || [25, 50, 75, 100];
  const alreadyReportedThresholds = new Set();

  function calculateScrollPercentage() {
    const documentElement = document.documentElement;
    const scrollTop = window.scrollY || documentElement.scrollTop;
    const scrollHeight =
      documentElement.scrollHeight - documentElement.clientHeight;

    if (scrollHeight <= 0) {
      return 100;
    }

    return Math.round((scrollTop / scrollHeight) * 100);
  }

  return function initializeScrollTracking(telemetryInstance) {
    function onScroll() {
      const currentScrollPercentage = calculateScrollPercentage();

      scrollThresholds.forEach(function (threshold) {
        if (
          currentScrollPercentage >= threshold &&
          !alreadyReportedThresholds.has(threshold)
        ) {
          alreadyReportedThresholds.add(threshold);

          telemetryInstance.trackEvent("scrollDepthReached", {
            percentage: threshold,
          });
        }
      });
    }

    let isTicking = false;

    window.addEventListener(
      "scroll",
      function () {
        if (isTicking) {
          return;
        }

        isTicking = true;

        window.requestAnimationFrame(function () {
          isTicking = false;
          onScroll();
        });
      },
      { passive: true },
    );

    onScroll();
  };
}
