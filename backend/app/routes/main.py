import json
import gspread
from google.oauth2.service_account import Credentials
from google import genai
import sqlite3
import pymysql
import psycopg2
from neo4j import GraphDatabase
from pymongo import MongoClient
import databricks.sql
import oracledb
import logging
import smtplib
from email.mime.text import MIMEText
from email.mime.multipart import MIMEMultipart
import secrets
import string
from datetime import datetime, timedelta
from flask import Blueprint, request, jsonify
from config import Config
from app.services.database_service import DatabaseService
import re
import requests
from google.auth.transport.requests import Request

# System instruction for BI tool
system_instruction = """You are a generative BI (Business Intelligence) tool. Your purpose is to create components based on user prompts and provided data.

You MUST strictly output **only a single, self-contained HTML block**. Do not include `<html>`, `<head>`, or `<body>` tags. Do not write any explanations outside of the HTML.

**IMPORTANT**: The following templates use `QueryUrl` or `fetch` to get data. You MUST IGNORE this. Instead, you must take the data provided in the `Spreadsheet data:` section of the prompt and embed it directly into the JavaScript of the template, for example by using `google.visualization.arrayToDataTable(...)` or by populating table rows directly.

Based on the user's request, you will generate one of the following outputs using the component definitions below:

1.  **If the user asks for a "graph" or "chart":**
    -   Generate ONLY the HTML and JavaScript for a single Google Chart, adapted from the Graph Template.

2.  **If the user asks for a "table":**
    -   Generate ONLY the HTML and JavaScript for a single paginated table, adapted from the Table Template.

3.  **If the user asks for an "insight" or "summary":**
    -   Generate ONLY a `<div>` containing the textual insight, adapted from the Insight Template.

4.  **If the user asks for a "dashboard":**
   - Generate a complete dashboard layout.
   - The dashboard MUST contain:
     - A flexbox container with **four** metric cards at the top.
     - A container with **two** different Google Charts.
     - A container with **two** different paginated tables.

--- COMPONENT DEFINITIONS ---


**Insight Template:**
<div class="d-flex justify-content-center my-4">
    <!-- Glassmorphic card for insight display -->
    <div class="insight-glass text-center shadow-sm rounded">
        <!-- Label or description -->
        <div class="insight-text mb-3">
            <span class="fw-semibold text-dark">Highest Value</span> is:
        </div>
        <!-- Main value -->
        <div class="insight-number mb-3">4,567</div>
        <!-- Additional info or timestamp -->
        <div class="small text-muted">Updated: 9/30/2025, 10:00:00 AM</div>
    </div>
</div>

<style>
    /* Glass card styling for insight with more padding */
    .insight-glass {
        background: rgba(255, 255, 255, 0.65); /* semi-transparent white */
        backdrop-filter: blur(12px);          /* blur effect */
        border-radius: 1rem;                  /* rounded corners */
        box-shadow: 0 8px 24px rgba(0,0,0,0.08); /* soft shadow */
        max-width: 400px;                     /* max width for card */
        padding: 3rem 3.5rem;                 /* increased internal padding */
    }

    /* Description text inside the card */
    .insight-text {
        font-size: 1.2rem;
        color: #444;
    }

    /* Large numeric value */
    .insight-number {
        font-size: 2.8rem;
        font-weight: 700;
        color: #111;
    }
</style>



**Table Template:**
<!-- Bootstrap CSS (needed for table + pagination styles) -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<div class="glass-card mt-4">
    <style>
        .glass-card {
            background: rgba(255, 255, 255, 0.6);
            backdrop-filter: blur(12px);
            border-radius: 1rem;
            box-shadow: 0 8px 24px rgba(0,0,0,0.1);
            padding: 2rem;
            margin: auto;
            width: 100%;
            max-width: 1200px;
            overflow-x: auto;
        }
        thead th {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(6px);
            position: sticky;
            top: 0;
            z-index: 1;
        }
    </style>

    <h2 class="mb-4 text-center fw-bold">📊 Table Title</h2>
    <table id="sheetTable_UNIQUE_ID" class="table table-hover table-bordered align-middle">
        <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Column A</th>
                <th>Column B</th>
                <th>Column C</th>
            </tr>
        </thead>
        <tbody>
            <tr><td>1</td><td>A1</td><td>B1</td><td>C1</td></tr>
            <tr><td>2</td><td>A2</td><td>B2</td><td>C2</td></tr>
            <tr><td>3</td><td>A3</td><td>B3</td><td>C3</td></tr>
            <tr><td>4</td><td>A4</td><td>B4</td><td>C4</td></tr>
            <tr><td>5</td><td>A5</td><td>B5</td><td>C5</td></tr>
            <tr><td>6</td><td>A6</td><td>B6</td><td>C6</td></tr>
            <tr><td>7</td><td>A7</td><td>B7</td><td>C7</td></tr>
            <tr><td>8</td><td>A8</td><td>B8</td><td>C8</td></tr>
            <tr><td>9</td><td>A9</td><td>B9</td><td>C9</td></tr>
            <tr><td>10</td><td>A10</td><td>B10</td><td>C10</td></tr>
        </tbody>
    </table>
    <!-- Pagination will be injected here -->
    <nav id="paginationContainer" class="d-flex justify-content-center mt-3"></nav>
</div>

<script>
(function() {
    const tableId = 'sheetTable_UNIQUE_ID';
    const rowsPerPage = 5;
    const table = document.getElementById(tableId);
    if (!table) return;

    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    const pageCount = Math.ceil(rows.length / rowsPerPage);
    const paginationContainer = document.getElementById('paginationContainer');
    let currentPage = 1;

    function showPage(page) {
        currentPage = page;
        const start = (page - 1) * rowsPerPage;
        const end = start + rowsPerPage;
        rows.forEach((row, index) => {
            row.style.display = (index >= start && index < end) ? '' : 'none';
        });
        updatePaginationUI();
    }

    function updatePaginationUI() {
        let paginationHTML = `
            <ul class="pagination pagination-md">
                <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                    <a class="page-link" href="#" data-page="${currentPage - 1}">« Prev</a>
                </li>
        `;
        for (let i = 1; i <= pageCount; i++) {
            paginationHTML += `
                <li class="page-item ${i === currentPage ? 'active' : ''}">
                    <a class="page-link" href="#" data-page="${i}">${i}</a>
                </li>
            `;
        }
        paginationHTML += `
                <li class="page-item ${currentPage === pageCount ? 'disabled' : ''}">
                    <a class="page-link" href="#" data-page="${currentPage + 1}">Next »</a>
                </li>
            </ul>
        `;
        paginationContainer.innerHTML = paginationHTML;

        paginationContainer.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', e => {
                e.preventDefault();
                const page = parseInt(link.dataset.page);
                if (page >= 1 && page <= pageCount) {
                    showPage(page);
                }
            });
        });
    }

    if (rows.length > 0) {
        showPage(1);
    }
})();
</script>


"""

# Configure logging
logging.basicConfig(level=logging.INFO)

main_bp = Blueprint('main', __name__)

# --- Status/Health Check ---
@main_bp.route('/', methods=['GET'])
def status():
    """Health check endpoint"""
    return jsonify({
        "status": "running",
        "message": "ChatBot API is running successfully",
        "version": "1.0.0"
    })

# Globals
CONFIG = {}
worksheets = []
spreadsheet = None
gc = None
gemini_client = None
db_conn = None
selected_tables = []

DB_FILE = "chatbots.db"

