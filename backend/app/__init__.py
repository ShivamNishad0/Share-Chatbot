from flask import Flask
from flask_cors import CORS
import os

def create_app():
    app = Flask(__name__)
    CORS(app)

    # Load configuration
    app.config.from_object('config.Config')

    # Configure static folder for uploads
    app.static_folder = 'static'
    app.static_url_path = '/static'

    # Import and register blueprints
    from .routes import main_bp
    app.register_blueprint(main_bp)

    return app
