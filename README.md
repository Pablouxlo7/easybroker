# EasyBroker PHP Client

Professional PHP client for consuming the EasyBroker API following SOLID principles and clean code practices.

## 🚀 Quick Start

```bash
# Install dependencies
composer install

# Run the example
php examples/list_properties.php
```

## 📁 Project Structure

```
easybroker-client/
├── src/
│   ├── HttpClient.php       # HTTP client with rate limiting
│   ├── Property.php          # Property model (immutable)
│   └── PropertyService.php   # Property business logic
├── examples/
│   └── list_properties.php   # Example usage script
├── tests/
│   └── PropertyTest.php      # Unit and integration tests
├── .gitignore                # Git ignore rules
├── composer.json             # Composer configuration
└── README.md                 # This file
```

## 🎯 Features

- ✅ **SOLID Architecture** - Clean separation of concerns
- ✅ **Automatic Rate Limiting** - Respects 20 requests/second limit
- ✅ **Efficient Pagination** - Uses PHP Generator for memory efficiency
- ✅ **Type Safety** - Full PHP 8.0+ type hints and strict mode
- ✅ **Immutable Models** - Property objects are immutable
- ✅ **Comprehensive Tests** - Unit and integration tests included
- ✅ **PSR-4 Autoloading** - Standard autoloading
- ✅ **Clean Code** - Well documented and maintainable

## 📚 Usage Examples

### Basic Usage - List All Properties

```php
<?php

require_once 'vendor/autoload.php';

use EasyBroker\HttpClient;
use EasyBroker\PropertyService;

// Initialize client
$httpClient = new HttpClient(
    'https://api.stagingeb.com',
    'l7u502p8v46ba3ppgvj5y2aad50lb9'
);

// Create service
$propertyService = new PropertyService($httpClient);

// Fetch all properties
foreach ($propertyService->getAllProperties() as $property) {
    echo sprintf(
        "[%s] %s\n",
        $property->getPublicId(),
        $property->getTitle()
    );
}
```

### Get Properties Page by Page

```php
$result = $propertyService->getPropertiesPage(page: 1, pageSize: 20);

foreach ($result['properties'] as $property) {
    echo sprintf(
        "Title: %s\nType: %s\nBedrooms: %d\n\n",
        $property->getTitle(),
        $property->getPropertyType() ?? 'N/A',
        $property->getBedrooms() ?? 0
    );
}

// Check pagination info
$pagination = $result['pagination'];
echo "Total: {$pagination['total']}\n";
echo "Has next page: " . ($pagination['next_page'] ? 'Yes' : 'No') . "\n";
```

### Search Properties

```php
// Search by title
$houses = $propertyService->searchProperties('house', 'title');

foreach ($houses as $property) {
    echo $property->getTitle() . "\n";
}
```

### Get Single Property

```php
$property = $propertyService->getProperty('EB-A1234');

echo "Title: " . $property->getTitle() . "\n";
echo "Location: " . $property->getLocation() . "\n";
echo "Bedrooms: " . $property->getBedrooms() . "\n";
echo "Bathrooms: " . $property->getBathrooms() . "\n";
```

### Count Total Properties

```php
$total = $propertyService->countProperties();
echo "Total properties available: {$total}\n";
```

### Limit Results

```php
// Get only first 10 properties
foreach ($propertyService->getAllProperties(10) as $property) {
    echo $property->getTitle() . "\n";
}
```

## 🏗️ Architecture

### Design Principles

#### SOLID Principles

**Single Responsibility Principle (SRP)**
- `HttpClient` - Only handles HTTP communication and rate limiting
- `PropertyService` - Only handles property-related business logic
- `Property` - Only represents property data

**Open/Closed Principle (OCP)**
- Easy to extend with new services without modifying existing code
- Closed for modification, open for extension

**Liskov Substitution Principle (LSP)**
- HttpClient can be replaced with any compatible implementation
- Property objects behave consistently

**Interface Segregation Principle (ISP)**
- Clean, focused interfaces with no unnecessary methods

**Dependency Inversion Principle (DIP)**
- PropertyService depends on HttpClient abstraction
- Easy to inject mocks for testing

### Design Patterns

