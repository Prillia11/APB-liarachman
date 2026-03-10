<?php
// Vercel PHP Router
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Base directory is the project root
$base_dir = dirname(__DIR__);

// Special case for root
if ($uri === '/' || $uri === '/index.php') {
    $file = $base_dir . '/index.php';
} else {
    // Determine the absolute path of the requested file
    $file = $base_dir . $uri;
    
    // Support clean URLs (append .php if it exists)
    if (!file_exists($file) && file_exists($file . '.php')) {
        $file .= '.php';
    }
}

// Security: Prevent directory traversal outside base_dir
$real_base = realpath($base_dir);
$real_file = realpath($file);

if ($real_file && strpos($real_file, $real_base) === 0 && is_file($real_file)) {
    $ext = pathinfo($real_file, PATHINFO_EXTENSION);
    if ($ext === 'php') {
        // Set up environment for traditional PHP applications
        $_SERVER['SCRIPT_FILENAME'] = $real_file;
        $_SERVER['SCRIPT_NAME'] = $uri;
        $_SERVER['PHP_SELF'] = $uri;
        
        // Change working directory so relative includes work
        chdir(dirname($real_file));
        
        // Execute the script
        require basename($real_file);
        exit;
    } else {
        // Fallback for static files if Vercel somehow didn't serve them
        $mime_types = [
            'css' => 'text/css',
            'js'  => 'application/javascript',
            'jpg' => 'image/jpeg',
            'jpeg'=> 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml'
        ];
        if (isset($mime_types[$ext])) {
            header('Content-Type: ' . $mime_types[$ext]);
        }
        readfile($real_file);
        exit;
    }
} else {
    http_response_code(404);
    echo "404 Not Found";
    exit;
}
