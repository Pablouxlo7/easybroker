<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/HttpClient.php';
require_once __DIR__ . '/../src/Models/Property.php';
require_once __DIR__ . '/../src/Services/PropertyService.php';

use EasyBroker\Api\ApiException;
use EasyBroker\Api\HttpClient;
use EasyBroker\Services\PropertyService;

/**
 * Main script to read and display all properties
 * of the EasyBroker test environment.
 * .
 */

// Test environment setup
const STAGING_BASE_URL = 'https://api.stagingeb.com';
const STAGING_API_KEY = 'l7u502p8v46ba3ppgvj5y2aad50lb9';

function displayProperty(EasyBroker\Models\Property $property, int $index): void
{
    echo sprintf("\n%d. %s\n", $index, str_repeat('-', 80));
    echo sprintf("   ID:       %s\n", $property->getPublicId());
    echo sprintf("   Title:   %s\n", $property->getTitle());
    
    if ($property->getPropertyType()) {
        echo sprintf("   Type:     %s\n", $property->getPropertyType());
    }
    
    if ($property->getBedrooms() !== null) {
        echo sprintf("   Rooms: %d\n", $property->getBedrooms());
    }
    
    if ($property->getBathrooms() !== null) {
        echo sprintf("   Bathrooms:    %d\n", $property->getBathrooms());
    }
    
    if ($property->getLocation()) {
        echo sprintf("   Location: %s\n", $property->getLocation());
    }
}

function main(): void
{
    try {
        echo "=== API CLIENT EasyBroker ===\n";
        echo "Environment: Testing (Staging)\n\n";

        $httpClient = new HttpClient(STAGING_BASE_URL, STAGING_API_KEY);
        $propertyService = new PropertyService($httpClient);

        echo "Obtaining property information...\n";
        $totalProperties = $propertyService->countProperties();
        echo sprintf("Total available properties: %d\n", $totalProperties);

        echo "\n" . str_repeat('=', 80) . "\n";
        echo "PROPERTY LIST\n";
        echo str_repeat('=', 80) . "\n";

        $counter = 0;
        foreach ($propertyService->getAllProperties() as $property) {
            $counter++;
            displayProperty($property, $counter);
        }

        echo "\n" . str_repeat('=', 80) . "\n";
        echo sprintf("%d properties were processed successfully.\n", $counter);

    } catch (ApiException $e) {
        echo "\n❌ API ERROR: " . $e->getMessage() . "\n";
        echo "Code: " . $e->getCode() . "\n";
        exit(1);
    } catch (Exception $e) {
        echo "\n❌ Unexpected error: " . $e->getMessage() . "\n";
        exit(1);
    }
}

if (php_sapi_name() === 'cli') {
    main();
}