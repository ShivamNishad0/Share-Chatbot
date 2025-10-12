import sqlite3
import logging
import oracledb
import pymssql  # Commented out to avoid import error
from pyairtable import Api
from databricks import sql
from supabase import create_client, Client
import snowflake.connector
from neo4j import GraphDatabase
import xmlrpc.client
import ssl
import os


logger = logging.getLogger(__name__)

class DatabaseService:
    def __init__(self, db_file='chatbots.db'):
        self.db_file = db_file

    def get_connection(self):
        return sqlite3.connect(self.db_file)

    def init_db(self):
        conn = self.get_connection()
        cursor = conn.cursor()

        # Create users table
        cursor.execute("""
            CREATE TABLE IF NOT EXISTS users (
                username TEXT PRIMARY KEY,
                password TEXT
            )
        """)

        # Create chatbots table
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
                odoo_url TEXT;
                odoo_db TEXT;
                odoo_username TEXT;
                odoo_password TEXT;
                selected_module TEXT;
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
                nav_border_thickness TEXT
            )
        """)


        conn.commit()
        conn.close()
        logger.info("Database initialized successfully")

    def create_user(self, username, password):
        conn = self.get_connection()
        cursor = conn.cursor()
        cursor.execute("INSERT INTO users (username, password) VALUES (?, ?)", (username, password))
        conn.commit()
        conn.close()

    def get_user(self, username):
        conn = self.get_connection()
        cursor = conn.cursor()
        cursor.execute("SELECT * FROM users WHERE username=?", (username,))
        row = cursor.fetchone()
        conn.close()
        return row


    def save_chatbot(self, chatbot_data):
        conn = self.get_connection()
        cursor = conn.cursor()
        cursor.execute("""
            INSERT OR REPLACE INTO chatbots (id, username, chatbot_name, gemini_api_key, gemini_model, data_source, sheet_id, selected_sheets, service_account_json, db_host, db_port, db_name, db_username, db_password, selected_tables, mongo_uri, mongo_db_name, selected_collections, airtable_api_key, airtable_base_id, databricks_hostname, databricks_http_path, databricks_token, supabase_url, supabase_anon_key, snowflake_account, snowflake_user, snowflake_password, snowflake_warehouse, snowflake_database, snowflake_schema, snowflake_role, share_key, company_logo, nav_color, text_color, content_bg_color, textarea_color, textarea_border_color, textarea_border_thickness, button_color, button_text_color, border_color, border_thickness, nav_border_color, nav_border_thickness, odoo_url, odoo_db, odoo_username, odoo_password, selected_module)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        """, (
            chatbot_data['id'],
            chatbot_data['username'],
            chatbot_data['chatbot_name'],
            chatbot_data['gemini_api_key'],
            chatbot_data['gemini_model'],
            chatbot_data['data_source'],
            chatbot_data.get('sheet_id'),
            chatbot_data.get('selected_sheets'),
            chatbot_data.get('service_account_json'),
            chatbot_data.get('db_host'),
            chatbot_data.get('db_port'),
            chatbot_data.get('db_name'),
            chatbot_data.get('db_username'),
            chatbot_data.get('db_password'),
            chatbot_data.get('selected_tables'),
            chatbot_data.get('mongo_uri'),
            chatbot_data.get('mongo_db_name'),
            chatbot_data.get('selected_collections'),
            chatbot_data.get('airtable_api_key'),
            chatbot_data.get('airtable_base_id'),
            chatbot_data.get('databricks_hostname'),
            chatbot_data.get('databricks_http_path'),
            chatbot_data.get('databricks_token'),
            chatbot_data.get('supabase_url'),
            chatbot_data.get('supabase_anon_key'),
            chatbot_data.get('snowflake_account'),
            chatbot_data.get('snowflake_user'),
            chatbot_data.get('snowflake_password'),
            chatbot_data.get('snowflake_warehouse'),
            chatbot_data.get('snowflake_database'),
            chatbot_data.get('snowflake_schema'),
            chatbot_data.get('snowflake_role'),
            chatbot_data.get('share_key'),
            chatbot_data.get('company_logo'),
            chatbot_data.get('nav_color'),
            chatbot_data.get('text_color'),
            chatbot_data.get('content_bg_color'),
            chatbot_data.get('textarea_color'),
            chatbot_data.get('textarea_border_color'),
            chatbot_data.get('textarea_border_thickness'),
            chatbot_data.get('button_color'),
            chatbot_data.get('button_text_color'),
            chatbot_data.get('border_color'),
            chatbot_data.get('border_thickness'),
            chatbot_data.get('nav_border_color'),
            chatbot_data.get('nav_border_thickness'),
            chatbot_data.get('odoo_url'),
            chatbot_data.get('odoo_db'),
            chatbot_data.get('odoo_username'),
            chatbot_data.get('odoo_password'),
            chatbot_data.get('selected_module')
        ))
        conn.commit()
        conn.close()

    def get_chatbots_by_user(self, username):
        conn = self.get_connection()
        conn.row_factory = sqlite3.Row
        cursor = conn.cursor()
        cursor.execute("SELECT * FROM chatbots WHERE username=?", (username,))
        rows = cursor.fetchall()
        conn.close()
        return [dict(row) for row in rows]

    def fetch_from_oracle(self, creds, query=None, version=None):
        """
        Fetch data from Oracle 19c or 23c using python-oracledb (thin mode).

        Args:
            creds (dict): Must have 'host', 'port', 'user', 'password', 'service_name'
            query (str): SQL SELECT query
            version (int): Oracle version (19 or 23)

        Returns:
            list[dict]: Query results as list of dicts
        """
        conn = None
        cur = None

        if version == 23:
            user = "C##" + creds["user"].upper() if not creds["user"].startswith("C##") else creds["user"].upper()
        else:
            user = creds["user"]

        try:
            # Build DSN
            dsn = oracledb.makedsn(
                creds["host"],
                creds.get("port", 1521),
                service_name=creds["service_name"]
            )

            # Connect
            conn = oracledb.connect(
                user=user,
                password=creds["password"],
                dsn=dsn
            )
            cur = conn.cursor()

            # Execute query
            cur.execute(query)

            # Extract results
            columns = [col[0] for col in cur.description]
            rows = cur.fetchall()
            results = [dict(zip(columns, row)) for row in rows]

            return results

        except Exception as e:
            logger.error(f"Oracle fetch failed: {str(e)}")
            return {"status": "error", "message": f"Oracle fetch failed: {str(e)}"}
        finally:
            if cur:
                cur.close()
            if conn:
                conn.close()

    def fetch_from_mssql(self, creds, query=None):
        """
        Fetch data from MS SQL Server using pymssql.

        Args:
            creds (dict): Must have 'host', 'port', 'user', 'password', 'database'
            query (str): SQL SELECT query

        Returns:
            list[dict]: Query results as list of dicts
        """
        conn = None
        cur = None

        try:
            # Connect
            conn = pymssql.connect(
                server=creds['host'],
                port=creds.get('port', 1433),
                user=creds['user'],
                password=creds['password'],
                database=creds['database']
            )
            cur = conn.cursor()

            # Execute query
            cur.execute(query)

            # Extract results
            columns = [column[0] for column in cur.description]
            rows = cur.fetchall()
            results = [dict(zip(columns, row)) for row in rows]

            return results

        except Exception as e:
            logger.error(f"MS SQL fetch failed: {str(e)}")
            return {"status": "error", "message": f"MS SQL fetch failed: {str(e)}"}
        finally:
            if cur:
                cur.close()
            if conn:
                conn.close()

    def fetch_from_airtable(self, creds, query=None):
        """
        Fetch data from Airtable using pyairtable.

        Args:
            creds (dict): Must have 'api_key', 'base_id'
            query (str): Table name to fetch from

        Returns:
            list[dict]: Records as list of dicts
        """
        try:
            api = Api(creds['api_key'])
            base = api.base(creds['base_id'])
            table = base.table(query)
            records = table.all()

            results = []
            for record in records:
                results.append({'id': record['id'], 'fields': record['fields']})
            return results

        except Exception as e:
            logger.error(f"Airtable fetch failed: {str(e)}")
            return {"status": "error", "message": f"Airtable fetch failed: {str(e)}"}

    def fetch_from_databricks(self, creds, query=None):
            """
            Fetch data from Databricks using databricks-sql-connector.

            Args:
                creds (dict): Must have 'server_hostname', 'http_path', 'access_token'
                query (str): SQL SELECT query

            Returns:
                list[dict]: Query results as list of dicts
            """
            conn = None
            cur = None

            try:
                # Connect with system CA or custom CA file for SSL verification
                ssl_ctx = ssl.create_default_context()
                conn = sql.connect(
                    server_hostname=creds['server_hostname'],
                    http_path=creds['http_path'],
                    access_token=creds['access_token'],
                    _tls_ctx=ssl_ctx,
                    timeout=60
                )
                cur = conn.cursor()

                # Execute query
                cur.execute(query)

                # Extract results
                columns = [desc[0] for desc in cur.description]
                rows = cur.fetchall()
                results = [dict(zip(columns, row)) for row in rows]

                return results

            except Exception as e:
                logger.error(f"Databricks fetch failed: {str(e)}")
                return {"status": "error", "message": f"Databricks fetch failed: {str(e)}"}
            finally:
                if cur:
                    cur.close()
                if conn:
                    conn.close()
    def fetch_from_supabase(self, creds, query=None):
        """
        Fetch data from Supabase using supabase-py.

        Args:
            creds (dict): Must have 'url', 'anon_key'
            query (str): Table name to fetch from, or None/'all' to list all tables

        Returns:
            list[dict] or list[str]: Records as list of dicts, or list of table names if query is None/'all'
        """
        try:
            supabase: Client = create_client(creds['url'], creds['anon_key'])
            if query is None or query.lower() == 'all':
                # Fetch all table names
                import requests
                api_url = creds['url'].rstrip('/') + '/rest/v1/'
                headers = {
                    'Authorization': f'Bearer {creds["anon_key"]}',
                    'apikey': creds['anon_key']
                }
                response = requests.get(api_url, headers=headers)
                if response.status_code == 200:
                    spec = response.json()
                    paths = spec.get('paths', {})
                    results = [path.strip('/') for path in paths.keys() if path.startswith('/') and path != '/']
                else:
                    results = []
            else:
                response = supabase.table(query).select('*').execute()
                results = response.data
            return results
        except Exception as e:
            logger.error(f"Supabase fetch failed: {str(e)}")
            return {"status": "error", "message": f"Supabase fetch failed: {str(e)}"}

    def fetch_from_snowflake(self, creds, query=None):
        """
        Fetch data from Snowflake using snowflake-connector-python.

        Args:
            creds (dict): Must have 'account', 'user', 'password', 'warehouse', 'database', 'schema', 'role'
            query (str): SQL SELECT query, or None/'all' to list all tables

        Returns:
            list[dict]: Query results as list of dicts, or list of table names if query is None/'all'
        """
        conn = None
        cur = None

        try:
            # Connect
            conn = snowflake.connector.connect(
                account=creds['account'],
                user=creds['user'],
                password=creds['password'],
                warehouse=creds['warehouse'],
                database=creds['database'],
                schema=creds['schema'],
                role=creds.get('role')
            )
            cur = conn.cursor()

            if query is None or query.lower() == 'all':
                # List all tables
                cur.execute("SHOW TABLES")
                results = [row[1] for row in cur.fetchall()]  # TABLE_NAME is second column
            else:
                # Execute query
                cur.execute(query)

                # Extract results
                columns = [desc[0] for desc in cur.description]
                rows = cur.fetchall()
                results = [dict(zip(columns, row)) for row in rows]

            return results

        except Exception as e:
            logger.error(f"Snowflake fetch failed: {str(e)}")
            return {"status": "error", "message": f"Snowflake fetch failed: {str(e)}"}
        finally:
            if cur:
                cur.close()
            if conn:
                conn.close()

    def fetch_from_odoo(self, creds, query=None):
        """
        Fetch data from Odoo using XML-RPC.

        Args:
            creds (dict): Must have 'url', 'db', 'username', 'password'
            query (str): Model name to fetch from, e.g. 'product.product'

        Returns:
            list[dict]: Records as list of dicts
        """
        try:
            url = creds['url']
            db = creds['db']
            username = creds['username']
            password = creds['password']

            # Create XML-RPC proxies with SSL certificate verification disabled
            import ssl
            context = ssl._create_unverified_context()

            common = xmlrpc.client.ServerProxy('{}/xmlrpc/2/common'.format(url), context=context)
            uid = common.authenticate(db, username, password, {})
            if uid is False:
                return {"status": "error", "message": "Authentication failed"}

            models = xmlrpc.client.ServerProxy('{}/xmlrpc/2/object'.format(url), context=context)

            if query is None or query.lower() == 'all':
                # For now, return empty list; we handle specific models in routes
                return []
            else:
                # query is the model name
                ids = models.execute_kw(db, uid, password, query, 'search', [[]])
                records = models.execute_kw(db, uid, password, query, 'read', [ids])
                return records

        except Exception as e:
            logger.error(f"Odoo fetch failed: {str(e)}")
            return {"status": "error", "message": f"Odoo fetch failed: {str(e)}"}
