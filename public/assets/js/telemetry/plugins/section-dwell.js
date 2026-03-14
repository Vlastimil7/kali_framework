// /assets/js/telemetry/plugins/section-dwell.js
function TelemetrySectionDwellTimePlugin(options = {}) {
  const defaultOptions = {
    sectionSelector: "[data-telemetry-section]",
    visibleRatioToActivate: 0.4, // 60 % sekce na obrazovce = aktivní
    minimumReportMilliseconds: 2000, // reportuj až od 2s (odfiltruje “proletěl jsem kolem”)
    reportEventName: "sectionDwellTime",
  };

  const finalOptions = { ...defaultOptions, ...options };

  return function initializeSectionDwellTracking(telemetry) {
    const sectionStateByElement = new Map();

    function getNowMilliseconds() {
      return Date.now();
    }

    function ensureSectionState(sectionElement) {
      if (!sectionStateByElement.has(sectionElement)) {
        sectionStateByElement.set(sectionElement, {
          sectionName:
            sectionElement.getAttribute("data-telemetry-section") ||
            sectionElement.id ||
            "unknown",
          isActive: false,
          activeStartMilliseconds: null,
          accumulatedMilliseconds: 0,
        });
      }

      return sectionStateByElement.get(sectionElement);
    }

    function startSectionTiming(sectionElement) {
      const state = ensureSectionState(sectionElement);
      if (state.isActive) {
        return;
      }

      state.isActive = true;
      state.activeStartMilliseconds = getNowMilliseconds();
    }

    function stopSectionTiming(sectionElement, stopReason) {
      const state = ensureSectionState(sectionElement);
      if (!state.isActive || state.activeStartMilliseconds === null) {
        return;
      }

      const nowMilliseconds = getNowMilliseconds();
      const elapsedMilliseconds =
        nowMilliseconds - state.activeStartMilliseconds;

      state.accumulatedMilliseconds += elapsedMilliseconds;
      state.isActive = false;
      state.activeStartMilliseconds = null;

      // reportuj jen pokud je to “smysluplný”
      if (
        state.accumulatedMilliseconds >= finalOptions.minimumReportMilliseconds
      ) {
        telemetry.trackEvent(finalOptions.reportEventName, {
          sectionName: state.sectionName,
          milliseconds: state.accumulatedMilliseconds,
          stopReason: stopReason,
        });

        // reset, ať neposíláš pořád stejné číslo dokola
        state.accumulatedMilliseconds = 0;
      }
    }

    const observer = new IntersectionObserver(
      function handleIntersections(entries) {
        entries.forEach(function (entry) {
          const sectionElement = entry.target;
          const intersectionRatio = entry.intersectionRatio;

          if (intersectionRatio >= finalOptions.visibleRatioToActivate) {
            startSectionTiming(sectionElement);
          } else {
            stopSectionTiming(sectionElement, "visibilityChanged");
          }
        });
      },
      {
        threshold: [0, finalOptions.visibleRatioToActivate, 1],
      },
    );

    // napoj všechny sekce
    const sectionElements = document.querySelectorAll(
      finalOptions.sectionSelector,
    );
    sectionElements.forEach(function (sectionElement) {
      ensureSectionState(sectionElement);
      observer.observe(sectionElement);
    });

    // flush při odchodu ze stránky: uzavři aktivní sekce
    window.addEventListener("pagehide", function () {
      sectionElements.forEach(function (sectionElement) {
        stopSectionTiming(sectionElement, "pageHidden");
      });

      telemetry.flushEvents("sectionDwellFinalFlush");
    });

    // když user přepne tab, taky uzavřeme aktivní sekce
    document.addEventListener("visibilitychange", function () {
      if (document.visibilityState === "hidden") {
        sectionElements.forEach(function (sectionElement) {
          stopSectionTiming(sectionElement, "documentHidden");
        });

        telemetry.flushEvents("sectionDwellFinalFlush");
      }
    });
  };
}