- **Factory Method** - `Property::fromArray()` creates instances
- **Service Layer** - PropertyService provides high-level operations
- **Value Object** - Property is an immutable value object
- **Generator Pattern** - Memory-efficient iteration over large datasets
- **Exception Handling** - Custom ApiException for API errors

### Clean Code Practices

- **Type Safety** - Strict type declarations and return types
- **Immutability** - Property objects cannot be modified after creation
- **Self-Documenting** - Clear method and variable names
- **Small Methods** - Each method does one thing well
- **DRY Principle** - No code duplication
- **KISS Principle** - Simple, straightforward solutions

## 🧪 Testing

### Run All Tests

```bash
# Install PHPUnit
composer require --dev phpunit/phpunit

# Run tests
./vendor/bin/phpunit tests/

# Or use composer script
composer test
```

### Test Coverage

The test suite includes:

- ✅ **PropertyTest** - Property model creation and methods
- ✅ **HttpClientTest** - HTTP client instantiation and errors
- ✅ **PropertyServiceTest** - Service methods and logic
- ✅ **IntegrationTest** - Real API calls (requires connection)

### Example Test

```php
public function testPropertyCreationFromArray(): void
{
    $data = [
        'public_id' => 'EB-TEST123',
        'title' => 'House for sale',
        'property_type' => 'House',
        'bedrooms' => 3,
        'bathrooms' => 2,
    ];

    $property = Property::fromArray($data);

    $this->assertEquals('EB-TEST123', $property->getPublicId());
    $this->assertEquals('House for sale', $property->getTitle());
    $this->assertEquals(3, $property->getBedrooms());
}
```

## 🔐 Configuration

### Staging Environment (Testing)

```php
const STAGING_BASE_URL = 'https://api.stagingeb.com';
const STAGING_API_KEY = 'l7u502p8v46ba3ppgvj5y2aad50lb9';

$httpClient = new HttpClient(STAGING_BASE_URL, STAGING_API_KEY);
```

### Production Environment

```php
const PRODUCTION_BASE_URL = 'https://api.easybroker.com';

// Get your API key from: https://www.easybroker.com/cuenta
$apiKey = getenv('EASYBROKER_API_KEY');

$httpClient = new HttpClient(PRODUCTION_BASE_URL, $apiKey);
```

### Environment Variables (Recommended)

Create a `.env` file:

```env
EASYBROKER_BASE_URL=https://api.easybroker.com
EASYBROKER_API_KEY=your_api_key_here
```

Then load it in your code:

```php
$httpClient = new HttpClient(
    getenv('EASYBROKER_BASE_URL'),
    getenv('EASYBROKER_API_KEY')
);
```

## 🔒 Security Best Practices

- ❌ Never commit API keys to version control
- ✅ Use environment variables for sensitive data
- ✅ Validate all user inputs
- ✅ Handle exceptions properly
- ✅ Use HTTPS for all API calls
- ✅ Keep dependencies up to date

## 📝 Requirements

- PHP 8.0 or higher
- ext-curl (for HTTP requests)
- ext-json (for JSON parsing)
- Composer (for dependency management)

## 🤝 Contributing

Contributions are welcome! Please follow these steps:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Write tests for your changes
4. Ensure all tests pass
5. Commit your changes (`git commit -m 'Add amazing feature'`)
6. Push to the branch (`git push origin feature/amazing-feature`)
7. Open a Pull Request

### Code Style

- Follow PSR-12 coding standard
- Use strict type declarations
- Write PHPDoc for all public methods
- Keep methods small and focused
- Write tests for new features

## 📄 License

This project is licensed under the MIT License - see the LICENSE file for details.

## 📚 Resources

- [EasyBroker API Documentation](https://dev.easybroker.com/docs)
- [API Reference](https://dev.easybroker.com/reference)
- [Staging Environment](https://api.stagingeb.com)

## 🙏 Acknowledgments

- EasyBroker for providing the API
- PHP community for best practices and patterns

## 📧 Support

For issues, questions, or contributions:

- Create an issue on GitHub
- Check existing documentation
- Review the test files for usage examples

---

**Built with ❤️ following PHP best practices and SOLID principles**
