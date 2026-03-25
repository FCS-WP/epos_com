export const orderStatus = {
  PENDING: "pending",
  PROCESSING: "processing",
  COMPLETED: "completed",
  ON_HOLD: "on-hold",
  CANCELLED: "cancelled",
  REFUNDED: "refunded",
  TRASH: "trash",
};

export const statusColors = {
  [orderStatus.PROCESSING]: { label: "Processing", customColor: "#2e7d32" },
  [orderStatus.COMPLETED]: { label: "Completed", customColor: "#1976d2" },
  [orderStatus.PENDING]: { label: "Pending Payment", customColor: "#d32f2f" },
  [orderStatus.CANCELLED]: { label: "Cancelled", customColor: "#000000de" },
  [orderStatus.REFUNDED]: { label: "Refunded", customColor: "#ff9d00ff" },
  [orderStatus.ON_HOLD]: { label: "On Hold", customColor: "#dab821ff" },
};

export const getStatusOptions = () => [
  { value: "", label: "All statuses" },
  { value: orderStatus.PENDING, label: "Pending" },
  { value: orderStatus.PROCESSING, label: "Processing" },
  { value: orderStatus.COMPLETED, label: "Completed" },
  { value: orderStatus.ON_HOLD, label: "On Hold" },
  { value: orderStatus.CANCELLED, label: "Cancelled" },
  { value: orderStatus.REFUNDED, label: "Refunded" },
  { value: orderStatus.TRASH, label: "Trash" },
];

export const statusWooCommerceOrders = {
  "wc-pending": "Pending payment",
  "wc-processing": "Processing",
  "wc-completed": "Completed",
  "wc-on-hold": "On hold",
  "wc-cancelled": "Cancelled",
  "wc-refunded": "Refunded",
  "wc-failed": "Failed",
  "wc-checkout-draft": "Draft",
};
