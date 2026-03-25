import React, { useState } from "react";
import {
  Button,
  Dialog,
  DialogActions,
  DialogContent,
  DialogTitle,
  TextField,
} from "@mui/material";
import { Api } from "../../../../api/admin";
import { toast } from "react-toastify";

export default function RefundButton({ orderID, onRefundSuccess }) {
  const [open, setOpen] = useState(false);
  const [reason, setReason] = useState("");
  const [loading, setLoading] = useState(false);

  const handleOpen = () => {
    setOpen(true);
    setReason("");
  };

  const handleClose = () => {
    setOpen(false);
    setReason("");
  };

  const handleRefund = async () => {
    if (!orderID) {
      toast.error("Order ID missing");
      return;
    }

    if (!reason.trim()) {
      toast.error("Please enter a refund reason");
      return;
    }

    if (!window.confirm("Are you sure you want to refund this entire order?")) {
      return;
    }

    try {
      setLoading(true);
      const { data: res } = await Api.refundOrder({
        order_id: orderID,
        reason: reason.trim(),
      });

      if (res.status === "success") {
        toast.success("Order refunded successfully");
        handleClose();
        if (onRefundSuccess) {
          onRefundSuccess();
        }
      } else {
        toast.error(res.message || "Failed to refund order");
      }
    } catch (error) {
      console.error("Refund error:", error);
      toast.error("An error occurred while processing the refund");
    } finally {
      setLoading(false);
    }
  };

  return (
    <>
      <Button
        variant="contained"
        color="secondary"
        size="small"
        onClick={handleOpen}
        className="button refund_order"
      >
        Refund
      </Button>

      <Dialog open={open} onClose={handleClose} maxWidth="sm" fullWidth>
        <DialogTitle>Refund Order</DialogTitle>
        <DialogContent>
          <TextField
            autoFocus
            margin="dense"
            label="Refund Reason"
            type="text"
            fullWidth
            multiline
            rows={4}
            value={reason}
            onChange={(e) => setReason(e.target.value)}
            placeholder="Enter the reason for refunding this order..."
          />
        </DialogContent>
        <DialogActions>
          <Button onClick={handleClose} disabled={loading}>
            Cancel
          </Button>
          <Button
            onClick={handleRefund}
            color="secondary"
            variant="contained"
            disabled={loading}
          >
            {loading ? "Processing..." : "Confirm Refund"}
          </Button>
        </DialogActions>
      </Dialog>
    </>
  );
}
