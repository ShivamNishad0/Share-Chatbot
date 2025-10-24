# API Documentation

## Overview
This document describes the REST API endpoints for the SmartCard AI ChatBot application.

## Base URL
```
http://localhost:5000
```

## Authentication
Most endpoints require authentication. Include credentials in request body or use share_key for shared chatbots.

## Endpoints

### Health Check
- **GET** `/`
- **Description**: Check if the API is running
- **Response**:
  ```json
  {
    "status": "running",
    "message": "ChatBot API is running successfully",
    "version": "1.0.0"
  }
  ```

### Set Credentials
- **POST** `/set_credentials`
- **Description**: Configure data source credentials and initialize connections
- **Request Body**:
  ```json
  {
    "data_source": "google_sheets|mysql|postgresql|etc",
    "gemini_api_key": "your-api-key",
    "gemini_model": "gemini-1.5-flash",
    // Additional fields based on data_source
  }
  ```
- **Response**: List of available items (sheets, tables, etc.)

### Load Sheets
- **POST** `/load_sheets`
- **Description**: Load Google Sheets for a given spreadsheet ID
- **Request Body**:
  ```json
  {
    "sheet_id": "spreadsheet-id",
    "service_account_json": "json-string"
  }
  ```

### Set Items
- **POST** `/set_items`
- **Description**: Select specific items (sheets/tables) to use
- **Request Body**:
  ```json
  {
    "item_names": ["item1", "item2"]
  }
  ```

### Chat
- **POST** `/chat`
- **Description**: Send a message to the chatbot and get a response
- **Request Body**:
  ```json
  {
    "message": "user query",
    "share_key": "optional-share-key"
  }
  ```
- **Response**:
  ```json
  {
    "response": "chatbot response"
  }
  ```

### Save Chatbot
- **POST** `/save_chatbot`
- **Description**: Save a chatbot configuration
- **Request Body**: Complete chatbot configuration object
- **Response**:
  ```json
  {
    "share_key": "generated-share-key"
  }
  ```

### List Chatbots
- **GET** `/list_chatbots?username={username}`
- **Description**: Get all chatbots for a user
- **Response**: Array of chatbot objects

### Edit Chatbot
- **GET** `/edit_chatbot?share_key={share_key}`
- **Description**: Get chatbot configuration for editing
- **Response**: Chatbot configuration object

### Delete Chatbot
- **DELETE** `/delete_chatbot?share_key={share_key}`
- **Description**: Delete a chatbot
- **Response**:
  ```json
  {
    "success": true
  }
  ```

### Check Chatbot Count
- **GET** `/check_chatbot_count?username={username}`
- **Description**: Check number of Google Sheets chatbots for a user
- **Response**:
  ```json
  {
    "count": 5
  }
  ```

### Shared Chatbot
- **GET** `/shared/{share_key}`
- **Description**: Access a shared chatbot interface
- **Response**: HTML page for the shared chatbot

### Shared Chatbot Login
- **POST** `/shared/{share_key}/login`
- **Description**: Authenticate for shared chatbot access
- **Request Body**:
  ```json
  {
    "username": "shared-username",
    "password": "shared-password"
  }
  ```

## Data Sources Supported
- Google Sheets
- MySQL
- PostgreSQL
- Neo4j
- MongoDB
- Oracle
- MS SQL Server
- Airtable
- Databricks
- Supabase
- Snowflake
- Odoo

## Error Responses
All endpoints return error responses in the following format:
```json
{
  "error": "Error message description"
}
```

## Rate Limiting
- No explicit rate limiting implemented
- Consider implementing based on usage patterns

## Versioning
- Current API version: 1.0.0
- No versioning strategy implemented yet
