(function () {
  "use strict";

  window.onload = function () {
    if (typeof PGWSDK === "undefined" || typeof Zippy2C2P === "undefined") {
      console.error("2C2P SDK or ZippyData not loaded.");
      return;
    }

    const uiRequest = {
      url: Zippy2C2P.paymentUrl,
      templateId: "ikea",
      mode: "DropIn",
      appBar: false,
      cancelConfirmation: false,
    };

    PGWSDK.paymentUI(uiRequest, function (response) {
      if (response.responseCode == "2000") {
        // Payment processed or redirected, move to return URL
        window.location.href = Zippy2C2P.returnUrl;
      } else if (response.responseCode == "0003") {
        alert("Payment cancelled. You will be redirected back to checkout.");
        window.location.reload();
      } else {
        console.log("2C2P Error: " + response.responseDescription);
      }
    });
  };
})();
