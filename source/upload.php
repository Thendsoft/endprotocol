<?php
/**
 * EndProtocol File Uploader Backend
 *
 * This script serves as the core API endpoint for file uploads
 * supporting both standard file uploads and Base64 image pasting.
 *
 * Original Developer: Thendsoft (thendsoft.su)
 * License: ThinkPublic License (TPL)
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Allow all domains to interact with the API

// --- Configuration ---
$uploadDir = 'files/';
// Common allowed file types (for security and compatibility)
$allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'mp4', 'mp3', 'zip', 'rar', 'pdf', 'txt', 'doc', 'docx', 'xlsx', 'pptx'];
// Strictly blocked extensions to prevent remote code execution
$blockedExtensions = ['php', 'php5', 'phtml', 'exe', 'sh', 'bat', 'js', 'html', 'htm', 'vbs', 'scr'];
$maxFileSize = 100 * 1024 * 1024; // 100MB limit

// --- Base URL Setup ---
$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http");
$serverHost = $_SERVER['HTTP_HOST'];
// Calculate the base directory path (relative to the server root)
$basePath = dirname($_SERVER['REQUEST_URI']) . '/files/';
$baseUrl = $protocol . "://" . $serverHost . $basePath;


// --- Directory and Security Setup ---
if (!is_dir($uploadDir)) {
    // Create upload directory if it doesn't exist
    mkdir($uploadDir, 0777, true);
    
    // Create a robust .htaccess file to prevent script execution inside 'files/' directory
    // This is a CRITICAL security measure for any public file hosting!
    $htaccessContent = "
<Files *>
  SetHandler none
  SetType application/octet-stream
</Files>
RemoveHandler .php .phtml .php3 .php4 .php5 .phps .cgi .pl .py .asp .aspx .shtml .shtm .js .vbs
RemoveType .php .phtml .php3 .php4 .php5 .phps .cgi .pl .py .asp .aspx .shtml .shtm .js .vbs
php_flag engine off
Options -ExecCGI
";
    file_put_contents($uploadDir . '.htaccess', trim($htaccessContent));
}


// --- Helper Functions ---

/**
 * Generates a random alphanumeric string for file renaming.
 * @param int $length The desired length of the string.
 * @return string The random string.
 */
function generateRandomString($length = 12) {
    return substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyz'), 0, $length);
}

/**
 * Standardized function for sending JSON responses and exiting.
 * @param string $status 'success' or 'error'.
 * @param string $message User-friendly message.
 * @param array $data Additional data (e.g., URL).
 */
function sendResponse($status, $message, $data = []) {
    echo json_encode(array_merge(['status' => $status, 'message' => $message], $data));
    exit;
}


// --- Main Upload Logic ---
try {
    // 1. Handle Standard File Upload (via FormData / $_FILES)
    if (!empty($_FILES['file']['name'])) {
        $file = $_FILES['file'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        // Security Check 1: Blocked Extensions
        if (in_array($ext, $blockedExtensions)) {
            sendResponse('error', 'The file type is strictly forbidden for security reasons.');
        }

        // Check if extension is one of the allowed types (optional, but good practice)
        if (!in_array($ext, $allowedExtensions)) {
            // NOTE: You might want to allow ALL types if this is a general file host,
            // but for a strict protocol base, keeping a list is safer.
            // For now, only check blocked, but you can uncomment this check if needed:
            // sendResponse('error', 'This file extension is not currently supported by the protocol.');
        }

        // Security Check 2: File Size Limit
        if ($file['size'] > $maxFileSize) {
            sendResponse('error', 'File size exceeds the ' . ($maxFileSize / 1024 / 1024) . 'MB limit.');
        }

        // Generate a new, unique filename
        $newName = generateRandomString() . '.' . $ext;
        $destination = $uploadDir . $newName;

        // Attempt to move the uploaded file
        if (move_uploaded_file($file['tmp_name'], $destination)) {
            // Get the actual MIME type of the file after upload (more reliable than client-side data)
            $mimeType = mime_content_type($destination);

            sendResponse('success', 'File successfully uploaded.', [
                'url' => $baseUrl . $newName,
                'type' => $mimeType
            ]);
        } else {
            // Handle move_uploaded_file failure (permissions, disk space, etc.)
            sendResponse('error', 'Failed to move the uploaded file on the server.');
        }
    } 
    
    // 2. Handle Base64 Image Upload (for clipboard pasting)
    elseif (!empty($_POST['base64_image'])) {
        $data = $_POST['base64_image'];
        
        // Use regex to extract the file extension from the Base64 header
        if (preg_match('/^data:image\/(\w+);base64,/', $data, $type)) {
            $ext = strtolower($type[1]);
            
            // Only allow common image formats for Base64 paste
            $allowedBase64 = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            if (!in_array($ext, $allowedBase64)) {
                 sendResponse('error', 'Invalid image format for Base64 paste.');
            }

            // Decode the Base64 string (remove the header part)
            $data = base64_decode(substr($data, strpos($data, ',') + 1));
            
            // Check file size limit for Base64 content
            if (strlen($data) > $maxFileSize) {
                sendResponse('error', 'Base64 data exceeds the ' . ($maxFileSize / 1024 / 1024) . 'MB limit.');
            }

            $newName = generateRandomString() . '.' . $ext;
            $destination = $uploadDir . $newName;

            // Save the decoded content to the file system
            if (file_put_contents($destination, $data)) {
                sendResponse('success', 'Screenshot successfully uploaded.', [
                    'url' => $baseUrl . $newName,
                    'type' => 'image/' . $ext
                ]);
            } else {
                sendResponse('error', 'Failed to save Base64 file on the server.');
            }
        } else {
            sendResponse('error', 'Malformed Base64 data received.');
        }
    } 
    
    // 3. No File/Data Received
    else {
        sendResponse('error', 'No file or valid data was received for upload.');
    }

} catch (Exception $e) {
    // Catch any unexpected PHP runtime exceptions
    sendResponse('error', 'Internal Server Error: ' . $e->getMessage());
}
?>
