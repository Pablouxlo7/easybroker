# Cliente PHP para API de EasyBroker

Cliente profesional en PHP para consumir la API de EasyBroker.

## 🚀 Instalación

```bash
composer install
```

## 💻 Uso Rápido

```bash
php examples/list_properties.php
```

## 📁 Estructura

```
easybroker-client/
├── src/
│   ├── HttpClient.php
│   ├── Models/
│   │   └── Property.php
│   └── Services/
│       └── PropertyService.php
├── examples/
│   └── list_properties.php
├── tests/
│   └── PropertyTest.php
└── composer.json
```

## 🎯 Características

- ✅ Arquitectura SOLID
- ✅ Rate limiting automático
- ✅ Paginación con Generator
- ✅ Type safety (PHP 8.0+)
- ✅ Tests unitarios
- ✅ PSR-4 autoloading

## 📚 Ejemplos

### Listar todas las propiedades

```php
$httpClient = new HttpClient(
    'https://api.stagingeb.com',
    'l7u502p8v46ba3ppgvj5y2aad50lb9'
);

$propertyService = new PropertyService($httpClient);

foreach ($propertyService->getAllProperties() as $property) {
    echo $property->getTitle() . "\n";
}
```

### Obtener una página

```php
$result = $propertyService->getPropertiesPage(1, 20);
foreach ($result['properties'] as $property) {
    echo $property->getTitle() . "\n";
}
```

## 🧪 Tests

```bash
./vendor/bin/phpunit tests/
```

## 📖 Documentación

- [API EasyBroker](https://dev.easybroker.com/docs)
- [Playground](https://api.stagingeb.com)

## 📄 Licencia

MIT License
