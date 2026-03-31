(function () {
  "use strict";

  var pollingTimer = null;
  var paymentDone = false;

  window.onload = function () {
    if (typeof PGWSDK === "undefined" || typeof Zippy2C2P === "undefined") {
      console.error("2C2P SDK or ZippyData not loaded.");
      return;
    }

    var uiRequest = {
      url: Zippy2C2P.paymentUrl,
      // templateId: "ikea",
      mode: "DropIn",
      appBar: false,
      cancelConfirmation: false,
    };

    PGWSDK.paymentUI(uiRequest, function (response) {
      if (response.responseCode == "2000") {
        paymentDone = true;
        stopPolling();
        window.location.href = Zippy2C2P.returnUrl;
      } else if (response.responseCode == "0003") {
        stopPolling();
        alert("Payment cancelled. You will be redirected back to checkout.");
        window.location.reload();
      } else {
        console.log("2C2P Error: " + response.responseDescription);
      }
    });

    startPaymentPolling(Zippy2C2P.orderId);
  };

  function stopPolling() {
    if (pollingTimer) {
      clearTimeout(pollingTimer);
      pollingTimer = null;
    }
  }

  /**
   * Polling with backoff: 5s, 10s, 15s, 20s... up to 30s max
   * Stops after 5 minutes total
   */
  function startPaymentPolling(orderId) {
    var attempt = 0;
    var maxDuration = 300000; // 5 minutes
    var startTime = Date.now();

    function poll() {
      if (paymentDone) return;
      if (Date.now() - startTime > maxDuration) return;

      attempt++;
      var delay = Math.min(5000 + attempt * 5000, 30000);

      jQuery.ajax({
        url: Zippy2C2P.ajaxUrl,
        type: "POST",
        data: {
          action: "zippy_check_payment_status",
          order_id: orderId,
        },
        success: function (response) {
          if (response.success && response.data.status === "paid") {
            paymentDone = true;
            window.location.href = response.data.redirect;
            return;
          }
          pollingTimer = setTimeout(poll, delay);
        },
        error: function () {
          pollingTimer = setTimeout(poll, delay);
        },
      });
    }

    // Start first poll after 5 seconds
    pollingTimer = setTimeout(poll, 5000);
  }
})();
