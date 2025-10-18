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
    </style>
</head>
<body>
    <div id="invisibleButton" title="Click 15 times to configure chatbot"></div>
    <button id="viewChatbotsBtn" class="btn btn-secondary mt-2">View My Chatbots</button>

    <!-- Modal -->
    <div class="modal fade" id="configModal" tabindex="-1" aria-labelledby="configModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="configModalLabel">Configure Chatbot</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form id="configForm">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" required />
                </div>
                <div class="mb-3">
                    <label for="chatbotName" class="form-label">Chatbot Name</label>
                    <input type="text" class="form-control" id="chatbotName" required />
                </div>
                <div class="mb-3">
                    <label for="chatbotId" class="form-label">Chatbot ID</label>
                    <input type="text" class="form-control" id="chatbotId" readonly />
                    <button type="button" class="btn btn-secondary mt-2" id="generateIdBtn">Generate ID</button>
                </div>
                <div class="mb-3">
                    <label for="dataSource" class="form-label">Data Source</label>
                    <select class="form-select" id="dataSource" required>
                        <option value="google_sheets" selected>Google Sheets</option>
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

                <div class="mb-3">
                    <label for="geminiApiKey" class="form-label">Gemini API Key</label>
                    <input type="password" class="form-control" id="geminiApiKey" required />
                </div>
                <div class="mb-3">
                    <label for="geminiModel" class="form-label">Gemini Model</label>
                    <select class="form-select" id="geminiModel" required>
                        <option value="gemini-1.5-flash">gemini-1.5-flash</option>
                        <option value="gemini-1.5-pro">gemini-1.5-pro</option>
                        <option value="gemini-pro">gemini-pro</option>
                        <option value="gemini-2.0-flash" selected>gemini-2.0-flash</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="companyLogo" class="form-label">Company Logo (optional)</label>
                    <input type="file" class="form-control" id="companyLogo" accept="image/*" />
                </div>

                <!-- Credential fields container -->
                <div id="credentialFields"></div>

                <div class="mb-3" id="tableSelectionContainer" style="display:none;">
                    <label class="form-label">Select Tables / Sheets</label>
                    <div id="tableSelection"></div>
                </div>
                <button type="submit" class="btn btn-primary">Save & Configure</button>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- Chatbots List Modal -->
    <div class="modal fade" id="chatbotsModal" tabindex="-1" aria-labelledby="chatbotsModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="chatbotsModalLabel">My Saved Chatbots</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div id="chatbotsGrid" class="row"></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Chat Container -->
    <div id="chatContainer" class="d-flex flex-column">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 id="chatTitle" class="mb-0"></h4>
            <button id="refreshBtn" class="btn btn-secondary btn-sm">🔄 Refresh</button>
        </div>
        <div id="chatMessages"></div>
        <div class="input-group">
            <input type="text" id="userInput" class="form-control" placeholder="Ask your chatbot..." autocomplete="off" />
            <button class="btn btn-secondary" id="micBtn">🎙️</button>
            <button class="btn btn-primary" id="sendBtn">Send</button>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const invisibleButton = document.getElementById('invisibleButton');
        const configModal = new bootstrap.Modal(document.getElementById('configModal'));
        const chatbotsModal = new bootstrap.Modal(document.getElementById('chatbotsModal'));
        const configForm = document.getElementById('configForm');
        const chatbotIdInput = document.getElementById('chatbotId');
        const generateIdBtn = document.getElementById('generateIdBtn');
        const viewChatbotsBtn = document.getElementById('viewChatbotsBtn');
        const dataSourceSelect = document.getElementById('dataSource');
        const credentialFieldsDiv = document.getElementById('credentialFields');
        const tableSelectionContainer = document.getElementById('tableSelectionContainer');
        const tableSelectionDiv = document.getElementById('tableSelection');
        const chatbotsGrid = document.getElementById('chatbotsGrid');
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
            loadChatbots();
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
                        <input type="text" class="form-control" id="sheetId" required />
                    </div>
                <div class="mb-3">
                    <label for="serviceAccountJson" class="form-label">Service Account JSON</label>
                    <textarea class="form-control" id="serviceAccountJson" rows="5" required></textarea>
                </div>
                <button type="button" class="btn btn-secondary mb-3" id="loadTablesBtn">Load Tables</button>
                `;
                document.getElementById('loadTablesBtn').addEventListener('click', () => fetchTablesForDataSource(dataSource));
            } else if(dataSource === 'mysql') {
                credentialFieldsDiv.innerHTML = `
                    <div class="mb-3">
                        <label for="dbHost" class="form-label">DB Host</label>
                        <input type="text" class="form-control" id="dbHost" required />
                    </div>
                    <div class="mb-3">
                        <label for="dbPort" class="form-label">DB Port</label>
                        <input type="number" class="form-control" id="dbPort" required />
                    </div>
                    <div class="mb-3">
                        <label for="dbUsername" class="form-label">DB Username</label>
                        <input type="text" class="form-control" id="dbUsername" required />
                    </div>
                    <div class="mb-3">
                        <label for="dbPassword" class="form-label">DB Password</label>
                        <input type="password" class="form-control" id="dbPassword" required />
                    </div>
                    <div class="mb-3">
                        <label for="dbName" class="form-label">DB Name</label>
                        <input type="text" class="form-control" id="dbName" required />
                    </div>
                    <button type="button" class="btn btn-secondary mb-3" id="loadTablesBtn">Load Tables</button>
                `;
                document.getElementById('loadTablesBtn').addEventListener('click', () => fetchTablesForDataSource(dataSource));
            } else if(dataSource === 'postgresql') {
                credentialFieldsDiv.innerHTML = `
                    <div class="mb-3">
                        <label for="dbHost" class="form-label">DB Host</label>
                        <input type="text" class="form-control" id="dbHost" required />
                    </div>
                    <div class="mb-3">
                        <label for="dbPort" class="form-label">DB Port</label>
                        <input type="number" class="form-control" id="dbPort" required />
                    </div>
                    <div class="mb-3">
                        <label for="dbUsername" class="form-label">DB Username</label>
                        <input type="text" class="form-control" id="dbUsername" required />
                    </div>
                    <div class="mb-3">
                        <label for="dbPassword" class="form-label">DB Password</label>
                        <input type="password" class="form-control" id="dbPassword" required />
                    </div>
                    <div class="mb-3">
                        <label for="dbName" class="form-label">DB Name</label>
                        <input type="text" class="form-control" id="dbName" required />
                    </div>
                    <button type="button" class="btn btn-secondary mb-3" id="loadTablesBtn">Load Tables</button>
                `;
                document.getElementById('loadTablesBtn').addEventListener('click', () => fetchTablesForDataSource(dataSource));
            } else if(dataSource === 'mssql') {
                credentialFieldsDiv.innerHTML = `
                    <div class="mb-3">
                        <label for="dbHost" class="form-label">DB Host</label>
                        <input type="text" class="form-control" id="dbHost" required />
                    </div>
                    <div class="mb-3">
                        <label for="dbPort" class="form-label">DB Port</label>
                        <input type="number" class="form-control" id="dbPort" required />
                    </div>
                    <div class="mb-3">
                        <label for="dbUsername" class="form-label">DB Username</label>
                        <input type="text" class="form-control" id="dbUsername" required />
                    </div>
                    <div class="mb-3">
                        <label for="dbPassword" class="form-label">DB Password</label>
                        <input type="password" class="form-control" id="dbPassword" required />
                    </div>
                    <div class="mb-3">
                        <label for="dbName" class="form-label">DB Name</label>
                        <input type="text" class="form-control" id="dbName" required />
                    </div>
                    <button type="button" class="btn btn-secondary mb-3" id="loadTablesBtn">Load Tables</button>
                `;
                document.getElementById('loadTablesBtn').addEventListener('click', () => fetchTablesForDataSource(dataSource));
            } else if(dataSource === 'neo4j') {
                credentialFieldsDiv.innerHTML = `
                    <div class="mb-3">
                        <label for="neo4jUri" class="form-label">Neo4j URI</label>
                        <input type="text" class="form-control" id="neo4jUri" required />
                    </div>
                    <div class="mb-3">
                        <label for="neo4jUsername" class="form-label">Neo4j Username</label>
                        <input type="text" class="form-control" id="neo4jUsername" required />
                    </div>
                    <div class="mb-3">
                        <label for="neo4jPassword" class="form-label">Neo4j Password</label>
                        <input type="password" class="form-control" id="neo4jPassword" required />
                    </div>
                    <div class="mb-3">
                        <label for="neo4jDbName" class="form-label">Neo4j Database Name</label>
                        <input type="text" class="form-control" id="neo4jDbName" required />
                    </div>
                    <button type="button" class="btn btn-secondary mb-3" id="loadTablesBtn">Load Tables</button>
                `;
                document.getElementById('loadTablesBtn').addEventListener('click', () => fetchTablesForDataSource(dataSource));
            } else if(dataSource === 'mongodb') {
                credentialFieldsDiv.innerHTML = `
                    <div class="mb-3">
                        <label for="mongoUri" class="form-label">MongoDB URI</label>
                        <input type="text" class="form-control" id="mongoUri" required />
                    </div>
                    <div class="mb-3">
                        <label for="mongoDbName" class="form-label">MongoDB Database Name</label>
                        <input type="text" class="form-control" id="mongoDbName" required />
                    </div>
                    <button type="button" class="btn btn-secondary mb-3" id="loadTablesBtn">Load Tables</button>
                `;
                document.getElementById('loadTablesBtn').addEventListener('click', () => fetchTablesForDataSource(dataSource));
            } else if(dataSource === 'oracle') {
                credentialFieldsDiv.innerHTML = `
                    <div class="mb-3">
                        <label for="dbHost" class="form-label">DB Host</label>
                        <input type="text" class="form-control" id="dbHost" required />
                    </div>
                    <div class="mb-3">
                        <label for="dbPort" class="form-label">DB Port</label>
                        <input type="number" class="form-control" id="dbPort" required />
                    </div>
                    <div class="mb-3">
                        <label for="dbUsername" class="form-label">DB Username</label>
                        <input type="text" class="form-control" id="dbUsername" required />
                    </div>
                    <div class="mb-3">
                        <label for="dbPassword" class="form-label">DB Password</label>
                        <input type="password" class="form-control" id="dbPassword" required />
                    </div>
                    <div class="mb-3">
                        <label for="dbName" class="form-label">DB Name</label>
                        <input type="text" class="form-control" id="dbName" required />
                    </div>
                    <button type="button" class="btn btn-secondary mb-3" id="loadTablesBtn">Load Tables</button>
                `;
                document.getElementById('loadTablesBtn').addEventListener('click', () => fetchTablesForDataSource(dataSource));
            } else if(dataSource === 'airtable') {
                credentialFieldsDiv.innerHTML = `
                    <div class="mb-3">
                        <label for="airtableApiKey" class="form-label">Airtable API Key</label>
                        <input type="password" class="form-control" id="airtableApiKey" required />
                    </div>
                    <div class="mb-3">
                        <label for="airtableBaseId" class="form-label">Airtable Base ID</label>
                        <input type="text" class="form-control" id="airtableBaseId" required />
                    </div>
                    <button type="button" class="btn btn-secondary mb-3" id="loadTablesBtn">Load Tables</button>
                `;
                document.getElementById('loadTablesBtn').addEventListener('click', () => fetchTablesForDataSource(dataSource));
            } else if(dataSource === 'databricks') {
                credentialFieldsDiv.innerHTML = `
                    <div class="mb-3">  
                        <label for="databricksHostname" class="form-label">Databricks Hostname</label>
                        <input type="text" class="form-control" id="databricksHostname" required />
                    </div>
                    <div class="mb-3">
                        <label for="databricksHttpPath" class="form-label">Databricks HTTP Path</label>
                        <input type="text" class="form-control" id="databricksHttpPath" required />
                    </div>
                    <div class="mb-3">
                        <label for="databricksToken" class="form-label">Databricks Token</label>
                        <input type="password" class="form-control" id="databricksToken" required />
                    </div>
                    <button type="button" class="btn btn-secondary mb-3" id="loadTablesBtn">Load Tables</button>
                `;
                document.getElementById('loadTablesBtn').addEventListener('click', () => fetchTablesForDataSource(dataSource));
            } else if(dataSource === 'supabase') {
                credentialFieldsDiv.innerHTML = `
                    <div class="mb-3">
                        <label for="supabaseUrl" class="form-label">Supabase URL</label>
                        <input type="text" class="form-control" id="supabaseUrl" required />
                    </div>
                    <div class="mb-3">
                        <label for="supabaseAnonKey" class="form-label">Supabase Anon Key</label>
                        <input type="password" class="form-control" id="supabaseAnonKey" required />
                    </div>
                    <button type="button" class="btn btn-secondary mb-3" id="loadTablesBtn">Load Tables</button>
                `;
                document.getElementById('loadTablesBtn').addEventListener('click', () => fetchTablesForDataSource(dataSource));
            } else if(dataSource === 'snowflake') {
                credentialFieldsDiv.innerHTML = `
                    <div class="mb-3">
                        <label for="snowflakeAccount" class="form-label">Snowflake Account</label>
                        <input type="text" class="form-control" id="snowflakeAccount" required />
                    </div>
                    <div class="mb-3">
                        <label for="snowflakeUser" class="form-label">Snowflake User</label>
                        <input type="text" class="form-control" id="snowflakeUser" required />
                    </div>
                    <div class="mb-3">
                        <label for="snowflakePassword" class="form-label">Snowflake Password</label>
                        <input type="password" class="form-control" id="snowflakePassword" required />
                    </div>
                    <div class="mb-3">
                        <label for="snowflakeWarehouse" class="form-label">Snowflake Warehouse</label>
                        <input type="text" class="form-control" id="snowflakeWarehouse" required />
                    </div>
                    <div class="mb-3">
                        <label for="snowflakeDatabase" class="form-label">Snowflake Database</label>
                        <input type="text" class="form-control" id="snowflakeDatabase" required />
                    </div>
                    <div class="mb-3">
                        <label for="snowflakeSchema" class="form-label">Snowflake Schema</label>
                        <input type="text" class="form-control" id="snowflakeSchema" required />
                    </div>
                    <div class="mb-3">
                        <label for="snowflakeRole" class="form-label">Snowflake Role</label>
                        <input type="text" class="form-control" id="snowflakeRole" required />
                    </div>
                    <button type="button" class="btn btn-secondary mb-3" id="loadTablesBtn">Load Tables</button>
                `;
                document.getElementById('loadTablesBtn').addEventListener('click', () => fetchTablesForDataSource(dataSource));
            } else if(dataSource === 'odoo') {
                credentialFieldsDiv.innerHTML = `
                    <div class="mb-3">
                        <label for="odooUrl" class="form-label">Odoo URL</label>
                        <input type="text" class="form-control" id="odooUrl" required />
                    </div>
                    <div class="mb-3">
                        <label for="odooDb" class="form-label">Odoo Database</label>
                        <input type="text" class="form-control" id="odooDb" required />
                    </div>
                    <div class="mb-3">
                        <label for="odooUsername" class="form-label">Odoo Username</label>
                        <input type="text" class="form-control" id="odooUsername" required />
                    </div>
                    <div class="mb-3">
                        <label for="odooPassword" class="form-label">Odoo Password</label>
                        <input type="password" class="form-control" id="odooPassword" required />
                    </div>
                    <div class="mb-3">
                        <label for="selectedModule" class="form-label">Odoo Module (optional)</label>
                        <input type="text" class="form-control" id="selectedModule" />
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

            if(!username || !chatbotName || !chatbotId) {
                alert('Please fill username, chatbot name, and generate ID.');
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
                chatTitle.textContent = `Chat with ${chatbotName}`;
                chatMessages.innerHTML = '';
                chatContainer.style.display = 'flex';
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

        async function loadChatbots() {
            const username = prompt("Enter your username to load chatbots:");
            if (!username) return;
            try {
                const response = await fetch(`${API_BASE}/list_chatbots?username=${encodeURIComponent(username)}`);
                if (!response.ok) throw new Error('Failed to load');
                const chatbots = await response.json();
                chatbotsGrid.innerHTML = '';
                chatbots.forEach(cb => {
                    const col = document.createElement('div');
                    col.className = 'col-md-6 mb-3';
                    col.innerHTML = `
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">${cb.chatbot_name}</h5>
                                <p class="card-text">Data Source: ${cb.data_source}</p>
                                <button class="btn btn-primary me-2" onclick="loadChatbot('${cb.share_key}')">Load</button>
                                <button class="btn btn-secondary" onclick="shareChatbot('${cb.share_key}', '${cb.chatbot_name}')">Share</button>
                            </div>
                        </div>
                    `;
                    chatbotsGrid.appendChild(col);
                });
                chatbotsModal.show();
            } catch(e) {
                alert('Error loading chatbots: ' + e.message);
            }
        }

        function loadChatbot(shareKeyParam) {
            shareKey = shareKeyParam;
            configured = true;
            chatTitle.textContent = 'Chat with Shared Bot';
            chatMessages.innerHTML = '';
            chatContainer.style.display = 'flex';
            chatbotsModal.hide();
        }

        function shareChatbot(shareKey, name) {
            const link = `${API_BASE}/shared/${shareKey}`;
            const iframe = `<iframe src="${link}" width="500" height="700" frameborder="0"></iframe>`;
            navigator.clipboard.writeText(iframe).then(() => alert('Iframe code copied to clipboard!'));
        }

        // Check URL params for share_key
        const urlParams = new URLSearchParams(window.location.search);
        const urlShareKey = urlParams.get('share_key');
        if(urlShareKey) {
            shareKey = urlShareKey;
            configured = true;
            document.title = 'User view';
            chatTitle.textContent = 'User View';
            chatMessages.innerHTML = '';
            chatContainer.style.display = 'flex';
            configModal.hide();
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

        // Initial render of credential fields
        renderCredentialFields(dataSourceSelect.value);
    </script>
</body>
</html>
