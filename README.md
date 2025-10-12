# Share-Chatbot

A powerful, shareable AI-powered chatbot application that connects to multiple data sources and generates interactive Business Intelligence (BI) components using Google's Gemini AI. Create chatbots that can analyze your data and present insights through charts, tables, and dashboards.

## Features

- **Multi-Data Source Integration**: Connect to Google Sheets, MySQL, PostgreSQL, Neo4j, MongoDB, Oracle, MS SQL, Airtable, Databricks, Supabase, Snowflake, and Odoo
- **AI-Powered BI Generation**: Uses Google's Gemini AI to create:
  - Interactive charts and graphs
  - Paginated data tables
  - Key insights and summaries
  - Complete dashboards
- **User Authentication**: Secure signup/login system with password reset functionality
- **Chatbot Management**: Save, load, and share custom chatbots
- **Responsive Web Interface**: Modern Bootstrap-based UI with speech recognition
- **RESTful API**: Comprehensive backend API for all operations
- **Shareable Chatbots**: Generate shareable links and embeddable iframes for your chatbots

## Supported Data Sources

- **Google Sheets**: Direct integration with Google Sheets API
- **Databases**:
  - MySQL
  - PostgreSQL
  - Microsoft SQL Server
  - Oracle Database
- **NoSQL**:
  - Neo4j (Graph Database)
  - MongoDB
- **Cloud Services**:
  - Airtable
  - Databricks
  - Supabase
  - Snowflake
- **ERP Systems**:
  - Odoo (CRM, Inventory, Sales modules)

## Prerequisites

- Python 3.8+
- Node.js (optional, for frontend development)
- Various database clients depending on your data sources

## Installation

### Backend Setup

1. **Clone the repository**:
   ```bash
   git clone <repository-url>
   cd share-chatbot
   ```

2. **Install Python dependencies**:
   ```bash
   cd backend
   pip install -r requirements.txt
   ```

3. **Configure environment variables** (optional):
   Create a `.env` file in the backend directory:
   ```env
   SECRET_KEY=your-secret-key-here
   DEBUG=true
   HOST=0.0.0.0
   PORT=5001
   CORS_ORIGINS=https://your-frontend-domain.com
   DATABASE_URL=chatbots.db
   ```

4. **Initialize the database**:
   The application will automatically create the SQLite database on first run.

### Frontend Setup

1. **Navigate to frontend directory**:
   ```bash
   cd frontend
   ```

2. **Serve the PHP files**:
   Use a PHP server or place files in your web server's document root.

   For development with PHP built-in server:
   ```bash
   php -S localhost:8000
   ```

## Configuration

### Backend Configuration

The application uses a configuration system that can be customized via environment variables or the `backend/config.py` file.

Key configuration options:
- `SECRET_KEY`: Flask secret key for sessions
- `DEBUG`: Enable/disable debug mode
- `HOST`/`PORT`: Server host and port
- `CORS_ORIGINS`: Allowed frontend domains
- `DATABASE_URL`: SQLite database file path

### Data Source Credentials

Each data source requires specific credentials:
- **Google Sheets**: Service Account JSON and Sheet ID
- **Databases**: Host, port, username, password, database name
- **APIs**: API keys and endpoints
- **Gemini AI**: API key for AI functionality

## Usage

### Starting the Application

1. **Start the backend**:
   ```bash
   cd backend
   python app.py
   ```
   The API will be available at `http://localhost:5001`

2. **Start the frontend**:
   Open `frontend/index.php` in your browser or serve via PHP server at `http://localhost:8000`

### Creating a Chatbot

1. **Access Configuration**:
   - Click the invisible button in the top-right corner 15 times to open the configuration modal

2. **Configure Data Source**:
   - Select your data source type
   - Enter required credentials
   - Load and select tables/sheets
   - Enter Gemini API key

3. **Save Chatbot**:
   - Provide username, chatbot name, and ID
   - Save the configuration
   - Receive a share key for sharing

### Using the Chatbot

- Type natural language queries about your data
- The AI will generate appropriate BI components:
  - "Show me a chart of sales by month" → Interactive Google Chart
  - "Display the top 10 customers" → Paginated table
  - "What's the highest value?" → Insight card
  - "Create a dashboard" → Complete dashboard with multiple components

### Sharing Chatbots

- Use the generated share key to create shareable links
- Embed chatbots using iframe code
- Shared chatbots maintain all styling and functionality

## API Endpoints

### Authentication
- `GET /` - Health check
- `POST /signup` - User registration
- `POST /login` - User authentication
- `POST /forgot-password` - Request password reset
- `POST /reset-password` - Reset password with token

### Chatbot Management
- `POST /set_credentials` - Configure data source credentials
- `POST /set_items` - Select tables/sheets to use
- `POST /save_chatbot` - Save chatbot configuration
- `GET /list_chatbots?username=<username>` - List user's chatbots
- `GET /check_chatbot_count?username=<username>` - Check Google Sheets chatbot limit

### Chat Functionality
- `POST /chat` - Send message and get AI response
- `GET /shared/<share_key>` - Access shared chatbot

### Utility
- `POST /load_sheets` - Load Google Sheets for selection

## Project Structure

```
share-chatbot/
├── backend/
│   ├── app.py                 # Flask application entry point
│   ├── config.py              # Configuration settings
│   ├── requirements.txt       # Python dependencies
│   ├── app/
│   │   ├── __init__.py        # Flask app factory
│   │   ├── routes/
│   │   │   ├── __init__.py
│   │   │   └── main.py        # Main API routes
│   │   ├── services/
│   │   │   ├── __init__.py
│   │   │   └── database_service.py  # Data source integrations
│   │   └── utils/
│   │       ├── __init__.py
│   │       └── helpers.py     # Utility functions
├── frontend/
│   └── index.php              # Main web interface
├── README.md                  # This file
└── .gitignore
```

## Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

### Development Guidelines

- Follow PEP 8 for Python code
- Use meaningful commit messages
- Add tests for new features
- Update documentation as needed
- Ensure all data sources are properly tested

## Security Considerations

- Store API keys and credentials securely
- Use HTTPS in production
- Regularly rotate service account keys
- Implement proper access controls for shared chatbots
- Validate all user inputs

## Troubleshooting

### Common Issues

1. **Gemini API Errors**: Verify your API key is valid and has proper permissions
2. **Database Connection Failures**: Check credentials and network connectivity
3. **Google Sheets Access**: Ensure service account has editor access to the spreadsheet
4. **CORS Errors**: Configure `CORS_ORIGINS` to include your frontend domain

### Logs

Check the console/application logs for detailed error messages. The backend logs all operations including API calls and database connections.

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Support

For support and questions:
- Create an issue in the repository
- Check the troubleshooting section
- Review the API documentation

---

**Note**: This application requires valid API keys for Google Gemini and various data sources. Ensure you comply with all service terms of use and data privacy regulations.
