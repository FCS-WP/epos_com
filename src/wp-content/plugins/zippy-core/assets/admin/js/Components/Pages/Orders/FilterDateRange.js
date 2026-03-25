import React, { useEffect } from "react";
import DatePicker from "react-datepicker";

export default function FilterDateRange({
  fromDate,
  setFromDate,
  toDate,
  setToDate,
}) {
  const handleSetFromDate = (date) => {
    if (date) {
      setFromDate(
        `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(
          2,
          "0"
        )}-${String(date.getDate()).padStart(2, "0")}`
      );
    } else {
      setFromDate("");
    }
  };

  const handleSetToDate = (date) => {
    if (date) {
      setToDate(
        `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(
          2,
          "0"
        )}-${String(date.getDate()).padStart(2, "0")}`
      );
    } else {
      setToDate("");
    }
  };

  return (
    <>
      <DatePicker
        selected={fromDate}
        onChange={setFromDate}
        isClearable
        placeholderText="From"
        wrapperClassName="date-input"
      />

      <DatePicker
        selected={toDate}
        onChange={setToDate}
        isClearable
        placeholderText="To"
        wrapperClassName="date-input"
      />
    </>

    // <DatePicker
    //   startDate={fromDate ? new Date(fromDate) : null}
    //   endDate={toDate ? new Date(toDate) : null}
    //   onChange={(update) => {
    //     setDateRange(update);
    //   }}
    //   selectsRange
    //   isClearable
    // />
  );
}
