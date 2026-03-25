"use client";

import { useState } from "react";
import {
  InputAdornment,
  OutlinedInput,
  FormControl,
  IconButton,
  Box,
} from "@mui/material";
import SearchIcon from "@mui/icons-material/Search";
import { useOrderProvider } from "../../../context/OrderContext";

export default function OrderSearch({
  placeholder = "Search orders...",
  onSearch,
}) {
  const { searchQuery } = useOrderProvider();
  const [value, setValue] = useState(searchQuery);

  const handleSearch = () => {
    onSearch?.(value);
  };

  const handleKeyDown = (e) => {
    if (e.key === "Enter") handleSearch();
  };

  return (
    <Box sx={{ flexGrow: 1, display: "flex", justifyContent: "end" }}>
      <Box sx={{ width: "250px" }}>
        <FormControl fullWidth variant="outlined" size="small">
          <OutlinedInput
            value={value}
            onChange={(e) => setValue(e.target.value)}
            onKeyDown={handleKeyDown}
            placeholder={placeholder}
            endAdornment={
              <InputAdornment position="end">
                <IconButton onClick={handleSearch} edge="end" size="small">
                  <SearchIcon fontSize="small" />
                </IconButton>
              </InputAdornment>
            }
            className="custom-input"
            sx={{
              borderRadius: 2,
              backgroundColor: "background.paper",
              "& .MuiOutlinedInput-notchedOutline": {
                borderColor: "divider",
              },
              "&:hover .MuiOutlinedInput-notchedOutline": {
                borderColor: "text.secondary",
              },
              "&.Mui-focused .MuiOutlinedInput-notchedOutline": {
                borderColor: "primary.main",
              },
            }}
          />
        </FormControl>
      </Box>
    </Box>
  );
}
