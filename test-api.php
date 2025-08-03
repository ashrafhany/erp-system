#!/usr/bin/env php
<?php

/**    $response = makeRequest('GET', $BASE_URL . '/products');
if ($response['status'] == 200 && isset($response['body']['data'])) {
    echo "SUCCESS\n";
    $productCount = count($response['body']['data']);
    echo "   - {$productCount} products found\n";

    if ($productCount > 0) {
        $testProductId = $response['body']['data'][0]['id'] ?? null;
        if ($testProductId) {
            echo "   - Using product ID {$testProductId} for further tests\n";
        } else {
            echo "   - Unable to extract product ID from response\n";
            // Create a new product to test with
            $response = makeRequest('POST', $BASE_URL . '/products', [
                'name' => 'Test Product for API Testing',
                'sku' => 'TEST-SKU-' . rand(1000, 9999),
                'description' => 'Test product created for API testing',
                'price' => 99.99,
                'cost' => 49.99,
                'category' => 'Test',
                'tax_rate' => 7.5,
                'status' => 'active',
                'initial_stock' => 10
            ]);
            if ($response['status'] == 201 && isset($response['body']['data']['id'])) {
                $testProductId = $response['body']['data']['id'];
                echo "   - Created test product with ID {$testProductId}\n";
            } else {
                echo "   - Failed to create test product. Exiting.\n";
                exit(1);
            }
        }
    } else {
        echo "   - No products found to use for further tests\n";
        exit(1);
    }
} else {pt to verify API functionality
 *
 * This script performs basic API tests to check functionality of the products and inventory endpoints
 */

// Configuration
$BASE_URL = 'http://localhost:8000/api/v1/test';  // Use the test API route
$AUTH_TOKEN = ''; // No auth token needed for test routes

// Headers for requests
$headers = [
    'Accept: application/json',
    'Content-Type: application/json'
];
if (!empty($AUTH_TOKEN)) {
    $headers[] = "Authorization: Bearer {$AUTH_TOKEN}";
}

echo "================================\n";
echo "MINI ERP SYSTEM API TEST SCRIPT\n";
echo "================================\n\n";

// Test product endpoints
echo "TESTING PRODUCT ENDPOINTS\n";
echo "--------------------------\n";

// Test GET /products
echo "1. Testing GET /products... ";
$response = makeRequest('GET', $BASE_URL . '/products');
if ($response['status'] == 200 && isset($response['body']['data'])) {
    echo "SUCCESS\n";
    $productCount = count($response['body']['data']);
    echo "   - {$productCount} products found\n";

    if ($productCount > 0) {
        $testProductId = $response['body']['data'][0]['id'];
        echo "   - Using product ID {$testProductId} for further tests\n";
    } else {
        echo "   - No products found to use for further tests\n";
        exit(1);
    }
} else {
    echo "FAILED\n";
    echo "   - Status: {$response['status']}\n";
    echo "   - Response: " . json_encode($response['body']) . "\n";
    exit(1);
}

// Test GET /products/{id}
echo "2. Testing GET /products/{$testProductId}... ";
$response = makeRequest('GET', $BASE_URL . "/products/{$testProductId}");
if ($response['status'] == 200 && isset($response['body']['data'])) {
    echo "SUCCESS\n";
    $productName = $response['body']['data']['name'] ?? 'Unknown';
    echo "   - Product name: {$productName}\n";
} else {
    echo "FAILED\n";
    echo "   - Status: {$response['status']}\n";
    echo "   - Response: " . json_encode($response['body']) . "\n";
}

// Test POST /products (create)
echo "3. Testing POST /products... ";
$newProduct = [
    'name' => 'API Test Product ' . date('YmdHis'),
    'description' => 'Test product created via API script',
    'sku' => 'TEST-' . date('YmdHis'),
    'price' => 99.99,
    'cost' => 49.99,
    'category' => 'Test',
    'tax_rate' => 7.5,
    'status' => 'active',
    'initial_stock' => 10
];
$response = makeRequest('POST', $BASE_URL . '/products', $newProduct);
if ($response['status'] == 201 && isset($response['body']['data'])) {
    echo "SUCCESS\n";
    echo "   - Created product ID: {$response['body']['data']['id']}\n";
    $newProductId = $response['body']['data']['id'];
} else {
    echo "FAILED\n";
    echo "   - Status: {$response['status']}\n";
    echo "   - Response: " . json_encode($response['body']) . "\n";
}

if (isset($newProductId)) {
    // Test PUT /products/{id} (update)
    echo "4. Testing PUT /products/{$newProductId}... ";
    $updatedProduct = [
        'name' => 'Updated API Test Product',
        'description' => 'Updated test product',
        'sku' => 'TEST-' . date('YmdHis'),
        'price' => 109.99,
        'cost' => 59.99,
        'category' => 'Test',
        'tax_rate' => 7.5,
        'status' => 'active',
    ];
    $response = makeRequest('PUT', $BASE_URL . "/products/{$newProductId}", $updatedProduct);
    if ($response['status'] == 200 && isset($response['body']['data'])) {
        echo "SUCCESS\n";
        echo "   - Updated product name: {$response['body']['data']['name']}\n";
    } else {
        echo "FAILED\n";
        echo "   - Status: {$response['status']}\n";
        echo "   - Response: " . json_encode($response['body']) . "\n";
    }

    // Test POST /products/{id}/inventory (adjust inventory)
    echo "5. Testing POST /products/{$newProductId}/inventory... ";
    $inventoryAdjustment = [
        'quantity_change' => 5,
        'type' => 'purchase',
        'reference' => 'TEST-PO-' . date('YmdHis'),
        'notes' => 'Test inventory adjustment',
        'unit_cost' => 49.99
    ];
    $response = makeRequest('POST', $BASE_URL . "/products/{$newProductId}/inventory", $inventoryAdjustment);
    if ($response['status'] == 201 && isset($response['body']['data'])) {
        echo "SUCCESS\n";
        echo "   - Inventory record ID: {$response['body']['data']['id']}\n";
        $inventoryId = $response['body']['data']['id'];
    } else {
        echo "FAILED\n";
        echo "   - Status: {$response['status']}\n";
        echo "   - Response: " . json_encode($response['body']) . "\n";
    }

    // Test GET /products/{id}/inventory (get inventory history)
    echo "6. Testing GET /products/{$newProductId}/inventory... ";
    $response = makeRequest('GET', $BASE_URL . "/products/{$newProductId}/inventory");
    if ($response['status'] == 200 && isset($response['body']['data'])) {
        echo "SUCCESS\n";
        $inventoryCount = count($response['body']['data']);
        echo "   - {$inventoryCount} inventory records found\n";
    } else {
        echo "FAILED\n";
        echo "   - Status: {$response['status']}\n";
        echo "   - Response: " . json_encode($response['body']) . "\n";
    }
}

