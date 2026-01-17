import axios from "axios";

export const makeRequest = async (
  endpoint,
  params = {},
  method = "GET",
  customBaseUrl = "zippy-pay/v1", // Default base URL
  token = "FEhI30q7ySHtMfzvSDo6RkxZUDVaQ1BBU3lBcGhYS3BrQStIUT09"
) => {
  const baseURL = "/wp-json";
  const api = axios.create({ baseURL });

  const headers = token ? { Authorization: `Bearer ${token}` } : {};

  const config = {
    url: customBaseUrl + endpoint, // Use customBaseUrl if provided
    params,
    method,
    headers,
  };

  try {
    const res = await api.request(config);
    return { data: res.data };
  } catch (error) {
    if (!error?.response) {
      console.error("❗Error", error.message);
      return { ...error, catchedError: error };
    }

    console.error(error.response.statusText);
    return error;
  }
};
