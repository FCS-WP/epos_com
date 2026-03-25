import React from "react";
import {
  Box,
  IconButton,
  MenuItem,
  Select,
  Stack,
  Typography,
} from "@mui/material";
import {
  FirstPage,
  LastPage,
  NavigateBefore,
  NavigateNext,
} from "@mui/icons-material";

const TablePaginationCustom = ({
  count,
  rowsPerPage,
  page,
  onPageChange,
  onRowsPerPageChange,
  rowsPerPageOptions = [5, 10, 25, 50, 100],
}) => {
  const totalPages = Math.ceil(count / rowsPerPage);
  const startCounter = page * rowsPerPage + 1;
  const endCounter = Math.min((page + 1) * rowsPerPage, count);

  const handleFirstPage = () => {
    onPageChange(null, 0);
  };

  const handlePreviousPage = () => {
    if (page > 0) {
      onPageChange(null, page - 1);
    }
  };

  const handleNextPage = () => {
    if (page < totalPages - 1) {
      onPageChange(null, page + 1);
    }
  };

  const handleLastPage = () => {
    onPageChange(null, totalPages - 1);
  };

  return (
    <Box
      display="flex"
      justifyContent="space-between"
      alignItems="center"
      p={2}
      sx={{
        borderTop: "1px solid",
        borderColor: "divider",
      }}
    >
      <Stack direction="row" spacing={1} alignItems="center">
        <Typography variant="body2">Rows per page:</Typography>
        <Select
          value={rowsPerPage}
          onChange={onRowsPerPageChange}
          size="small"
          sx={{ minWidth: 70 }}
        >
          {rowsPerPageOptions.map((option) => (
            <MenuItem key={option} value={option}>
              {option}
            </MenuItem>
          ))}
        </Select>
      </Stack>

      <Stack direction="row" spacing={2} alignItems="center">
        <Typography variant="body2">
          {startCounter} - {endCounter} of {count}
        </Typography>

        <Stack direction="row" spacing={0.5}>
          <IconButton
            onClick={handleFirstPage}
            disabled={page === 0}
            size="small"
            aria-label="first page"
          >
            <FirstPage />
          </IconButton>

          <IconButton
            onClick={handlePreviousPage}
            disabled={page === 0}
            size="small"
            aria-label="previous page"
          >
            <NavigateBefore />
          </IconButton>

          <IconButton
            onClick={handleNextPage}
            disabled={page >= totalPages - 1}
            size="small"
            aria-label="next page"
          >
            <NavigateNext />
          </IconButton>

          <IconButton
            onClick={handleLastPage}
            disabled={page >= totalPages - 1}
            size="small"
            aria-label="last page"
          >
            <LastPage />
          </IconButton>
        </Stack>
      </Stack>
    </Box>
  );
};

export default TablePaginationCustom;
