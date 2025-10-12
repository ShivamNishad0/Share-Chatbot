import json
import logging
from .database_service import DatabaseService

logger = logging.getLogger(__name__)

class ChatbotService:
    def __init__(self):
        self.db_service = DatabaseService()

    def validate_credentials(self, config):
        """Validate the provided credentials based on data source"""
        data_source = config.get('data_source')

        if data_source == 'google_sheets':
            return self._validate_google_sheets_config(config)
        elif data_source in ['mysql', 'postgresql']:
            return self._validate_database_config(config)
        elif data_source == 'neo4j':
            return self._validate_neo4j_config(config)
        elif data_source == 'mongodb':
            return self._validate_mongodb_config(config)

        else:
            return False, 'Invalid data source'

    def _validate_google_sheets_config(self, config):
        required_fields = ['sheet_id', 'service_account_json', 'gemini_api_key']
        for field in required_fields:
            if not config.get(field):
                return False, f'Missing required field: {field}'

        try:
            service_json = json.loads(config['service_account_json'])
            required_keys = ['type', 'project_id', 'private_key_id', 'private_key',
                           'client_email', 'client_id', 'auth_uri', 'token_uri',
                           'auth_provider_x509_cert_url', 'client_x509_cert_url']
            missing_keys = [key for key in required_keys if key not in service_json]
            if missing_keys:
                return False, f'Invalid Service Account JSON: missing keys {missing_keys}'

            if service_json.get('type') != 'service_account':
                return False, 'Invalid Service Account JSON: type must be service_account'

            return True, 'Configuration valid'
        except json.JSONDecodeError:
            return False, 'Invalid Service Account JSON: not valid JSON'
        except Exception as e:
            return False, f'Configuration validation failed: {str(e)}'

    def _validate_database_config(self, config):
        required_fields = ['db_host', 'db_port', 'db_name', 'db_username', 'db_password', 'gemini_api_key']
        for field in required_fields:
            if not config.get(field):
                return False, f'Missing required field: {field}'
        return True, 'Configuration valid'

    def _validate_neo4j_config(self, config):
        required_fields = ['neo4j_uri', 'neo4j_db_name', 'neo4j_username', 'neo4j_password', 'gemini_api_key']
        for field in required_fields:
            if not config.get(field):
                return False, f'Missing required field: {field}'
        return True, 'Configuration valid'

    def _validate_mongodb_config(self, config):
        required_fields = ['mongo_uri', 'mongo_db_name', 'gemini_api_key']
        for field in required_fields:
            if not config.get(field):
                return False, f'Missing required field: {field}'
        return True, 'Configuration valid'
