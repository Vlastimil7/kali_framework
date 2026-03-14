function TelemetryActivityPingPlugin(options = {}) {
  const defaultOptions = {
    pingIntervalMilliseconds: 5000,     // každých 5 s
    activityWindowMilliseconds: 3000,   // byl aktivní v posledních 3 s?
    eventName: "activityPing",
  };

  const finalOptions = { ...defaultOptions, ...options };

  return function initializeActivityPing(telemetry) {
    let lastActivityTimestamp = null;
    let pingTimerId = null;

    function markActivity() {
      lastActivityTimestamp = Date.now();
    }

    // Co považujeme za aktivitu
    const activityEvents = [
      "mousemove",
      "scroll",
      "click",
      "keydown",
      "touchstart",
    ];

    activityEvents.forEach((eventName) => {
      document.addEventListener(eventName, markActivity, {
        passive: true,
        capture: true,
      });
    });

    function sendPingIfActive() {
      if (!lastActivityTimestamp) {
        return;
      }

      const now = Date.now();
      const millisecondsSinceLastActivity = now - lastActivityTimestamp;

      if (millisecondsSinceLastActivity <= finalOptions.activityWindowMilliseconds) {
        telemetry.trackEvent(finalOptions.eventName, {
          active: true,
        });
      }
    }

    pingTimerId = window.setInterval(
      sendPingIfActive,
      finalOptions.pingIntervalMilliseconds
    );

    // při odchodu flush
    window.addEventListener("pagehide", function () {
      sendPingIfActive();
      telemetry.flushEvents("activityPingFinal");
    });
  };
}
