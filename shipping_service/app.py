from flask import Flask, request, jsonify
from flask_cors import CORS

app = Flask(__name__)
CORS(app)

# Dummy data untuk kota dan biaya pengiriman
SHIPPING_COSTS = {
    'jakarta': {
        'regular': {'cost': 15000, 'etd': '2-3'},
        'express': {'cost': 25000, 'etd': '1'},
    },
    'bandung': {
        'regular': {'cost': 12000, 'etd': '2-3'},
        'express': {'cost': 22000, 'etd': '1'},
    },
    'surabaya': {
        'regular': {'cost': 20000, 'etd': '3-4'},
        'express': {'cost': 35000, 'etd': '1-2'},
    },
    'yogyakarta': {
        'regular': {'cost': 18000, 'etd': '2-3'},
        'express': {'cost': 30000, 'etd': '1'},
    },
    'semarang': {
        'regular': {'cost': 17000, 'etd': '2-3'},
        'express': {'cost': 28000, 'etd': '1'},
    }
}

@app.route('/shipping/calculate', methods=['POST'])
def calculate_shipping():
    data = request.get_json()
    destination = data.get('destination', '').lower()
    weight = data.get('weight', 1000)  # weight in grams, default 1kg
    
    # Jika kota tidak ditemukan, gunakan Jakarta sebagai default
    city_costs = SHIPPING_COSTS.get(destination, SHIPPING_COSTS['jakarta'])
    
    # Hitung biaya berdasarkan berat
    weight_multiplier = weight / 1000  # convert to kg
    
    shipping_options = []
    for service, details in city_costs.items():
        cost = int(details['cost'] * weight_multiplier)
        shipping_options.append({
            'service': service.upper(),
            'cost': cost,
            'etd': details['etd']
        })
    
    return jsonify({
        'status': 'success',
        'shipping_options': shipping_options
    })

if __name__ == '__main__':
    app.run(port=5003, debug=True) 