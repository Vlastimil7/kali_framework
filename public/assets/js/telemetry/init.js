(function initTelemetry() {
  // console.log("telemetry init start", !!window.Telemetry);

  if (!window.Telemetry) return;

  Telemetry.initialize({
    applicationName: "vk-dev",
    debugMode: false, // pro test
  });

  // console.log("Telemetry initialized", Telemetry);

  if (typeof window.TelemetryClickTrackingPlugin === "function") {
    Telemetry.registerPlugin(TelemetryClickTrackingPlugin());
   // console.log("click plugin registered");
  }

  if (typeof window.TelemetryScrollDepthPlugin === "function") {
    Telemetry.registerPlugin(TelemetryScrollDepthPlugin({ thresholds: [25, 50, 75, 90, 100] }));
  }

  if (typeof window.TelemetryVisibilityPlugin === "function") {
    Telemetry.registerPlugin(TelemetryVisibilityPlugin());
  }

  if (typeof window.TelemetrySectionDwellTimePlugin === "function") {
    Telemetry.registerPlugin(TelemetrySectionDwellTimePlugin({
      visibleRatioToActivate: 0.6,
      minimumReportMilliseconds: 2000,
    }));
  }

  if (typeof window.TelemetryActivityPingPlugin === "function") {
    Telemetry.registerPlugin(TelemetryActivityPingPlugin({
      pingIntervalMilliseconds: 5000,
      activityWindowMilliseconds: 3000,
    }));
  }

  //console.log("telemetry init done");
})();
