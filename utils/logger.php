<?php
    date_default_timezone_set('America/La_Paz');
    
    function log_activity($message) {
        $date = date('Y-m-d H:i:s');
        $log_file = __DIR__ . '/../logs/activity_' . date('Y-m-d') . '.log';
        $ip = $_SERVER['REMOTE_ADDR'];
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        
        // Crear directorio logs si no existe
        if (!file_exists(__DIR__ . '/../logs')) {
            mkdir(__DIR__ . '/../logs', 0777, true);
        }
        
        // Formatear el mensaje del log
        $log_message = "[{$date}] IP: {$ip} | {$message} | Agent: {$user_agent}\n";
        
        // Escribir en el archivo de log
        file_put_contents($log_file, $log_message, FILE_APPEND);
    }
?>