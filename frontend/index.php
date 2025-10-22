<?php
// Admin config page with invisible button and modal configuration form
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Admin config</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        body {
            background-color: #f8f9fa;
            padding: 20px;
        }
        #invisibleButton {
            position: fixed;
            top: 10px;
            right: 10px;
            width: 50px;
            height: 50px;
            background: transparent;
            cursor: pointer;
            z-index: 10000;
        }
        #chatContainer {
            max-width: 1500px;
            margin: 40px auto 0 auto;
            border: 1px solid #ccc;
            border-radius: 8px;
            background: white;
            padding: 20px;
            display: none;
            flex-direction: column;
            height: 650px;
        }
        #chatMessages {
            flex-grow: 1;
            overflow-y: auto;
            border: 1px solid #ddd;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 4px;
            background: #fefefe;
        }
        .btn-light-green {
            background-color: #28a745;
            border-color: #28a745;
            color: white;
        }

        .btn-light-green:hover {
            background-color: #218838;
            border-color: #1e7e34;
            color: white;
        }
        .text-green {
            color: #28a745;
        }
        .footer-green-bg {
            background-color: #28a745;
        }
        .message {
            margin-bottom: 10px;
            padding: 12px 16px;
            border-radius: 18px;
            max-width: 70%;
            width: fit-content;
            word-wrap: break-word;
            word-break: break-all;
            white-space: pre-wrap;
            animation: fadeIn 0.5s ease-in;
            position: relative;
        }

    .message.user {
        background: linear-gradient(135deg, <?php echo $button_color; ?> 0%, #0056b3 100%);
        color: black;
        margin-left: auto;
        text-align: right;
        box-shadow: 0 4px 12px rgba(0,123,255,0.3);
    }

    .message.bot {
        background: rgba(255, 255, 255, 0.9);
        color: #333;
        margin-right: auto;
        text-align: left;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .timestamp {
        font-size: 0.75rem;
        opacity: 0.7;
        margin-top: 5px;
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
        #tableSelection {
            max-height: 150px;
            overflow-y: auto;
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 4px;
        }
        #micBtn {
            background-color: transparent;
            border-color: transparent;
        }
        #micBtn.recording {
            background-color: transparent;
            border-color: transparent;
            color: white;
        }
        #refreshBtn {
            background: transparent;
            border: none;
            color: #6c757d;
        }
        #refreshBtn:hover {
            background: rgba(108, 117, 125, 0.1);
        }
        .loading {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .loading-dots {
            display: flex;
            gap: 4px;
        }
        .loading-dot {
            width: 8px;
            height: 8px;
            background: #007bff;
            border-radius: 50%;
            animation: loading 1.4s infinite ease-in-out;
        }
        .loading-dot:nth-child(2) { animation-delay: 0.2s; }
        .loading-dot:nth-child(3) { animation-delay: 0.4s; }
        @keyframes loading {
            0%, 60%, 100% { transform: translateY(0); }
            30% { transform: translateY(-10px); }
        }

        .hover-shadow {
            transition: box-shadow 0.3s ease;
        }

        .hover-shadow:hover {
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
        }

        .load-btn:hover {
            background-color: #218838 !important;
            border-color: #1e7e34 !important;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(40, 167, 69, 0.3);
            transition: all 0.3s ease;
        }

        .share-btn:hover {
            background-color: #5a6268 !important;
            border-color: #545b62 !important;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(108, 117, 125, 0.3);
            transition: all 0.3s ease;
        }

        .load-btn, .share-btn {
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .load-btn:active, .share-btn:active {
            transform: translateY(0);
        }

        .input-wrapper {
            position: relative;
            background-color: white;
            border-radius: 30px;
            padding: 3px;
            background: linear-gradient(90deg, 
                #e0e0e0 0%, 
                #e0e0e0 25%, 
                #28a745 50%, 
                #e0e0e0 75%, 
                #e0e0e0 100%
            );
            background-size: 200% 100%;
            animation: movingBorder 3s linear infinite;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        @keyframes movingBorder {
            0% {
                background-position: 0% 0%;
            }
            100% {
                background-position: 200% 0%;
            }
        }

        .input-inner {
            position: relative;
            background-color: white;
            border-radius: 27px;
            overflow: hidden;
        }

        .floating-label {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            background-color: white;
            padding: 0 5px;
            color: #999;
            pointer-events: none;
            transition: all 0.3s ease;
            font-size: 16px;
            z-index: 3;
        }

        #usernameInput:focus {
            outline: none;
            box-shadow: none !important;
        }

        /* Float label when input is focused or has value */
        #usernameInput:focus ~ .floating-label,
        #usernameInput:not(:placeholder-shown) ~ .floating-label {
            top: -1px;
            font-size: 0px;
            color: #28a745;
            font-weight: 400;
        }

        /* Pause border animation and turn fully green when focused */
        .input-wrapper:focus-within {
            animation-play-state: paused;
            background: linear-gradient(90deg, 
                #28a745 0%, 
                #28a745 100%
            );
        }

        .chat-input-wrapper {
      position: relative;
      width: 100%;
    }

    .chat-input-wrapper input {
      padding-right: 100px; /* space for buttons */
      border-radius: 50px;
      height: 45px;
    }

    .chat-input-wrapper .mic-btn,
    .chat-input-wrapper .send-btn {
      position: absolute;
      top: 50%;
      transform: translateY(-50%);
      border: none;
      width: 70px;
      height: 40px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      transition: 0.3s;
      font-size: 1.1rem;
    }

    /* Mic button */
    .chat-input-wrapper .mic-btn {
      right: 55px;
      background-color: #e9ecef;
      color: #6c757d;
    }

    .chat-input-wrapper .mic-btn:hover {
      background-color: #28a745;
      color: #fff;
    }

    /* Send button */
    .chat-input-wrapper .send-btn {
      right: 10px;
      background-color: #28a745;
      color: #fff;
      font-size: 1.2rem;
    }

    .chat-input-wrapper .send-btn:hover {
      background-color: #218838;
    }

    /* Optional: input focus glow */
    .chat-input-wrapper input:focus {
      box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
      border-color: #28a745;
    }

    </style>
</head>
<body>
    <div class="d-flex align-items-center">
        <img src="public/images/logo.png" alt="Logo" class="me-2 ms-2" style="height: 40px;">    
        <h5 class="mb-0 fw-bold">SmartCard AI</h5>
    </div>
    <div style="border-bottom: 2px solid #28a745; width: 100%;"></div>
    <div id="invisibleButton" title="Click 15 times to configure chatbot"></div>
    <div class="text-center mb-4 mb-md-5 px-2 px-md-0">
        <h1 id="mainH1" class="display-3 display-md-1 fw-bold mb-4 mx-md-5 lh-base mt-4 mt-md-5">Generative BI Chatbot</h1>
        <span id="mainSpan" class="text-green display-3 display-md-1 fw-bold mx-md-3 lh-base d-block">SmartCard AI</span>
        <h2 id="mainH2" class="text-muted fs-6 fs-sm-5">Build Business Intelligence Chatbots within Few Minutes</h2>
        <!-- <p class="text-muted fs-6 fs-sm-5">Leverage Generative AI to Develop Business Intelligence Chatbots that Seamlessly Integrate with Your Data Sources.</p> -->
    </div>
    <div id="adminConfigSection">
        <div class="text-center mb-5">
            <h2 class="fs-3 fs-md-2 fw-bold text-dark">Admin Config</h2>
            <h3 class="fs-6 fs-md-7 text-secondary mt-1">Configure Your Chatbots</h3>
        </div>
        <div class="d-flex justify-content-center align-items-center mb-3" style="gap: 5px;">
            <div class="input-wrapper" style="max-width: 600px; width: 80%;">
                <div class="input-inner" style="box-shadow: 0 2px 8px rgba(40,167,69,0.3); border-radius: 27px;">
                    <input 
                        type="text" 
                        id="usernameInput" 
                        class="form-control" 
                        placeholder=" " 
                        style="border-radius: 27px; padding: 15px 20px; padding-top: 20px; position: relative; z-index: 2; width: 100%; border: none; background-color: white; height: 48px;" 
                    />
                    <label for="usernameInput" class="floating-label">Admin Username</label>
                </div>
            </div>
            <button 
                id="viewChatbotsBtn" 
                class="btn" 
                style="background: #28a745; color: white; border-radius: 30px; padding: 12px 35px; font-weight: 500; border: none; box-shadow: 0 2px 8px rgba(40,167,69,0.3); height: 51px; display: flex; align-items: center;">
                See Chatbots
            </button>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="configModal" tabindex="-1" aria-labelledby="configModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content" style="border: 2px solid #28a745; border-radius: 15px; box-shadow: 0 8px 32px rgba(40, 167, 69, 0.3);">
          <div class="modal-header" style="background: linear-gradient(90deg, #28a745 0%, #218838 100%); color: white; border-bottom: 2px solid #28a745;">
            <h5 class="modal-title" id="configModalLabel">Configure Chatbot</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body" style="padding: 20px;">
            <form id="configForm">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="username" class="form-label" style="color: #28a745; font-weight: 600;">Username</label>
                        <input type="text" placeholder="Username" class="form-control" id="username" required style="border: 2px solid #28a745; border-radius: 10px; padding: 5px;" />
                    </div>
                    <div class="col-md-6">
                        <label for="chatbotName" class="form-label" style="color: #28a745; font-weight: 600;">Chatbot Name</label>
                        <input type="text" placeholder="Chatbot Name" class="form-control" id="chatbotName" required style="border: 2px solid #28a745; border-radius: 10px; padding: 5px;" />
                    </div>
                </div>
                <div class="mb-3">
                    <!-- Company Logo Section -->
                    <label class="form-label" style="color: #28a745; font-weight: 600;">Company Logo</label>
                    <div id="logoPreview" style="width: 100%; height: 100px; border: 2px dashed #28a745; border-radius: 10px; display: flex; align-items: center; justify-content: center; background: rgba(40, 167, 69, 0.1); overflow: hidden; margin-bottom: 10px;">
                        <img id="logoImage" src="" alt="Logo Preview" style="max-width: 100%; max-height: 100%; object-fit: contain; display: none;" />
                        <span id="logoPlaceholder" style="color: #28a745; font-weight: 600;">No logo selected</span>
                    </div>
                    <label for="companyLogo" class="btn" style="background: #28a745; color: white; border: none; border-radius: 10px; padding: 5px 15px; font-weight: 600; cursor: pointer;">Upload Company Logo</label>
                    <input type="file" id="companyLogo" accept="image/*" style="display: none;" />
                </div>
                <div class="row mb-3">
                    <div class="col-md-8">
                        <label for="chatbotId" class="form-label" style="color: #28a745; font-weight: 600;">Chatbot ID</label>
                        <input type="text" placeholder="Chatbot ID" class="form-control" id="chatbotId" readonly style="border: 2px solid #28a745; border-radius: 10px; padding: 5px;" />
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="button" class="btn w-100" id="generateIdBtn" style="background: #28a745; color: white; border: none; border-radius: 10px; padding: 5px;">Generate ID</button>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="dataSource" class="form-label" style="color: #28a745; font-weight: 600;">Data Source</label>
                        <select class="form-select" id="dataSource" required style="border: 2px solid #28a745; border-radius: 10px; padding: 5px;">
                            <option selected>None</option>
                            <option value="google_sheets">Google Sheets</option>
                            <option value="mysql">MySQL</option>
                            <option value="postgresql">PostgreSQL</option>
                            <option value="mssql">MS SQL</option>
                            <option value="neo4j">Neo4j</option>
                            <option value="mongodb">MongoDB</option>
                            <option value="oracle">Oracle</option>
                            <option value="airtable">Airtable</option>
                            <option value="databricks">Databricks</option>
                            <option value="supabase">Supabase</option>
                            <option value="snowflake">Snowflake</option>
                            <option value="odoo">Odoo</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="geminiModel" class="form-label" style="color: #28a745; font-weight: 600;">Gemini Model</label>
                        <select class="form-select" id="geminiModel" required style="border: 2px solid #28a745; border-radius: 10px; padding: 5px;">
                            <option value="gemini-1.5-flash">gemini-1.5-flash</option>
                            <option value="gemini-1.5-pro">gemini-1.5-pro</option>
                            <option value="gemini-pro">gemini-pro</option>
                            <option value="gemini-2.0-flash" selected>gemini-2.0-flash</option>
                        </select>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="setusername" class="form-label" style="color: #28a745; font-weight: 600;">Set Username</label>
                        <input type="text" placeholder="Username" class="form-control" id="shared_username" required style="border: 2px solid #28a745; border-radius: 10px; padding: 5px;" />
                    </div>
                    <div class="col-md-6">
                        <label for="setpassword" class="form-label" style="color: #28a745; font-weight: 600;">Set Password</label>
                        <input type="password" placeholder="Password" class="form-control" id="shared_password" required style="border: 2px solid #28a745; border-radius: 10px; padding: 5px;" />
                    </div>
                </div>
                <div class="mb-3">
                    <label for="geminiApiKey" class="form-label" style="color: #28a745; font-weight: 600;">Gemini API Key</label>
                    <input type="password" placeholder="Gemini API Key" class="form-control" id="geminiApiKey" required style="border: 2px solid #28a745; border-radius: 10px; padding: 5px;" />
                </div>

                <!-- Credential fields container -->
                <div id="credentialFields"></div>

                <div class="mb-3" id="tableSelectionContainer" style="display:none;">
                    <label class="form-label" style="color: #28a745; font-weight: 600;">Select Tables / Sheets</label>
                    <div id="tableSelection"></div>
                </div>
                <button type="submit" class="btn" style="background: #28a745; color: white; border: none; border-radius: 10px; padding: 12px 25px; font-weight: 600; transition: background 0.3s;">Save & Configure</button>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- Share Modal -->
    <div class="modal fade" id="shareModal" tabindex="-1" aria-labelledby="shareModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="shareModalLabel">Share Chatbot</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <p>Share this link:</p>
            <input type="text" id="shareLinkInput" class="form-control" readonly />
            <button class="btn btn-primary mt-2" id="copyShareLinkBtn" style="background-color: #28a745; border-color: #28a745;">Copy Link</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Chatbots Grid Container -->
    <div id="chatbotsGridContainer" class="container mt-4" style="display: none;">
        <h3 class="text-left mb-4">My Saved Chatbots</h3>
        <div id="chatbotsGrid" class="row"></div>
    </div>

    <?php if (isset($_GET['share_key'])): ?>
    <!-- Chat Container -->
    <div id="chatContainer" class="d-flex flex-column mb-4" style="display: flex;">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 id="chatTitle" class="mb-0">User Interface</h4>
            <button id="refreshBtn" class="btn btn-secondary btn-sm">🔄 Refresh</button>
        </div>
        <div id="chatMessages"></div>
        <div class="chat-input-wrapper">
            <input type="text" id="userInput" class="form-control" placeholder="Ask your chatbot..." autocomplete="off">
            <button class="mic-btn" id="micBtn">🎙️</button>
            <button class="send-btn" id="sendBtn">Send</button>
        </div>
    </div>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const invisibleButton = document.getElementById('invisibleButton');
        const configModal = new bootstrap.Modal(document.getElementById('configModal'));
        const shareModal = new bootstrap.Modal(document.getElementById('shareModal'));
        const configForm = document.getElementById('configForm');
        const chatbotIdInput = document.getElementById('chatbotId');
        const generateIdBtn = document.getElementById('generateIdBtn');
        const viewChatbotsBtn = document.getElementById('viewChatbotsBtn');
        const dataSourceSelect = document.getElementById('dataSource');
        const credentialFieldsDiv = document.getElementById('credentialFields');
        const tableSelectionContainer = document.getElementById('tableSelectionContainer');
        const tableSelectionDiv = document.getElementById('tableSelection');
        const chatbotsGrid = document.getElementById('chatbotsGrid');
        const chatbotsGridContainer = document.getElementById('chatbotsGridContainer');
        const chatContainer = document.getElementById('chatContainer');
        const chatTitle = document.getElementById('chatTitle');
        const chatMessages = document.getElementById('chatMessages');
        const userInput = document.getElementById('userInput');
        const sendBtn = document.getElementById('sendBtn');
        const micBtn = document.getElementById('micBtn');
        const refreshBtn = document.getElementById('refreshBtn');

        const API_BASE = 'https://share-chatbot-2.onrender.com';
        // const API_BASE = 'http://localhost:5001';

        let clickCount = 0;
        let configured = false;
        let shareKey = null;
        let recognition;
        let isRecording = false;
        let isLoading = false;

        // Speech Recognition setup
        if ('webkitSpeechRecognition' in window || 'SpeechRecognition' in window) {
            const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
            recognition = new SpeechRecognition();
            recognition.continuous = false;
            recognition.interimResults = false;
            recognition.lang = 'en-US';
            recognition.onresult = (event) => {
                const transcript = event.results[0][0].transcript;
                userInput.value += transcript;
            };
            recognition.onend = () => {
                isRecording = false;
                micBtn.classList.remove('recording');
                micBtn.textContent = '🎙️';
            };
        } else {
            micBtn.disabled = true;
            micBtn.textContent = 'No Mic';
        }

        invisibleButton.addEventListener('click', () => {
            clickCount++;
            if(clickCount >= 15) {
                clickCount = 0;
                configModal.show();
            }
        });

        generateIdBtn.addEventListener('click', () => {
            chatbotIdInput.value = 'cb-' + Math.random().toString(36).substring(2, 10);
        });

        viewChatbotsBtn.addEventListener('click', () => {
            const username = usernameInput.value.trim();
            if (!username) {
                alert('Please enter a username.');
                return;
            }
            loadChatbots(username);
        });

        dataSourceSelect.addEventListener('change', () => {
            renderCredentialFields(dataSourceSelect.value);
            tableSelectionContainer.style.display = 'none';
            tableSelectionDiv.innerHTML = '';
        });

        function renderCredentialFields(dataSource) {
            credentialFieldsDiv.innerHTML = '';
            if(dataSource === 'google_sheets') {
                credentialFieldsDiv.innerHTML = `
                    <div class="mb-3">
                            <label for="sheetId" class="form-label">Google Sheet ID</label>
                            <input type="text" placeholder="Sheet ID" class="form-control" id="sheetId" required style="border: 2px solid #28a745; border-radius: 10px; padding: 5px;" />      
                    </div>
                    <div class="mb-3">
                        <label for="serviceAccountJson" class="form-label">Service Account JSON</label>
                        <textarea class="form-control" id="serviceAccountJson" rows="4" required style="border: 2px solid #28a745; border-radius: 10px; padding: 5px;"></textarea>
                    </div>
                    <button type="button" class="btn btn-secondary mb-3" id="loadTablesBtn" style="padding: 5px;">Load Tables</button>
                `;
                document.getElementById('loadTablesBtn').addEventListener('click', () => fetchTablesForDataSource(dataSource));
            } else if(dataSource === 'mysql') {
                credentialFieldsDiv.innerHTML = `
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="dbHost" class="form-label">DB Host</label>
                            <input type="text" placeholder="DB Host" class="form-control" id="dbHost" required style="border: 2px solid #28a745; border-radius: 10px; padding: 5px;" />
                        </div>
                        <div class="col-md-6">
                            <label for="dbPort" class="form-label">DB Port</label>
                            <input type="number" placeholder="DB Port" class="form-control" id="dbPort" required style="border: 2px solid #28a745; border-radius: 10px; padding: 5px;" />
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="dbUsername" class="form-label">DB Username</label>
                            <input type="text" placeholder="DB Username" class="form-control" id="dbUsername" required style="border: 2px solid #28a745; border-radius: 10px; padding: 5px;" />
                        </div>
                        <div class="col-md-6">
                            <label for="dbPassword" class="form-label">DB Password</label>
                            <input type="password" placeholder="DB Password" class="form-control" id="dbPassword" required style="border: 2px solid #28a745; border-radius: 10px; padding: 5px;" />
                        </div>
                    </div>
                    <div class="mb-3">
                            <label for="dbName" class="form-label">DB Name</label>
                            <input type="text" placeholder="DB Name" class="form-control" id="dbName" required style="border: 2px solid #28a745; border-radius: 10px; padding: 5px;" />
                    </div>
                    <button type="button" class="btn btn-secondary mb-3" id="loadTablesBtn" style="padding: 5px;">Load Tables</button>

                `;
                document.getElementById('loadTablesBtn').addEventListener('click', () => fetchTablesForDataSource(dataSource));
            } else if(dataSource === 'postgresql') {
                credentialFieldsDiv.innerHTML = `
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="dbHost" class="form-label">DB Host</label>
                            <input type="text" placeholder="DB Host" class="form-control" id="dbHost" required style="border: 2px solid #28a745; border-radius: 10px; padding: 5px;" />
                        </div>
                        <div class="col-md-6">
                            <label for="dbPort" class="form-label">DB Port</label>
                            <input type="number" placeholder="DB Port" class="form-control" id="dbPort" required style="border: 2px solid #28a745; border-radius: 10px; padding: 5px;" />
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="dbUsername" class="form-label">DB Username</label>
                            <input type="text" placeholder="DB Username" class="form-control" id="dbUsername" required style="border: 2px solid #28a745; border-radius: 10px; padding: 5px;" />
                        </div>
                        <div class="col-md-6">
                            <label for="dbPassword" class="form-label">DB Password</label>
                            <input type="password" placeholder="DB Password" class="form-control" id="dbPassword" required style="border: 2px solid #28a745; border-radius: 10px; padding: 5px;" />
                        </div>
                    </div>
                    <div class="mb-3">
                            <label for="dbName" class="form-label">DB Name</label>
                            <input type="text" placeholder="DB Name" class="form-control" id="dbName" required style="border: 2px solid #28a745; border-radius: 10px; padding: 5px;" />
                    </div>
                    <button type="button" class="btn btn-secondary mb-3" id="loadTablesBtn" style="padding: 5px;">Load Tables</button>

                `;
                document.getElementById('loadTablesBtn').addEventListener('click', () => fetchTablesForDataSource(dataSource));
            } else if(dataSource === 'mssql') {
                credentialFieldsDiv.innerHTML = `
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="dbHost" class="form-label">DB Host</label>
                            <input type="text" placeholder="DB Host" class="form-control" id="dbHost" required style="border: 2px solid #28a745; border-radius: 10px; padding: 5px;" />
                        </div>
                        <div class="col-md-6">
                            <label for="dbPort" class="form-label">DB Port</label>
                            <input type="number" placeholder="DB Port" class="form-control" id="dbPort" required style="border: 2px solid #28a745; border-radius: 10px; padding: 5px;" />
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="dbUsername" class="form-label">DB Username</label>
                            <input type="text" placeholder="DB Username" class="form-control" id="dbUsername" required style="border: 2px solid #28a745; border-radius: 10px; padding: 5px;" />
                        </div>
                        <div class="col-md-6">
                            <label for="dbPassword" class="form-label">DB Password</label>
                            <input type="password" placeholder="DB Password" class="form-control" id="dbPassword" required style="border: 2px solid #28a745; border-radius: 10px; padding: 5px;" />
                        </div>
                    </div>
                    <div class="mb-3">
                            <label for="dbName" class="form-label">DB Name</label>
                            <input type="text" placeholder="DB Name" class="form-control" id="dbName" required style="border: 2px solid #28a745; border-radius: 10px; padding: 5px;" /> 
                    </div>
                    <button type="button" class="btn btn-secondary mb-3" id="loadTablesBtn" style="padding: 5px;">Load Tables</button>

                `;
                document.getElementById('loadTablesBtn').addEventListener('click', () => fetchTablesForDataSource(dataSource));
            } else if(dataSource === 'neo4j') {
                credentialFieldsDiv.innerHTML = `
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="neo4jUri" class="form-label">Neo4j URI</label>
                            <input type="text" placeholder="Neo4j URI" class="form-control" id="neo4jUri" required style="border: 2px solid #28a745; border-radius: 10px; padding: 5px;" />
                        </div>
                        <div class="col-md-6">
                            <label for="neo4jUsername" class="form-label">Neo4j Username</label>
                            <input type="text" placeholder="Neo4j Username" class="form-control" id="neo4jUsername" required style="border: 2px solid #28a745; border-radius: 10px; padding: 5px;" />
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="neo4jPassword" class="form-label">Neo4j Password</label>
                            <input type="password" placeholder="Neo4j Password" class="form-control" id="neo4jPassword" required style="border: 2px solid #28a745; border-radius: 10px; padding: 5px;" />
                        </div>
                        <div class="col-md-6">
                            <label for="neo4jDbName" class="form-label">Neo4j Database Name</label>
                            <input type="text" placeholder="Neo4j Database Name" class="form-control" id="neo4jDbName" required style="border: 2px solid #28a745; border-radius: 10px; padding: 5px;" />
                        </div>
                    </div>
                    <button type="button" class="btn btn-secondary mb-3" id="loadTablesBtn" style="padding: 5px;">Load Tables</button>
                `;
                document.getElementById('loadTablesBtn').addEventListener('click', () => fetchTablesForDataSource(dataSource));
            } else if(dataSource === 'mongodb') {
                credentialFieldsDiv.innerHTML = `
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="mongoUri" class="form-label">MongoDB URI</label>
                            <input type="text" placeholder="MongoDB URI" class="form-control" id="mongoUri" required style="border: 2px solid #28a745; border-radius: 10px; padding: 5px;" />
                        </div>
                        <div class="col-md-6">
                            <label for="mongoDbName" class="form-label">MongoDB Database Name</label>
                            <input type="text" placeholder="MongoDB Database Name" class="form-control" id="mongoDbName" required style="border: 2px solid #28a745; border-radius: 10px; padding: 5px;" />
                        </div>
                    </div>
                        <button type="button" class="btn btn-secondary mb-3" id="loadTablesBtn" style="padding: 5px;">Load Tables</button>
                `;
                document.getElementById('loadTablesBtn').addEventListener('click', () => fetchTablesForDataSource(dataSource));
            } else if(dataSource === 'oracle') {
                credentialFieldsDiv.innerHTML = `
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="dbHost" class="form-label">DB Host</label>
                            <input type="text" placeholder="DB Host" class="form-control" id="dbHost" required style="border: 2px solid #28a745; border-radius: 10px; padding: 5px;" />
                        </div>
                        <div class="col-md-6">
                            <label for="dbPort" class="form-label">DB Port</label>
                            <input type="number" placeholder="DB Port" class="form-control" id="dbPort" required style="border: 2px solid #28a745; border-radius: 10px; padding: 5px;" />
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="dbUsername" class="form-label">DB Username</label>
                            <input type="text" placeholder="DB Username" class="form-control" id="dbUsername" required style="border: 2px solid #28a745; border-radius: 10px; padding: 5px;" />
                        </div>
                        <div class="col-md-6">
                            <label for="dbPassword" class="form-label">DB Password</label>
                            <input type="password" placeholder="DB Password" class="form-control" id="dbPassword" required style="border: 2px solid #28a745; border-radius: 10px; padding: 5px;" />
                        </div>
                    </div>
                    <div class="mb-3">
                            <label for="dbName" class="form-label">DB Name</label>
                            <input type="text" placeholder="DB Name" class="form-control" id="dbName" required style="border: 2px solid #28a745; border-radius: 10px; padding: 5px;" />
                    </div>
                    <button type="button" class="btn btn-secondary mb-3" id="loadTablesBtn" style="padding: 5px;">Load Tables</button>
                `;
                document.getElementById('loadTablesBtn').addEventListener('click', () => fetchTablesForDataSource(dataSource));
            } else if(dataSource === 'airtable') {
                credentialFieldsDiv.innerHTML = `
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="airtableApiKey" class="form-label">Airtable API Key</label>
                            <input type="password" placeholder="Airtable API Key" class="form-control" id="airtableApiKey" required style="border: 2px solid #28a745; border-radius: 10px; padding: 5px;" />
                        </div>
                        <div class="col-md-6">
                            <label for="airtableBaseId" class="form-label">Airtable Base ID</label>
                            <input type="text" placeholder="Airtable Base ID" class="form-control" id="airtableBaseId" required style="border: 2px solid #28a745; border-radius: 10px; padding: 5px;" />
                        </div>
                    </div>
                    <button type="button" class="btn btn-secondary mb-3" id="loadTablesBtn" style="padding: 5px;">Load Tables</button>
                `;
                document.getElementById('loadTablesBtn').addEventListener('click', () => fetchTablesForDataSource(dataSource));
            } else if(dataSource === 'databricks') {
                credentialFieldsDiv.innerHTML = `
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="databricksHostname" class="form-label">Databricks Hostname</label>
                            <input type="text" placeholder="Databricks Hostname" class="form-control" id="databricksHostname" required style="border: 2px solid #28a745; border-radius: 10px; padding: 5px;" />
                        </div>
                        <div class="col-md-6">
                            <label for="databricksHttpPath" class="form-label">Databricks HTTP Path</label>
                            <input type="text" placeholder="Databricks HTTP Path" class="form-control" id="databricksHttpPath" required style="border: 2px solid #28a745; border-radius: 10px; padding: 5px;" />
                        </div>
                    </div>
                    <div class="mb-3">
                            <label for="databricksToken" class="form-label">Databricks Token</label>
                            <input type="password" placeholder="Databricks Token" class="form-control" id="databricksToken" required style="border: 2px solid #28a745; border-radius: 10px; padding: 5px;" />
                    </div>
                    <button type="button" class="btn btn-secondary mb-3" id="loadTablesBtn" style="padding: 5px;">Load Tables</button>
                `;
                document.getElementById('loadTablesBtn').addEventListener('click', () => fetchTablesForDataSource(dataSource));
            } else if(dataSource === 'supabase') {
                credentialFieldsDiv.innerHTML = `
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="supabaseUrl" class="form-label">Supabase URL</label>
                            <input type="text" placeholder="Supabase URL" class="form-control" id="supabaseUrl" required style="border: 2px solid #28a745; border-radius: 10px; padding: 5px;" />
                        </div>
                        <div class="col-md-6">
                            <label for="supabaseAnonKey" class="form-label">Supabase Anon Key</label>
                            <input type="password" placeholder="Supabase Anon Key" class="form-control" id="supabaseAnonKey" required style="border: 2px solid #28a745; border-radius: 10px; padding: 5px;" />
                        </div>
                    </div>
                    <button type="button" class="btn btn-secondary mb-3" id="loadTablesBtn" style="padding: 5px;">Load Tables</button>
                `;
                document.getElementById('loadTablesBtn').addEventListener('click', () => fetchTablesForDataSource(dataSource));
            } else if(dataSource === 'snowflake') {
                credentialFieldsDiv.innerHTML = `
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="snowflakeAccount" class="form-label">Snowflake Account</label>
                            <input type="text" placeholder="Snowflake Account" class="form-control" id="snowflakeAccount" required style="border: 2px solid #28a745; border-radius: 10px; padding: 5px;" />
                        </div>
                        <div class="col-md-6">
                            <label for="snowflakeUser" class="form-label">Snowflake User</label>
                            <input type="text" placeholder="Snowflake User" class="form-control" id="snowflakeUser" required style="border: 2px solid #28a745; border-radius: 10px; padding: 5px;" />
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="snowflakePassword" class="form-label">Snowflake Password</label>
                            <input type="password" placeholder="Snowflake Password" class="form-control" id="snowflakePassword" required style="border: 2px solid #28a745; border-radius: 10px; padding: 5px;" />
                        </div>
                        <div class="col-md-6">
                            <label for="snowflakeWarehouse" class="form-label">Snowflake Warehouse</label>
                            <input type="text" placeholder="Snowflake Warehouse" class="form-control" id="snowflakeWarehouse" required style="border: 2px solid #28a745; border-radius: 10px; padding: 5px;" />
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="snowflakeDatabase" class="form-label">Snowflake Database</label>
                            <input type="text" placeholder="Snowflake Database" class="form-control" id="snowflakeDatabase" required style="border: 2px solid #28a745; border-radius: 10px; padding: 5px;" />
                        </div>
                        <div class="col-md-6">
                            <label for="snowflakeSchema" class="form-label">Snowflake Schema</label>
                            <input type="text" placeholder="Snowflake Schema" class="form-control" id="snowflakeSchema" required style="border: 2px solid #28a745; border-radius: 10px; padding: 5px;" />
                        </div>
                    </div>
                    <div class="mb-3">
                            <label for="snowflakeRole" class="form-label">Snowflake Role</label>
                            <input type="text" placeholder="Snowflake Role" class="form-control" id="snowflakeRole" required style="border: 2px solid #28a745; border-radius: 10px; padding: 5px;" />
                    </div>
                    <button type="button" class="btn btn-secondary mb-3" id="loadTablesBtn">Load Tables</button>
                `;
                document.getElementById('loadTablesBtn').addEventListener('click', () => fetchTablesForDataSource(dataSource));
            } else if(dataSource === 'odoo') {
                credentialFieldsDiv.innerHTML = `
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="odooUrl" class="form-label">Odoo URL</label>
                            <input type="text" placeholder="Odoo URL" class="form-control" id="odooUrl" required style="border: 2px solid #28a745; border-radius: 10px; padding: 5px;"/>
                        </div>
                        <div class="col-md-6">
                            <label for="odooDb" class="form-label">Odoo Database</label>
                            <input type="text" placeholder="Odoo Database" class="form-control" id="odooDb" required style="border: 2px solid #28a745; border-radius: 10px; padding: 5px;"/>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="odooUsername" class="form-label">Odoo Username</label>
                            <input type="text" placeholder="Odoo Username" class="form-control" id="odooUsername" required style="border: 2px solid #28a745; border-radius: 10px; padding: 5px;"/>
                        </div>
                        <div class="col-md-6">
                            <label for="odooPassword" class="form-label">Odoo Password</label>
                            <input type="password" placeholder="Odoo Password" class="form-control" id="odooPassword" required style="border: 2px solid #28a745; border-radius: 10px; padding: 5px;"/>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="selectedModule" class="form-label">Odoo Module</label>
                        <select class="form-select" placeholder="Odoo Module" id="selectedModule" required style="border: 2px solid #28a745; border-radius: 10px; padding: 5px;">
                            <option value="CRM">CRM</option>
                            <option value="Sales">Sales</option>
                            <option value="Inventry">Inventry</option>
                        </select>
                    </div>
                    <button type="button" class="btn btn-secondary mb-3" id="loadTablesBtn">Load Tables</button>
                `;
                document.getElementById('loadTablesBtn').addEventListener('click', () => fetchTablesForDataSource(dataSource));
                } else {
                    // For other data sources, you can add credential fields similarly
                    credentialFieldsDiv.innerHTML = `<p>No credential fields defined for ${dataSource} yet.</p>`;
                }
        }
                

        async function fetchTablesForDataSource(dataSource) {
            tableSelectionDiv.innerHTML = '';
            tableSelectionContainer.style.display = 'none';

            // Show loading
            tableSelectionDiv.innerHTML = 'Loading...';
            tableSelectionContainer.style.display = 'block';

            // Prepare form data for set_credentials endpoint
            const formData = new URLSearchParams();
            formData.append('data_source', dataSource);
            formData.append('gemini_api_key', ''); // Empty for now
            formData.append('gemini_model', ''); // Empty for now

            // Collect actual credentials from form fields
            if(dataSource === 'google_sheets') {
                formData.append('sheet_id', document.getElementById('sheetId').value);
                formData.append('service_account_json', document.getElementById('serviceAccountJson').value);
            } else if(dataSource === 'mysql' || dataSource === 'postgresql' || dataSource === 'mssql' || dataSource === 'oracle') {
                formData.append('db_host', document.getElementById('dbHost').value);
                formData.append('db_port', document.getElementById('dbPort').value);
                formData.append('db_username', document.getElementById('dbUsername').value);
                formData.append('db_password', document.getElementById('dbPassword').value);
                formData.append('db_name', document.getElementById('dbName').value);
            } else if(dataSource === 'neo4j') {
                formData.append('neo4j_uri', document.getElementById('neo4jUri').value);
                formData.append('neo4j_username', document.getElementById('neo4jUsername').value);
                formData.append('neo4j_password', document.getElementById('neo4jPassword').value);
                formData.append('neo4j_db_name', document.getElementById('neo4jDbName').value);
            } else if(dataSource === 'mongodb') {
                formData.append('mongo_uri', document.getElementById('mongoUri').value);
                formData.append('mongo_db_name', document.getElementById('mongoDbName').value);
            } else if(dataSource === 'airtable') {
                formData.append('airtable_api_key', document.getElementById('airtableApiKey').value);
                formData.append('airtable_base_id', document.getElementById('airtableBaseId').value);
            } else if(dataSource === 'databricks') {
                formData.append('databricks_hostname', document.getElementById('databricksHostname').value);
                formData.append('databricks_http_path', document.getElementById('databricksHttpPath').value);
                formData.append('databricks_token', document.getElementById('databricksToken').value);
            } else if(dataSource === 'supabase') {
                formData.append('supabase_url', document.getElementById('supabaseUrl').value);
                formData.append('supabase_anon_key', document.getElementById('supabaseAnonKey').value);
            } else if(dataSource === 'snowflake') {
                formData.append('snowflake_account', document.getElementById('snowflakeAccount').value);
                formData.append('snowflake_user', document.getElementById('snowflakeUser').value);
                formData.append('snowflake_password', document.getElementById('snowflakePassword').value);
                formData.append('snowflake_warehouse', document.getElementById('snowflakeWarehouse').value);
                formData.append('snowflake_database', document.getElementById('snowflakeDatabase').value);
                formData.append('snowflake_schema', document.getElementById('snowflakeSchema').value);
                formData.append('snowflake_role', document.getElementById('snowflakeRole').value);
            } else if(dataSource === 'odoo') {
                formData.append('odoo_url', document.getElementById('odooUrl').value);
                formData.append('odoo_db', document.getElementById('odooDb').value);
                formData.append('odoo_username', document.getElementById('odooUsername').value);
                formData.append('odoo_password', document.getElementById('odooPassword').value);
                formData.append('selected_module', document.getElementById('selectedModule').value);
            }

            try {
                const response = await fetch(`${API_BASE}/set_credentials`, {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: formData.toString()
                });
                if(!response.ok) {
                    tableSelectionDiv.innerHTML = 'Failed to load tables.';
                    return;
                }
                const data = await response.json();
                if(data.items && data.items.length > 0) {
                    tableSelectionDiv.innerHTML = '';
                    data.items.forEach(item => {
                        const div = document.createElement('div');
                        div.innerHTML = `<input type="checkbox" value="${item}" /> ${item}`;
                        tableSelectionDiv.appendChild(div);
                    });
                } else {
                    tableSelectionDiv.innerHTML = 'No tables found.';
                }
            } catch(e) {
                tableSelectionDiv.innerHTML = 'Error loading tables.';
            }
        }

        configForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const username = document.getElementById('username').value.trim();
            const chatbotName = document.getElementById('chatbotName').value.trim();
            const chatbotId = chatbotIdInput.value.trim();
            const dataSource = dataSourceSelect.value;
            const selectedTables = Array.from(tableSelectionDiv.querySelectorAll('input[type=checkbox]:checked')).map(cb => cb.value);
            const sharedUsername = document.getElementById('shared_username').value.trim();
            const sharedPassword = document.getElementById('shared_password').value.trim();

            if(!username || !chatbotName || !chatbotId) {
                alert('Please fill username, chatbot name, and generate ID.');
                return;
            }
            if(!sharedUsername || !sharedPassword) {
                alert('Please fill set username and password.');
                return;
            }

            const geminiApiKey = document.getElementById('geminiApiKey').value.trim();
            const geminiModel = document.getElementById('geminiModel').value;
            if(!geminiApiKey) {
                alert('Please enter Gemini API Key.');
                return;
            }
            if(selectedTables.length === 0) {
                alert('Please select at least one item.');
                return;
            }

            const formData = new FormData();
            formData.append('username', username);
            formData.append('chatbot_id', chatbotId);
            formData.append('chatbot_name', chatbotName);
            formData.append('data_source', dataSource);
            formData.append('gemini_api_key', geminiApiKey);
            formData.append('gemini_model', geminiModel);
            formData.append('selected_tables', JSON.stringify(selectedTables));
            formData.append('shared_username', sharedUsername);
            formData.append('shared_password', sharedPassword);

            // Handle logo upload
            const logoFile = document.getElementById('companyLogo').files[0];
            if (logoFile) {
                formData.append('company_logo', logoFile);
            }

            // Debug: Log formData contents
            console.log('FormData contents:');
            for (let [key, value] of formData.entries()) {
                console.log(key, value);
            }

            if(dataSource === 'google_sheets') {
                const sheetId = document.getElementById('sheetId').value.trim();
                const serviceAccountJson = document.getElementById('serviceAccountJson').value.trim();
                if(!sheetId || !serviceAccountJson) {
                    alert('Please enter Google Sheet ID and Service Account JSON.');
                    return;
                }
                formData.append('sheet_id', sheetId);
                formData.append('service_account_json', serviceAccountJson);
                formData.append('selected_sheets', JSON.stringify(selectedTables));
            } else if(dataSource === 'mysql' || dataSource === 'postgresql' || dataSource === 'mssql' || dataSource === 'oracle') {
                const dbHost = document.getElementById('dbHost').value.trim();
                const dbPort = document.getElementById('dbPort').value;
                const dbUsername = document.getElementById('dbUsername').value.trim();
                const dbPassword = document.getElementById('dbPassword').value.trim();
                const dbName = document.getElementById('dbName').value.trim();
                if(!dbHost || !dbPort || !dbUsername || !dbPassword || !dbName) {
                    alert('Please fill all database fields.');
                    return;
                }
                formData.append('db_host', dbHost);
                formData.append('db_port', dbPort);
                formData.append('db_username', dbUsername);
                formData.append('db_password', dbPassword);
                formData.append('db_name', dbName);
            } else if(dataSource === 'neo4j') {
                const neo4jUri = document.getElementById('neo4jUri').value.trim();
                const neo4jUsername = document.getElementById('neo4jUsername').value.trim();
                const neo4jPassword = document.getElementById('neo4jPassword').value.trim();
                const neo4jDbName = document.getElementById('neo4jDbName').value.trim();
                if(!neo4jUri || !neo4jUsername || !neo4jPassword || !neo4jDbName) {
                    alert('Please fill all Neo4j fields.');
                    return;
                }
                formData.append('neo4j_uri', neo4jUri);
                formData.append('neo4j_username', neo4jUsername);
                formData.append('neo4j_password', neo4jPassword);
                formData.append('neo4j_db_name', neo4jDbName);
            } else if(dataSource === 'mongodb') {
                const mongoUri = document.getElementById('mongoUri').value.trim();
                const mongoDbName = document.getElementById('mongoDbName').value.trim();
                if(!mongoUri || !mongoDbName) {
                    alert('Please fill all MongoDB fields.');
                    return;
                }
                formData.append('mongo_uri', mongoUri);
                formData.append('mongo_db_name', mongoDbName);
                formData.append('selected_collections', JSON.stringify(selectedTables));
            } else if(dataSource === 'airtable') {
                const airtableApiKey = document.getElementById('airtableApiKey').value.trim();
                const airtableBaseId = document.getElementById('airtableBaseId').value.trim();
                if(!airtableApiKey || !airtableBaseId) {
                    alert('Please fill all Airtable fields.');
                    return;
                }
                formData.append('airtable_api_key', airtableApiKey);
                formData.append('airtable_base_id', airtableBaseId);
            } else if(dataSource === 'databricks') {
                const databricksHostname = document.getElementById('databricksHostname').value.trim();
                const databricksHttpPath = document.getElementById('databricksHttpPath').value.trim();
                const databricksToken = document.getElementById('databricksToken').value.trim();
                if(!databricksHostname || !databricksHttpPath || !databricksToken) {
                    alert('Please fill all Databricks fields.');
                    return;
                }
                formData.append('databricks_hostname', databricksHostname);
                formData.append('databricks_http_path', databricksHttpPath);
                formData.append('databricks_token', databricksToken);
            } else if(dataSource === 'supabase') {
                const supabaseUrl = document.getElementById('supabaseUrl').value.trim();
                const supabaseAnonKey = document.getElementById('supabaseAnonKey').value.trim();
                if(!supabaseUrl || !supabaseAnonKey) {
                    alert('Please fill all Supabase fields.');
                    return;
                }
                formData.append('supabase_url', supabaseUrl);
                formData.append('supabase_anon_key', supabaseAnonKey);
            } else if(dataSource === 'snowflake') {
                const snowflakeAccount = document.getElementById('snowflakeAccount').value.trim();
                const snowflakeUser = document.getElementById('snowflakeUser').value.trim();
                const snowflakePassword = document.getElementById('snowflakePassword').value.trim();
                const snowflakeWarehouse = document.getElementById('snowflakeWarehouse').value.trim();
                const snowflakeDatabase = document.getElementById('snowflakeDatabase').value.trim();
                const snowflakeSchema = document.getElementById('snowflakeSchema').value.trim();
                const snowflakeRole = document.getElementById('snowflakeRole').value.trim();
                if(!snowflakeAccount || !snowflakeUser || !snowflakePassword || !snowflakeWarehouse || !snowflakeDatabase || !snowflakeSchema || !snowflakeRole) {
                    alert('Please fill all Snowflake fields.');
                    return;
                }
                formData.append('snowflake_account', snowflakeAccount);
                formData.append('snowflake_user', snowflakeUser);
                formData.append('snowflake_password', snowflakePassword);
                formData.append('snowflake_warehouse', snowflakeWarehouse);
                formData.append('snowflake_database', snowflakeDatabase);
                formData.append('snowflake_schema', snowflakeSchema);
                formData.append('snowflake_role', snowflakeRole);
            } else if(dataSource === 'odoo') {
                const odooUrl = document.getElementById('odooUrl').value.trim();
                const odooDb = document.getElementById('odooDb').value.trim();
                const odooUsername = document.getElementById('odooUsername').value.trim();
                const odooPassword = document.getElementById('odooPassword').value.trim();
                const selectedModule = document.getElementById('selectedModule').value.trim();
                if(!odooUrl || !odooDb || !odooUsername || !odooPassword) {
                    alert('Please fill all Odoo fields.');
                    return;
                }
                formData.append('odoo_url', odooUrl);
                formData.append('odoo_db', odooDb);
                formData.append('odoo_username', odooUsername);
                formData.append('odoo_password', odooPassword);
                formData.append('selected_module', selectedModule);
            }

            try {
                const response = await fetch(`${API_BASE}/save_chatbot`, {
                    method: 'POST',
                    body: formData
                });
                if(!response.ok) {
                    alert('Failed to save chatbot.');
                    return;
                }
                const data = await response.json();
                shareKey = data.share_key;
                configured = true;
                configModal.hide();
                alert('Chatbot saved successfully! Share key: ' + shareKey);
            } catch(e) {
                alert('Error saving chatbot: ' + e.message);
            }
        });

        sendBtn.addEventListener('click', sendMessage);
        userInput.addEventListener('keydown', (e) => {
            if(e.key === 'Enter') {
                e.preventDefault();
                sendMessage();
            }
        });

        micBtn.addEventListener('click', () => {
            if (isRecording) {
                recognition.stop();
            } else {
                recognition.start();
                isRecording = true;
                micBtn.classList.add('recording');
                micBtn.textContent = '🎙️';
            }
        });

        refreshBtn.addEventListener('click', () => {
            chatMessages.innerHTML = '';
            appendMessage('bot', 'Chat refreshed. Start a new conversation!');
        });

        function showLoading() {
            if (isLoading) return;
            isLoading = true;
            const loadingDiv = document.createElement('div');
            loadingDiv.className = 'message bot loading';
            loadingDiv.innerHTML = `
                <div class="loading-dots">
                    <div class="loading-dot"></div>
                    <div class="loading-dot"></div>
                    <div class="loading-dot"></div>
                </div>
            `;
            chatMessages.appendChild(loadingDiv);
            chatMessages.scrollTop = chatMessages.scrollHeight;
            sendBtn.disabled = true;
            userInput.disabled = true;
        }

        function hideLoading() {
            if (!isLoading) return;
            isLoading = false;
            const loadingMessage = chatMessages.querySelector('.loading');
            if (loadingMessage) {
                loadingMessage.remove();
            }
            sendBtn.disabled = false;
            userInput.disabled = false;
        }

        async function sendMessage() {
            if(!configured) {
                alert('Please configure the chatbot first by clicking the top-right invisible button 15 times.');
                return;
            }
            const message = userInput.value.trim();
            if(!message) return;

            appendMessage('user', message);
            userInput.value = '';
            showLoading();

            const payload = {message: message};
            if(shareKey) {
                payload.share_key = shareKey;
            } else if(configId) {
                payload.config_id = configId;
            }

            try {
                const response = await fetch(`${API_BASE}/chat`, {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify(payload)
                });
                if(!response.ok) {
                    hideLoading();
                    appendMessage('bot', 'Error: Failed to get response from server.');
                    return;
                }
                const data = await response.json();
                hideLoading();
                appendMessage('bot', data.response);
            } catch(e) {
                hideLoading();
                appendMessage('bot', 'Error: ' + e.message);
            }
        }

        async function loadChatbots(username) {
            try {
                const response = await fetch(`${API_BASE}/list_chatbots?username=${encodeURIComponent(username)}`);
                if (!response.ok) throw new Error('Failed to load');
                const chatbots = await response.json();
                chatbotsGrid.innerHTML = '';
                chatbots.forEach(cb => {
                    const col = document.createElement('div');
                    col.className = 'col-md-3 mb-3';
                    col.innerHTML = `
                        <div class="card h-100 text-center shadow-sm hover-shadow">
                            <div class="card-body d-flex flex-column align-items-center justify-content-center">
                                <div class="mb-3" style="width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; background-color: rgba(0, 123, 255, 0.1);">
                                    <img src="public/images/${cb.data_source}.svg" alt="${cb.data_source}" style="width: 30px; height: 30px;">
                                </div>
                                <h5 class="card-title mb-3">${cb.chatbot_name}</h5>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-primary btn-sm load-btn" style="background-color: #28a745; border-color: #28a745;" onclick="loadChatbot('${cb.share_key}')">Load</button>
                                    <button class="btn btn-secondary btn-sm share-btn" onclick="shareChatbot('${cb.share_key}', '${cb.chatbot_name}')">Share</button>
                                </div>
                            </div>
                        </div>
                    `;
                    chatbotsGrid.appendChild(col);
                });
                chatbotsGridContainer.style.display = 'block';
            } catch(e) {
                alert('Error loading chatbots: ' + e.message);
            }
        }

        function loadChatbot(shareKeyParam) {
            window.open('?share_key=' + shareKeyParam, '_blank');
        }

        function shareChatbot(shareKey, name) {
            const link = `${API_BASE}/shared/${shareKey}`;
            shareLinkInput.value = link;
            shareModal.show();

        }

        function copyToClipboard(text) {
            const textArea = document.createElement('textarea');
            textArea.value = text;
            textArea.style.position = 'fixed';
            textArea.style.left = '-9999px';
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            try {
                const successful = document.execCommand('copy');
                if (successful) {
                    showCopyFeedback();
                } else {
                    throw new Error('Copy command was unsuccessful');
                }
            } catch (err) {
                console.error('Copy failed: ', err);
                alert('Failed to copy link. Please copy manually.');
            } finally {
                document.body.removeChild(textArea);
            }
        }

        copyShareLinkBtn.addEventListener('click', async () => {
            const text = shareLinkInput.value;
            if (navigator.clipboard) {
                try {
                    await navigator.clipboard.writeText(text);
                    showCopyFeedback();
                } catch (err) {
                    console.error('Clipboard API failed: ', err);
                    copyToClipboard(text);
                }
            } else {
                copyToClipboard(text);
            }
        });

        function showCopyFeedback() {
            const originalText = copyShareLinkBtn.textContent;
            copyShareLinkBtn.textContent = 'Copied!';
            copyShareLinkBtn.style.backgroundColor = '#6c757d';
            copyShareLinkBtn.style.borderColor = '#6c757d';
            setTimeout(() => {
                copyShareLinkBtn.textContent = originalText;
                copyShareLinkBtn.style.backgroundColor = '#28a745';
                copyShareLinkBtn.style.borderColor = '#28a745';
            }, 2000);
        }

        // Check URL params for share_key
        const urlParams = new URLSearchParams(window.location.search);
        const urlShareKey = urlParams.get('share_key');
        if(urlShareKey) {
            shareKey = urlShareKey;
            configured = true;
            document.title = 'User Interface';
            configModal.hide();
            // Hide admin config section for user view
            document.getElementById('adminConfigSection').style.display = 'none';
            // Hide main headings and show user interface heading
            document.getElementById('mainH1').style.display = 'none';
            document.getElementById('mainSpan').remove();
            document.getElementById('mainH2').remove();
            document.getElementById('invisibleButton').remove();
        }

        function getCurrentTime() {
            return new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
        }

        function appendMessage(sender, text) {
            const p = document.createElement('p');
            p.className = 'message ' + sender;
            if (sender === 'bot') {
                // Check if response is wrapped in markdown code block
                if (text.startsWith('```html') && text.endsWith('```')) {
                    text = text.slice(7, -3); // Remove ```html and ```
                }
                // Create a temporary div to parse the HTML and extract scripts
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = text;
                const scripts = Array.from(tempDiv.querySelectorAll('script'));
                // Remove scripts from the HTML content
                scripts.forEach(script => script.remove());
                p.innerHTML = tempDiv.innerHTML; // Render HTML without scripts
                // Execute scripts after Google Charts is loaded
                if (scripts.length > 0 && typeof google !== 'undefined' && google.charts) {
                    google.charts.setOnLoadCallback(() => {
                        scripts.forEach(script => {
                            const newScript = document.createElement('script');
                            newScript.textContent = script.textContent;
                            document.head.appendChild(newScript);
                        });
                    });
                }
            } else {
                p.textContent = text;
            }
            const timestamp = document.createElement('div');
            timestamp.className = 'timestamp';
            timestamp.textContent = getCurrentTime();
            p.appendChild(timestamp);
            chatMessages.appendChild(p);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        // Logo preview functionality
        document.getElementById('companyLogo').addEventListener('change', function(event) {
            const file = event.target.files[0];
            const logoPreview = document.getElementById('logoPreview');
            const logoImage = document.getElementById('logoImage');
            const logoPlaceholder = document.getElementById('logoPlaceholder');
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    logoImage.src = e.target.result;
                    logoImage.style.display = 'block';
                    logoPlaceholder.style.display = 'none';
                };
                reader.readAsDataURL(file);
            } else {
                logoImage.style.display = 'none';
                logoPlaceholder.style.display = 'block';
                logoImage.src = '';
            }
        });

        // Initial render of credential fields
        renderCredentialFields(dataSourceSelect.value);
    </script>
</body>
</html>