# Use Python 3.11 slim image
FROM python:3.11-slim

# Set working directory
WORKDIR /app

# Copy requirements and install dependencies
COPY backend/requirements.txt .
RUN pip install --no-cache-dir -r requirements.txt

# Copy the rest of the application
COPY backend/ ./

# Expose port 5001
EXPOSE 5001

# Run the application
CMD ["python", "app.py"]
