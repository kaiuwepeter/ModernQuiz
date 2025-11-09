<?php
header('Content-Type: application/json');
echo json_encode([
    'status' => 'ok',
    'message' => 'Public root works!',
    'document_root' => $_SERVER['DOCUMENT_ROOT'],
    'script_filename' => $_SERVER['SCRIPT_FILENAME'],
    'current_dir' => __DIR__
]);
