from flask import Flask, request, jsonify
from flask_sqlalchemy import SQLAlchemy
from flask_cors import CORS
import os
from datetime import datetime
from werkzeug.security import generate_password_hash, check_password_hash

app = Flask(__name__)
CORS(app)

# Database configuration
BASE_DIR = os.path.abspath(os.path.dirname(__file__))
DATABASE_URL = os.environ.get('DATABASE_URL', 'sqlite:///data/users.db')
if DATABASE_URL.startswith('sqlite:///') and not DATABASE_URL.startswith('sqlite:////'):
    # Convert relative path to absolute path
    db_path = DATABASE_URL.replace('sqlite:///', '')
    if not os.path.isabs(db_path):
        db_path = os.path.join(BASE_DIR, db_path)
    DATABASE_URL = 'sqlite:///' + db_path

app.config['SQLALCHEMY_DATABASE_URI'] = DATABASE_URL
app.config['SQLALCHEMY_TRACK_MODIFICATIONS'] = False

db = SQLAlchemy(app)

# Models
class User(db.Model):
    __tablename__ = 'users'
    id = db.Column(db.Integer, primary_key=True)
    email = db.Column(db.String(255), unique=True, nullable=False)
    first_name = db.Column(db.String(255), nullable=False)
    last_name = db.Column(db.String(255), nullable=False)
    password = db.Column(db.String(255), nullable=False)
    active = db.Column(db.Boolean, default=True)
    inserted_at = db.Column(db.DateTime, nullable=False)
    updated_at = db.Column(db.DateTime, nullable=False)

class Role(db.Model):
    __tablename__ = 'roles'
    id = db.Column(db.Integer, primary_key=True)
    name = db.Column(db.String(255), nullable=False)
    inserted_at = db.Column(db.DateTime, nullable=False)
    updated_at = db.Column(db.DateTime, nullable=False)

class UserRole(db.Model):
    __tablename__ = 'user_roles'
    user_id = db.Column(db.Integer, db.ForeignKey('users.id'), primary_key=True)
    role_id = db.Column(db.Integer, db.ForeignKey('roles.id'), primary_key=True)
    inserted_at = db.Column(db.DateTime, nullable=False)
    updated_at = db.Column(db.DateTime, nullable=False)

# Routes
@app.route('/users/register', methods=['POST'])
def register():
    data = request.get_json()
    
    # Validate required fields
    required_fields = ['email', 'first_name', 'last_name', 'password']
    for field in required_fields:
        if not data or field not in data or not data[field]:
            return jsonify({
                'status': 'error',
                'message': f'Field {field} is required'
            }), 400
    
    # Check if email already exists
    if User.query.filter_by(email=data['email']).first():
        return jsonify({
            'status': 'error',
            'message': 'Email already registered'
        }), 400
    
    try:
        now = datetime.utcnow()
        user = User(
            email=data['email'],
            first_name=data['first_name'],
            last_name=data['last_name'],
            password=generate_password_hash(data['password']),
            active=True,
            inserted_at=now,
            updated_at=now
        )
        
        db.session.add(user)
        db.session.commit()
        
        return jsonify({
            'status': 'success',
            'message': 'User registered successfully',
            'data': {
                'id': user.id,
                'email': user.email,
                'first_name': user.first_name,
                'last_name': user.last_name,
                'active': user.active
            }
        }), 201
        
    except Exception as e:
        db.session.rollback()
        return jsonify({
            'status': 'error',
            'message': 'Registration failed',
            'error': str(e)
        }), 500

@app.route('/users/login', methods=['POST'])
def login():
    data = request.get_json()
    
    # Validate required fields
    if not data or 'email' not in data or 'password' not in data:
        return jsonify({
            'status': 'error',
            'message': 'Email and password are required'
        }), 400
    
    user = User.query.filter_by(email=data['email']).first()
    
    if user and check_password_hash(user.password, data['password']):
        return jsonify({
            'status': 'success',
            'message': 'Login successful',
            'data': {
                'id': user.id,
                'email': user.email,
                'first_name': user.first_name,
                'last_name': user.last_name,
                'active': user.active
            }
        }), 200
    
    return jsonify({
        'status': 'error',
        'message': 'Invalid credentials'
    }), 401

@app.route('/users/profile/<int:user_id>', methods=['GET'])
def get_profile(user_id):
    user = User.query.get_or_404(user_id)
    return jsonify({
        'id': user.id,
        'email': user.email,
        'first_name': user.first_name,
        'last_name': user.last_name,
        'active': user.active
    })

@app.route('/users/profile/<int:user_id>', methods=['PUT'])
def update_profile(user_id):
    user = User.query.get_or_404(user_id)
    data = request.get_json()
    
    user.first_name = data.get('first_name', user.first_name)
    user.last_name = data.get('last_name', user.last_name)
    user.updated_at = datetime.utcnow()
    
    if 'password' in data:
        user.password = generate_password_hash(data['password'])
    
    db.session.commit()
    
    return jsonify({
        'id': user.id,
        'email': user.email,        'first_name': user.first_name,
        'last_name': user.last_name,
        'active': user.active
    })

# Health check endpoint
@app.route('/health')
def health_check():
    return jsonify({
        'status': 'healthy',
        'service': 'user-service',
        'timestamp': datetime.now().isoformat()
    })

if __name__ == '__main__':
    with app.app_context():
        db.create_all()
    port = int(os.environ.get('PORT', 5002))
    app.run(host='0.0.0.0', port=port, debug=False)