import React, { useState, useEffect } from "react";
import { Autocomplete, Box, CircularProgress } from "@mui/material";
import { useOrderProvider } from "../../context/OrderContext";

/**
 * Generic search-select input
 * Props:
 * - value: selected value
 * - setValue: setter value
 * - placeholder: placeholder text
 * - fetchOptions: async function to fetch options based on input text
 * - minWidth, height: customize size
 */
export default function SearchSelect({
  value,
  setValue,
  placeholder = "Search",
  fetchOptions,
  minWidth = 220,
  height = 32,
  minCharacters = 3,
}) {
  const {searchQuery} = useOrderProvider();
  
  const [options, setOptions] = useState([]);
  const [inputValue, setInputValue] = useState(searchQuery);
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    if (!inputValue) {
      setOptions([]);
      return;
    }

    if (inputValue.length < minCharacters) {
      setOptions([
        { id: null, label: `Please type at least ${minCharacters} characters` },
      ]);
      return;
    }

    let active = true;
    setLoading(true);

    const load = async () => {
      try {
        const results = await fetchOptions(inputValue);
        if (active) setOptions(results.length ? results : []);
      } finally {
        setLoading(false);
      }
    };

    load();

    return () => {
      active = false;
    };
  }, [inputValue, fetchOptions, minCharacters]);

  return (
    <Autocomplete
      size="small"
      value={value}
      onChange={(e, newValue) => {
        if (newValue?.id !== null) setValue(newValue);
      }}
      inputValue={inputValue}
      onInputChange={(e, newInputValue) => setInputValue(newInputValue)}
      options={options}
      loading={loading}
      getOptionLabel={(option) => option.label || ""}
      isOptionEqualToValue={(option, val) => option.id === val?.id}
      noOptionsText={
        inputValue.length < minCharacters
          ? `Type at least ${minCharacters} characters`
          : "No options"
      }
      renderInput={(params) => (
        <Box
          ref={params.InputProps.ref}
          sx={{
            display: "flex",
            alignItems: "center",
            border: "1px solid #ccc",
            borderRadius: 1,
            px: 1,
            height,
            minWidth,
            "&:focus-within": { borderColor: "#1976d2" },
          }}
        >
          <input
            {...params.inputProps}
            placeholder={placeholder}
            style={{
              border: "none",
              outline: "none",
              width: "100%",
              fontSize: 14,
              height: "100%",
            }}
          />
          {loading && (
            <CircularProgress color="inherit" size={16} sx={{ ml: 1 }} />
          )}
        </Box>
      )}
    />
  );
}
