# Developer Guide

## Overview
This guide provides information for developers who want to contribute to or extend the SmartCard AI ChatBot application.

## Architecture

### Backend (Flask)
- **Framework**: Flask 2.x
- **Database**: SQLite (with support for external databases)
- **AI Integration**: Google Gemini API
- **External APIs**: Multiple data source integrations

### Frontend
- **Technology**: HTML, CSS, JavaScript
- **Styling**: Bootstrap 5
- **Icons**: Font Awesome

### Key Components
- `app/models/chatbot.py`: Chatbot configuration model
- `app/services/database_service.py`: Data source connection service
- `app/routes/main.py`: API endpoints and business logic

## Development Setup

### Prerequisites
- Python 3.8+
- pip
- Virtual environment (recommended)

### Installation
1. Clone the repository
2. Create virtual environment:
   ```bash
   python -m venv venv
   source venv/bin/activate  # On Windows: venv\Scripts\activate
   ```
3. Install dependencies:
   ```bash
   pip install -r requirements.txt
   ```
4. Set up environment variables (create `.env` file):
   ```env
   FLASK_APP=run.py
   FLASK_ENV=development
   SECRET_KEY=your-secret-key
   FRONTEND_URL=http://localhost:3000
   ```

### Running the Application
```bash
# Backend
python run.py

# Frontend (if separate)
cd frontend
python -m http.server 3000
```

## Code Structure

### Models
```python
class Chatbot:
    """Represents a chatbot configuration"""
    def __init__(self, ...):
        # Initialize attributes

    def to_dict(self):
        """Convert to dictionary"""
        return {...}

    @classmethod
    def from_dict(cls, data):
        """Create from dictionary"""
        return cls(...)
```

### Services
```python
class DatabaseService:
    """Handles database operations and external connections"""

    def __init__(self, db_file='chatbots.db'):
        self.db_file = db_file

    def init_db(self):
        """Initialize database tables"""
        # Create tables

    def save_chatbot(self, chatbot_data):
        """Save chatbot configuration"""
        # Database operations

    # Data source specific methods
    def fetch_from_mysql(self, creds, query):
        """Fetch data from MySQL"""
        # Connection and query logic
```

### Routes
```python
@main_bp.route('/chat', methods=['POST'])
def chat():
    """Handle chat requests"""
    # Process request
    # Generate AI response
    # Return response
```

## Adding New Data Sources

### 1. Update Database Schema
Add new columns to the `chatbots` table in `database_service.py` if needed.

### 2. Implement Fetch Method
Add a new method in `DatabaseService`:
```python
def fetch_from_newsource(self, creds, query=None):
    """
    Fetch data from NewSource.

    Args:
        creds (dict): Credentials dictionary
        query (str): Query or table name

    Returns:
        list[dict]: Query results
    """
    try:
        # Connection logic
        # Query execution
        # Data processing
        return results
    except Exception as e:
        logger.error(f"NewSource fetch failed: {str(e)}")
        return {"status": "error", "message": str(e)}
```

### 3. Update Route Handler
Modify `/set_credentials` endpoint in `main.py` to handle the new data source:
```python
elif data_source == 'newsource':
    try:
        # Connection test
        # List available items
        return jsonify({'type': 'tables', 'items': items})
    except Exception as e:
        return jsonify({'error': f'NewSource connection failed: {str(e)}'}), 400
```

### 4. Update Chat Logic
Add handling in the `/chat` endpoint for the new data source.

## API Integration Guidelines

### Error Handling
- Always wrap external API calls in try-catch blocks
- Return consistent error response format:
  ```json
  {"status": "error", "message": "Error description"}
  ```

### Logging
- Use the logging module for all operations
- Log errors, warnings, and important operations
- Include relevant context in log messages

### Authentication
- Validate API keys and credentials
- Implement proper session management for web interface
- Use secure password hashing for user accounts

## Testing

### Unit Tests
```python
import unittest
from app.services.database_service import DatabaseService

class TestDatabaseService(unittest.TestCase):
    def setUp(self):
        self.service = DatabaseService(':memory:')

    def test_init_db(self):
        self.service.init_db()
        # Assert tables created

    def test_save_chatbot(self):
        # Test chatbot saving
        pass
```

### Integration Tests
- Test complete workflows
- Mock external API calls
- Test database operations

## Deployment

### Docker
```dockerfile
FROM python:3.9-slim

WORKDIR /app
COPY requirements.txt .
RUN pip install -r requirements.txt

COPY . .
EXPOSE 5000

CMD ["python", "run.py"]
```

### Environment Variables
- `FLASK_ENV`: Set to 'production' for production
- `SECRET_KEY`: Strong random key for sessions
- `DATABASE_URL`: Database connection string
- `GEMINI_API_KEY`: Default API key (optional)

### Security Considerations
- Use HTTPS in production
- Implement rate limiting
- Regular security audits
- Keep dependencies updated

## Contributing

### Code Style
- Follow PEP 8 guidelines
- Use descriptive variable names
- Add docstrings to all functions and classes
- Write clear commit messages

### Pull Requests
1. Create a feature branch
2. Write tests for new functionality
3. Update documentation
4. Ensure all tests pass
5. Submit pull request with description

### Documentation
- Update this guide for new features
- Add API documentation for new endpoints
- Include code examples

## Troubleshooting Development

### Common Issues
- **Import errors**: Check virtual environment activation
- **Database errors**: Verify SQLite file permissions
- **API errors**: Check API keys and network connectivity

### Debugging
- Use Flask debug mode: `export FLASK_DEBUG=1`
- Check logs in console and files
- Use breakpoints in IDE

## Performance Optimization

### Database
- Use connection pooling for high traffic
- Implement query caching
- Optimize database schema

### API Calls
- Implement request caching
- Use async operations for I/O bound tasks
- Batch API requests when possible

### Frontend
- Minimize bundle size
- Implement lazy loading
- Use CDN for static assets