# --- Initialize database ---
def init_db():
    conn = sqlite3.connect(DB_FILE)
    cursor = conn.cursor()
    cursor.execute("""
        CREATE TABLE IF NOT EXISTS users (
            username TEXT PRIMARY KEY,
            password TEXT
        )
    """)

    cursor.execute("""
        CREATE TABLE IF NOT EXISTS user_agreements (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT,
            accepted_terms BOOLEAN DEFAULT 0,
            accepted_privacy BOOLEAN DEFAULT 0,
            terms_timestamp DATETIME,
            privacy_timestamp DATETIME,
            FOREIGN KEY (username) REFERENCES users (username)
        )
    """)

    cursor.execute("""
        CREATE TABLE IF NOT EXISTS chatbots (
            id TEXT PRIMARY KEY,
            username TEXT,
            chatbot_name TEXT,
            gemini_api_key TEXT,
            gemini_model TEXT,
            data_source TEXT,
            sheet_id TEXT,
            selected_sheets TEXT,
            service_account_json TEXT,
            db_host TEXT,
            db_port INTEGER,
            db_name TEXT,
            db_username TEXT,
            db_password TEXT,
            selected_tables TEXT,
            mongo_uri TEXT,
            mongo_db_name TEXT,
            selected_collections TEXT,
            airtable_api_key TEXT,
            airtable_base_id TEXT,
            databricks_hostname TEXT,
            databricks_http_path TEXT,
            databricks_token TEXT,
            supabase_url TEXT,
            supabase_anon_key TEXT,
            snowflake_account TEXT,
            snowflake_user TEXT,
            snowflake_password TEXT,
            snowflake_warehouse TEXT,
            snowflake_database TEXT,
            snowflake_schema TEXT,
            snowflake_role TEXT,
            share_key TEXT UNIQUE,
            company_logo TEXT,
            nav_color TEXT,
            text_color TEXT,
            content_bg_color TEXT,
            textarea_color TEXT,
            textarea_border_color TEXT,
            textarea_border_thickness TEXT,
            button_color TEXT,
            button_text_color TEXT,
            border_color TEXT,
            border_thickness TEXT,
            nav_border_color TEXT,
            nav_border_thickness TEXT,
            odoo_url TEXT,
            odoo_db TEXT,
            odoo_username TEXT,
            odoo_password TEXT,
            selected_module TEXT
        )
    """)

    # Create password reset tokens table
    cursor.execute("""
        CREATE TABLE IF NOT EXISTS password_resets (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT NOT NULL,
            token TEXT NOT NULL UNIQUE,
            expires_at DATETIME NOT NULL,
            used BOOLEAN DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (username) REFERENCES users (username)
        )
    """)
    

    conn.commit()
    conn.close()

init_db()

# --- Utility Functions ---
def generate_reset_token(length=32):
    """Generate a secure random token for password reset"""
    alphabet = string.ascii_letters + string.digits
    return ''.join(secrets.choice(alphabet) for _ in range(length))

def send_reset_email(username, reset_token, frontend_url=None):
    if frontend_url is None:
        frontend_url = Config().FRONTEND_URL
    """Send password reset email using SMTP"""
    try:
        # SMTP configuration
        smtp_server = "mail.smartcardai.com"
        smtp_port = 587
        smtp_username = "support@smartcardai.com"
        smtp_password = "Smart@Mail2025!"

        # Create message
        msg = MIMEMultipart('alternative')
        msg['Subject'] = "Password Reset Request - SmartCard AI"
        msg['From'] = f"SmartCard AI <{smtp_username}>"
        msg['To'] = username

        # Email content
        reset_link = f"{frontend_url}/reset-password.php?token={reset_token}"

        html_content = f"""
        <html>
        <body>
            <h2>Password Reset Request</h2>
            <p>Hello {username},</p>
            <p>You have requested to reset your password for your SmartCard AI account.</p>
            <p>Click the link below to reset your password:</p>
            <p><a href="{reset_link}" style="background-color: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Reset Password</a></p>
            <p>If the button doesn't work, copy and paste this link into your browser:</p>
            <p>{reset_link}</p>
            <p><strong>Note:</strong> This link will expire in 24 hours for security reasons.</p>
            <p>If you didn't request this password reset, please ignore this email.</p>
            <br>
            <p>Best regards,<br>SmartCard AI Team</p>
        </body>
        </html>
        """

        text_content = f"""
        Password Reset Request

        Hello {username},

        You have requested to reset your password for your SmartCard AI account.

        Click the link below to reset your password:
        {reset_link}

        Note: This link will expire in 24 hours for security reasons.

        If you didn't request this password reset, please ignore this email.

        Best regards,
        SmartCard AI Team
        """

        # Attach parts
        part1 = MIMEText(text_content, 'plain')
        part2 = MIMEText(html_content, 'html')
        msg.attach(part1)
        msg.attach(part2)

        # Send email
        server = smtplib.SMTP(smtp_server, smtp_port)
        server.starttls()
        server.login(smtp_username, smtp_password)
        server.sendmail(smtp_username, username, msg.as_string())
        server.quit()

        logging.info(f"Password reset email sent successfully to {username}")
        return True

    except Exception as e:
        logging.error(f"Failed to send password reset email: {str(e)}")
        return False

# --- Signup ---
@main_bp.route('/signup', methods=['POST'])
def signup():
    data = request.json
    username = data.get('username')
    password = data.get('password')
    accepted_terms = data.get('accepted_terms', False)
    accepted_privacy = data.get('accepted_privacy', False)

    if not username or not password:
        return jsonify({"success": False, "message": "Username and password required"}), 400

    if not accepted_terms or not accepted_privacy:
        return jsonify({"success": False, "message": "You must accept both Terms & Conditions and Privacy Policy"}), 400

    conn = sqlite3.connect(DB_FILE)
    cursor = conn.cursor()

    # Check if user already exists
    cursor.execute("SELECT * FROM users WHERE username=?", (username,))
    if cursor.fetchone():
        conn.close()
        return jsonify({"success": False, "message": "User already exists"}), 400

    # Insert user
    cursor.execute("INSERT INTO users (username, password) VALUES (?, ?)", (username, password))

    # Insert agreement records with timestamps
    from datetime import datetime
    current_time = datetime.now().isoformat()

    cursor.execute("""
        INSERT INTO user_agreements (username, accepted_terms, accepted_privacy, terms_timestamp, privacy_timestamp)
        VALUES (?, ?, ?, ?, ?)
    """, (username, accepted_terms, accepted_privacy, current_time, current_time))

    conn.commit()
    conn.close()
    return jsonify({"success": True})

# --- Login ---
@main_bp.route('/login', methods=['POST'])
def login():
    data = request.json
    username = data.get('username')
    password = data.get('password')
    conn = sqlite3.connect(DB_FILE)
    cursor = conn.cursor()
    cursor.execute("SELECT * FROM users WHERE username=? AND password=?", (username, password))
    if cursor.fetchone():
        conn.close()
        return jsonify({"success": True})
    conn.close()
    return jsonify({"success": False, "message": "Invalid credentials"}), 400

# --- Forgot Password ---
@main_bp.route('/forgot-password', methods=['POST'])
def forgot_password():
    data = request.json
    username = data.get('username')

    if not username:
        return jsonify({"success": False, "message": "Username is required"}), 400

    conn = sqlite3.connect(DB_FILE)
    cursor = conn.cursor()

    # Check if user exists
    cursor.execute("SELECT * FROM users WHERE username=?", (username,))
    if not cursor.fetchone():
        conn.close()
        return jsonify({"success": False, "message": "User not found"}), 404

    # Clean up expired tokens
    cursor.execute("DELETE FROM password_resets WHERE expires_at < ?", (datetime.now().isoformat(),))

    # Generate new token
    reset_token = generate_reset_token()

    # Set expiration time (24 hours from now)
    expires_at = datetime.now() + timedelta(hours=24)

    # Insert new token
    cursor.execute("""
        INSERT INTO password_resets (username, token, expires_at, used)
        VALUES (?, ?, ?, ?)
    """, (username, reset_token, expires_at.isoformat(), False))

    conn.commit()
    conn.close()

    # Send email
    email_sent = send_reset_email(username, reset_token)

    if email_sent:
        return jsonify({"success": True, "message": "Password reset instructions sent to your email"})
    else:
        return jsonify({"success": False, "message": "Failed to send email. Please try again later."}), 500

# --- Reset Password ---
@main_bp.route('/reset-password', methods=['POST'])
def reset_password():
    data = request.json
    token = data.get('token')
    new_password = data.get('new_password')

    if not token or not new_password:
        return jsonify({"success": False, "message": "Token and new password are required"}), 400

    if len(new_password) < 6:
        return jsonify({"success": False, "message": "Password must be at least 6 characters long"}), 400

    conn = sqlite3.connect(DB_FILE)
    cursor = conn.cursor()

    # Check if token exists and is valid
    cursor.execute("""
        SELECT username, used, expires_at
        FROM password_resets
        WHERE token = ?
    """, (token,))

    result = cursor.fetchone()

    if not result:
        conn.close()
        return jsonify({"success": False, "message": "Invalid or expired token"}), 400

    username, used, expires_at = result

    # Check if token is already used
    if used:
        conn.close()
        return jsonify({"success": False, "message": "Token has already been used"}), 400

    # Check if token is expired
    if datetime.fromisoformat(expires_at) < datetime.now():
        conn.close()
        return jsonify({"success": False, "message": "Token has expired"}), 400

    # Update password
    cursor.execute("UPDATE users SET password = ? WHERE username = ?", (new_password, username))

    # Mark token as used
    cursor.execute("UPDATE password_resets SET used = 1 WHERE token = ?", (token,))

    conn.commit()
    conn.close()

    return jsonify({"success": True, "message": "Password reset successfully"})

