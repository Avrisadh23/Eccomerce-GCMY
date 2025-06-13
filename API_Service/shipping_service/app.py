from flask import Flask, request, jsonify
from flask_sqlalchemy import SQLAlchemy
import os
from datetime import datetime
from flask_cors import CORS

app = Flask(__name__)
CORS(app)

# Database configuration
BASE_DIR = os.path.abspath(os.path.dirname(__file__))
DATABASE_URL = os.environ.get('DATABASE_URL', 'sqlite:///data/shipping.db')
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
class ShippingRate(db.Model):
    __tablename__ = 'shipping_rates'
    id = db.Column(db.Integer, primary_key=True)
    region = db.Column(db.String(100), nullable=False)
    base_rate = db.Column(db.Float, nullable=False)
    rate_per_kg = db.Column(db.Float, nullable=False)
    inserted_at = db.Column(db.DateTime, nullable=False)
    updated_at = db.Column(db.DateTime, nullable=False)

class Shipment(db.Model):
    __tablename__ = 'shipments'
    id = db.Column(db.Integer, primary_key=True)
    order_id = db.Column(db.Integer, nullable=False)
    tracking_number = db.Column(db.String(100), nullable=False)
    status = db.Column(db.String(50), nullable=False)
    shipping_address = db.Column(db.Text, nullable=False)
    shipping_cost = db.Column(db.Float, nullable=False)
    estimated_delivery = db.Column(db.DateTime)
    inserted_at = db.Column(db.DateTime, nullable=False)
    updated_at = db.Column(db.DateTime, nullable=False)

# Routes
@app.route('/shipping/calculate', methods=['POST'])
def calculate_shipping():
    data = request.get_json()
    region = data.get('region', 'default')
    weight = data.get('weight', 1.0)
    
    rate = ShippingRate.query.filter_by(region=region).first()
    if not rate:
        rate = ShippingRate.query.filter_by(region='default').first()
    
    if not rate:
        return jsonify({
            'error': 'Shipping rate not found for the specified region'
        }), 404
    
    shipping_cost = rate.base_rate + (weight * rate.rate_per_kg)
    
    return jsonify({
        'shipping_cost': shipping_cost,
        'region': rate.region,
        'estimated_days': 3-7
    })

@app.route('/shipping/track/<tracking_number>', methods=['GET'])
def track_shipment(tracking_number):
    shipment = Shipment.query.filter_by(tracking_number=tracking_number).first()
    
    if not shipment:
        return jsonify({
            'error': 'Shipment not found'
        }), 404
    
    return jsonify({
        'tracking_number': shipment.tracking_number,
        'status': shipment.status,
        'shipping_address': shipment.shipping_address,
        'estimated_delivery': shipment.estimated_delivery.isoformat() if shipment.estimated_delivery else None
    })

@app.route('/shipping/rates', methods=['GET'])
def get_shipping_rates():
    rates = ShippingRate.query.all()
    return jsonify([{
        'id': rate.id,
        'region': rate.region,
        'base_rate': rate.base_rate,
        'rate_per_kg': rate.rate_per_kg
    } for rate in rates])

@app.route('/shipping/create', methods=['POST'])
def create_shipment():
    data = request.get_json()
    now = datetime.utcnow()
    
    shipment = Shipment(
        order_id=data['order_id'],
        tracking_number=f"SHIP{now.strftime('%Y%m%d%H%M%S')}",
        status='pending',
        shipping_address=data['shipping_address'],
        shipping_cost=data['shipping_cost'],
        estimated_delivery=datetime.fromisoformat(data['estimated_delivery']) if 'estimated_delivery' in data else None,
        inserted_at=now,
        updated_at=now
    )
    
    db.session.add(shipment)
    db.session.commit()
    
    return jsonify({
        'id': shipment.id,
        'tracking_number': shipment.tracking_number,
        'status': shipment.status,
        'shipping_address': shipment.shipping_address,
        'shipping_cost': shipment.shipping_cost,
        'estimated_delivery': shipment.estimated_delivery.isoformat() if shipment.estimated_delivery else None
    }), 201

@app.route('/shipping/<tracking_number>/status', methods=['PUT'])
def update_shipment_status(tracking_number):
    shipment = Shipment.query.filter_by(tracking_number=tracking_number).first()
    
    if not shipment:
        return jsonify({
            'error': 'Shipment not found'
        }), 404
    
    data = request.get_json()
    shipment.status = data['status']
    shipment.updated_at = datetime.utcnow()
    
    if 'estimated_delivery' in data:
        shipment.estimated_delivery = datetime.fromisoformat(data['estimated_delivery'])
    
    db.session.commit()
    
    return jsonify({
        'tracking_number': shipment.tracking_number,
        'status': shipment.status,
        'estimated_delivery': shipment.estimated_delivery.isoformat() if shipment.estimated_delivery else None
    })

if __name__ == '__main__':
    with app.app_context():
        db.create_all()
        
        # Add default shipping rate if none exists
        if not ShippingRate.query.first():
            now = datetime.utcnow()
            default_rate = ShippingRate(
                region='default',            base_rate=10.0,
            rate_per_kg=2.0,
                inserted_at=now,
                updated_at=now
            )
            db.session.add(default_rate)
            db.session.commit()

# Health check endpoint
@app.route('/health')
def health_check():
    return jsonify({
        'status': 'healthy',
        'service': 'shipping-service',
        'timestamp': datetime.now().isoformat()
    })
            
if __name__ == '__main__':
    with app.app_context():
        db.create_all()
        
        # Add default shipping rate if none exists
        if not ShippingRate.query.first():
            now = datetime.utcnow()
            default_rate = ShippingRate(
                region='default',
                base_rate=10.0,
                rate_per_kg=2.0,
                inserted_at=now,
                updated_at=now
            )
            db.session.add(default_rate)
            db.session.commit()
            
    port = int(os.environ.get('PORT', 5004))
    app.run(host='0.0.0.0', port=port, debug=False)