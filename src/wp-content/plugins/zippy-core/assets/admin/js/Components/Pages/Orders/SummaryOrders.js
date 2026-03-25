// SummaryOrders.js
import { Box } from "@mui/material";
import React, { useEffect, useState } from "react";
import SummaryCard from "./SummaryCard";
import { Api } from "../../../api/admin";
import { useOrderProvider } from "../../../context/OrderContext";

const SummaryOrders = () => {
  const { filteredOrders } = useOrderProvider();

  const [orderSummary, setOrderSummary] = useState({
    total_orders: 0,
    pending_orders: 0,
    completed_orders: 0,
    cancelled_orders: 0,
  });

  const getSummaryOrders = async () => {
    const { data } = await Api.getSummaryOrders({
      date_from: filteredOrders?.date_from,
      date_to: filteredOrders?.date_to,
    });
    if (data.status === "success") {
      setOrderSummary(data.results);
    }
  };

  useEffect(() => {
    getSummaryOrders();
  }, [filteredOrders]);

  return (
    <Box sx={{ display: "flex", flexWrap: "wrap", gap: 2, paddingBottom: 2 }}>
      <SummaryCard title="Total Orders" value={orderSummary.total_orders} />
      <SummaryCard
        title="Pending Orders"
        value={orderSummary.pending_orders}
        status="pending"
      />
      <SummaryCard
        title="Completed Orders"
        value={orderSummary.completed_orders}
        status="completed"
      />
      <SummaryCard
        title="Cancelled Orders"
        value={orderSummary.cancelled_orders}
        status="cancelled"
      />
    </Box>
  );
};

export default SummaryOrders;