# --- Set credentials and list items ---
@main_bp.route('/set_credentials', methods=['POST'])
def set_credentials():
    global CONFIG, gc, spreadsheet, gemini_client, db_conn
    CONFIG = request.form.to_dict()
    data_source = CONFIG.get('data_source')

    api_key = CONFIG.get('gemini_api_key', '')
    if api_key and api_key.strip():
        gemini_client = genai.Client(api_key=api_key)
    else:
        gemini_client = None

    if data_source == 'google_sheets':
        try:
            service_json_str = CONFIG['service_account_json']
            logging.info(f"Service account JSON string length: {len(service_json_str)}")
            service_json = json.loads(service_json_str)
            logging.info("Service account JSON parsed successfully.")

            # Validate required keys for service account
            required_keys = ['type', 'project_id', 'private_key_id', 'private_key', 'client_email', 'client_id', 'auth_uri', 'token_uri', 'auth_provider_x509_cert_url', 'client_x509_cert_url']
            missing_keys = [key for key in required_keys if key not in service_json]
            if missing_keys:
                logging.error(f"Missing keys in service account JSON: {missing_keys}")
                return jsonify({'error': f'Invalid Service Account JSON: missing keys {missing_keys}'}), 400

            if service_json.get('type') != 'service_account':
                logging.error("Service account JSON type is not 'service_account'")
                return jsonify({'error': 'Invalid Service Account JSON: type must be service_account'}), 400

        except json.JSONDecodeError as e:
            logging.error(f"JSON decode error: {str(e)}")
            return jsonify({'error': 'Invalid Service Account JSON: not valid JSON'}), 400
        except Exception as e:
            logging.error(f"Failed to load service account JSON: {str(e)}")
            return jsonify({'error': 'Invalid Service Account JSON'}), 400

        try:
            creds = Credentials.from_service_account_info(service_json, scopes=["https://www.googleapis.com/auth/spreadsheets.readonly"])
            logging.info(f"Credentials created successfully.")
            gc = gspread.authorize(creds)
            spreadsheet = gc.open_by_key(CONFIG['sheet_id'])
            sheets = [{'title': ws.title, 'gid': ws.id} for ws in spreadsheet.worksheets()]
            CONFIG['sheets'] = sheets
            items = [s['title'] for s in sheets]
            return jsonify({'type': 'sheets', 'items': items})
        except Exception as e:
            logging.error(f"Failed to authorize or open spreadsheet: {str(e)}")
            return jsonify({'error': 'Failed to authorize or open spreadsheet'}), 400

    elif data_source == 'mysql':
        try:
            db_conn = pymysql.connect(
                host=CONFIG['db_host'],
                port=int(CONFIG['db_port']),
                user=CONFIG['db_username'],
                password=CONFIG['db_password'],
                database=CONFIG['db_name']
            )
            cursor = db_conn.cursor()
            cursor.execute("SHOW TABLES")
            items = [row[0] for row in cursor.fetchall()]
            cursor.close()
            return jsonify({'type': 'tables', 'items': items})
        except Exception as e:
            return jsonify({'error': f'MySQL connection failed: {str(e)}'}), 400

    elif data_source == 'postgresql':
        try:
            db_conn = psycopg2.connect(
                host=CONFIG['db_host'],
                port=int(CONFIG['db_port']),
                user=CONFIG['db_username'],
                password=CONFIG['db_password'],
                database=CONFIG['db_name']
            )
            cursor = db_conn.cursor()
            cursor.execute("SELECT tablename FROM pg_tables WHERE schemaname='public'")
            items = [row[0] for row in cursor.fetchall()]
            cursor.close()
            return jsonify({'type': 'tables', 'items': items})
        except Exception as e:
            return jsonify({'error': f'PostgreSQL connection failed: {str(e)}'}), 400

    elif data_source == 'neo4j':
        try:
            uri = CONFIG['neo4j_uri']
            # Change URI scheme from neo4j+s to neo4j+ssc for self-signed certs
            if uri.startswith("neo4j+s://"):
                uri = uri.replace("neo4j+s://", "neo4j+ssc://")
            elif uri.startswith("bolt+s://"):
                uri = uri.replace("bolt+s://", "bolt+ssc://")
            # For local single instance, change neo4j:// to bolt://
            elif uri.startswith("neo4j://") and "localhost" in uri:
                uri = uri.replace("neo4j://", "bolt://")

            username = CONFIG['neo4j_username']
            password = CONFIG['neo4j_password']
            database = CONFIG['neo4j_db_name']
            # Override database name to 'neo4j' for Aura default if needed
            if database != 'neo4j':
                logging.warning(f"Overriding Neo4j database name from {database} to 'neo4j' for Aura compatibility")
                database = 'neo4j'
            CONFIG['db_name'] = database  # Set for consistency in other parts
            logging.info(f"Neo4j connection: uri={uri}, username={username}, database={database}")
            logging.info(f"Received neo4j_db_name in set_credentials: {database}")
            # Remove encrypted and trust parameters for URI scheme neo4j+ssc
            driver = GraphDatabase.driver(
                uri,
                auth=(username, password)
            )
            db_conn = driver
            with driver.session(database=database) as session:
                result = session.run("MATCH (n) RETURN DISTINCT labels(n) AS labels")
                labels_set = set()
                for record in result:
                    labels_list = record["labels"]
                    labels_set.update(labels_list)
                items = list(labels_set)
            return jsonify({'type': 'labels', 'items': items})
        except Exception as e:
            logging.error(f"Neo4j connection failed: {str(e)}")
            return jsonify({'error': f'Neo4j connection failed: {str(e)}'}), 400

    elif data_source == 'mongodb':
        try:
            # Note: Disabling TLS certificate verification for development. For production, use proper CA certificates.
            client = MongoClient(CONFIG['mongo_uri'], tls=True, tlsAllowInvalidCertificates=True)
            db = client[CONFIG['mongo_db_name']]
            items = db.list_collection_names()
            db_conn = db
            return jsonify({'type': 'collections', 'items': items})
        except Exception as e:
            return jsonify({'error': f'MongoDB connection failed: {str(e)}'}), 400

    elif data_source == 'oracle':
        try:
            version = int(CONFIG.get('oracle_version', 19))
            creds = {
                'host': CONFIG['db_host'],
                'port': int(CONFIG.get('db_port', 1521)),
                'user': CONFIG['db_username'],
                'password': CONFIG['db_password'],
                'service_name': CONFIG['db_name']
            }
            db_service = DatabaseService()
            # Test connection by fetching table list
            query = "SELECT table_name FROM all_tables WHERE owner = USER"
            results = db_service.fetch_from_oracle(creds, query, version)
            if isinstance(results, dict) and results.get('status') == 'error':
                return jsonify({'error': results['message']}), 400
            items = [row['TABLE_NAME'] for row in results]
            return jsonify({'type': 'tables', 'items': items})
        except Exception as e:
            return jsonify({'error': f'Oracle connection failed: {str(e)}'}), 400
    elif data_source == 'mssql':
        try:
            creds = {
                'host': CONFIG['db_host'],
                'port': int(CONFIG.get('db_port', 1433)),
                'user': CONFIG['db_username'],
                'password': CONFIG['db_password'],
                'database': CONFIG['db_name']
            }
            db_service = DatabaseService()
            query = "SELECT TABLE_SCHEMA + '.' + TABLE_NAME AS table_name FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE = 'BASE TABLE'"
            results = db_service.fetch_from_mssql(creds, query)
            if isinstance(results, dict) and results.get('status') == 'error':
                return jsonify({'error': results['message']}), 400
            items = [row['table_name'] for row in results]
            return jsonify({'type': 'tables', 'items': items})
        except Exception as e:
            return jsonify({'error': f'MS SQL connection failed: {str(e)}'}), 400

    elif data_source == 'airtable':
        try:
            from pyairtable import Api
            api = Api(CONFIG['airtable_api_key'])
            base = api.base(CONFIG['airtable_base_id'])
            tables = base.tables()
            items = [table.name for table in tables]
            return jsonify({'type': 'tables', 'items': items})
        except Exception as e:
            return jsonify({'error': f'Airtable connection failed: {str(e)}'}), 400

    elif data_source == 'databricks':
        try:
            creds = {
                'server_hostname': CONFIG['databricks_hostname'],
                'http_path': CONFIG['databricks_http_path'],
                'access_token': CONFIG['databricks_token']
            }
            db_service = DatabaseService()
            query = "SHOW TABLES"
            results = db_service.fetch_from_databricks(creds, query)
            if isinstance(results, dict) and results.get('status') == 'error':
                return jsonify({'error': results['message']}), 400
            items = [row['tableName'] for row in results]
            return jsonify({'type': 'tables', 'items': items})
        except Exception as e:
            return jsonify({'error': f'Databricks connection failed: {str(e)}'}), 400

    elif data_source == 'supabase':
        try:
            import requests
            # Get the OpenAPI spec to list tables
            api_url = CONFIG['supabase_url'].rstrip('/') + '/rest/v1/'
            headers = {
                'Authorization': f'Bearer {CONFIG["supabase_anon_key"]}',
                'apikey': CONFIG['supabase_anon_key']
            }
            response = requests.get(api_url, headers=headers)
            if response.status_code == 200:
                spec = response.json()
                paths = spec.get('paths', {})
                items = [path.strip('/') for path in paths.keys() if path.startswith('/') and path != '/']
            else:
                items = []
            return jsonify({'type': 'tables', 'items': items})
        except Exception as e:
            return jsonify({'error': f'Supabase connection failed: {str(e)}'}), 400
    elif data_source == 'snowflake':
        try:
            creds = {
                'account': CONFIG['snowflake_account'],
                'user': CONFIG['snowflake_user'],
                'password': CONFIG['snowflake_password'],
                'warehouse': CONFIG['snowflake_warehouse'],
                'database': CONFIG['snowflake_database'],
                'schema': CONFIG['snowflake_schema'],
                'role': CONFIG.get('snowflake_role')
            }
            db_service = DatabaseService()
            results = db_service.fetch_from_snowflake(creds, query='all')
            if isinstance(results, dict) and results.get('status') == 'error':
                return jsonify({'error': results['message']}), 400
            items = results
            return jsonify({'type': 'tables', 'items': items})
        except Exception as e:
            return jsonify({'error': f'Snowflake connection failed: {str(e)}'}), 400

    elif data_source == 'odoo':
        try:
            module = CONFIG.get('selected_module')
            creds = {
                'url': CONFIG['odoo_url'],
                'db': CONFIG['odoo_db'],
                'username': CONFIG['odoo_username'],
                'password': CONFIG['odoo_password']
            }
            db_service = DatabaseService()
            if module == 'CRM':
                model = 'res.partner'
            elif module == 'Inventory':
                model = 'product.product'
            elif module == 'Sales':
                model = 'sale.order'
            else:
                return jsonify({'error': 'Invalid module'}), 400
            results = db_service.fetch_from_odoo(creds, model)
            if isinstance(results, dict) and results.get('status') == 'error':
                return jsonify({'error': results['message']}), 400
            items = [str(record['id']) + ' - ' + (record.get('name') or record.get('display_name') or 'No name') for record in results]
            return jsonify({'type': 'items', 'items': items})
        except Exception as e:
            return jsonify({'error': f'Odoo connection failed: {str(e)}'}), 400

    else:
        return jsonify({'error': 'Invalid data source'}), 400

