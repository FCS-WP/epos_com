import { makeRequest } from "./axios";
export const webApi = {
  async createPaymentSession(params) {
    return await makeRequest("/antom/createPaymentSession", params, "POST");
  },
  async checkPaymentTransaction(params) {
    return await makeRequest("/antom/checkPaymentTransaction", params, "POST");
  },
  async pollPaymentTransaction(params) {
    return await makeRequest("/", params, "POST",'',);
  },
};
