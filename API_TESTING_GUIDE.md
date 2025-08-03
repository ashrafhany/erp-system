# API Testing Guide for Mini ERP System

This document provides instructions on how to test the APIs for the Mini ERP System, specifically the Product and Inventory management features.

## Prerequisites

- PHP (7.4 or higher)
- Composer
- Laravel
- Postman (for GUI testing)
- cURL (for command-line testing)

## Available Testing Methods

There are three ways to test the APIs:

1. **Using PHPUnit Tests** - Automated testing using Laravel's testing framework
2. **Using Postman** - GUI-based testing with the provided collection
3. **Using the test-api.php script** - Command-line testing script

## 1. Testing with PHPUnit

The project includes comprehensive PHPUnit tests for all API endpoints.

### Running the tests

```bash
# Run all API tests
php artisan test --filter=Api

# Run specific test classes
php artisan test --filter=ProductApiTest
php artisan test --filter=InventoryApiTest

# Run a specific test method
php artisan test --filter=test_can_get_all_products
```

### Test Classes

- `ProductApiTest.php` - Tests for the Product API endpoints
- `InventoryApiTest.php` - Tests for the Inventory API endpoints

## 2. Testing with Postman

A Postman collection and environment have been provided for manual testing.

### Import files

1. Import the following files into Postman:
   - `Product_Inventory_API_Tests.postman_collection.json`
   - `Product_Inventory_API_Local.postman_environment.json`

2. Select the "Mini ERP System - Local Env" environment

### Environment Setup

Make sure the following variables are set in your environment:

- `base_url` - Default is `http://localhost:8000/api/v1/test` (for testing without authentication)
- `auth_token` - Your authentication token (only needed for protected routes)

### Running the Collection

1. Start your Laravel server:
   ```bash
   php artisan serve
   ```

2. Open the collection in Postman and run requests individually or use the "Run Collection" feature

## 3. Testing with test-api.php Script

A PHP script is provided to test the APIs from the command line.

### Running the script

```bash
# Make sure your Laravel server is running
php artisan serve

# In another terminal, run the test script
php test-api.php
```

The script will:
- Test product endpoints (GET, POST, PUT, DELETE)
- Test inventory endpoints and reporting
- Clean up any test data it creates

### Customizing the script

Edit the following variables at the top of the script if needed:

```php
$BASE_URL = 'http://localhost:8000/api/v1';
$AUTH_TOKEN = ''; // Add your auth token if needed
```

## API Endpoints Overview

### Products

- `GET /products` - List all products (with filtering options)
- `GET /products/{id}` - Get a specific product
- `POST /products` - Create a new product
- `PUT /products/{id}` - Update a product
- `DELETE /products/{id}` - Delete a product
- `GET /products/{id}/inventory` - Get inventory history for a product
- `POST /products/{id}/inventory` - Adjust inventory for a product
- `GET /products-low-stock` - Get products with stock below threshold

### Inventory

- `GET /inventory` - List all inventory records (with filtering options)
- `GET /inventory/{id}` - Get a specific inventory record
- `POST /inventory` - Create a new inventory record
- `PUT /inventory/{id}` - Update an inventory record
- `DELETE /inventory/{id}` - Delete an inventory record
- `GET /inventory-reports/valuation` - Get inventory valuation report
- `GET /inventory-reports/movements` - Get inventory movements report

## Troubleshooting

If you encounter issues:

1. **Server not running** - Make sure `php artisan serve` is running
2. **Database issues** - Try `php artisan migrate:fresh --seed` to reset the database
3. **Authorization issues** - For testing, use the `/api/v1/test/` routes which don't require authentication
4. **404 errors** - Ensure you're using the correct API endpoints with the correct prefix

### Test Routes vs. Production Routes

This project provides two sets of API routes:

1. **Test Routes**: `/api/v1/test/...` - No authentication required, for testing only
2. **Production Routes**: `/api/v1/...` - Requires authentication with Sanctum

For testing purposes, use the test routes. In production, you would use the authenticated routes.

## Further Documentation

For more detailed API documentation, see `API_DOCUMENTATION.md`.
