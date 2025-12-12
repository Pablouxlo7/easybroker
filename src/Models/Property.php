<?php

declare(strict_types=1);

namespace EasyBroker\Api\Models;

/**
 * Modelo que representa una propiedad de EasyBroker.
 * 
 * Encapsula los datos de una propiedad y proporciona
 * una interfaz limpia para acceder a ellos.
 */
class Property
{
    private string $publicId;
    private string $title;
    private ?string $propertyType;
    private ?int $bedrooms;
    private ?int $bathrooms;
    private ?string $location;
    private array $rawData;

    /**
     * Constructor privado. Usar el método estático fromArray.
     */
    private function __construct(array $data)
    {
        $this->rawData = $data;
        $this->publicId = $data['public_id'] ?? '';
        $this->title = $data['title'] ?? '';
        $this->propertyType = $data['property_type'] ?? null;
        $this->bedrooms = isset($data['bedrooms']) ? (int)$data['bedrooms'] : null;
        $this->bathrooms = isset($data['bathrooms']) ? (int)$data['bathrooms'] : null;
        $this->location = $this->extractLocation($data);
    }

    /**
     * Crea una instancia de Property desde un array.
     *
     * @param array $data Datos de la propiedad desde la API
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self($data);
    }

    /**
     * Extrae la ubicación de los datos de la propiedad.
     *
     * @param array $data Datos de la propiedad
     * @return string|null Ubicación formateada
     */
    private function extractLocation(array $data): ?string
    {
        if (!isset($data['location'])) {
            return null;
        }

        $location = $data['location'];
        $parts = array_filter([
            $location['name'] ?? null,
            $location['city'] ?? null,
            $location['state'] ?? null,
        ]);

        return !empty($parts) ? implode(', ', $parts) : null;
    }

    public function getPublicId(): string
    {
        return $this->publicId;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getPropertyType(): ?string
    {
        return $this->propertyType;
    }

    public function getBedrooms(): ?int
    {
        return $this->bedrooms;
    }

    public function getBathrooms(): ?int
    {
        return $this->bathrooms;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function getRawData(): array
    {
        return $this->rawData;
    }

    public function toArray(): array
    {
        return [
            'public_id' => $this->publicId,
            'title' => $this->title,
            'property_type' => $this->propertyType,
            'bedrooms' => $this->bedrooms,
            'bathrooms' => $this->bathrooms,
            'location' => $this->location,
        ];
    }

    public function __toString(): string
    {
        return sprintf(
            "[%s] %s (%s)",
            $this->publicId,
            $this->title,
            $this->propertyType ?? 'Sin tipo'
        );
    }
}