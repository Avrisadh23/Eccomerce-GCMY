from flask import Flask, request, jsonify
from flask_sqlalchemy import SQLAlchemy
import os
from datetime import datetime
from flask_cors import CORS


app = Flask(__name__)
CORS(app)

# Database configuration
BASE_DIR = os.path.abspath(os.path.dirname(__file__))
DATABASE_URL = os.environ.get('DATABASE_URL', 'sqlite:///data/orders.db')
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
class Order(db.Model):
    __tablename__ = 'orders'
    id = db.Column(db.Integer, primary_key=True)
    user_id = db.Column(db.Integer, nullable=False)
    status = db.Column(db.String(50), nullable=False)
    total_amount = db.Column(db.Float, nullable=False)
    shipping_address = db.Column(db.Text, nullable=False)
    payment_method = db.Column(db.String(50), nullable=False)
    inserted_at = db.Column(db.DateTime, nullable=False)
    updated_at = db.Column(db.DateTime, nullable=False)
    items = db.relationship('OrderItem', backref='order', lazy=True)

class OrderItem(db.Model):
    __tablename__ = 'order_items'
    id = db.Column(db.Integer, primary_key=True)
    order_id = db.Column(db.Integer, db.ForeignKey('orders.id'), nullable=False)
    product_id = db.Column(db.Integer, nullable=False)
    quantity = db.Column(db.Integer, nullable=False)
    price = db.Column(db.Float, nullable=False)
    inserted_at = db.Column(db.DateTime, nullable=False)
    updated_at = db.Column(db.DateTime, nullable=False)

# Routes
@app.route('/orders', methods=['GET'])
def get_orders():
    user_id = request.args.get('user_id', type=int)
    if user_id:
        orders = Order.query.filter_by(user_id=user_id).all()
    else:
        orders = Order.query.all()
    
    return jsonify([{
        'id': order.id,
        'user_id': order.user_id,
        'status': order.status,
        'total_amount': order.total_amount,
        'shipping_address': order.shipping_address,
        'payment_method': order.payment_method,
        'items': [{
            'id': item.id,
            'product_id': item.product_id,
            'quantity': item.quantity,
            'price': item.price
        } for item in order.items]
    } for order in orders])

@app.route('/orders/<int:id>', methods=['GET'])
def get_order(id):
    order = Order.query.get_or_404(id)
    return jsonify({
        'id': order.id,
        'user_id': order.user_id,
        'status': order.status,
        'total_amount': order.total_amount,
        'shipping_address': order.shipping_address,
        'payment_method': order.payment_method,
        'items': [{
            'id': item.id,
            'product_id': item.product_id,
            'quantity': item.quantity,
            'price': item.price
        } for item in order.items]
    })

@app.route('/orders', methods=['POST'])
def create_order():
    try:
        data = request.get_json()
        now = datetime.utcnow()
        order = Order(
            user_id=data['user_id'],
            status='pending',
            total_amount=data['total_amount'],
            shipping_address=data['shipping_address'],
            payment_method=data['payment_method'],
            inserted_at=now,
            updated_at=now
        )
        db.session.add(order)
        for item_data in data['items']:
            item = OrderItem(
                product_id=item_data['product_id'],
                quantity=item_data['quantity'],
                price=item_data['price'],
                inserted_at=now,
                updated_at=now
            )
            order.items.append(item)
        db.session.commit()
        return jsonify({
            'status': 'success',
            'message': 'Order created successfully',
            'data': {
                'id': order.id,
                'user_id': order.user_id,
                'status': order.status,
                'total_amount': order.total_amount,
                'shipping_address': order.shipping_address,
                'payment_method': order.payment_method,
                'items': [{
                    'id': item.id,
                    'product_id': item.product_id,
                    'quantity': item.quantity,
                    'price': item.price
                } for item in order.items]
            }
        }), 201
    except Exception as e:
        db.session.rollback()
        return jsonify({
            'status': 'error',
            'message': 'Failed to create order',
            'error': str(e)
        }), 500

@app.route('/orders/<int:id>/status', methods=['PUT'])
def update_order_status(id):
    order = Order.query.get_or_404(id)
    data = request.get_json()
    
    order.status = data['status']
    order.updated_at = datetime.utcnow()
    
    db.session.commit()
    
    return jsonify({
        'id': order.id,
        'user_id': order.user_id,
        'status': order.status,
        'total_amount': order.total_amount,        
        'shipping_address': order.shipping_address,
        'payment_method': order.payment_method
    })

# Health check endpoint
@app.route('/health')
def health_check():
    return jsonify({
        'status': 'healthy',
        'service': 'order-service',
        'timestamp': datetime.now().isoformat()
    })


if __name__ == '__main__':
    with app.app_context():
        db.create_all()
    port = int(os.environ.get('PORT', 5003))
    app.run(host='0.0.0.0', port=port, debug=False)