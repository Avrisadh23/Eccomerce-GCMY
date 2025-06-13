# 🚀 E-Commerce Application - Setup Guide

## 📋 Overview

Aplikasi e-commerce berbasis microservices dengan Docker. Setup yang sederhana dan mudah untuk development dengan akses langsung ke setiap service.

## 🏗️ Architecture

```
🌐 Laravel Frontend (Port 8000)
├── 🛍️  Product Service (Port 8001)
├── 👤 User Service (Port 8002)  
├── 📦 Order Service (Port 8003)
└── 🚚 Shipping Service (Port 8004)
```

## 📱 Access URLs

| Service | URL | Description |
|---------|-----|-------------|
| **Frontend** | http://localhost:8000 | Laravel application |
| **Product API** | http://localhost:8001 | Product management |
| **User API** | http://localhost:8002 | User authentication |
| **Order API** | http://localhost:8003 | Order processing |
| **Shipping API** | http://localhost:8004 | Shipping management |

## 🔧 Manual Commands

### Start Services
```bash
# Start microservices
docker-compose up -d

# Start Laravel frontend
cd Frontend
php artisan serve --host=0.0.0.0 --port=8000
```

### Access the Application
```bash
# Open browser to access the application
open http://localhost:8000

# Or use curl to test
curl http://localhost:8000
```

### Frontend Routes Available
- **Homepage**: http://localhost:8000 - Welcome page with system status
- **Products (Public)**: http://localhost:8000/products-public - Browse products without login
- **Categories (Public)**: http://localhost:8000/categories-public - Browse categories without login  
- **Login**: http://localhost:8000/login - User login page
- **Register**: http://localhost:8000/register - User registration page

### Stop Services
```bash
# Stop microservices

docker-compose down

# Stop Laravel (if running manually)
# Press Ctrl+C in Laravel terminal
```

### Check Status
```bash
# Check Docker services
docker-compose ps

# Check ports
lsof -i :8000-8004

# Check health
curl http://localhost:8001/health
curl http://localhost:8002/health
curl http://localhost:8003/health
curl http://localhost:8004/health
```

## 🧪 API Testing Examples

### Product Service
```bash
# Get products
curl http://localhost:8001/products

# Create product
curl -X POST http://localhost:8001/products \
  -H "Content-Type: application/json" \
  -d '{"name":"Test Product","description":"Test","price":99.99,"stock":10}'

# Get specific product
curl http://localhost:8001/products/1
```

### User Service
```bash
# Register user
curl -X POST http://localhost:8002/users/register \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","first_name":"Test","last_name":"User","password":"password123"}'

# Login user
curl -X POST http://localhost:8002/users/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password123"}'
```

### Order Service
```bash
# Get orders
curl http://localhost:8003/orders

# Create order
curl -X POST http://localhost:8003/orders \
  -H "Content-Type: application/json" \
  -d '{"user_id":1,"total_amount":99.99,"items":[{"product_id":1,"quantity":1,"price":99.99}],"shipping_address":"123 Test St","payment_method":"credit_card"}'
```

### Shipping Service
```bash
# Calculate shipping
curl -X POST http://localhost:8004/shipping/calculate \
  -H "Content-Type: application/json" \
  -d '{"origin":"Jakarta","destination":"Surabaya","weight":1.0,"dimensions":{"length":10,"width":8,"height":6}}'

# Create shipment
curl -X POST http://localhost:8004/shipping/create \
  -H "Content-Type: application/json" \
  -d '{"order_id":1,"carrier":"JNE","service_type":"regular","shipping_address":"123 Test St","shipping_cost":15.00,"estimated_delivery":"2025-06-15"}'
```

## 🧪 Test Results

Aplikasi telah diuji secara menyeluruh dengan hasil berikut:

```
🧪 E-Commerce API Testing
Total Tests: 15
Passed: 15
Failed: 0
Pass Rate: 100%
🎉 EXCELLENT! Application is working great!
```

**Performance:**
- Average API response time: **14ms**
- All health checks passing
- All services healthy and responsive

**Tested Features:**
- ✅ Product management (CRUD operations)
- ✅ User registration and authentication
- ✅ Order processing
- ✅ Shipping calculations
- ✅ Health monitoring
- ✅ Frontend accessibility

## 📚 API Documentation

### Product Service (Port 8001)
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/products` | Get all products (with pagination) |
| GET | `/products/{id}` | Get specific product |
| POST | `/products` | Create new product |
| PUT | `/products/{id}` | Update product |
| DELETE | `/products/{id}` | Delete product |
| GET | `/categories` | Get all categories |
| POST | `/categories` | Create new category |
| GET | `/health` | Health check |

### User Service (Port 8002)
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/users/register` | Register new user |
| POST | `/users/login` | User login |
| GET | `/users/profile/{id}` | Get user profile |
| GET | `/health` | Health check |

### Order Service (Port 8003)  
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/orders` | Get all orders |
| GET | `/orders/{id}` | Get specific order |
| POST | `/orders` | Create new order |
| PUT | `/orders/{id}` | Update order |
| GET | `/health` | Health check |

### Shipping Service (Port 8004)
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/shipping/calculate` | Calculate shipping cost |
| POST | `/shipping/create` | Create shipment |
| GET | `/shipping/rates` | Get shipping rates |
| GET | `/shipping/{id}` | Get shipment details |
| GET | `/health` | Health check |

## 📁 File Structure

```
E-commerce/
├── start.sh                 # Start application
├── stop.sh                  # Stop application  
├── test.sh                  # Test all services
├── docker-compose.yml       # Docker configuration
├── logs/                    # Application logs
│   ├── laravel.log         # Laravel logs
│   └── laravel.pid         # Laravel process ID
├── API_Service/             # Microservices
│   ├── product_service/
│   ├── user_service/
│   ├── order_service/
│   └── shipping_service/
└── Frontend/                # Laravel application
```

## 🔍 Troubleshooting

### Port Conflicts
```bash
# Check which process is using a port
lsof -i :8001

# Kill process on specific port
kill -9 $(lsof -t -i:8001)
```

### Docker Issues
```bash
# Rebuild containers
docker-compose build --no-cache

# View logs
docker-compose logs [service-name]

# Remove all containers and volumes
docker-compose down -v
```

### Laravel Issues
```bash
# Clear Laravel cache
cd Frontend
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Fix permissions
chmod -R 755 storage bootstrap/cache
```

**Happy Coding! 🚀**
