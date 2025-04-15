import os

from dotenv import load_dotenv
from flask import Flask
from flask_sqlalchemy import SQLAlchemy

db = SQLAlchemy()


def create_app():
    load_dotenv()

    app = Flask(__name__)
    app.config['SQLALCHEMY_DATABASE_URI'] = os.getenv('DATABASE_URL')
    app.config['SQLALCHEMY_TRACK_MODIFICATIONS'] = False
    app.config['SECRET_KEY'] = os.getenv('SECRET_KEY')

    db.init_app(app)

    if os.getenv("DISABLE_MIGRATIONS", "False").lower() == "true":
        import sys
        if len(sys.argv) > 1 and sys.argv[1] in ["db", "migrate", "upgrade", "downgrade"]:
            raise RuntimeError("Migrations are disabled. Please enable them to proceed.")

    from app.controllers.notes_controller import notes_bp
    app.register_blueprint(notes_bp)

    with app.app_context():
        db.create_all()

    return app
