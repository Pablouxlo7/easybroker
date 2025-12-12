<?php

declare(strict_types=1);

namespace EasyBroker\Services;

use EasyBroker\Api\ApiException;
use EasyBroker\Api\HttpClient;
use EasyBroker\Models\Property;
use Generator;

/**
 * Service to interact with EasyBroker properties
 *
 * It provides high-level methods for querying properties
 *  with automatic pagination and error handling.
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
     * It retrieves all properties using automatic pagination
     *
     * @param int $limit Maximum number of properties to obtain (0 = all)
     * @return Generator<Property>
     * @throws ApiException
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
     * Get a specific properties page.
     *
     * @param int $page Page number (1-indexed)
     * @param int $pageSize Page size
     * @return array{properties: Property[], pagination: array}
     * @throws ApiException
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
     * Get the details of a specific property
     *
     * @param string $propertyId Public property ID
     * @return Property
     * @throws ApiException
     */
    public function getProperty(string $propertyId): Property
    {
        $response = $this->httpClient->get(self::PROPERTIES_ENDPOINT . '/' . $propertyId);
        return Property::fromArray($response);
    }

    /**
     * Count the total number of available properties.
     *
     * @return int
     * @throws ApiException
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