# --- Load sheets for Google Sheets ---
@main_bp.route('/load_sheets', methods=['POST'])
def load_sheets():
    data = request.json
    sheet_id = data.get('sheet_id')
    service_account_json_str = data.get('service_account_json')
    if not sheet_id or not service_account_json_str:
        return jsonify({'error': 'sheet_id and service_account_json required'}), 400
    try:
        service_json = json.loads(service_account_json_str)
        # Validate required keys for service account
        required_keys = ['type', 'project_id', 'private_key_id', 'private_key', 'client_email', 'client_id', 'auth_uri', 'token_uri', 'auth_provider_x509_cert_url', 'client_x509_cert_url']
        missing_keys = [key for key in required_keys if key not in service_json]
        if missing_keys:
            return jsonify({'error': f'Invalid Service Account JSON: missing keys {missing_keys}'}), 400
        if service_json.get('type') != 'service_account':
            return jsonify({'error': 'Invalid Service Account JSON: type must be service_account'}), 400
        creds = Credentials.from_service_account_info(service_json, scopes=["https://www.googleapis.com/auth/spreadsheets.readonly"])
        gc = gspread.authorize(creds)
        spreadsheet = gc.open_by_key(sheet_id)
        sheets = [ws.title for ws in spreadsheet.worksheets()]
        return jsonify({'sheets': sheets})
    except json.JSONDecodeError:
        return jsonify({'error': 'Invalid Service Account JSON: not valid JSON'}), 400
    except Exception as e:
        logging.error(f"Failed to load sheets: {str(e)}")
        return jsonify({'error': 'Failed to load sheets'}), 400

# --- Set selected items ---
@main_bp.route('/set_items', methods=['POST'])
def set_items():
    global worksheets, selected_tables, CONFIG
    data_source = CONFIG.get('data_source')
    selected = request.form.getlist('item_names')

    if data_source == 'google_sheets':
        worksheets[:] = [spreadsheet.worksheet(name) for name in selected]
        CONFIG['selected_sheets'] = selected
        selected_tables = []
    else:
        selected_tables = selected
        worksheets = []

    return jsonify({'selected_items': selected})

