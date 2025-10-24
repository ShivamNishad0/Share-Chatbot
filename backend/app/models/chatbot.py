class Chatbot:
    """
    Represents a chatbot configuration with various data source integrations.

    This class encapsulates all the necessary information to configure and run a chatbot
    that can connect to multiple data sources like Google Sheets, databases, APIs, etc.
    It supports different authentication methods and data source types.

    Attributes:
        id (str): Unique identifier for the chatbot
        username (str): Owner of the chatbot
        chatbot_name (str): Display name of the chatbot
        gemini_api_key (str): API key for Google Gemini AI
        gemini_model (str): Gemini model to use (e.g., 'gemini-1.5-flash')
        data_source (str): Type of data source ('google_sheets', 'mysql', 'postgresql', etc.)
        sheet_id (str): Google Sheets ID (for Google Sheets data source)
        selected_sheets (list): List of selected sheet names
        service_account_json (str): JSON string for Google service account credentials
        db_host (str): Database host URL/IP
        db_port (int): Database port number
        db_name (str): Database name
        db_username (str): Database username
        db_password (str): Database password
        selected_tables (list): List of selected table names
        mongo_uri (str): MongoDB connection URI
        mongo_db_name (str): MongoDB database name
        selected_collections (list): List of selected MongoDB collections
        airtable_api_key (str): Airtable API key
        airtable_base_id (str): Airtable base ID
        databricks_hostname (str): Databricks server hostname
        databricks_http_path (str): Databricks HTTP path
        databricks_token (str): Databricks access token
        odoo_url (str): Odoo server URL
        odoo_db (str): Odoo database name
        odoo_username (str): Odoo username
        odoo_password (str): Odoo password
        selected_module (str): Selected Odoo module ('CRM', 'Inventory', 'Sales')
        shared_username (str): Username for shared chatbot access
        shared_password (str): Password for shared chatbot access
    """

    def __init__(self, id, username, chatbot_name, gemini_api_key, gemini_model,
                 data_source=None, sheet_id=None, selected_sheets=None,
                 service_account_json=None, db_host=None, db_port=None,
                 db_name=None, db_username=None, db_password=None,
                 selected_tables=None, mongo_uri=None, mongo_db_name=None,
                 selected_collections=None, airtable_api_key=None, airtable_base_id=None,
                 databricks_hostname=None, databricks_http_path=None, databricks_token=None,
                 odoo_url=None, odoo_db=None, odoo_username=None, odoo_password=None, selected_module=None,
                 shared_username=None, shared_password=None):
        self.id = id
        self.username = username
        self.chatbot_name = chatbot_name
        self.gemini_api_key = gemini_api_key
        self.gemini_model = gemini_model
        self.data_source = data_source
        self.sheet_id = sheet_id
        self.selected_sheets = selected_sheets
        self.service_account_json = service_account_json
        self.db_host = db_host
        self.db_port = db_port
        self.db_name = db_name
        self.db_username = db_username
        self.db_password = db_password
        self.selected_tables = selected_tables
        self.mongo_uri = mongo_uri
        self.mongo_db_name = mongo_db_name
        self.selected_collections = selected_collections
        self.airtable_api_key = airtable_api_key
        self.airtable_base_id = airtable_base_id
        self.databricks_hostname = databricks_hostname
        self.databricks_http_path = databricks_http_path
        self.databricks_token = databricks_token
        self.odoo_url = odoo_url
        self.odoo_db = odoo_db
        self.odoo_username = odoo_username
        self.odoo_password = odoo_password
        self.selected_module = selected_module
        self.shared_username = shared_username
        self.shared_password = shared_password

    def to_dict(self):
        """
        Convert the Chatbot instance to a dictionary representation.

        Returns:
            dict: Dictionary containing all chatbot attributes
        """
        return {
            'id': self.id,
            'username': self.username,
            'chatbot_name': self.chatbot_name,
            'gemini_api_key': self.gemini_api_key,
            'gemini_model': self.gemini_model,
            'data_source': self.data_source,
            'sheet_id': self.sheet_id,
            'selected_sheets': self.selected_sheets,
            'service_account_json': self.service_account_json,
            'db_host': self.db_host,
            'db_port': self.db_port,
            'db_name': self.db_name,
            'db_username': self.db_username,
            'db_password': self.db_password,
            'selected_tables': self.selected_tables,
            'mongo_uri': self.mongo_uri,
            'mongo_db_name': self.mongo_db_name,
            'selected_collections': self.selected_collections,
            'airtable_api_key': self.airtable_api_key,
            'airtable_base_id': self.airtable_base_id,
            'databricks_hostname': self.databricks_hostname,
            'databricks_http_path': self.databricks_http_path,
            'databricks_token': self.databricks_token,
            'odoo_url': self.odoo_url,
            'odoo_db': self.odoo_db,
            'odoo_username': self.odoo_username,
            'odoo_password': self.odoo_password,
            'selected_module': self.selected_module,
            'shared_username': self.shared_username,
            'shared_password': self.shared_password
        }

    @classmethod
    def from_dict(cls, data):
        """
        Create a Chatbot instance from a dictionary.

        Args:
            data (dict): Dictionary containing chatbot attributes

        Returns:
            Chatbot: New Chatbot instance
        """
        return cls(
            id=data.get('id'),
            username=data.get('username'),
            chatbot_name=data.get('chatbot_name'),
            gemini_api_key=data.get('gemini_api_key'),
            gemini_model=data.get('gemini_model'),
            data_source=data.get('data_source'),
            sheet_id=data.get('sheet_id'),
            selected_sheets=data.get('selected_sheets'),
            service_account_json=data.get('service_account_json'),
            db_host=data.get('db_host'),
            db_port=data.get('db_port'),
            db_name=data.get('db_name'),
            db_username=data.get('db_username'),
            db_password=data.get('db_password'),
            selected_tables=data.get('selected_tables'),
            mongo_uri=data.get('mongo_uri'),
            mongo_db_name=data.get('mongo_db_name'),
            selected_collections=data.get('selected_collections'),
            airtable_api_key=data.get('airtable_api_key'),
            airtable_base_id=data.get('airtable_base_id'),
            databricks_hostname=data.get('databricks_hostname'),
            databricks_http_path=data.get('databricks_http_path'),
            databricks_token=data.get('databricks_token'),
            odoo_url=data.get('odoo_url'),
            odoo_db=data.get('odoo_db'),
            odoo_username=data.get('odoo_username'),
            odoo_password=data.get('odoo_password'),
            selected_module=data.get('selected_module'),
            shared_username=data.get('shared_username'),
            shared_password=data.get('shared_password')
        )
