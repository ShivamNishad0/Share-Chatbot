import os

class Config:
    SECRET_KEY = os.environ.get('SECRET_KEY') or 'dev-secret-key-change-in-production'
    DEBUG = os.environ.get('DEBUG', 'false').lower() == 'true'
    HOST = os.environ.get('HOST') or '0.0.0.0'
    PORT = int(os.environ.get('PORT') or 5001)

    # CORS settings - allow frontend domain
    CORS_ORIGINS = os.environ.get('CORS_ORIGINS') or 'https://share-chatbot-1.onrender.com'

    # Database settings
    DATABASE_URL = os.environ.get('DATABASE_URL') or 'chatbots.db'

    # Render environment detection
    IS_RENDER = os.environ.get('RENDER') == 'true'

    @property
    def FRONTEND_URL(self):
        if self.IS_RENDER:
            return 'https://share-chatbot-1.onrender.com'
        return 'http://localhost:8000'
