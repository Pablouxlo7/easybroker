<?php

declare(strict_types=1);

namespace EasyBroker\Api\Services;

use EasyBroker\Api\HttpClient;
use EasyBroker\Api\Models\Property;
use Generator;

/**
 * Servicio para interactuar con propiedades de EasyBroker.
 * 
 * Proporciona métodos de alto nivel para consultar propiedades
 * con paginación automática y manejo de errores.
 */
class PropertyService
{
    private const PROPERTIES_ENDPOINT = '/v1/properties';
    private const DEFAULT_PAGE_SIZE = 20;

    private HttpClient $httpClient;

    public function __construct(HttpClient $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * Obtiene todas las propiedades usando paginación automática.
     * 
     * @param int $limit Número máximo de propiedades a obtener (0 = todas)
     * @return Generator<Property>
     */
    public function getAllProperties(int $limit = 0): Generator
    {
        $page = 1;
        $totalFetched = 0;

        do {
            $response = $this->httpClient->get(self::PROPERTIES_ENDPOINT, [
                'page' => $page,
                'limit' => self::DEFAULT_PAGE_SIZE,
            ]);

            $properties = $response['content'] ?? [];
            $pagination = $response['pagination'] ?? [];
            
            foreach ($properties as $propertyData) {
                if ($limit > 0 && $totalFetched >= $limit) {
                    return;
                }

                yield Property::fromArray($propertyData);
                $totalFetched++;
            }

            $hasNextPage = $pagination['next_page'] ?? false;
            $page++;

        } while ($hasNextPage && ($limit === 0 || $totalFetched < $limit));
    }

    /**
     * Obtiene una página específica de propiedades.
     *
     * @param int $page Número de página (1-indexed)
     * @param int $pageSize Tamaño de página
     * @return array{properties: Property[], pagination: array}
     */
    public function getPropertiesPage(int $page = 1, int $pageSize = self::DEFAULT_PAGE_SIZE): array
    {
        $response = $this->httpClient->get(self::PROPERTIES_ENDPOINT, [
            'page' => $page,
            'limit' => $pageSize,
        ]);

        $properties = array_map(
            fn($data) => Property::fromArray($data),
            $response['content'] ?? []
        );

        return [
            'properties' => $properties,
            'pagination' => $response['pagination'] ?? [],
        ];
    }

    /**
     * Obtiene los detalles de una propiedad específica.
     *
     * @param string $propertyId ID público de la propiedad
     * @return Property
     */
    public function getProperty(string $propertyId): Property
    {
        $response = $this->httpClient->get(self::PROPERTIES_ENDPOINT . '/' . $propertyId);
        return Property::fromArray($response);
    }

    /**
     * Cuenta el total de propiedades disponibles.
     *
     * @return int
     */
    public function countProperties(): int
    {
        $response = $this->httpClient->get(self::PROPERTIES_ENDPOINT, [
            'page' => 1,
            'limit' => 1,
        ]);

        return $response['pagination']['total'] ?? 0;
    }
}