# --- Chat endpoint ---
@main_bp.route('/chat', methods=['POST'])
def chat():
    global worksheets, selected_tables, CONFIG, gemini_client, db_conn
    logging.info("Chat endpoint called")

    def get_access_token(service_json):
        """Obtain OAuth2 access token from service account JSON."""
        try:
            creds = Credentials.from_service_account_info(service_json, scopes=["https://www.googleapis.com/auth/spreadsheets.readonly"])
            creds.refresh(Request())
            return creds.token
        except Exception as e:
            logging.error(f"Failed to get access token: {str(e)}")
            return None

    def execute_gviz_query(sheet_id, query, sheet_gid, access_token):
        """Execute a Google Visualization API query and return parsed results."""
        base_url = f"https://docs.google.com/spreadsheets/d/{sheet_id}/gviz/tq"
        params = {
            'tq': query,
            'gid': sheet_gid
        }
        headers = {
            'Authorization': f'Bearer {access_token}'
        }
        try:
            response = requests.get(base_url, params=params, headers=headers)
            if response.status_code != 200:
                logging.error(f"GViz query failed with status {response.status_code}: {response.text}")
                return None
            # Response is JSONP, e.g. "/*O_o*/\ngoogle.visualization.Query.setResponse({...});"
            # Extract JSON inside the parentheses
            json_text = re.search(r'google\.visualization\.Query\.setResponse\((.*)\);', response.text, re.DOTALL)
            if not json_text:
                logging.error("Failed to parse GViz query response")
                return None
            data = json.loads(json_text.group(1))
            table = data.get('table')
            if not table:
                logging.error("No table data in GViz response")
                return None
            cols = [col['label'] or col['id'] for col in table.get('cols', [])]
            rows = table.get('rows', [])
            results = []
            for row in rows:
                entry = {}
                for i, cell in enumerate(row.get('c', [])):
                    val = cell.get('v') if cell else None
                    entry[cols[i]] = val
                results.append(entry)
            return results
        except Exception as e:
            logging.error(f"Exception during GViz query: {str(e)}")
            return None

    data = request.json
    share_key = data.get('share_key')
    if share_key:
        logging.info(f"Loading config for share_key: {share_key}")
        # Load config from shared chatbot
        conn = sqlite3.connect(DB_FILE)
        conn.row_factory = sqlite3.Row
        cursor = conn.cursor()
        cursor.execute("SELECT * FROM chatbots WHERE share_key=?", (share_key,))
        row = cursor.fetchone()
        conn.close()
        if not row:
            logging.error(f"Chatbot not found for share_key: {share_key}")
            return jsonify({'response': 'Chatbot not found'}), 404
        logging.info("Chatbot config loaded successfully")
        cb = dict(row)
        logging.info(f"Chatbot data_source: {cb['data_source']}, selected_sheets: {cb.get('selected_sheets')}, selected_tables: {cb.get('selected_tables')}")
        # Set CONFIG from cb
        CONFIG = {
            'gemini_api_key': cb['gemini_api_key'],
            'gemini_model': cb['gemini_model'],
            'data_source': cb['data_source'],
            'sheet_id': cb.get('sheet_id'),
            'service_account_json': cb.get('service_account_json'),
            'db_host': cb.get('db_host'),
            'db_port': cb.get('db_port'),
            'db_name': cb.get('db_name'),
            'db_username': cb.get('db_username'),
            'db_password': cb.get('db_password'),
            'mongo_uri': cb.get('mongo_uri'),
            'mongo_db_name': cb.get('mongo_db_name'),
            'airtable_api_key': cb.get('airtable_api_key'),
            'airtable_base_id': cb.get('airtable_base_id'),
            'databricks_hostname': cb.get('databricks_hostname'),
            'databricks_http_path': cb.get('databricks_http_path'),
            'databricks_token': cb.get('databricks_token'),
            'supabase_url': cb.get('supabase_url'),
            'supabase_anon_key': cb.get('supabase_anon_key'),
            'snowflake_account': cb.get('snowflake_account'),
            'snowflake_user': cb.get('snowflake_user'),
            'snowflake_password': cb.get('snowflake_password'),
            'snowflake_warehouse': cb.get('snowflake_warehouse'),
            'snowflake_database': cb.get('snowflake_database'),
            'snowflake_schema': cb.get('snowflake_schema'),
            'snowflake_role': cb.get('snowflake_role'),
            'odoo_url': cb.get('odoo_url'),
            'odoo_db': cb.get('odoo_db'),
            'odoo_username': cb.get('odoo_username'),
            'odoo_password': cb.get('odoo_password'),
            'selected_module': cb.get('selected_module')
        }
        selected_tables = json.loads(cb.get('selected_tables') or '[]')
        selected_sheets = json.loads(cb.get('selected_sheets') or '[]')
        CONFIG['selected_sheets'] = selected_sheets
        worksheets = []  # Reset
        try:
            gemini_client = genai.Client(api_key=CONFIG['gemini_api_key'])
            logging.info("Gemini client initialized successfully")
        except Exception as e:
            logging.error(f"Failed to initialize Gemini client: {str(e)}")
            return jsonify({'response': f'Failed to initialize Gemini: {str(e)}'})
        # Set up connections for shared chatbot
        db_conn = None
        if CONFIG['data_source'] == 'google_sheets':
            try:
                service_json = json.loads(CONFIG['service_account_json'])
                creds = Credentials.from_service_account_info(service_json, scopes=["https://www.googleapis.com/auth/spreadsheets.readonly"])
                gc = gspread.authorize(creds)
                spreadsheet = gc.open_by_key(CONFIG['sheet_id'])
                worksheets = [spreadsheet.worksheet(name) for name in selected_sheets]
                logging.info(f"Google Sheets setup successful, worksheets: {len(worksheets)}")
            except Exception as e:
                logging.error(f"Failed to set up Google Sheets for shared chatbot: {str(e)}")
                return jsonify({'response': f'Failed to set up Google Sheets: {str(e)}'})
        elif CONFIG['data_source'] == 'mysql':
            try:
                db_conn = pymysql.connect(
                    host=CONFIG['db_host'],
                    port=int(CONFIG['db_port']),
                    user=CONFIG['db_username'],
                    password=CONFIG['db_password'],
                    database=CONFIG['db_name']
                )
                logging.info("MySQL connection successful")
            except Exception as e:
                logging.error(f"Failed to connect to MySQL for shared chatbot: {str(e)}")
                return jsonify({'response': f'Failed to connect to MySQL: {str(e)}'})
        elif CONFIG['data_source'] == 'postgresql':
            try:
                db_conn = psycopg2.connect(
                    host=CONFIG['db_host'],
                    port=int(CONFIG['db_port']),
                    user=CONFIG['db_username'],
                    password=CONFIG['db_password'],
                    database=CONFIG['db_name']
                )
                logging.info("PostgreSQL connection successful")
            except Exception as e:
                logging.error(f"Failed to connect to PostgreSQL for shared chatbot: {str(e)}")
                return jsonify({'response': f'Failed to connect to PostgreSQL: {str(e)}'})
        elif CONFIG['data_source'] == 'neo4j':
            try:
                uri = CONFIG['db_host']
                if uri.startswith("neo4j+s://"):
                    uri = uri.replace("neo4j+s://", "neo4j+ssc://")
                elif uri.startswith("bolt+s://"):
                    uri = uri.replace("bolt+s://", "bolt+ssc://")
                elif uri.startswith("neo4j://") and "localhost" in uri:
                    uri = uri.replace("neo4j://", "bolt://")
                username = CONFIG['db_username']
                password = CONFIG['db_password']
                database = CONFIG['db_name']
                if database != 'neo4j':
                    logging.warning(f"Overriding Neo4j database name from {database} to 'neo4j' for Aura compatibility")
                    database = 'neo4j'
                CONFIG['db_name'] = database
                driver = GraphDatabase.driver(uri, auth=(username, password))
                db_conn = driver
                logging.info("Neo4j connection successful")
            except Exception as e:
                logging.error(f"Failed to connect to Neo4j for shared chatbot: {str(e)}")
                return jsonify({'response': f'Failed to connect to Neo4j: {str(e)}'})
        elif CONFIG['data_source'] == 'mongodb':
            try:
                client = MongoClient(CONFIG['mongo_uri'], tls=True, tlsAllowInvalidCertificates=True)
                db_conn = client[CONFIG['mongo_db_name']]
                logging.info("MongoDB connection successful")
            except Exception as e:
                logging.error(f"Failed to connect to MongoDB for shared chatbot: {str(e)}")
                return jsonify({'response': f'Failed to connect to MongoDB: {str(e)}'})
        # For other data sources (oracle, mssql, airtable, databricks, supabase, snowflake, odoo), connections are handled in the chat function using DatabaseService
        logging.info(f"Setup complete for data_source: {CONFIG['data_source']}")

    config_id = data.get('config_id')
    if config_id:
        # Load config from chat_configs
        conn = sqlite3.connect(DB_FILE)
        conn.row_factory = sqlite3.Row
        cursor = conn.cursor()
        cursor.execute("SELECT * FROM chat_configs WHERE id=?", (config_id,))
        row = cursor.fetchone()
        conn.close()
        if not row:
            return jsonify({'response': 'Config not found'}), 404
        cb = dict(row)
        # Set CONFIG from cb
        CONFIG = {
            'gemini_api_key': cb['gemini_api_key'],
            'gemini_model': cb['gemini_model'],
            'data_source': cb['data_source'],
            'sheet_id': cb.get('sheet_id'),
            'service_account_json': cb.get('service_account_json'),
            'db_host': cb.get('db_host'),
            'db_port': cb.get('db_port'),
            'db_name': cb.get('db_name'),
            'db_username': cb.get('db_username'),
            'db_password': cb.get('db_password'),
            'mongo_uri': cb.get('mongo_uri'),
            'mongo_db_name': cb.get('mongo_db_name'),
            'airtable_api_key': cb.get('airtable_api_key'),
            'airtable_base_id': cb.get('airtable_base_id'),
            'databricks_hostname': cb.get('databricks_hostname'),
            'databricks_http_path': cb.get('databricks_http_path'),
            'databricks_token': cb.get('databricks_token'),
            'supabase_url': cb.get('supabase_url'),
            'supabase_anon_key': cb.get('supabase_anon_key'),
            'snowflake_account': cb.get('snowflake_account'),
            'snowflake_user': cb.get('snowflake_user'),
            'snowflake_password': cb.get('snowflake_password'),
            'snowflake_warehouse': cb.get('snowflake_warehouse'),
            'snowflake_database': cb.get('snowflake_database'),
            'snowflake_schema': cb.get('snowflake_schema'),
            'snowflake_role': cb.get('snowflake_role'),
            'odoo_url': cb.get('odoo_url'),
            'odoo_db': cb.get('odoo_db'),
            'odoo_username': cb.get('odoo_username'),
            'odoo_password': cb.get('odoo_password'),
            'selected_module': cb.get('selected_module')
        }
        selected_tables = json.loads(cb.get('selected_tables') or '[]')
        selected_sheets = json.loads(cb.get('selected_sheets') or '[]')
        CONFIG['selected_sheets'] = selected_sheets
        worksheets = []  # Reset
        gemini_client = genai.Client(api_key=CONFIG['gemini_api_key'])
        # Set up connections for config_id
        db_conn = None
        if CONFIG['data_source'] == 'google_sheets':
            try:
                service_json = json.loads(CONFIG['service_account_json'])
                creds = Credentials.from_service_account_info(service_json, scopes=["https://www.googleapis.com/auth/spreadsheets.readonly"])
                gc = gspread.authorize(creds)
                spreadsheet = gc.open_by_key(CONFIG['sheet_id'])
                worksheets = [spreadsheet.worksheet(name) for name in selected_sheets]
            except Exception as e:
                logging.error(f"Failed to set up Google Sheets for config_id: {str(e)}")
                return jsonify({'response': f'Failed to set up Google Sheets: {str(e)}'})
        elif CONFIG['data_source'] == 'mysql':
            try:
                db_conn = pymysql.connect(
                    host=CONFIG['db_host'],
                    port=int(CONFIG['db_port']),
                    user=CONFIG['db_username'],
                    password=CONFIG['db_password'],
                    database=CONFIG['db_name']
                )
            except Exception as e:
                logging.error(f"Failed to connect to MySQL for config_id: {str(e)}")
                return jsonify({'response': f'Failed to connect to MySQL: {str(e)}'})
        elif CONFIG['data_source'] == 'postgresql':
            try:
                db_conn = psycopg2.connect(
                    host=CONFIG['db_host'],
                    port=int(CONFIG['db_port']),
                    user=CONFIG['db_username'],
                    password=CONFIG['db_password'],
                    database=CONFIG['db_name']
                )
            except Exception as e:
                logging.error(f"Failed to connect to PostgreSQL for config_id: {str(e)}")
                return jsonify({'response': f'Failed to connect to PostgreSQL: {str(e)}'})
        elif CONFIG['data_source'] == 'neo4j':
            try:
                uri = CONFIG['db_host']
                if uri.startswith("neo4j+s://"):
                    uri = uri.replace("neo4j+s://", "neo4j+ssc://")
                elif uri.startswith("bolt+s://"):
                    uri = uri.replace("bolt+s://", "bolt+ssc://")
                elif uri.startswith("neo4j://") and "localhost" in uri:
                    uri = uri.replace("neo4j://", "bolt://")
                username = CONFIG['db_username']
                password = CONFIG['db_password']
                database = CONFIG['db_name']
                if database != 'neo4j':
                    logging.warning(f"Overriding Neo4j database name from {database} to 'neo4j' for Aura compatibility")
                    database = 'neo4j'
                CONFIG['db_name'] = database
                driver = GraphDatabase.driver(uri, auth=(username, password))
                db_conn = driver
            except Exception as e:
                logging.error(f"Failed to connect to Neo4j for config_id: {str(e)}")
                return jsonify({'response': f'Failed to connect to Neo4j: {str(e)}'})
        elif CONFIG['data_source'] == 'mongodb':
            try:
                client = MongoClient(CONFIG['mongo_uri'], tls=True, tlsAllowInvalidCertificates=True)
                db_conn = client[CONFIG['mongo_db_name']]
            except Exception as e:
                logging.error(f"Failed to connect to MongoDB for config_id: {str(e)}")
                return jsonify({'response': f'Failed to connect to MongoDB: {str(e)}'})
        # For other data sources (oracle, mssql, airtable, databricks, supabase, snowflake, odoo), connections are handled in the chat function using DatabaseService

    data_source = CONFIG.get('data_source')
    user_input = data.get('message')

    if data_source == 'google_sheets' and not worksheets:
        return jsonify({'response': 'Select at least one sheet first.'})
    elif data_source in ['mysql', 'postgresql', 'neo4j', 'mongodb', 'oracle'] and not selected_tables:
        return jsonify({'response': 'Select at least one item first.'})

    user_input = request.json.get('message')

    # Parse limit from user input, e.g., "show me 5 rows"
    limit = None
    match = re.search(r'show me (\d+) rows?', user_input, re.IGNORECASE)
    if match:
        limit = int(match.group(1))

    if data_source == 'google_sheets':
        try:
            service_json = json.loads(CONFIG['service_account_json'])
        except Exception as e:
            logging.error(f"Invalid service account JSON: {str(e)}")
            return jsonify({'response': 'Invalid service account JSON.'})

        try:
            creds = Credentials.from_service_account_info(service_json, scopes=["https://www.googleapis.com/auth/spreadsheets.readonly"])
            gc = gspread.authorize(creds)
            spreadsheet = gc.open_by_key(CONFIG['sheet_id'])
        except Exception as e:
            logging.error(f"Failed to authorize or open spreadsheet: {str(e)}")
            return jsonify({'response': 'Failed to authorize or open spreadsheet'}), 400

        all_data = {}
        for sheet_name in CONFIG['selected_sheets']:
            try:
                worksheet = spreadsheet.worksheet(sheet_name)
                records = worksheet.get_all_records()[:1000]
                if limit:
                    records = records[:limit]
                all_data[sheet_name] = records
            except Exception as e:
                logging.error(f"Failed to fetch data from sheet {sheet_name} using gspread: {str(e)}")
                return jsonify({'response': f'Failed to fetch data from sheet {sheet_name} using gspread.'})
        data_desc = "Spreadsheet data (gspread, limited to 1000 rows per sheet)"
    elif data_source == 'neo4j':
        try:
            all_data = {}
            driver = db_conn
            with driver.session(database=CONFIG['db_name']) as session:
                for label in selected_tables:
                    result = session.run(f"MATCH (n:{label}) RETURN n")
                    records = [dict(record['n']) for record in result]
                    if limit:
                        records = records[:limit]
                    all_data[label] = records
            data_desc = "Graph data"
        except Exception as e:
            return jsonify({'response': f'Error fetching from Neo4j: {str(e)}'})
    elif data_source == 'mongodb':
        try:
            all_data = {}
            for collection in selected_tables:
                coll = db_conn[collection]
                documents = list(coll.find())
                if limit:
                    documents = documents[:limit]
                # Convert ObjectId and datetime to string for JSON serialization
                for doc in documents:
                    for key, value in doc.items():
                        if hasattr(value, '__class__'):
                            if value.__class__.__name__ == 'ObjectId':
                                doc[key] = str(value)
                            elif value.__class__.__name__ == 'datetime':
                                doc[key] = value.isoformat()
                all_data[collection] = documents
            data_desc = "MongoDB data"
        except Exception as e:
            return jsonify({'response': f'Error fetching from MongoDB: {str(e)}'})

    elif data_source == 'postgresql':
        all_data = {}
        for table in selected_tables:
            cursor = db_conn.cursor()
            # Wrap table name in double quotes to preserve case
            cursor.execute(f'SELECT * FROM "{table}"')
            columns = [desc[0] for desc in cursor.description]
            rows = cursor.fetchall()
            all_data[table] = [dict(zip(columns, row)) for row in rows]
            cursor.close()
        data_desc = "PostgreSQL data"
    elif data_source == 'oracle':
        all_data = {}
        version = int(CONFIG.get('oracle_version', 19))
        creds = {
            'host': CONFIG['db_host'],
            'port': int(CONFIG.get('db_port', 1521)),
            'user': CONFIG['db_username'],
            'password': CONFIG['db_password'],
            'service_name': CONFIG['db_name']
        }
        db_service = DatabaseService()
        for table in selected_tables:
            query = f"SELECT * FROM {table}"
            results = db_service.fetch_from_oracle(creds, query, version)
            if isinstance(results, dict) and results.get('status') == 'error':
                return jsonify({'response': f'Error fetching from {table}: {results["message"]}'})
            all_data[table] = results
        data_desc = "Oracle data"
    elif data_source == 'mssql':
        all_data = {}
        creds = {
            'host': CONFIG['db_host'],
            'port': int(CONFIG.get('db_port', 1433)),
            'user': CONFIG['db_username'],
            'password': CONFIG['db_password'],
            'database': CONFIG['db_name']
        }
        db_service = DatabaseService()
        for table in selected_tables:
            if '.' in table:
                schema, table_name = table.split('.', 1)
                query = f"SELECT TOP 1000 * FROM [{schema}].[{table_name}]"
            else:
                query = f"SELECT TOP 1000 * FROM [{table}]"
            results = db_service.fetch_from_mssql(creds, query)
            if isinstance(results, dict) and results.get('status') == 'error':
                return jsonify({'response': f'Error fetching from {table}: {results["message"]}'})
            all_data[table] = results
        data_desc = "MS SQL data"
    elif data_source == 'airtable':
        all_data = {}
        creds = {
            'api_key': CONFIG['airtable_api_key'],
            'base_id': CONFIG['airtable_base_id']
        }
        db_service = DatabaseService()
        for table in selected_tables:
            results = db_service.fetch_from_airtable(creds, table)
            if isinstance(results, dict) and results.get('status') == 'error':
                return jsonify({'response': f'Error fetching from {table}: {results["message"]}'})
            all_data[table] = results
        data_desc = "Airtable data"
    elif data_source == 'databricks':
        all_data = {}
        creds = {
            'server_hostname': CONFIG['databricks_hostname'],
            'http_path': CONFIG['databricks_http_path'],
            'access_token': CONFIG['databricks_token']
        }
        db_service = DatabaseService()
        for table in selected_tables:
            query = f"SELECT * FROM {table}"
            results = db_service.fetch_from_databricks(creds, query)
            if isinstance(results, dict) and results.get('status') == 'error':
                return jsonify({'response': f'Error fetching from {table}: {results["message"]}'})
            all_data[table] = results
        data_desc = "Databricks data"
    elif data_source == 'supabase':
        all_data = {}
        creds = {
            'url': CONFIG['supabase_url'],
            'anon_key': CONFIG['supabase_anon_key']
        }
        db_service = DatabaseService()
        for table in selected_tables:
            results = db_service.fetch_from_supabase(creds, table)
            if isinstance(results, dict) and results.get('status') == 'error':
                return jsonify({'response': f'Error fetching from {table}: {results["message"]}'})
            all_data[table] = results
        data_desc = "Supabase data"
    elif data_source == 'snowflake':
        all_data = {}
        creds = {
            'account': CONFIG['snowflake_account'],
            'user': CONFIG['snowflake_user'],
            'password': CONFIG['snowflake_password'],
            'warehouse': CONFIG['snowflake_warehouse'],
            'database': CONFIG['snowflake_database'],
            'schema': CONFIG['snowflake_schema'],
            'role': CONFIG.get('snowflake_role')
        }
        db_service = DatabaseService()
        for table in selected_tables:
            query = f"SELECT * FROM {table}"
            results = db_service.fetch_from_snowflake(creds, query)
            if isinstance(results, dict) and results.get('status') == 'error':
                return jsonify({'response': f'Error fetching from {table}: {results["message"]}'})
            all_data[table] = results
        data_desc = "Snowflake data"
    elif data_source == 'odoo':
        all_data = {}
        creds = {
            'url': CONFIG['odoo_url'],
            'db': CONFIG['odoo_db'],
            'username': CONFIG['odoo_username'],
            'password': CONFIG['odoo_password']
        }
        module = CONFIG.get('selected_module')
        if module == 'CRM':
            model = 'res.partner'
        elif module == 'Inventory':
            model = 'product.product'
        elif module == 'Sales':
            model = 'sale.order'
        else:
            return jsonify({'response': 'Invalid module'})
        db_service = DatabaseService()
        results = db_service.fetch_from_odoo(creds, model)
        if isinstance(results, dict) and results.get('status') == 'error':
            return jsonify({'response': f'Error fetching from {model}: {results["message"]}'})
        # Filter by selected_ids
        selected_ids = [int(item.split(' - ')[0]) for item in selected_tables]
        all_data[model] = [record for record in results if record['id'] in selected_ids]
        data_desc = "Odoo data"
    else:
        all_data = {}
        for table in selected_tables:
            cursor = db_conn.cursor()
            cursor.execute(f"SELECT * FROM {table}")
            columns = [desc[0] for desc in cursor.description]
            rows = cursor.fetchall()
            all_data[table] = [dict(zip(columns, row)) for row in rows]
            cursor.close()
        data_desc = "Database data"

    prompt = system_instruction + f"\nSpreadsheet data: {json.dumps(all_data, indent=2, default=str)}\nUser: {user_input}"
    logging.info(f"Prompt length: {len(prompt)}, data keys: {list(all_data.keys())}")

    if gemini_client is None:
        logging.error("Gemini client is None")
        return jsonify({'response': 'Gemini client not initialized. Please set credentials first.'})

    try:
        logging.info("Calling Gemini API")
        response = gemini_client.models.generate_content(
            model=CONFIG['gemini_model'],
            contents=prompt
        )
        logging.info("Gemini API call completed")
    except Exception as e:
        logging.error(f"Gemini API error: {str(e)}")
        return jsonify({'response': f'Gemini API error: {str(e)}'})

    bot_reply = "Sorry, no response."
    if response.candidates and response.candidates[0].content.parts:
        bot_reply = response.candidates[0].content.parts[0].text
        logging.info(f"Bot reply length: {len(bot_reply)}")
    else:
        logging.warning("No candidates or parts in Gemini response")

    return jsonify({'response': bot_reply})

