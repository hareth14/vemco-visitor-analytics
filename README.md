# Vemco Visitor Analytics API

## Project Overview

This project is a backend API built with Laravel to power a simple admin dashboard for tracking visitor analytics and managing locations and sensors.

The API follows RESTful design principles and uses MySQL for the database and Redis for caching. The project includes the following core functionalities:

- **Locations API:** Manage locations.
- **Sensors API:** Manage sensors and filter by status with Redis caching.
- **Visitors API:** Track daily visitor counts per location and sensor, with optional date filtering and Redis caching.
- **Summary API:** Provides summary statistics (total visitors in last 7 days, active vs inactive sensors) with Redis caching.

### Bonus Features
- Laravel Resource classes for consistent API responses.
- Unit tests for controllers.
- Pagination support on sensors endpoint.
- Docker Compose setup for Laravel, MySQL, Redis, and Nginx services.

---

## Requirements

- PHP >= 8.1
- Composer
- Docker & Docker Compose (optional but recommended)
- MySQL
- Redis

---

## Getting Started

### Traditional (Non-Docker) Setup

1. **Clone the repository:**
   ```bash
   git clone <your-repository-url>
   cd vemco-visitor-analytics
   ```

2. **Install dependencies:**
   ```bash
   composer install
   ```

3. **Setup environment:**
   Copy `.env.example` to `.env` and configure your database and Redis credentials:
   ```bash
   cp .env.example .env
   ```

4. **Generate application key:**
   ```bash
   php artisan key:generate
   ```

5. **Run migrations and seed data:**
   ```bash
   php artisan migrate --seed
   ```

6. **Start the development server:**
   ```bash
   php artisan serve
   ```

---

### Docker Setup (Recommended)

This project includes a Docker Compose setup for easy deployment of Laravel with MySQL, Redis, and Nginx.

1. **Ensure Docker and Docker Compose are installed on your machine.**

2. **Build and start the containers:**
   ```bash
   docker-compose up -d --build
   ```

3. **Run database migrations and seeders inside the app container:**
   ```bash
   docker-compose exec app php artisan migrate --seed
   ```

4. **Access the application:**

   Open your browser and visit [http://localhost:8080](http://localhost:8080)

5. **Stop containers when done:**
   ```bash
   docker-compose down
   ```

⚠️ Important Note About Redis & Laravel Cache
Laravel sometimes caches configuration values from the .env file into a compiled config.php file.
To ensure Redis works correctly (especially for caching tagged data), we automatically run:

  ```bash
  php artisan config:clear
  ```
  ```bash
  php artisan cache:clear
  ```

during the Docker build process.

If you still experience issues with Redis not responding or falling back to file caching, check the Laravel logs for Redis connection errors and make sure the Redis container is healthy.

---

## API Endpoints

### Locations

- `GET /api/locations` - List all locations.
- `POST /api/locations` - Create a new location.  
  **Request body example:**  
  ```json
  {
    "name": "Mall A"
  }
  ```

### Sensors

- `GET /api/sensors` - List all sensors with status and location info.  
  Optional query parameter: `?status=active` or `?status=inactive`.  
  Supports pagination.  
  Cached using Redis.

- `POST /api/sensors` - Create a new sensor.  
  **Request body example:**  
  ```json
  {
    "name": "Sensor 04",
    "status": "active",
    "location_id": 1
  }
  ```

### Visitors

- `GET /api/visitors` - List daily visitor counts per location including sensor info.  
  Optional query parameter: `?date=YYYY-MM-DD` to filter by date.  
  Cached using Redis for popular dates.

- `POST /api/visitors` - Create visitor count data.  
  **Request body example:**  
  ```json
  {
    "location_id": 1,
    "sensor_id": 1,
    "date": "2025-05-11",
    "count": 450
  }
  ```

### Summary

- `GET /api/summary` - Returns a summary including total visitors over last 7 days and count of active vs inactive sensors.  
  Uses Redis caching.

---

## Testing

Run unit and feature tests using:

```bash
php artisan test
```

Or inside Docker:

```bash
docker-compose exec app php artisan test
```

---

## Postman Collection

A Postman collection is provided to facilitate API testing.

- Import the `vemo-analytics.postman_collection.json` file located in the `postman` directory.
- The collection includes all endpoints with example requests and expected responses.

---

## Notes

- No authentication is required for this API.
- Ensure Redis and MySQL services are running for caching and database operations.
- Code is structured to maintain clean, readable, and maintainable architecture.

---

## Contact

For any questions or support, please open an issue in the repository.

---