// Test GET /products-low-stock
echo "7. Testing GET /products-low-stock... ";
$response = makeRequest('GET', $BASE_URL . '/products-low-stock?threshold=100');
if ($response['status'] == 200 && isset($response['body']['data'])) {
    echo "SUCCESS\n";
    $lowStockCount = count($response['body']['data']);
    echo "   - {$lowStockCount} products with low stock found\n";
} else {
    echo "FAILED\n";
    echo "   - Status: {$response['status']}\n";
    echo "   - Response: " . json_encode($response['body']) . "\n";
}

// Test inventory endpoints
echo "\nTESTING INVENTORY ENDPOINTS\n";
echo "----------------------------\n";

// Test GET /inventory
echo "1. Testing GET /inventory... ";
$response = makeRequest('GET', $BASE_URL . '/inventory');
if ($response['status'] == 200 && isset($response['body']['data'])) {
    echo "SUCCESS\n";
    $inventoryCount = count($response['body']['data']);
    echo "   - {$inventoryCount} inventory records found\n";

    if ($inventoryCount > 0 && !isset($inventoryId)) {
        $inventoryId = $response['body']['data'][0]['id'] ?? null;
        if ($inventoryId) {
            echo "   - Using inventory ID {$inventoryId} for further tests\n";
        } else {
            echo "   - Unable to extract inventory ID from response\n";
        }
    }
} else {
    echo "FAILED\n";
    echo "   - Status: {$response['status']}\n";
    echo "   - Response: " . json_encode($response['body']) . "\n";
}

// Test GET /inventory/{id}
if (isset($inventoryId)) {
    echo "2. Testing GET /inventory/{$inventoryId}... ";
    $response = makeRequest('GET', $BASE_URL . "/inventory/{$inventoryId}");
    if ($response['status'] == 200 && isset($response['body']['data'])) {
        echo "SUCCESS\n";
        $type = $response['body']['data']['type'] ?? 'Unknown';
        $quantityChange = $response['body']['data']['quantity_change'] ?? 'Unknown';
        echo "   - Inventory type: {$type}\n";
        echo "   - Quantity change: {$quantityChange}\n";
    } else {
        echo "FAILED\n";
        echo "   - Status: {$response['status']}\n";
        echo "   - Response: " . json_encode($response['body']) . "\n";
    }
}

// Test GET /inventory-reports/valuation
echo "3. Testing GET /inventory-reports/valuation... ";
$response = makeRequest('GET', $BASE_URL . '/inventory-reports/valuation');
if ($response['status'] == 200 && isset($response['body']['data'])) {
    echo "SUCCESS\n";
    $totalValue = $response['body']['total_inventory_value'] ?? 'Not available';
    echo "   - Total inventory value: {$totalValue}\n";
} else {
    echo "FAILED\n";
    echo "   - Status: {$response['status']}\n";
    echo "   - Response: " . json_encode($response['body']) . "\n";
}

// Test GET /inventory-reports/movements
echo "4. Testing GET /inventory-reports/movements... ";
$dateFrom = date('Y-m-d', strtotime('-1 week'));
$dateTo = date('Y-m-d');
$response = makeRequest('GET', $BASE_URL . "/inventory-reports/movements?date_from={$dateFrom}&date_to={$dateTo}");
if ($response['status'] == 200 && isset($response['body']['data'])) {
    echo "SUCCESS\n";
    $movementsCount = count($response['body']['data']);
    echo "   - {$movementsCount} movement records found\n";
} else {
    echo "FAILED\n";
    echo "   - Status: {$response['status']}\n";
    echo "   - Response: " . json_encode($response['body']) . "\n";
}

// Cleanup - delete test product if created
if (isset($newProductId)) {
    echo "\nCLEANUP\n";
    echo "-------\n";

    echo "Deleting test product (ID: {$newProductId})... ";
    $response = makeRequest('DELETE', $BASE_URL . "/products/{$newProductId}");
    if ($response['status'] == 204) {
        echo "SUCCESS\n";
    } else {
        echo "FAILED\n";
        echo "   - Status: {$response['status']}\n";
        echo "   - Response: " . json_encode($response['body']) . "\n";
    }
}

echo "\nAPI TEST COMPLETED\n";

// Helper function to make HTTP requests
function makeRequest($method, $url, $data = null) {
    global $headers;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    if ($method == 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    } elseif ($method == 'PUT') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    } elseif ($method == 'DELETE') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return [
        'status' => $httpCode,
        'body' => json_decode($response, true)
    ];
}