# --- Save chatbot ---
@main_bp.route('/save_chatbot', methods=['POST'])
def save_chatbot():
    try:
        data = request.form
        logging.info(f"Received save_chatbot data: {dict(data)}")
        # Validation: Check required fields
        required_fields = ['chatbot_id', 'chatbot_name', 'gemini_api_key', 'gemini_model']
        for field in required_fields:
            if not data.get(field):
                logging.error(f"Missing required field: {field}")
                return jsonify({"success": False, "message": f"{field} is required"}), 400

        username = data['username']
        data_source = data.get('data_source')

        conn = sqlite3.connect(DB_FILE)
        cursor = conn.cursor()
        # Removed user existence check as username is not required for saving chatbot

        # Initialize all conditional variables to None
        selected_sheets = None
        selected_tables = None
        selected_collections = None
        db_host = None
        db_port = None
        db_name = None
        db_username = None
        db_password = None
        mongo_uri = None
        mongo_db_name = None
        airtable_api_key = None
        airtable_base_id = None
        databricks_hostname = None
        databricks_http_path = None
        databricks_token = None
        supabase_url = None
        supabase_anon_key = None
        snowflake_account = None
        snowflake_user = None
        snowflake_password = None
        snowflake_warehouse = None
        snowflake_database = None
        snowflake_schema = None
        snowflake_role = None

        if data_source == 'google_sheets':
            selected_sheets = data.get('selected_sheets')
        elif data_source == 'neo4j':
            selected_tables = data.get('selected_tables')
            db_host = data.get('neo4j_uri')
            db_port = None
            db_name = data.get('neo4j_db_name')
            logging.info(f"Saving Neo4j chatbot: db_name={db_name}")
            db_username = data.get('neo4j_username')
            db_password = data.get('neo4j_password')
        elif data_source == 'mongodb':
            selected_collections = data.get('selected_collections')
            mongo_uri = data.get('mongo_uri')
            mongo_db_name = data.get('mongo_db_name')
        elif data_source == 'oracle':
            selected_tables = data.get('selected_tables')
            db_host = data.get('db_host')
            db_port_str = data.get('db_port')
            db_port = int(db_port_str) if db_port_str else None
            db_name = data.get('db_name')
            db_username = data.get('db_username')
            db_password = data.get('db_password')
        elif data_source == 'mssql':
            selected_tables = data.get('selected_tables')
            db_host = data.get('db_host')
            db_port_str = data.get('db_port')
            db_port = int(db_port_str) if db_port_str else None
            db_name = data.get('db_name')
            db_username = data.get('db_username')
            db_password = data.get('db_password')
        elif data_source == 'airtable':
            selected_tables = data.get('selected_tables')
            airtable_api_key = data.get('airtable_api_key')
            airtable_base_id = data.get('airtable_base_id')
        elif data_source == 'databricks':
            selected_tables = data.get('selected_tables')
            databricks_hostname = data.get('databricks_hostname')
            databricks_http_path = data.get('databricks_http_path')
            databricks_token = data.get('databricks_token')
        elif data_source == 'supabase':
            selected_tables = data.get('selected_tables')
            supabase_url = data.get('supabase_url')
            supabase_anon_key = data.get('supabase_anon_key')
        elif data_source == 'snowflake':
            selected_tables = data.get('selected_tables')
            snowflake_account = data.get('snowflake_account')
            snowflake_user = data.get('snowflake_user')
            snowflake_password = data.get('snowflake_password')
            snowflake_warehouse = data.get('snowflake_warehouse')
            snowflake_database = data.get('snowflake_database')
            snowflake_schema = data.get('snowflake_schema')
            snowflake_role = data.get('snowflake_role')
        elif data_source == 'odoo':
            selected_tables = data.get('selected_tables')
            # Odoo fields are handled in chatbot_data
        else:
            selected_tables = data.get('selected_tables')
            db_host = data.get('db_host')
            db_port_str = data.get('db_port')
            db_port = int(db_port_str) if db_port_str else None
            db_name = data.get('db_name')
            db_username = data.get('db_username')
            db_password = data.get('db_password')

        # Generate share_key if not exists
        share_key = data.get('share_key')
        if not share_key:
            share_key = secrets.token_urlsafe(16)
            logging.info(f"Generated new share_key: {share_key}")

        chatbot_data = {
            'id': data['chatbot_id'],
            'username': username,
            'chatbot_name': data['chatbot_name'],
            'gemini_api_key': data['gemini_api_key'],
            'gemini_model': data['gemini_model'],
            'data_source': data_source,
            'sheet_id': data.get('sheet_id'),
            'selected_sheets': selected_sheets,
            'service_account_json': data.get('service_account_json'),
            'db_host': db_host,
            'db_port': db_port,
            'db_name': db_name,
            'db_username': db_username,
            'db_password': db_password,
            'selected_tables': selected_tables,
            'mongo_uri': mongo_uri,
            'mongo_db_name': mongo_db_name,
            'selected_collections': selected_collections,
            'airtable_api_key': airtable_api_key,
            'airtable_base_id': airtable_base_id,
            'databricks_hostname': databricks_hostname,
            'databricks_http_path': databricks_http_path,
            'databricks_token': databricks_token,
            'supabase_url': supabase_url,
            'supabase_anon_key': supabase_anon_key,
            'snowflake_account': snowflake_account,
            'snowflake_user': snowflake_user,
            'snowflake_password': snowflake_password,
            'snowflake_warehouse': snowflake_warehouse,
            'snowflake_database': snowflake_database,
            'snowflake_schema': snowflake_schema,
            'snowflake_role': snowflake_role,
            'odoo_url': data.get('odoo_url'),
            'odoo_db': data.get('odoo_db'),
            'odoo_username': data.get('odoo_username'),
            'odoo_password': data.get('odoo_password'),
            'selected_module': data.get('selected_module'),
            'share_key': share_key,
            'company_logo': data.get('company_logo'),
            'nav_color': data.get('nav_color'),
            'text_color': data.get('text_color'),
            'content_bg_color': data.get('content_bg_color'),
            'textarea_color': data.get('textarea_color'),
            'textarea_border_color': data.get('textarea_border_color'),
            'textarea_border_thickness': data.get('textarea_border_thickness'),
            'button_color': data.get('button_color'),
            'button_text_color': data.get('button_text_color'),
            'border_color': data.get('border_color'),
            'border_thickness': data.get('border_thickness'),
            'nav_border_color': data.get('nav_border_color'),
            'nav_border_thickness': data.get('nav_border_thickness')
        }
        db_service = DatabaseService()
        db_service.save_chatbot(chatbot_data)
        # Return share_key in response
        return jsonify({"share_key": share_key})
    except Exception as e:
        # Logging: Log exceptions
        logging.error(f"Error saving chatbot: {str(e)}")
        return jsonify({"success": False, "message": str(e)}), 500

