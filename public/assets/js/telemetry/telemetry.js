// /assets/js/telemetry/telemetry.js
(function (window) {
  const defaultTelemetryConfig = {
    endpointUrl: window.APP_BASE_URL + "/api/v1/telemetry/collect",
    applicationName: "web",
    pagePath: "",
    sessionStorageKey: "telemetrySessionId",
    userIdentifier: null,

    maximumEventsPerBatch: 25,
    flushIntervalMilliseconds: 10000,
    maximumQueueLength: 200,

    debugMode: false,
  };


  function getCurrentIsoTimestamp() {
    return new Date().toISOString();
  }

  function generateRandomUuid() {
    return ([1e7] + -1e3 + -4e3 + -8e3 + -1e11).replace(/[018]/g, (character) =>
      (
        character ^
        (crypto.getRandomValues(new Uint8Array(1))[0] & (15 >> (character / 4)))
      ).toString(16),
    );
  }

  function getOrCreateSessionId(storageKey) {
    try {
      let sessionId = sessionStorage.getItem(storageKey);

      if (!sessionId) {
        sessionId = generateRandomUuid();
        sessionStorage.setItem(storageKey, sessionId);
      }

      return sessionId;
    } catch (error) {
      return generateRandomUuid();
    }
  }

  function detectDeviceHints() {
    const hasTouch = "ontouchstart" in window || navigator.maxTouchPoints > 0;

    const viewportWidth = window.innerWidth;
    const viewportHeight = window.innerHeight;

    let deviceCategory = "desktop";

    if (viewportWidth <= 768) {
      deviceCategory = "mobile";
    } else if (viewportWidth <= 1024 && hasTouch) {
      deviceCategory = "tablet";
    }

    return {
      deviceCategory, // mobile | tablet | desktop
      viewportWidth,
      viewportHeight,
      hasTouch,
      devicePixelRatio: window.devicePixelRatio || 1,
    };
  }

  const Telemetry = {
    configuration: { ...defaultTelemetryConfig },
    eventQueue: [],
    registeredPlugins: [],
    flushTimerId: null,
    sessionId: null,

    initialize(customConfig = {}) {
      this.configuration = {
        ...defaultTelemetryConfig,
        ...customConfig,
      };

      this.sessionId = getOrCreateSessionId(
        this.configuration.sessionStorageKey,
      );

      if (!this.configuration.pagePath) {
        this.configuration.pagePath =
          window.location.pathname + window.location.search;
      }

      this.configuration.deviceHints = detectDeviceHints();

      if (this.configuration.flushIntervalMilliseconds > 0) {
        this.flushTimerId = window.setInterval(() => {
          this.flushEvents("interval");
        }, this.configuration.flushIntervalMilliseconds);
      }

      this.logDebug("Telemetry initialized", this.configuration);
      return this;
    },

    registerPlugin(pluginInitializer) {
      if (typeof pluginInitializer === "function") {
        pluginInitializer(this);
        this.registeredPlugins.push(pluginInitializer);
      }
      return this;
    },

    trackEvent(eventName, eventData = {}, options = {}) {
      if (!eventName) {
        return;
      }

      const telemetryEvent = {
        eventName: eventName,
        timestamp: getCurrentIsoTimestamp(),
        applicationName: this.configuration.applicationName,
        pagePath: this.configuration.pagePath,
        sessionId: this.sessionId,
        userIdentifier: this.configuration.userIdentifier,
        eventData: eventData,
      };

      if (this.eventQueue.length >= this.configuration.maximumQueueLength) {
        this.eventQueue.shift();
      }

      this.eventQueue.push(telemetryEvent);
      this.logDebug("Event queued", telemetryEvent);

      if (
        !options.disableAutomaticFlush &&
        this.eventQueue.length >= this.configuration.maximumEventsPerBatch
      ) {
        this.flushEvents("batchSizeReached");
      }
    },

    flushEvents(reason = "manual") {
      if (this.eventQueue.length === 0) {
        return;
      }

      const eventsToSend = this.eventQueue.slice(
        0,
        this.configuration.maximumEventsPerBatch,
      );

      const payload = {
        applicationName: this.configuration.applicationName,
        pagePath: this.configuration.pagePath,
        sessionId: this.sessionId,
        deviceHints: this.configuration.deviceHints,
        events: eventsToSend.map(function (eventItem) {
          return {
            eventName: eventItem.eventName,
            eventData: eventItem.eventData,
          };
        }),
      };

      const sendSuccessful = this.sendPayload(payload);

      if (sendSuccessful) {
        this.eventQueue = this.eventQueue.slice(eventsToSend.length);
      } else {
        this.logDebug("Send failed, keeping events in queue");
      }
    },

    sendPayload(payload) {
      const jsonPayload = JSON.stringify(payload);

      if (navigator.sendBeacon) {
        try {
          const beaconBlob = new Blob([jsonPayload], {
            type: "application/json",
          });

          const beaconResult = navigator.sendBeacon(
            this.configuration.endpointUrl,
            beaconBlob,
          );

          this.logDebug("sendBeacon result", beaconResult);

          if (beaconResult) {
            return true;
          }
        } catch (error) {
          this.logDebug("sendBeacon error", error);
        }
      }

      try {
        fetch(this.configuration.endpointUrl, {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
          body: jsonPayload,
          keepalive: true,
          credentials: "same-origin",
        }).catch(() => {});

        this.logDebug("fetch keepalive used");
        return true;
      } catch (error) {
        this.logDebug("fetch error", error);
        return false;
      }
    },

    logDebug(...argumentsList) {
      if (this.configuration.debugMode) {
        console.log("[Telemetry]", ...argumentsList);
      }
    },
  };

  window.Telemetry = Telemetry;
})(window);
