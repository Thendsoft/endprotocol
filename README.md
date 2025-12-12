# EndProtocol: Open Source File Sharing Base

## 🚀 Overview

**EndProtocol** is a minimalist, secure, and highly optimized open-source foundation for building modern file-sharing services. Designed for speed and compatibility, it provides a robust PHP backend API and a clean JavaScript/HTML frontend for simple drag-and-drop, click-to-upload, and clipboard pasting functionality.

This project serves as a starting point—a *protocol base*—that any developer can fork, extend, and adapt to create their own branded file hosting platform. The core focus is on security, simplicity, and performance, ensuring that the service runs smoothly even on low-spec hardware.

Initial concept and development by [Thendsoft](https://thendsoft.su).

## ✨ Features

* **Minimalist & Performant UI:** A clean, dark-mode interface built with minimal CSS for fast loading times and compatibility across devices.
* **Drag & Drop Support:** Intuitive file selection using the visual drop zone.
* **Clipboard Paste Functionality:** Easily upload screenshots or copied images directly from the clipboard (`Ctrl` + `V`).
* **PHP Backend API (`upload.php`):** Handles file validation, unique naming, and secure storage.
* **Security Focused:** Automatic creation of a `.htaccess` file in the upload directory to prevent script execution (critical security layer).
* **Progress Tracking:** Real-time upload progress bar using XMLHttpRequest.
* **Standardized JSON Responses:** Easy integration with other services and applications.
* **Auto-Copy on Image Upload:** Automatically copies the URL to the clipboard after uploading a screenshot.

## ⚙️ Installation and Setup

EndProtocol is designed to be easily deployed on any standard web server running PHP.

### Prerequisites

1.  **Web Server:** Apache, Nginx, or similar.
2.  **PHP:** Version 7.4 or higher (PHP 8+ recommended).
    * Ensure the `fileinfo` PHP extension is enabled (required for `mime_content_type` used for security/typing).
    * Ensure the server has sufficient permissions to write/create directories.

### Step-by-Step Guide

1.  **Clone the Repository (or Download):**
    ```bash
    git clone 
    cd endprotocol
    ```

2.  **Upload Files:**
    Transfer the following two core files and the styles to your server's public directory (e.g., `public_html` or `/var/www/html/`):
    * `index.php` (The Frontend)
    * `upload.php` (The Backend API)

3.  **Permissions Check (Crucial!):**
    The `upload.php` script attempts to create an `files/` directory automatically. Ensure your web server process (e.g., `www-data` on Linux) has **write access** to the directory where `upload.php` resides.

4.  **Security Initialization:**
    The first time a file is uploaded, `upload.php` will create the `files/` directory and immediately place a highly restrictive `.htaccess` file inside it. **Verify that this `.htaccess` file exists and contains rules to disable PHP/CGI execution.** This is your primary defense against malicious uploads.

5.  **Access the Service:**
    Navigate to the URL where you uploaded `index.php` in your web browser. You should see the drag-and-drop interface ready for uploads.

## 🔧 Configuration (in `upload.php`)

The main settings for the backend are located at the top of `upload.php`. You can easily adjust them:

| Variable | Description | Default Value |
| :--- | :--- | :--- |
| `$uploadDir` | Relative path where files will be stored. | `'files/'` |
| `$allowedExtensions` | Array of file extensions allowed for upload. | `['jpg', 'jpeg', 'png', 'gif', ...]` |
| `$blockedExtensions` | Array of extensions strictly forbidden (e.g., all scripting files). **Do not remove these.** | `['php', 'exe', 'sh', 'bat', ...]` |
| `$maxFileSize` | Maximum size allowed for a single file upload. | `100 * 1024 * 1024` (100 MB) |

## 📜 License

This project is released under the **TPL - ThinkPublic License**.

This license encourages modification, distribution, and commercial use while requiring clear attribution to the original EndProtocol project and Thendsoft, ensuring the source remains open for the community. Please see the license file (if provided) for full details.
