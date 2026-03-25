import React, { useEffect, useState } from "react";
import {
  Button,
  TextField,
  Stack,
  FormControl,
  InputLabel,
  Select,
  MenuItem,
} from "@mui/material";
import FilterDateRange from "./FilterDateRange";
import { useOrderProvider } from "../../../context/OrderContext";
import { getStatusOptions } from "../../../const/pages/orders/order-constants";
import CustomerFilterOrder from "./CustomerFilterOrder";

const formatDate = (dateStr) => {
  if (!dateStr) return "";

  dateStr = `${dateStr.getFullYear()}-${String(dateStr.getMonth() + 1).padStart(
    2,
    "0"
  )}-${String(dateStr.getDate()).padStart(2, "0")}`;

  const [y, m, d] = dateStr.split("-");
  return `${y}-${m}-${d}`;
};

const FilterOrder = () => {
  const { filteredOrders, handleFilterOrder } = useOrderProvider();

  const [status, setStatus] = useState("");
  const [fromDate, setFromDate] = useState(null);
  const [toDate, setToDate] = useState(null);
  const [customerSearchSelected, setCustomerSearchSelected] = useState(null);

  const onFilter = () => {
    handleFilterOrder({
      date_from: formatDate(fromDate),
      date_to: formatDate(toDate),
      order_status: status,
      customer_id: customerSearchSelected ? customerSearchSelected.id : null,
      customer_selected: customerSearchSelected,
    });
  };

  useEffect(() => {
    if (filteredOrders) {
      setStatus(filteredOrders.order_status || "");
      setFromDate(
        filteredOrders.date_from ? new Date(filteredOrders.date_from) : null
      );
      setToDate(
        filteredOrders.date_to ? new Date(filteredOrders.date_to) : null
      );
      setCustomerSearchSelected(filteredOrders.customer_selected || null);
    }
  }, [filteredOrders]);

  return (
    <Stack
      direction="row"
      alignItems="center"
      flexWrap="wrap"
      gap={1}
      rowGap={1}
    >
      {/* Filter date created order */}
      <FilterDateRange
        fromDate={fromDate}
        setFromDate={setFromDate}
        toDate={toDate}
        setToDate={setToDate}
      />

      {/* Filter by status */}
      <FormControl size="small">
        <Select
          value={status}
          onChange={(e) => setStatus(e.target.value)}
          displayEmpty
          sx={{ height: "32px", fontSize: "14px", minWidth: "180px" }}
          MenuProps={{
            PaperProps: {
              sx: {
                mt: 1,
                ml: 8,
              },
            },
          }}
        >
          {getStatusOptions().map((option) => (
            <MenuItem key={option.value} value={option.value}>
              {option.label}
            </MenuItem>
          ))}
        </Select>
      </FormControl>

      {/* Filter by customer email */}
      {/* <CustomerFilterOrder
        customerSearchSelected={customerSearchSelected}
        setCustomerSearchSelected={setCustomerSearchSelected}
      /> */}

      {/* Filter button */}
      <Button
        variant="outlined"
        sx={{
          height: "32px",
          fontSize: "12px",
          borderRadius: "2px",
          background: "#f6f7f7",
          color: "#2271b1",
          border: "1px solid #2271b1",
          boxShadow: "none",
          "&:hover": { background: "#e1e4e6", boxShadow: "none" },
          "@media (max-width: 600px)": {
            height: "32px",
            fontSize: "10px",
          },
        }}
        onClick={onFilter}
      >
        Filter
      </Button>
    </Stack>
  );
};

export default FilterOrder;