# # --- Save chat config ---
# @main_bp.route('/save_chat_config', methods=['POST'])
# def save_chat_config():
#     try:
#         data = request.form
#         conn = sqlite3.connect(DB_FILE)
#         cursor = conn.cursor()
#         cursor.execute("""
#             INSERT INTO chat_configs (
#                 data_source, gemini_api_key, gemini_model, sheet_id, service_account_json,
#                 db_host, db_port, db_name, db_username, db_password, selected_sheets, selected_tables,
#                 neo4j_uri, neo4j_db_name, neo4j_username, neo4j_password, mongo_uri, mongo_db_name,
#                 airtable_api_key, airtable_base_id, databricks_hostname, databricks_http_path, databricks_token,
#                 supabase_url, supabase_anon_key, snowflake_account, snowflake_user, snowflake_password,
#                 snowflake_warehouse, snowflake_database, snowflake_schema, snowflake_role,
#                 odoo_url, odoo_db, odoo_username, odoo_password, selected_module
#             ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
#         """, (
#             data.get('data_source'),
#             data.get('gemini_api_key'),
#             data.get('gemini_model'),
#             data.get('sheet_id'),
#             data.get('service_account_json'),
#             data.get('db_host'),
#             data.get('db_port'),
#             data.get('db_name'),
#             data.get('db_username'),
#             data.get('db_password'),
#             data.get('selected_sheets'),
#             data.get('selected_tables'),
#             data.get('neo4j_uri'),
#             data.get('neo4j_db_name'),
#             data.get('neo4j_username'),
#             data.get('neo4j_password'),
#             data.get('mongo_uri'),
#             data.get('mongo_db_name'),
#             data.get('airtable_api_key'),
#             data.get('airtable_base_id'),
#             data.get('databricks_hostname'),
#             data.get('databricks_http_path'),
#             data.get('databricks_token'),
#             data.get('supabase_url'),
#             data.get('supabase_anon_key'),
#             data.get('snowflake_account'),
#             data.get('snowflake_user'),
#             data.get('snowflake_password'),
#             data.get('snowflake_warehouse'),
#             data.get('snowflake_database'),
#             data.get('snowflake_schema'),
#             data.get('snowflake_role'),
#             data.get('odoo_url'),
#             data.get('odoo_db'),
#             data.get('odoo_username'),
#             data.get('odoo_password'),
#             data.get('selected_module')
#         ))
#         config_id = cursor.lastrowid
#         conn.commit()
#         conn.close()
#         return jsonify({"config_id": config_id})
#     except Exception as e:
#         logging.error(f"Error saving chat config: {str(e)}")
#         return jsonify({"success": False, "message": str(e)}), 500

