import React, { createContext, useContext, useState, useEffect } from "react";
import { Api } from "../api/admin";
import { OrderContext } from "./CoreContext";

export const OrderProvider = ({ children }) => {
  const [orders, setOrders] = useState([]);
  const [page, setPage] = useState(0);
  const [rowsPerPage, setRowsPerPage] = useState(10);
  const [searchQuery, setSearchQuery] = useState('');
  const [totalOrders, setTotalOrders] = useState(0);
  const [loadingOrders, setLoadingOrders] = useState(true);
  const [filteredOrders, setFilteredOrders] = useState(null);

  const fetchOrders = async (s = "") => {
    try {
      setLoadingOrders(true);
      const params = {
        ...filteredOrders,
        search: s,
        page: page + 1,
        per_page: rowsPerPage,
      };
      const res = await Api.getOrders(params);
      if (res.data.status === "success") {
        setOrders(res.data.orders);
        setTotalOrders(res.data.total_orders);
      }
    } catch (err) {
      console.error("Error fetching orders:", err);
    } finally {
      setLoadingOrders(false);
    }
  };

  useEffect(() => {
    fetchOrders(searchQuery);
  }, [page, rowsPerPage, filteredOrders]);

  const handleFilterOrder = (filters) => {
    setPage(0);
    setFilteredOrders(filters);
  };

  const fetchSearchOrders = (query) => {
    setSearchQuery(query);
    fetchOrders(query);
  }

  const value = {
    orders,
    totalOrders,
    loadingOrders,
    page,
    searchQuery,
    rowsPerPage,
    filteredOrders,
    setRowsPerPage,
    setPage,
    setOrders,
    handleFilterOrder,
    fetchSearchOrders,
    fetchOrders,
  };

  return (
    <OrderContext.Provider value={value}>{children}</OrderContext.Provider>
  );
};

export const useOrderProvider = () => useContext(OrderContext);
