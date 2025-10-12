import json
import logging
from datetime import datetime

logger = logging.getLogger(__name__)

def generate_id(prefix='cb-', length=8):
    """Generate a unique ID with given prefix and length"""
    import random
    import string
    random_string = ''.join(random.choices(string.ascii_letters + string.digits, k=length))
    return f"{prefix}{random_string}"

def safe_json_loads(json_string):
    """Safely parse JSON string"""
    try:
        return json.loads(json_string)
    except json.JSONDecodeError as e:
        logger.error(f"JSON decode error: {str(e)}")
        return None
    except Exception as e:
        logger.error(f"Error parsing JSON: {str(e)}")
        return None

def format_timestamp(timestamp=None):
    """Format timestamp to readable string"""
    if timestamp is None:
        timestamp = datetime.now()
    return timestamp.strftime('%Y-%m-%d %H:%M:%S')

def validate_required_fields(data, required_fields):
    """Validate that all required fields are present in data"""
    missing_fields = []
    for field in required_fields:
        if not data.get(field):
            missing_fields.append(field)
    return missing_fields

def sanitize_input(input_string):
    """Basic input sanitization"""
    if not isinstance(input_string, str):
        return input_string
    # Remove potentially dangerous characters
    return input_string.replace('<', '<').replace('>', '>').replace('&', '&amp;').replace('"', '"').replace("'", '&#x27;')
