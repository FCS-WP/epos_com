import React, { useState } from "react";
import { Button, Select, MenuItem, FormControl, Stack } from "@mui/material";
import { toast } from "react-toastify";
import { Api } from "../../../api/admin";
import { statusWooCommerceOrders } from "../../../const/pages/orders/order-constants";

const BulkAction = ({ selectedOrders, setOrders }) => {
  const [action, setAction] = useState("");
  const [loading, setLoading] = useState(false);

  const getStatusFromAction = (action) => {
    return action.replace(/^wc-/, "");
  };

  const handleSubmit = async () => {
    setLoading(true);

    try {
      let apiFunc = Api.updateOrderStatus;
      let payload = {
        order_ids: selectedOrders,
        status: action,
        action: "change_status",
      };
      let successMsg = "Orders updated!";
      let onSuccess = (data) => {
        const updatedOrders = data.updated_orders || [];
        if (updatedOrders.length) {
          const statusText = getStatusFromAction(action);
          setOrders((prev) =>
            prev.map((order) =>
              updatedOrders.includes(order.id)
                ? { ...order, status: statusText }
                : order
            )
          );
        }
      };

      //Trash
      if (action === "trash") {
        apiFunc = Api.moveToTrashOrder;
        payload = { order_ids: selectedOrders };
        successMsg = "Orders moved to trash!";
        onSuccess = (data) => {
          const trashed = data.trashed_orders || [];
          setOrders((prev) => prev.filter((o) => !trashed.includes(o.id)));
        };
      }

      //Restore
      if (action === "restore") {
        payload = {
          order_ids: selectedOrders,
          status: "wc-pending",
          action: "restore",
        };
        successMsg = "Orders restored from trash!";
        onSuccess = (data) => {
          const restored = data.updated_orders || [];
          setOrders((prev) => prev.filter((o) => !restored.includes(o.id)));
        };
      }

      const { data } = await apiFunc(payload);

      if (data.status === "success") {
        toast.success(successMsg);
        onSuccess(data);
      } else {
        toast.error(data.message || "Failed to process orders.");
      }
    } catch (err) {
      toast.error("Error: " + err.message);
    } finally {
      setLoading(false);
    }
  };

  const selectedLabel =
    action === "trash"
      ? "Move to Trash"
      : action === "restore"
      ? "Restore from Trash"
      : statusWooCommerceOrders[action]
      ? `Change to ${statusWooCommerceOrders[action]}`
      : "Apply Action";

  return (
    <Stack direction="row" spacing={1} alignItems="center">
      <FormControl size="small">
        <Select
          value={action}
          onChange={(e) => setAction(e.target.value)}
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
          <MenuItem value="">Bulk Actions</MenuItem>
          {Object.entries(statusWooCommerceOrders).map(([key, label]) => (
            <MenuItem key={key} value={key}>
              Change status to {label}
            </MenuItem>
          ))}
          <MenuItem value="trash">Move to Trash</MenuItem>
          <MenuItem value="restore">Restore from Trash</MenuItem>
        </Select>
      </FormControl>

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
        onClick={handleSubmit}
        disabled={loading}
      >
        {loading ? "Updating..." : selectedLabel}
      </Button>
    </Stack>
  );
};

export default BulkAction;
