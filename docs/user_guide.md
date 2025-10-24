# User Guide

## Introduction
Welcome to SmartCard AI ChatBot! This guide will help you create and manage AI-powered chatbots that can interact with your data from various sources.

## Getting Started

### 1. Account Setup
1. Visit the application URL
2. Create an account or log in
3. Accept the terms of service and privacy policy

### 2. Creating Your First Chatbot

#### Step 1: Choose Data Source
Select from the following data sources:
- **Google Sheets**: Connect to your Google Sheets spreadsheets
- **Databases**: MySQL, PostgreSQL, Oracle, MS SQL Server
- **NoSQL**: MongoDB, Neo4j
- **APIs**: Airtable, Supabase, Databricks, Snowflake
- **ERP**: Odoo

#### Step 2: Configure Credentials
Enter the required credentials for your chosen data source:
- API keys
- Database connection details
- Service account credentials (for Google Sheets)

#### Step 3: Select Data
Choose specific sheets, tables, or collections to include in your chatbot's knowledge base.

#### Step 4: Configure AI
- Enter your Google Gemini API key
- Select the Gemini model (e.g., gemini-1.5-flash)
- Customize appearance (colors, logo, etc.)

#### Step 5: Test and Save
Test your chatbot with sample queries, then save the configuration.

## Using Your Chatbot

### Chat Interface
- Type natural language questions about your data
- The chatbot will analyze your data and provide insights
- Supports various output formats: tables, charts, summaries

### Example Queries
- "Show me the total sales by month"
- "What are the top 10 products?"
- "Create a chart of user registrations"
- "Summarize the data"

### Sharing Your Chatbot
1. After saving, you'll receive a share key
2. Share the URL: `https://your-domain/shared/{share_key}`
3. Optionally set up authentication for shared access

## Advanced Features

### Custom Styling
Customize your chatbot's appearance:
- Navigation colors
- Button styles
- Background colors
- Company logo

### Multiple Data Sources
Create chatbots that combine data from multiple sources for comprehensive insights.

### Authentication
Set up username/password protection for shared chatbots.

## Troubleshooting

### Common Issues

#### Connection Failed
- Verify credentials are correct
- Check network connectivity
- Ensure the data source is accessible

#### No Response from AI
- Check your Gemini API key is valid
- Verify API quota hasn't been exceeded
- Ensure the model name is correct

#### Data Not Loading
- Confirm selected items exist
- Check permissions on data source
- Verify connection parameters

### Support
If you encounter issues:
1. Check the browser console for error messages
2. Verify all required fields are filled
3. Contact support with error details

## Best Practices

### Data Preparation
- Clean and organize your data before connecting
- Use descriptive column names
- Ensure data types are consistent

### Query Optimization
- Be specific in your questions
- Use clear, concise language
- Break complex queries into smaller parts

### Security
- Use strong passwords for shared chatbots
- Regularly rotate API keys
- Limit data access to necessary items only

## Limitations

### Data Size
- Large datasets may impact performance
- Consider filtering data before connecting

### API Limits
- Respect rate limits for external APIs
- Monitor usage quotas

### Browser Compatibility
- Modern browsers recommended
- Some features may not work in older browsers
