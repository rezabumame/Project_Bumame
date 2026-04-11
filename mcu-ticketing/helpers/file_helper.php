<?php

function file_url($path) {
    if (empty($path)) return '#';
    $path = ltrim($path, '/');
    if (strpos($path, 'uploads/') !== 0) {
        $path = 'uploads/' . $path;
    }
    return 'index.php?page=download_file&path=' . urlencode($path);
}
