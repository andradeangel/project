<?php
    date_default_timezone_set('America/La_Paz');
    function log_activity($message, $level = 'info') {
        $log_file = __DIR__ . '/../logs/app_' . date('Y-m-d') . '.log';
        $timestamp = date('Y-m-d H:i:s');
        $log_message = "[$timestamp] [$level] $message" . PHP_EOL;
        file_put_contents($log_file, $log_message, FILE_APPEND);
    }
?>