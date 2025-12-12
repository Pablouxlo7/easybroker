<?php

declare(strict_types=1);

namespace EasyBroker\Tests;

use PHPUnit\Framework\TestCase;
use EasyBroker\Models\Property;

class PropertyTest extends TestCase
{
    public function testPropertyCreationFromArray(): void
    {
        $data = [
            'public_id' => 'EB-TEST123',
            'title' => 'Casa en venta',
            'property_type' => 'Casa',
            'bedrooms' => 3,
            'bathrooms' => 2,
            'location' => [
                'name' => 'Colonia Roma',
                'city' => 'Ciudad de México',
                'state' => 'CDMX',
            ],
        ];

        $property = Property::fromArray($data);

        $this->assertEquals('EB-TEST123', $property->getPublicId());
        $this->assertEquals('Casa en venta', $property->getTitle());
        $this->assertEquals('Casa', $property->getPropertyType());
        $this->assertEquals(3, $property->getBedrooms());
        $this->assertEquals(2, $property->getBathrooms());
    }

    public function testPropertyWithMissingFields(): void
    {
        $data = [
            'public_id' => 'EB-TEST456',
            'title' => 'Terreno',
        ];

        $property = Property::fromArray($data);

        $this->assertEquals('EB-TEST456', $property->getPublicId());
        $this->assertEquals('Terreno', $property->getTitle());
        $this->assertNull($property->getPropertyType());
        $this->assertNull($property->getBedrooms());
    }
}