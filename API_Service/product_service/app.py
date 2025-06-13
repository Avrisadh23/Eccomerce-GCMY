from flask import Flask, request, jsonify
from flask_sqlalchemy import SQLAlchemy
import os
from datetime import datetime
from flask_cors import CORS

app = Flask(__name__)
CORS(app)

# Database configuration
BASE_DIR = os.path.abspath(os.path.dirname(__file__))
DATABASE_URL = os.environ.get('DATABASE_URL', 'sqlite:///data/ecommerce.db')
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
class Product(db.Model):
    __tablename__ = 'products'
    id = db.Column(db.Integer, primary_key=True)
    nama = db.Column(db.String(255), nullable=False)
    harga = db.Column(db.Float, nullable=False)
    stok = db.Column(db.Integer, nullable=False)
    deskripsi = db.Column(db.Text)
    kategori_id = db.Column(db.Integer, db.ForeignKey('categories.id'))
    gambar = db.Column(db.String(255))
    weight = db.Column(db.Integer, nullable=False, default=1000)  # berat dalam gram
    origin_city_id = db.Column(db.Integer, nullable=True)         # id kota asal dari logistic location service
    created_at = db.Column(db.DateTime, nullable=False)
    updated_at = db.Column(db.DateTime, nullable=False)

class Category(db.Model):
    __tablename__ = 'categories'
    id = db.Column(db.Integer, primary_key=True)
    nama = db.Column(db.String(255), nullable=False)
    deskripsi = db.Column(db.Text)
    created_at = db.Column(db.DateTime, nullable=False)
    updated_at = db.Column(db.DateTime, nullable=False)
    products = db.relationship('Product', backref='category', lazy=True)

# Routes
@app.route('/products', methods=['GET'])
def get_products():
    page = request.args.get('page', 1, type=int)
    per_page = request.args.get('per_page', 10, type=int)
    kategori_id = request.args.get('kategori_id', type=int)
    
    query = Product.query
    if kategori_id:
        query = query.filter_by(kategori_id=kategori_id)
    
    pagination = query.paginate(page=page, per_page=per_page)
    products = pagination.items
    
    return jsonify({
        'status': 'success',
        'message': 'Products retrieved successfully',
        'data': {
            'products': [{
                'id': p.id,
                'nama': p.nama,
                'harga': p.harga,
                'stok': p.stok,
                'deskripsi': p.deskripsi,
                'kategori_id': p.kategori_id,
                'gambar': p.gambar,
                'weight': p.weight,                   # tambahkan di response
                'origin_city_id': p.origin_city_id,   # tambahkan di response
                'created_at': p.created_at.isoformat(),
                'updated_at': p.updated_at.isoformat()
            } for p in products],
            'total': pagination.total,
            'pages': pagination.pages,
            'current_page': page
        }
    })

@app.route('/products/<int:id>', methods=['GET'])
def get_product(id):
    product = Product.query.get_or_404(id)
    return jsonify({
        'status': 'success',
        'message': 'Product retrieved successfully',
        'data': {
            'id': product.id,
            'nama': product.nama,
            'harga': product.harga,
            'stok': product.stok,
            'deskripsi': product.deskripsi,
            'kategori_id': product.kategori_id,
            'gambar': product.gambar,
            'weight': product.weight,                 # tambahkan di response
            'origin_city_id': product.origin_city_id, # tambahkan di response
            'created_at': product.created_at.isoformat(),
            'updated_at': product.updated_at.isoformat()
        }
    })

@app.route('/categories', methods=['GET'])
def get_categories():
    categories = Category.query.all()
    return jsonify({
        'status': 'success',
        'message': 'Categories retrieved successfully',
        'data': [{
            'id': c.id,
            'nama': c.nama,
            'deskripsi': c.deskripsi,
            'created_at': c.created_at.isoformat(),
            'updated_at': c.updated_at.isoformat()
        } for c in categories]
    })

@app.route('/products', methods=['POST'])
def create_product():
    data = request.get_json()
    now = datetime.utcnow()
    
    product = Product(
        nama=data['name'],
        deskripsi=data.get('description'),
        harga=data['price'],
        stok=data['stock'],
        kategori_id=data.get('category_id'),
        gambar=data.get('image'),
        weight=data.get('weight', 1000),                 # ambil dari request, default 1000
        origin_city_id=data.get('origin_city_id'),       # ambil dari request
        created_at=now,
        updated_at=now
    )
    
    db.session.add(product)
    db.session.commit()
    
    return jsonify({
        'status': 'success',
        'message': 'Product created successfully',
        'data': {
            'id': product.id,
            'nama': product.nama,
            'harga': product.harga,
            'stok': product.stok,
            'deskripsi': product.deskripsi,
            'kategori_id': product.kategori_id,
            'gambar': product.gambar,
            'weight': product.weight,                     # tambahkan di response
            'origin_city_id': product.origin_city_id,     # tambahkan di response
            'created_at': product.created_at.isoformat(),
            'updated_at': product.updated_at.isoformat()
        }
    }), 201

@app.route('/categories', methods=['POST'])
def create_category():
    data = request.get_json()
    now = datetime.utcnow()
    
    category = Category(
        nama=data['nama'],
        deskripsi=data.get('deskripsi'),
        created_at=now,
        updated_at=now
    )
    
    db.session.add(category)
    db.session.commit()
    
    return jsonify({
        'status': 'success',
        'message': 'Category created successfully',
        'data': {
            'id': category.id,
            'nama': category.nama,
            'deskripsi': category.deskripsi,
            'created_at': category.created_at.isoformat(),
            'updated_at': category.updated_at.isoformat()
        }
    }), 201

@app.route('/products/<int:id>', methods=['PUT'])
def update_product(id):
    product = Product.query.get_or_404(id)
    data = request.get_json()
    
    product.nama = data.get('name', product.nama)
    product.harga = data.get('price', product.harga)
    product.stok = data.get('stock', product.stok)
    product.deskripsi = data.get('description', product.deskripsi)
    product.kategori_id = data.get('category_id', product.kategori_id)
    product.gambar = data.get('image', product.gambar)
    product.weight = data.get('weight', product.weight)                 # update weight
    product.origin_city_id = data.get('origin_city_id', product.origin_city_id) # update origin_city_id
    product.updated_at = datetime.utcnow()
    
    db.session.commit()
    
    return jsonify({
        'status': 'success',
        'message': 'Product updated successfully',
        'data': {
            'id': product.id,
            'nama': product.nama,
            'harga': product.harga,
            'stok': product.stok,
            'deskripsi': product.deskripsi,
            'kategori_id': product.kategori_id,
            'gambar': product.gambar,
            'weight': product.weight,                     # tambahkan di response
            'origin_city_id': product.origin_city_id,     # tambahkan di response
            'created_at': product.created_at.isoformat(),
            'updated_at': product.updated_at.isoformat()
        }
    })

@app.route('/products/<int:id>', methods=['DELETE'])
def delete_product(id):
    product = Product.query.get_or_404(id)
    db.session.delete(product)
    db.session.commit()
    
    return jsonify({
        'status': 'success',
        'message': 'Product deleted successfully'
    })

# Health check endpoint
@app.route('/health')
def health_check():
    return jsonify({
        'status': 'healthy',
        'service': 'product-service',
        'timestamp': datetime.now().isoformat()
    })

if __name__ == '__main__':
    with app.app_context():
        db.create_all()
    port = int(os.environ.get('PORT', 5001))
    app.run(host='0.0.0.0', port=port, debug=False)