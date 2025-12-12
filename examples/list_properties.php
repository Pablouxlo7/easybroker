<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/HttpClient.php';
require_once __DIR__ . '/../src/Models/Property.php';
require_once __DIR__ . '/../src/Services/PropertyService.php';

use EasyBroker\Api\HttpClient;
use EasyBroker\Api\Services\PropertyService;
use EasyBroker\Api\ApiException;

/**
 * Script principal para leer y mostrar todas las propiedades
 * del ambiente de pruebas de EasyBroker.
 */

// Configuración del ambiente de pruebas
const STAGING_BASE_URL = 'https://api.stagingeb.com';
const STAGING_API_KEY = 'l7u502p8v46ba3ppgvj5y2aad50lb9';

function displayProperty(EasyBroker\Api\Models\Property $property, int $index): void
{
    echo sprintf("\n%d. %s\n", $index, str_repeat('-', 80));
    echo sprintf("   ID:       %s\n", $property->getPublicId());
    echo sprintf("   Título:   %s\n", $property->getTitle());
    
    if ($property->getPropertyType()) {
        echo sprintf("   Tipo:     %s\n", $property->getPropertyType());
    }
    
    if ($property->getBedrooms() !== null) {
        echo sprintf("   Recámaras: %d\n", $property->getBedrooms());
    }
    
    if ($property->getBathrooms() !== null) {
        echo sprintf("   Baños:    %d\n", $property->getBathrooms());
    }
    
    if ($property->getLocation()) {
        echo sprintf("   Ubicación: %s\n", $property->getLocation());
    }
}

function main(): void
{
    try {
        echo "=== Cliente de API EasyBroker ===\n";
        echo "Ambiente: Pruebas (Staging)\n\n";

        $httpClient = new HttpClient(STAGING_BASE_URL, STAGING_API_KEY);
        $propertyService = new PropertyService($httpClient);

        echo "Obteniendo información de propiedades...\n";
        $totalProperties = $propertyService->countProperties();
        echo sprintf("Total de propiedades disponibles: %d\n", $totalProperties);

        echo "\n" . str_repeat('=', 80) . "\n";
        echo "LISTADO DE PROPIEDADES\n";
        echo str_repeat('=', 80) . "\n";

        $counter = 0;
        foreach ($propertyService->getAllProperties() as $property) {
            $counter++;
            displayProperty($property, $counter);
        }

        echo "\n" . str_repeat('=', 80) . "\n";
        echo sprintf("Se procesaron %d propiedades exitosamente.\n", $counter);

    } catch (ApiException $e) {
        echo "\n❌ Error de API: " . $e->getMessage() . "\n";
        echo "Código: " . $e->getCode() . "\n";
        exit(1);
    } catch (Exception $e) {
        echo "\n❌ Error inesperado: " . $e->getMessage() . "\n";
        exit(1);
    }
}

if (php_sapi_name() === 'cli') {
    main();
}