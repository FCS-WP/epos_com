import { AMSCheckoutPage } from "@alipay/ams-checkout";
import { webApi } from "./api";

class Antom {
  constructor() {
    this.maxAttempts = 5;
    this.retryDelay = 3000;
    this.$loadingIndicator = $("#zippy_antom_loader");
    this.$antom_error = $("#antom_error");

    this.checkoutApp = new AMSCheckoutPage({
      environment: "prod",
      locale: "en_US",
      onLog: this.handleLog,
      onError: this.handleError,
      onEventCallback: this.onEventCallback.bind(this),
    });
  }

  handleLog({ code, message }) {
    console.log("Log:", code, message);
  }

  handleError({ code, result }) {
    console.error("SDK Error:", code, result);
  }

  async getPaymentSessionData(orderId) {
    try {
      const response = await webApi.createPaymentSession({ order_id: orderId });
      return response.data;
    } catch (error) {
      console.error("Error fetching payment session data:", error);
      return null;
    }
  }

  async checkPaymentTransaction(orderId) {
    try {
      const response = await webApi.checkPaymentTransaction({
        order_id: orderId,
      });
      return response.data;
    } catch (error) {
      console.error("Error fetching payment transaction data:", error);
      return null;
    }
  }

  async create(orderId) {
    const paymentSessionData = await this.getPaymentSessionData(orderId);
    if (!paymentSessionData?.data?.data?.paymentSessionData) {
      console.error("Payment session data missing.");
      this.showError();
      return;
    }

    await this.checkoutApp.mountComponent(
      { sessionData: paymentSessionData.data.data.paymentSessionData },
      "#zippy_antom"
    );
  }

  showLoading() {
    this.$loadingIndicator.addClass("show-loading");
  }

  hideLoading() {
    this.$loadingIndicator.removeClass("show-loading");
  }

  showError() {
    console.error("🚨 Antom Checkout encountered an error.");
    this.$antom_error.addClass("show-error");
    this.hideLoading();
  }

  async remove() {
    this.checkoutApp.unmount();
  }

  async pollPaymentStatus(orderId, attempt = 1) {
    try {
      const response = await webApi.pollPaymentTransaction({
        "wc-api": "wc_zippy_antom_redirect",
        order_id: orderId,
      });
      if (attempt > this.maxAttempts) {
        console.warn("❌ Max retries reached, stopping payment checks.");
        this.hideLoading();
        this.showError();
        window.location.href = response?.data?.redirect_url;
        return;
      }
      // this.showLoading();
      if (response?.data?.data?.paymentStatus === "SUCCESS") {
        console.log("✅ Payment Successful!");
        window.location.href = response?.data?.redirect_url;
        return;
      }
      setTimeout(
        () => this.pollPaymentStatus(orderId, attempt + 1),
        this.retryDelay
      );
    } catch (error) {
      console.error(`⚠️ Error in attempt ${attempt}:`, error);
      setTimeout(
        () => this.pollPaymentStatus(orderId, attempt + 1),
        this.retryDelay
      );
    }
  }

  onEventCallback({ code, result }) {
    switch (code) {
      case "SDK_PAYMENT_SUCCESSFUL":
      case "SDK_PAYMENT_PROCESSING":
      case "SDK_PAYMENT_CANCEL":
        console.log("Payment Processing:", result);
        this.handleSuccessfulPayment();
        break;
      case "SDK_PAYMENT_FAIL":
      case "SDK_PAYMENT_ERROR":
        console.error("Payment Error:", result);
        this.remove();
        this.showError();
        break;
      case "SDK_END_OF_LOADING":
        console.log("SDK loading ended.");
        this.handleSuccessfulPayment();
        break;
      default:
        console.warn("Unhandled SDK event:", code);
        break;
    }
  }
  

  handleSuccessfulPayment() {
    let currentUrl = new URL(window.location.href);
    let redirectPage = currentUrl.origin + "/antom-payment/pending";
    window.location.replace(redirectPage);
  }
}

export default Antom;


