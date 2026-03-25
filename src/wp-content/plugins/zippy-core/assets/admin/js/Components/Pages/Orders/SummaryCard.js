import { Box, Typography } from "@mui/material";
import React from "react";
import { statusColors } from "../../../const/pages/orders/order-constants";

const SummaryCard = ({ title, value, status }) => {
  const info = statusColors[status] || {};

  const borderColor = info.customColor || (info.color ? info.color : "#ccc");
  const textColor = info.customColor || undefined;

  return (
    <Box
      sx={{
        flex: 1,
        p: 2,
        m: 1,
        bgcolor: "#fff",
        color: textColor,
        borderRadius: 2,
        border: `2px solid ${borderColor}`,
        boxShadow: "0 2px 8px rgba(0,0,0,0.05)",
        minWidth: 150,
      }}
    >
      <Typography variant="subtitle2" color="textSecondary">
        {title}
      </Typography>
      <Typography variant="h5" sx={{ mt: 1 }}>
        {value}
      </Typography>
    </Box>
  );
};

export default SummaryCard;
