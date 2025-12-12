<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EndProtocol - Open Source File Share</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-dark: #1e1e1e; /* Dark theme base */
            --accent: #007bff; /* Standard blue for links/actions*/
            --accent-glow: rgba(0, 123, 255, 0.4);
            --card-bg: #2d2d2d;
            --border-color: #444;
            --text-main: #e0e0e0;
            --text-sec: #999;
            --success-color: #28a745;
            --error-color: #dc3545;
        }

        /* Base Reset */
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Outfit', sans-serif; }

        body {
            background-color: var(--bg-dark);
            height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: var(--text-main);
            padding: 20px;
        }

        /* --- UI Card --- */
        .card {
            width: 90%;
            max-width: 500px;
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.4);
            text-align: center;
            z-index: 10;
        }

        /* Branding for the open protocol */
        .logo {
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 5px;
        }
        .logo span { color: var(--accent); } /* Accent for the 'end' part */
        .tagline { 
            color: var(--text-sec); 
            font-size: 0.85rem; 
            margin-bottom: 25px; 
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* --- Drop Zone --- */
        .drop-zone {
            border: 2px dashed var(--border-color);
            border-radius: 6px;
            padding: 30px 20px;
            transition: all 0.2s;
            background: rgba(0,0,0,0.1);
            cursor: pointer;
            user-select: none;
        }

        .drop-zone:hover { border-color: #666; background: rgba(0,0,0,0.2); }
        
        .drop-zone.active {
            border-color: var(--accent);
            background: rgba(0, 123, 255, 0.1);
        }

        .icon-box {
            width: 40px;
            height: 40px;
            margin: 0 auto 15px;
            fill: var(--text-sec);
            transition: fill 0.3s;
        }
        
        .drop-zone.active .icon-box { fill: var(--accent); }

        .hint-primary { font-size: 1rem; margin-bottom: 5px; font-weight: 500; }
        .hint-secondary { font-size: 0.8rem; color: var(--text-sec); }
        .hint-secondary kbd {
            background: rgba(255,255,255,0.1);
            padding: 1px 4px;
            border-radius: 3px;
            font-family: monospace;
            border: 1px solid rgba(255,255,255,0.05);
            font-size: 0.75rem;
        }

        /* --- Progress Bar --- */
        .progress-wrap {
            height: 5px;
            background: #3a3a3a;
            border-radius: 3px;
            margin-top: 20px;
            overflow: hidden;
            opacity: 0;
            transition: opacity 0.3s;
        }
        
        .progress-fill {
            height: 100%;
            width: 0%;
            background: var(--accent);
            transition: width 0.1s linear;
        }

        /* --- Result Area (Copy Link) --- */
        .result-area {
            margin-top: 20px;
            display: none; /* Hidden by default */
            animation: fadeIn 0.4s ease forwards;
        }

        .link-group {
            display: flex;
            background: #3a3a3a;
            border: 1px solid #4a4a4a;
            border-radius: 6px;
            overflow: hidden;
            cursor: pointer;
        }

        .link-group:hover { border-color: #6a6a6a; }
        .link-group:active { border-color: var(--accent); }

        .link-input {
            background: transparent;
            border: none;
            color: var(--text-main);
            padding: 12px;
            width: 100%;
            font-family: 'Consolas', monospace;
            font-size: 0.9rem;
            outline: none;
            cursor: pointer;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .copy-btn {
            background: #4a4a4a;
            border: none;
            color: #fff;
            padding: 0 15px;
            cursor: pointer;
            font-weight: 500;
            transition: background 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            border-left: 1px solid #5a5a5a;
        }
        
        .link-group:hover .copy-btn { background: #5a5a5a; }

        .copy-hint {
            margin-top: 8px;
            font-size: 0.75rem;
            color: var(--text-sec);
        }

        /* --- Footer (Open Source specific) --- */
        .footer {
            margin-top: 30px;
            font-size: 0.8rem;
            color: #666;
        }
        .footer a { 
            color: var(--accent); 
            text-decoration: none; 
            transition: color 0.3s; 
            font-weight: 500;
        }
        .footer a:hover { color: #88aaff; }

        /* --- Toasts (Notifications) --- */
        .toast-container {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 100;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .toast {
            background: #333;
            border-left: 4px solid var(--accent);
            color: white;
            padding: 12px 20px;
            border-radius: 4px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.3);
            font-size: 0.85rem;
            transform: translateX(120%);
            transition: transform 0.3s ease-out;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .toast.show { transform: translateX(0); }
        .toast.success { border-color: var(--success-color); }
        .toast.error { border-color: var(--error-color); }

        /* --- Animations --- */
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

        /* Mobile Adjustments */
        @media (max-width: 600px) {
            .card { padding: 20px; }
            .toast-container { right: 10px; bottom: 10px; }
        }
    </style>
</head>
<body>

    <div class="card">
        <div class="logo">End<span>Protocol</span></div>
        <div class="tagline">An Open Source File Sharing Base (by Thendsoft)</div>

        <div class="drop-zone" id="dropZone">
            <svg class="icon-box" viewBox="0 0 24 24">
                <path d="M19.35 10.04C18.67 6.59 15.64 4 12 4 9.11 4 6.6 5.64 5.35 8.04 2.34 8.36 0 10.91 0 14c0 3.31 2.69 6 6 6h13c2.76 0 5-2.24 5-5 0-2.64-2.05-4.78-4.65-4.96zM14 13v4h-4v-4H7l5-5 5 5h-3z"/>
            </svg>
            <div class="hint-primary">Drag & Drop or Click to Upload</div>
            <div class="hint-secondary">Paste from clipboard using <kbd>Ctrl</kbd> + <kbd>V</kbd></div>
        </div>
        <input type="file" id="fileInput" hidden>

        <div class="progress-wrap" id="progressWrap">
            <div class="progress-fill" id="progressFill"></div>
        </div>

        <div class="result-area" id="resultArea">
            <div class="link-group" id="linkGroup">
                <input type="text" class="link-input" id="urlInput" readonly value="Your link will appear here...">
                <button class="copy-btn">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                </button>
            </div>
            <div class="copy-hint">Click the link box to copy the URL.</div>
        </div>
    </div>

    <div class="footer">
        Based on EndProtocol &bull; Initial code by <a href="https://thendsoft.su" target="_blank">Thendsoft</a> &copy; <?php echo date('Y'); ?>
    </div>

    <div class="toast-container" id="toastContainer"></div>

    <script>
        // --- DOM Elements ---
        const dropZone = document.getElementById('dropZone');
        const fileInput = document.getElementById('fileInput');
        const progressWrap = document.getElementById('progressWrap');
        const progressFill = document.getElementById('progressFill');
        const resultArea = document.getElementById('resultArea');
        const urlInput = document.getElementById('urlInput');
        const linkGroup = document.getElementById('linkGroup');

        // --- 1. Toast Notification System ---
        function showToast(message, type = 'default') {
            const container = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            
            // Simple visual indicator for status
            let icon = '';
            if (type === 'success') icon = '✓';
            if (type === 'error') icon = '✕';
            
            toast.innerHTML = `<span><b>${icon}</b> ${message}</span>`;
            
            container.appendChild(toast);
            
            // Entrance animation
            setTimeout(() => toast.classList.add('show'), 10);

            // Auto-removal after 4 seconds
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 300); // Wait for transition
            }, 4000);
        }

        // --- 2. Drag & Drop Visuals and File Input Trigger ---
        dropZone.addEventListener('click', () => fileInput.click());

        // Add 'active' class on drag events
        ['dragover', 'dragenter'].forEach(evt => {
            dropZone.addEventListener(evt, (e) => {
                e.preventDefault();
                dropZone.classList.add('active');
            });
        });

        // Remove 'active' class on drag exit or drop
        ['dragleave', 'drop'].forEach(evt => {
            dropZone.addEventListener(evt, (e) => {
                dropZone.classList.remove('active');
            });
        });

        // Handle file drop event
        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            if (e.dataTransfer.files.length) uploadFile(e.dataTransfer.files[0]);
        });

        // Handle file selection from standard input
        fileInput.addEventListener('change', () => {
            if (fileInput.files.length) uploadFile(fileInput.files[0]);
        });

        // --- 3. Paste from Clipboard (Screenshots/Images) ---
        window.addEventListener('paste', (e) => {
            const items = (e.clipboardData || e.originalEvent.clipboardData).items;
            for (let item of items) {
                // Check if the pasted item is a file (usually an image/screenshot)
                if (item.kind === 'file') {
                    const blob = item.getAsFile();
                    // Check if it's an image. Skip if not.
                    if (blob.type.startsWith('image/')) {
                        // Create a synthetic File object for upload
                        const file = new File([blob], "pasted_image." + blob.type.split('/')[1], { type: blob.type });
                        uploadFile(file, true); // Pass 'true' for Base64 mode if needed, but for FormData we don't need it.
                    } else {
                        showToast('Pasted content is not a supported image.', 'error');
                    }
                }
            }
        });

        // --- 4. Upload Logic (XMLHttpRequest) ---
        function uploadFile(file) {
            // Reset UI state before new upload
            resultArea.style.display = 'none';
            progressWrap.style.opacity = '1';
            progressFill.style.width = '0%';
            
            showToast(`Uploading: ${file.name.substring(0, 30)}...`);

            const formData = new FormData();
            
            // Check if the file is a standard file or a pasted image (needs Base64 conversion for older server logic)
            // For simplicity and compatibility, we will always use the 'file' input for now.
            // Note: If you want to use the Base64 logic from upload.php, you need to modify this part to check file type
            // and use POST['base64_image'] for images.
            formData.append('file', file);


            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'upload.php', true); // Target the backend API

            // Handle progress bar update
            xhr.upload.onprogress = (e) => {
                if (e.lengthComputable) {
                    const percent = (e.loaded / e.total) * 100;
                    progressFill.style.width = percent + '%';
                }
            };

            // Handle response from the server
            xhr.onload = () => {
                progressWrap.style.opacity = '0'; // Hide progress bar on completion/error
                if (xhr.status === 200) {
                    try {
                        const res = JSON.parse(xhr.responseText);
                        if (res.status === 'success') {
                            handleSuccess(res);
                        } else {
                            showToast(`Upload failed: ${res.message}`, 'error');
                        }
                    } catch (e) {
                        showToast('Server response parsing error.', 'error');
                    }
                } else {
                    showToast(`Network error: HTTP status ${xhr.status}`, 'error');
                }
            };

            // Handle network connection issues
            xhr.onerror = () => showToast('Could not connect to the server.', 'error');
            xhr.send(formData);
        }

        // --- 5. Success & Copy Logic ---
        function handleSuccess(data) {
            showToast('File successfully uploaded!', 'success');
            
            // Display the URL
            resultArea.style.display = 'block';
            urlInput.value = data.url;

            // Auto-copy the link to clipboard if it's an image (common for screenshots)
            if (data.type && data.type.startsWith('image/')) {
                copyToClipboard(data.url, true); // true for silent copy
            }
        }

        // Trigger copy function when the link group is clicked
        linkGroup.addEventListener('click', () => {
            copyToClipboard(urlInput.value);
        });

        /**
         * Copies text to the clipboard using modern API.
         * @param {string} text The text to copy.
         * @param {boolean} silent If true, doesn't show a success toast.
         */
        async function copyToClipboard(text, silent = false) {
            try {
                // Use modern Clipboard API
                await navigator.clipboard.writeText(text);
                
                // Visual feedback
                urlInput.select();
                linkGroup.style.borderColor = varCss('--success-color'); // Green border
                setTimeout(() => linkGroup.style.borderColor = varCss('--border-color'), 500);

                if (!silent) showToast('Link copied to clipboard!', 'success');
            } catch (err) {
                // Fallback for older browsers (less reliable)
                urlInput.select();
                document.execCommand('copy');
                showToast('Link copied (fallback method)', 'success');
            }
        }

        /** Helper to get CSS variable value in JS **/
        function varCss(name) {
            return getComputedStyle(document.documentElement).getPropertyValue(name).trim();
        }
    </script>
</body>
</html>
