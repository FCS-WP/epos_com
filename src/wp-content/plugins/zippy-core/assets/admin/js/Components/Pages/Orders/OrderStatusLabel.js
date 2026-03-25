import React from "react";
import { Chip } from "@mui/material";
import { statusColors } from "../../../const/pages/orders/order-constants";

export default function OrderStatusLabel({ status }) {
  const normalized = status?.toLowerCase() || "";
  const info = statusColors[normalized] || { label: status, color: "default" };

  return (
    <Chip
      label={info.label}
      variant="outlined"
      size="small"
      sx={{
        fontWeight: "bold",
        textTransform: "capitalize",
        ...(info.customColor && {
          borderColor: info.customColor,
          color: info.customColor,
        }),
      }}
    />
  );
}
