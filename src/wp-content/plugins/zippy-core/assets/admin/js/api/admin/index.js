import { makeRequest } from "../axios";

export const Api = {
  async getOrders(params) {
    return await makeRequest("/orders", params);
  },
  async updateOrderStatus(params) {
    return await makeRequest(
      "/bulk-action-update-order-status",
      params,
      "POST",
    );
  },
  async moveToTrashOrder(params) {
    return await makeRequest("/move-to-trash", params, "POST");
  },
  async getOrderInfo(params) {
    return await makeRequest("/get-order-info", params);
  },
  async removeOrderItem(params) {
    return await makeRequest("/remove-item-order", params, "POST");
  },
  async updateOrderItemMetaData(orderId, action, params) {
    return await makeRequest(
      `/update-quantity-order-item?order_id=${orderId}&action=${action}`,
      params,
      "POST",
    );
  },
  async applyCouponToOrder(params) {
    return await makeRequest("/apply_coupon_to_order", params, "POST");
  },
  async getCustomers(params) {
    return await makeRequest("/get-list-customers", params);
  },
  async updatePriceProductByUser(params) {
    return await makeRequest("/update-price-product-by-user", params);
  },
  async getAdminNameFromOrder(params) {
    return await makeRequest("/admin-name-from-order", params);
  },
  async products(params) {
    return await makeRequest("/products", params, "GET");
  },
  async addProductsToOrder(order_id, action, params) {
    return await makeRequest(
      `/add-items-order?order_id=${order_id}&action=${action}`,
      params,
      "POST",
    );
  },
  async categories(params) {
    return await makeRequest("/categories", params, "GET");
  },
  async product(params) {
    return await makeRequest("/product", params, "GET");
  },
  async getOrderDetails(params) {
    return await makeRequest("/order-details", params);
  },
  async exportOrders(params) {
    return await makeRequest("/export-orders", params, "POST");
  },
  async downloadInvoice(params) {
    return await makeRequest("/download-invoice", params, "POST");
  },
  async getSummaryOrders(params) {
    return await makeRequest("/summary-orders", params);
  },
  async searchCustomers(params) {
    return await makeRequest("/search-customers", params);
  },
  async refundOrder(params) {
    return await makeRequest("/refund-order", params, "POST");
  },

  //Export all orders
  async exportStart(params) {
    return await makeRequest("/export-start", params, "POST");
  },
  async exportProcessChunk(params) {
    return await makeRequest("/export-process-chunk", params, "POST");
  },
  async exportFinalize(params) {
    return await makeRequest("/export-finalize", params, "POST");
  },
};

export const SettingApi = {
  async getModulesConfigs(params) {
    return await makeRequest("/core-settings", params);
  },

  async updateModulesConfigs(params) {
    return await makeRequest("/core-settings", params, "POST");
  },

  // Invoices
  async getInvoiceOptions(params) {
    return await makeRequest("/core-settings/orders/invoices", params);
  },
  async updateInvoiceOptions(params) {
    return await makeRequest("/core-settings/orders/invoices", params, "POST");
  },

  // Orders Details
  async getOrderDetailSettings(params) {
    return await makeRequest("core-settings/orders/details");
  },
  async updateDetailSettings(params) {
    return await makeRequest("/core-settings/orders/details", params, "POST");
  },
};
