<?php
function logMessage($message, $context = []) {

    $logfile = __DIR__ . '/../logs/trigger_test.log';
    $entry = "[" . date('Y-m-d H:i:s') . "] " . $message;

    if (!empty($context)) {
        $entry .= " | " . json_encode($context);
    }
    $entry .= "\n";
    
    file_put_contents($logfile, $entry, FILE_APPEND);
}
