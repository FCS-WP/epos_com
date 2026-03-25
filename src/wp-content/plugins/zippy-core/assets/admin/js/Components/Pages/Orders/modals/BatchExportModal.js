import React, { useState, useEffect, useRef } from 'react';
import {
    Dialog,
    DialogTitle,
    DialogContent,
    DialogActions,
    Button,
    LinearProgress,
    Box,
    Typography,
    Alert,
    IconButton
} from '@mui/material';
import CloseIcon from '@mui/icons-material/Close';
import CloudDownloadIcon from '@mui/icons-material/CloudDownload';
import { Api } from '../../../../api/admin';

const BatchExportModal = ({ open, onClose, filters }) => {
    const [status, setStatus] = useState('idle'); // idle, starting, processing, completed, error
    const [progress, setProgress] = useState(0);
    const [processedCount, setProcessedCount] = useState(0);
    const [totalCount, setTotalCount] = useState(0);
    const [error, setError] = useState(null);
    const [fileUrl, setFileUrl] = useState(null);
    
    const isCancelled = useRef(false);

    useEffect(() => {
        if (open) {
            startExport();
        } else {
            // Reset state when closing
            isCancelled.current = true;
            setStatus('idle');
            setProgress(0);
            setProcessedCount(0);
            setTotalCount(0);
            setError(null);
            setFileUrl(null);
        }
    }, [open]);

    const startExport = async () => {
        isCancelled.current = false;
        setStatus('starting');
        setError(null);

        try {
            // 1. Get total and init
            const startRes = await Api.exportStart({ filter: filters });
            if (startRes.error) throw new Error(startRes.error.message);
            if (startRes.data?.status === 'error') throw new Error(startRes.data.message);

            const result = startRes.data?.data || startRes.data;
            if (!result || !result.total_items) {
                throw new Error('Could not retrieve export initialization data.');
            }

            const { total_items, export_id, chunk_size } = result;
            setTotalCount(total_items);
            setStatus('processing');

            let currentOffset = 0;
            
            // 2. Loop chunks
            while (currentOffset < total_items) {
                if (isCancelled.current) return;

                const chunkRes = await Api.exportProcessChunk({
                    export_id,
                    offset: currentOffset,
                    limit: chunk_size,
                    filter: filters
                });

                if (chunkRes.error) throw new Error(chunkRes.error.message);
                if (chunkRes.data?.status === 'error') throw new Error(chunkRes.data.message);

                const chunkResult = chunkRes.data?.data || chunkRes.data;
                const { processed } = chunkResult;
                
                currentOffset += processed;
                
                setProcessedCount(currentOffset);
                setProgress(Math.round((currentOffset / total_items) * 100));
            }

            // 3. Finalize
            if (isCancelled.current) return;
            const finalRes = await Api.exportFinalize({ export_id });
            if (finalRes.error) throw new Error(finalRes.error.message);
            if (finalRes.data?.status === 'error') throw new Error(finalRes.data.message);

            const finalResult = finalRes.data?.data || finalRes.data;
            setFileUrl(finalResult.file_url);
            setStatus('completed');

        } catch (err) {
            console.error('Export Error:', err);
            setError(err.message || 'An error occurred during export.');
            setStatus('error');
        }
    };

    const handleClose = () => {
        if (status === 'processing' && !window.confirm('Closing this window will stop the export. Are you sure?')) {
            return;
        }
        isCancelled.current = true;
        onClose();
    };

    return (
        <Dialog open={open} onClose={handleClose} maxWidth="sm" fullWidth>
            <DialogTitle sx={{ m: 0, p: 2, display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                Order Export Progress (CSV)
                <IconButton onClick={handleClose}>
                    <CloseIcon />
                </IconButton>
            </DialogTitle>
            
            <DialogContent dividers>
                {status === 'error' && (
                    <Alert severity="error" sx={{ mb: 2 }}>{error}</Alert>
                )}

                {status === 'processing' && (
                    <Box sx={{ width: '100%', mt: 2 }}>
                        <Typography variant="body2" color="text.secondary" gutterBottom>
                            Processing orders (CSV): {processedCount} / {totalCount}
                        </Typography>
                        <LinearProgress variant="determinate" value={progress} sx={{ height: 10, borderRadius: 5 }} />
                        <Typography variant="h6" sx={{ mt: 1, textAlign: 'center' }}>
                            {progress}%
                        </Typography>
                        <Alert severity="warning" sx={{ mt: 2 }}>
                            Do not close this window until the process is finished.
                        </Alert>
                    </Box>
                )}

                {status === 'starting' && (
                    <Box sx={{ textAlign: 'center', p: 3 }}>
                        <Typography>Initializing CSV export session...</Typography>
                        <LinearProgress sx={{ mt: 2 }} />
                    </Box>
                )}

                {status === 'completed' && (
                    <Box sx={{ textAlign: 'center', p: 3 }}>
                        <Typography variant="h6" color="success.main" gutterBottom>
                            CSV Export Completed Successfully!
                        </Typography>
                        <Typography variant="body1" sx={{ mb: 3 }}>
                            Your file with {totalCount} orders is ready.
                        </Typography>
                        <Button 
                            variant="contained" 
                            color="success" 
                            startIcon={<CloudDownloadIcon />}
                            href={fileUrl}
                            target="_blank"
                            size="large"
                            sx={{
                                color: '#fff !important',
                                '& .MuiButton-startIcon': {
                                    color: '#fff !important'
                                },
                                '&:hover': {
                                    backgroundColor: 'success.dark',
                                    color: '#fff !important'
                                }
                            }}
                        >
                            Download CSV File
                        </Button>
                    </Box>
                )}
            </DialogContent>

            <DialogActions>
                <Button onClick={handleClose} color="inherit">
                    {status === 'completed' ? 'Close' : 'Cancel Export'}
                </Button>
            </DialogActions>
        </Dialog>
    );
};

export default BatchExportModal;