# --- Check chatbot count for restrictions ---
@main_bp.route('/check_chatbot_count', methods=['GET'])
def check_chatbot_count():
    username = request.args.get('username')
    if not username:
        return jsonify({"error": "Username required"}), 400
    conn = sqlite3.connect(DB_FILE)
    cursor = conn.cursor()
    cursor.execute("SELECT COUNT(*) FROM chatbots WHERE username=? AND data_source='google_sheets'", (username,))
    count = cursor.fetchone()[0]
    conn.close()
    return jsonify({"count": count})

# --- List saved chatbots ---
@main_bp.route('/list_chatbots', methods=['GET'])
def list_chatbots():
    username = request.args.get('username')
    if not username:
        return jsonify({"error": "Username required"}), 400
    conn = sqlite3.connect(DB_FILE)
    conn.row_factory = sqlite3.Row
    cursor = conn.cursor()
    cursor.execute("SELECT * FROM chatbots WHERE username=?", (username,))
    rows = cursor.fetchall()
    conn.close()
    return jsonify([dict(row) for row in rows])

# --- Shared Chatbot ---
@main_bp.route('/shared/<share_key>', methods=['GET'])
def shared_chatbot(share_key):
    logging.info(f"Shared chatbot requested with share_key: {share_key}")
    conn = sqlite3.connect(DB_FILE)
    conn.row_factory = sqlite3.Row
    cursor = conn.cursor()
    cursor.execute("SELECT * FROM chatbots WHERE share_key=?", (share_key,))
    row = cursor.fetchone()
    conn.close()
    if not row:
        logging.warning(f"Chatbot not found for share_key: {share_key}")
        return "Chatbot not found", 404

    logging.info(f"Chatbot found for share_key: {share_key}, chatbot_name: {row['chatbot_name']}")

    cb = dict(row)
    # Apply default styles if not set
    styles = {
        'nav_color': cb.get('nav_color', '#007bff'),
        'text_color': cb.get('text_color', '#000000'),
        'content_bg_color': cb.get('content_bg_color', '#ffffff'),
        'textarea_color': cb.get('textarea_color', '#ffffff'),
        'textarea_border_color': cb.get('textarea_border_color', '#cccccc'),
        'textarea_border_thickness': cb.get('textarea_border_thickness', '1px'),
        'button_color': cb.get('button_color', '#007bff'),
        'button_text_color': cb.get('button_text_color', '#ffffff'),
        'border_color': cb.get('border_color', '#007bff'),
        'border_thickness': cb.get('border_thickness', '2px'),
        'nav_border_color': cb.get('nav_border_color', '#007bff'),
        'nav_border_thickness': cb.get('nav_border_thickness', '2px'),
        'company_logo': cb.get('company_logo', '')
    }

    html = f"""
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>{cb['chatbot_name']}</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>
            body {{
                background-color: {styles['content_bg_color']};
                color: {styles['text_color']};
            }}
            .navbar {{
                background-color: {styles['nav_color']} !important;
                border-bottom: {styles['nav_border_thickness']} solid {styles['nav_border_color']} !important;
            }}
            .chat-container {{
                border: {styles['border_thickness']} solid {styles['border_color']};
                border-radius: 5px;
                padding: 20px;
                margin-top: 20px;
            }}
            .form-control {{
                background-color: {styles['textarea_color']} !important;
                border-color: {styles['textarea_border_color']} !important;
                border-width: {styles['textarea_border_thickness']} !important;
            }}
            .btn-primary {{
                background-color: {styles['button_color']} !important;
                border-color: {styles['button_color']} !important;
                color: {styles['button_text_color']} !important;
            }}
        </style>
    </head>
    <body>
        <nav class="navbar navbar-expand-lg">
            <div class="container">
                {f'<img src="{styles["company_logo"]}" alt="Logo" style="height: 40px; margin-right: 10px;">' if styles['company_logo'] else ''}
                <span class="navbar-brand">{cb['chatbot_name']}</span>
            </div>
        </nav>
        <div class="container">
            <div class="chat-container">
                <h4>Chat with {cb['chatbot_name']}</h4>
                <div id="chat" class="mb-3" style="height: 300px; overflow-y: auto; border: 1px solid #ccc; padding: 10px;"></div>
                <div class="input-group">
                    <input type="text" id="user_input" class="form-control" placeholder="Ask about your data...">
                    <button class="btn btn-primary" onclick="sendMessage()">Send</button>
                </div>
            </div>
        </div>
        <script>
            const API_BASE = window.location.origin;
            const shareKey = "{share_key}";

            async function sendMessage() {{
                const input = document.getElementById('user_input').value;
                if(!input) return;
                const chatDiv = document.getElementById('chat');
                chatDiv.innerHTML += `<p class="user"><b>You:</b> ${{input}}</p>`;

                const res = await fetch(`${{API_BASE}}/chat`, {{
                    method:'POST',
                    headers:{{'Content-Type':'application/json'}},
                    body: JSON.stringify({{message: input, share_key: shareKey}})
                }});
                const data = await res.json();
                chatDiv.innerHTML += `<p class="bot"><b>Bot:</b> ${{data.response}}</p>`;
                chatDiv.scrollTop = chatDiv.scrollHeight;
                document.getElementById('user_input').value = '';
            }}
        </script>
    </body>
    </html>
    """
    return html
