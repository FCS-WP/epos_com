import Antom from "./Antom";
("use strict");

$(document).ready(function () {
  resetAntomSelection();
  handlePageActions();

  function resetAntomSelection() {
    $("#payment_method_zippy_antom_payment").prop("checked", false);
  }

  function handlePageActions() {
    const currentUrl = new URL(window.location.href);
    const currentPage = currentUrl.pathname;

    const pageActions = {
      "/antom-payment/pending/": pollPaymentStatus,
      "/antom-payment/": initializeAntomCheckout,
    };

    if (pageActions[currentPage]) {
      pageActions[currentPage]();
    }
  }

  function initializeAntomCheckout() {
    const $checkoutContainer = $("#zippy_antom");
    const orderId = $("#antom_order_id").val()?.trim();
    if (!$checkoutContainer.length || !orderId) {
      console.warn("Antom Checkout: Missing container or order ID.");
      return;
    }

    const antomInstance = new Antom();
    antomInstance.showLoading();
    antomInstance
      .create(orderId)
      .then(() => antomInstance.hideLoading())
      .catch(() => antomInstance.showError());
  }

  function pollPaymentStatus() {
    const order_id = $("#antom_order_id_pending").val()?.trim();
    if (!order_id) return;

    const antomInstance = new Antom();
    antomInstance.showLoading();
    antomInstance.pollPaymentStatus(order_id);
  }
});
