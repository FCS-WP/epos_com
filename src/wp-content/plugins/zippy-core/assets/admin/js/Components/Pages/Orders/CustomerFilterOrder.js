import React, { useEffect, useState } from "react";
import SearchSelect from "../../Select/SearchSelect";
import { Api } from "../../../api/admin";
import { useOrderProvider } from "../../../context/OrderContext";

export default function CustomerFilterOrder({
  customerSearchSelected,
  setCustomerSearchSelected,
}) {
  async function fetchCustomers(input) {
    if (!input || input.length < 3) return [];
    const response = await Api.searchCustomers({ q: input });
    return response.data.results.map((customer) => ({
      id: customer.id,
      label: customer.label,
    }));
  }

  return (
    <SearchSelect
      value={customerSearchSelected}
      setValue={setCustomerSearchSelected}
      placeholder="Search customer"
      fetchOptions={fetchCustomers}
    />
  );
}
