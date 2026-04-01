# Laravel EAV Package

A complete and powerful Entity-Attribute-Value (EAV) package for Laravel.

## Documentation

**[Laravel EAV — Documentation](https://fiachehr.ir/docs/laravel-eav.html)** (hosted on [fiachehr.ir](https://fiachehr.ir))

## Features

- ✅ Clean architecture (Domain, Application, Infrastructure, Presentation)
- ✅ MorphTo relationships support for any model
- ✅ Attribute groups management
- ✅ **20+ attribute types** (Text, Textarea, Number, Decimal, Date, DateTime, Boolean, File, and more)
- ✅ **Flexible validation system** with validation rules (Email, URL, Image, Video, etc. can be applied via validations)
- ✅ **Optimized database structure** with separate columns for different data types
- ✅ **Advanced search and filtering** capabilities
- ✅ **Query Builder** for complex EAV queries
- ✅ **Indexed columns** for fast searches
- ✅ **Multilingual support** with language-based filtering and optional translation system integration
- ✅ Validation support
- ✅ Easy to use trait for models
- ✅ Backward compatible with existing data

## Requirements

- PHP >= 8.3
- Laravel **10.x**, **11.x**, or **12.x** (see `composer.json` `illuminate/*` constraints)

## Installation

```bash
composer require fiachehr/laravel-eav
```

After installation, publish and run the migrations:

```bash
php artisan migrate
```

## Usage

### 1. Use the Trait in Your Models

```php
use Fiachehr\LaravelEav\Domain\Shared\Traits\HasAttributes;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasAttributes;
    
    // Your model code...
}
```

### 2. Working with Attributes

#### 2.1. Getting Attribute Values

```php
// Get all attributes with their values
$product->eavAttributes; // Returns collection with pivot values

// Get attribute value by ID or slug
$value = $product->getEavAttributeValue(1); // by ID
$value = $product->getEavAttributeValue('color'); // by slug
$value = $product->getEavAttributeValueBySlug('color');

// Get all attribute values as key-value pairs
// Keys can be 'id', 'slug', or 'logical_id'
$values = $product->getEavAttributeValues('slug'); 
// Returns: ['color' => 'red', 'size' => 'large', 'price' => 100]

$values = $product->getEavAttributeValues('id'); 
// Returns: [1 => 'red', 2 => 'large', 3 => 100]

// Get all attribute values as Eloquent models
$attributeValues = $product->eavAttributeValues; 
// Returns collection of EloquentAttributeValue models
```

#### 2.2. Setting Attribute Values

```php
// Set attribute values (by ID or slug)
$product->setEavAttributeValues([
    1 => 'Value 1',           // by ID
    'color' => 'red',         // by slug
    'size' => 'large',        // by slug
    'price' => 100.50,        // number
    'is_active' => true,      // boolean
    'created_at' => '2024-01-01', // date
]);

// Set a single attribute value
$product->setEavAttributeValue('color', 'blue');
$product->setEavAttributeValue(1, 'new value');

// Sync attribute values (removes old ones not in the array)
$product->syncEavAttributeValues([
    'color' => 'red',
    'size' => 'medium',
]);

// Remove a specific attribute
$product->removeEavAttributeValue('color');
$product->removeEavAttributeValue(1);

// Clear all attribute values
$product->clearEavAttributeValues();
```

### 3. Validation System

You can apply validations to attributes to ensure data integrity. Validations are applied based on the attribute type.

#### 3.1. Setting Validations

```php
use Fiachehr\LaravelEav\Domain\Enums\AttributeType;
use Fiachehr\LaravelEav\Domain\Enums\ValidationType;

// Create an attribute with email validation
$attribute = Attribute::create([
    'title' => 'Email Address',
    'type' => AttributeType::TEXT,
    'validations' => [
        ValidationType::REQUIRED->value,
        ValidationType::EMAIL->value,
    ],
]);

// Create an attribute with image file validation
$attribute = Attribute::create([
    'title' => 'Profile Image',
    'type' => AttributeType::FILE,
    'validations' => [
        ValidationType::REQUIRED->value,
        ValidationType::IMAGE->value,
        ['type' => ValidationType::MAX_FILE_SIZE->value, 'parameter' => 2048], // 2MB
    ],
]);

// Create an attribute with min/max length validation
$attribute = Attribute::create([
    'title' => 'Description',
    'type' => AttributeType::TEXTAREA,
    'validations' => [
        ValidationType::REQUIRED->value,
        ['type' => ValidationType::MIN_LENGTH->value, 'parameter' => 10],
        ['type' => ValidationType::MAX_LENGTH->value, 'parameter' => 500],
    ],
]);
```

#### 3.2. Validating Attribute Values

```php
use Fiachehr\LaravelEav\Domain\Services\AttributeValidator;

$validator = new AttributeValidator();

try {
    // Validate a value against attribute validations
    $validator->validate(
        AttributeType::TEXT,
        'user@example.com',
        [ValidationType::EMAIL->value]
    );
    
    // Value is valid
} catch (\Illuminate\Validation\ValidationException $e) {
    // Handle validation errors
    $errors = $e->validator->errors();
}
```

#### 3.3. Getting Laravel Validation Rules

```php
use Fiachehr\LaravelEav\Domain\Services\AttributeValidator;

$validator = new AttributeValidator();

// Get Laravel validation rules for an attribute
$rules = $validator->getValidationRules(
    AttributeType::TEXT,
    [
        ValidationType::REQUIRED->value,
        ValidationType::EMAIL->value,
        ['type' => ValidationType::MAX_LENGTH->value, 'parameter' => 255],
    ]
);

// Use in Laravel Form Request
$request->validate([
    'email' => $rules,
]);
```

### 4. Multilingual Support

The package provides built-in multilingual support for attributes and attribute groups. Each attribute and attribute group has a `language` field that stores the language code (e.g., 'en', 'fa', 'de').

#### 4.1. Creating Attributes with Language

```php
use Fiachehr\LaravelEav\Domain\Entities\Attribute;
use Fiachehr\LaravelEav\Domain\Enums\AttributeType;
use Fiachehr\LaravelEav\Domain\Repositories\AttributeRepositoryInterface;

// Create attribute in English
$attributeEn = new Attribute(
    id: null,
    logicalId: 'color-en',
    title: 'Color',
    slug: 'color',
    type: AttributeType::TEXT,
    description: 'Product color',
    values: [],
    validations: [],
    isActive: true,
    language: 'en'
);

// Create attribute in Persian
$attributeFa = new Attribute(
    id: null,
    logicalId: 'color-fa',
    title: 'رنگ',
    slug: 'color-fa',
    type: AttributeType::TEXT,
    description: 'رنگ محصول',
    values: [],
    validations: [],
    isActive: true,
    language: 'fa'
);

$repository = app(AttributeRepositoryInterface::class);
$repository->create($attributeEn);
$repository->create($attributeFa);
```

#### 4.2. Filtering by Language

##### Using Model Scopes

```php
use Fiachehr\LaravelEav\Infrastructure\Persistence\Eloquent\EloquentAttribute;

// Get attributes for a specific language
$englishAttributes = EloquentAttribute::forLanguage('en')->get();
$persianAttributes = EloquentAttribute::forLanguage('fa')->get();

// Get attributes for current locale
$currentLanguageAttributes = EloquentAttribute::forCurrentLanguage()->get();

// Get attributes for multiple languages
$multiLanguageAttributes = EloquentAttribute::forLanguages(['en', 'fa', 'de'])->get();

// Exclude a specific language
$nonEnglishAttributes = EloquentAttribute::excludeLanguage('en')->get();

// Combine with other scopes
$activeEnglishAttributes = EloquentAttribute::forLanguage('en')
    ->where('is_active', true)
    ->get();
```

##### Using Repository Methods

```php
use Fiachehr\LaravelEav\Domain\Repositories\AttributeRepositoryInterface;
use Fiachehr\LaravelEav\Domain\Repositories\AttributeGroupRepositoryInterface;

$attributeRepository = app(AttributeRepositoryInterface::class);
$groupRepository = app(AttributeGroupRepositoryInterface::class);

// Get attributes by language
$englishAttributes = $attributeRepository->findByLanguage('en');
$currentLanguageAttributes = $attributeRepository->findByCurrentLanguage();
$multiLanguageAttributes = $attributeRepository->findByLanguages(['en', 'fa']);

// Same for attribute groups
$englishGroups = $groupRepository->findByLanguage('en');
$currentLanguageGroups = $groupRepository->findByCurrentLanguage();
```

#### 4.3. Translation System

The package includes a built-in translation system using the `eav_translations` table. This allows you to store translations for attribute fields (like `title` and `description`) in multiple languages.

##### Using the HasTranslations Trait

To enable translations for your Attribute or AttributeGroup models, use the `HasTranslations` trait:

```php
use Fiachehr\LaravelEav\Domain\Shared\Traits\HasTranslations;
use Fiachehr\LaravelEav\Infrastructure\Persistence\Eloquent\EloquentAttribute;

class Attribute extends EloquentAttribute
{
    use HasTranslations;
    
    // Define which fields are translatable
    protected $translatable = [
        'title',
        'description',
    ];
}
```

##### Getting Translations

```php
use Fiachehr\LaravelEav\Infrastructure\Persistence\Eloquent\EloquentAttribute;

$attribute = EloquentAttribute::with('translations')->find(1);

// Get translation for a specific field and locale
$translatedTitle = $attribute->getTranslation('title', 'fa');
// Returns: 'رنگ' or null if not found

// Get all translations grouped by locale
$allTranslations = $attribute->getAllTranslations();
// Returns: ['en' => ['title' => 'Color', 'description' => '...'], 'fa' => ['title' => 'رنگ', ...]]

// Get translations for a specific locale
$persianTranslations = $attribute->getTranslationsForLocale('fa');
// Returns: ['title' => 'رنگ', 'description' => '...']

// Get translations for a specific key across all locales
$titleTranslations = $attribute->getTranslationsForKey('title');
// Returns: ['en' => 'Color', 'fa' => 'رنگ', 'de' => 'Farbe']

// Check if translation exists for a locale
if ($attribute->hasTranslation('fa')) {
    // Translation exists for Persian
}

// Check if translation exists for a specific key and locale
if ($attribute->hasTranslationForKey('title', 'fa')) {
    // Title translation exists for Persian
}

// Access translations relationship directly
$translations = $attribute->translations;
// Returns: Collection of EloquentTranslation models
```

##### Setting Translations

```php
// Set a single translation
$attribute->setTranslation('title', 'fa', 'رنگ');
$attribute->setTranslation('description', 'fa', 'رنگ محصول');

// Set multiple translations at once
$attribute->setTranslations([
    'title' => [
        'en' => 'Color',
        'fa' => 'رنگ',
        'de' => 'Farbe',
    ],
    'description' => [
        'en' => 'Product color',
        'fa' => 'رنگ محصول',
        'de' => 'Produktfarbe',
    ],
]);

// Or using locale-first format
$attribute->setTranslations([
    'en' => [
        'title' => 'Color',
        'description' => 'Product color',
    ],
    'fa' => [
        'title' => 'رنگ',
        'description' => 'رنگ محصول',
    ],
]);
```

##### Deleting Translations

```php
// Delete a specific translation
$attribute->deleteTranslation('title', 'fa');

// Delete all translations for a specific locale
$attribute->deleteTranslationsForLocale('fa');

// Delete all translations for a specific key across all locales
$attribute->deleteTranslationsForKey('title');

// Delete all translations for this model
$attribute->deleteAllTranslations();
```

##### Using with Custom Models

If you extend `EloquentAttribute` in your project, the translation system automatically uses the base class name for compatibility:

```php
use Fiachehr\LaravelEav\Infrastructure\Persistence\Eloquent\EloquentAttribute as BaseEloquentAttribute;
use Fiachehr\LaravelEav\Domain\Shared\Traits\HasTranslations;

class Attribute extends BaseEloquentAttribute
{
    use HasTranslations;
    
    protected $translatable = ['title', 'description'];
}

// The translations relation will automatically work with existing data
// that uses the base class name (EloquentAttribute)
$attribute = Attribute::with('translations')->find(1);
$translations = $attribute->translations; // Works correctly!
```

##### Database Structure

Translations are stored in the `eav_translations` table:

```sql
- id
- translatable_id (ID of the attribute/group)
- translatable_type (Model class name)
- locale (Language code: 'en', 'fa', 'de', etc.)
- key (Field name: 'title', 'description', etc.)
- value (Translation text)
```

**Note:** The translation system uses polymorphic relations, so it works seamlessly with models that extend `EloquentAttribute` or use the trait directly.

#### 4.4. Working with Multilingual Attributes in Models

```php
use Fiachehr\LaravelEav\Domain\Shared\Traits\HasAttributes;

class Product extends Model
{
    use HasAttributes;
}

// Get attributes for current language
$product = Product::find(1);
$currentLanguageAttributes = $product->eavAttributes()
    ->where('language', app()->getLocale())
    ->get();

// Or filter by specific language
$englishAttributes = $product->eavAttributes()
    ->where('language', 'en')
    ->get();
```

#### 4.5. Language Configuration

Make sure your Laravel application has the locale configured:

```php
// config/app.php
'locale' => 'en',
'supported_locales' => ['en', 'fa', 'de', 'es'],
```

The package will automatically use `app()->getLocale()` for the `forCurrentLanguage()` scope.

### 5. Advanced Search and Filtering

#### 5.1. Using Model Scopes

```php
// Search products by attribute value (exact match)
$products = Product::whereEavAttribute('color', 'red')->get();

// Search with LIKE (partial match)
$products = Product::whereEavAttributeLike('title', 'laptop')->get();

// Search by number range
$products = Product::whereEavAttributeBetween('price', 100, 500)->get();

// Search by date range
$products = Product::whereEavAttributeDateBetween('created_at', '2024-01-01', '2024-12-31')->get();

// Search where value is IN array
$products = Product::whereEavAttributeIn('color', ['red', 'blue', 'green'])->get();

// Search where value is NOT IN array
$products = Product::whereEavAttributeNotIn('status', ['deleted', 'archived'])->get();

// Search where value is NULL
$products = Product::whereEavAttributeNull('notes')->get();

// Search where value is NOT NULL
$products = Product::whereEavAttributeNotNull('description')->get();

// Multiple conditions (AND - all must match)
$products = Product::whereEavAttributes([
    ['attribute' => 'color', 'value' => 'red'],
    ['attribute' => 'size', 'value' => 'large', 'operator' => '='],
    ['attribute' => 'price', 'value' => 100, 'operator' => '>='],
])->get();

// Combine with other Laravel query methods
$products = Product::whereEavAttribute('color', 'red')
    ->where('status', 'active')
    ->orderBy('created_at', 'desc')
    ->paginate(20);
```

#### 5.2. Using Query Builder (Recommended for Complex Queries)

```php
use Fiachehr\LaravelEav\Infrastructure\Query\EavQueryBuilder;

// Basic usage - get product IDs matching criteria
$query = Product::eavQuery();
$query->whereText('color', 'red')
      ->whereNumber('price', '>', 100)
      ->whereBoolean('in_stock', true);

$productIds = $query->getAttributableIds();
$products = Product::whereIn('id', $productIds)->get();

// Or use the helper method
$products = Product::findByEav(function ($query) {
    $query->whereText('color', 'red')
          ->whereNumberBetween('price', 100, 500)
          ->whereDateBetween('created_at', '2024-01-01', '2024-12-31');
});
```

#### 5.3. Text Search Methods

```php
$query = Product::eavQuery();

// Exact match
$query->whereText('title', 'Laptop');

// LIKE search (contains)
$query->whereTextLike('description', 'gaming');

// IN array
$query->whereTextIn('color', ['red', 'blue', 'green']);

// NOT IN array
$query->whereTextNotIn('status', ['deleted', 'archived']);

// IS NULL
$query->whereTextNull('notes');

// IS NOT NULL
$query->whereTextNotNull('description');
```

#### 5.4. Number Search Methods

```php
$query = Product::eavQuery();

// Exact match
$query->whereNumber('price', 100);

// Comparison operators
$query->whereNumber('price', '>', 100);
$query->whereNumber('price', '>=', 100);
$query->whereNumber('price', '<', 1000);
$query->whereNumber('price', '<=', 1000);
$query->whereNumber('price', '!=', 0);

// Range
$query->whereNumberBetween('price', 100, 500);

// IN array
$query->whereNumberIn('category_id', [1, 2, 3, 5, 8]);

// NOT IN array
$query->whereNumberNotIn('category_id', [4, 6, 7]);

// IS NULL
$query->whereNumberNull('discount');

// IS NOT NULL
$query->whereNumberNotNull('price');
```

#### 5.5. Decimal Search Methods

```php
$query = Product::eavQuery();

// Exact match
$query->whereDecimal('price', 99.99);

// Comparison
$query->whereDecimal('price', '>', 50.00);
$query->whereDecimal('price', '<=', 200.00);
```

#### 5.6. Date/Time Search Methods

```php
$query = Product::eavQuery();

// Date exact match
$query->whereDate('created_at', '2024-01-01');

// Date comparison
$query->whereDate('created_at', '>=', '2024-01-01');
$query->whereDate('created_at', '<', '2024-12-31');

// Date range
$query->whereDateBetween('created_at', '2024-01-01', '2024-12-31');

// DateTime exact match
$query->whereDateTime('published_at', '2024-01-01 10:00:00');

// DateTime comparison
$query->whereDateTime('published_at', '>=', '2024-01-01 00:00:00');
```

#### 5.7. Boolean Search Methods

```php
$query = Product::eavQuery();

// Boolean value
$query->whereBoolean('is_active', true);
$query->whereBoolean('is_featured', false);

// Shortcuts
$query->whereTrue('is_active');
$query->whereFalse('is_deleted');
```

#### 5.8. JSON Search Methods

```php
$query = Product::eavQuery();

// JSON contains key-value
$query->whereJsonContains('metadata', 'tags', 'electronics');
$query->whereJsonContains('specifications', 'ram', '8GB');
```

#### 5.9. Multiple Conditions

```php
$query = Product::eavQuery();

// AND conditions (all must match)
$query->whereText('color', 'red')
      ->whereNumber('price', '>', 100)
      ->whereBoolean('in_stock', true);

// OR conditions (any can match)
$query->whereAny([
    ['attribute' => 'color', 'value' => 'red'],
    ['attribute' => 'color', 'value' => 'blue'],
    ['attribute' => 'color', 'value' => 'green'],
]);

// Complex: (color = red AND price > 100) OR (color = blue AND price < 50)
$query->where(function ($q) {
    $q->whereText('color', 'red')
      ->whereNumber('price', '>', 100);
})->orWhere(function ($q) {
    $q->whereText('color', 'blue')
      ->whereNumber('price', '<', 50);
});

// Very complex: (color IN ['red','blue'] AND price > 100) OR (size = 'large' AND price < 200)
$query->where(function ($q) {
    $q->whereTextIn('color', ['red', 'blue'])
      ->whereNumber('price', '>', 100);
})->orWhere(function ($q) {
    $q->whereText('size', 'large')
      ->whereNumber('price', '<', 200);
});
```

#### 5.10. Ordering and Sorting

```php
$query = Product::eavQuery();

// Order by text value
$query->orderByText('title', 'asc');
$query->orderByText('name', 'desc');

// Order by number value
$query->orderByNumber('price', 'asc');
$query->orderByNumber('rating', 'desc');

// Order by date
$query->orderByDate('created_at', 'desc');
$query->orderByDateTime('published_at', 'asc');

// Multiple ordering
$query->orderByNumber('price', 'asc')
      ->orderByText('title', 'asc');
```

#### 5.11. Pagination and Limits

```php
$query = Product::eavQuery();

// Limit results
$query->limit(10);

// Offset
$query->offset(20);

// Get count
$count = $query->count();

// Get distinct attribute values
$colors = $query->getDistinctAttributeValues('color');
// Returns: ['red', 'blue', 'green', 'yellow']
```

#### 5.12. Getting Results

```php
$query = Product::eavQuery();
$query->whereText('color', 'red')
      ->whereNumber('price', '>', 100);

// Get matching entity IDs
$productIds = $query->getAttributableIds();
$products = Product::whereIn('id', $productIds)->get();

// Get count
$count = $query->count();

// Get all attribute values for matching entities
$colors = $query->getAttributeValues('color');

// Get distinct attribute values
$uniqueColors = $query->getDistinctAttributeValues('color');

// Get the underlying query builder for advanced operations
$dbQuery = $query->getQuery();
$results = $dbQuery->get();
```

#### 5.13. Selecting Specific Attributes

```php
// Get products with only specific attributes loaded
$products = Product::with(['eavAttributes' => function ($query) {
    $query->whereIn('slug', ['color', 'size', 'price']);
}])->get();

// Get attribute values for specific attributes only
$product = Product::find(1);
$specificValues = $product->eavAttributes()
    ->whereIn('slug', ['color', 'size'])
    ->get()
    ->mapWithKeys(function ($attr) {
        return [$attr->slug => $attr->pivot->value_text ?? $attr->pivot->value_number ?? $attr->pivot->value];
    });

// Get products with their attribute values as array
$products = Product::with('eavAttributes')->get()->map(function ($product) {
    return [
        'id' => $product->id,
        'name' => $product->name,
        'attributes' => $product->getEavAttributeValues('slug'),
    ];
});
```

#### 5.14. Aggregate Functions

```php
$query = Product::eavQuery();
$query->whereText('category', 'electronics');

// Get sum of prices
$totalPrice = $query->sum('price');

// Get average price
$avgPrice = $query->avg('price');

// Get minimum price
$minPrice = $query->min('price');

// Get maximum price
$maxPrice = $query->max('price');

// Get count
$count = $query->count();
```

### 6. Working with Attribute Groups

```php
// Get all attribute groups
$product->eavAttributeGroups;

// Attach groups
$product->attachEavAttributeGroups([1, 2, 3]);

// Sync groups (replaces all existing)
$product->syncEavAttributeGroups([1, 2]);

// Detach groups
$product->detachEavAttributeGroups([1]);

// Get attributes through groups
$attributes = $product->getEavAttributesThroughGroups();
```

### 7. Available Attribute Types

The package supports **20+ attribute types** with a flexible validation system:

#### Text Types
- `TEXT` - Simple text input (can be validated as Email, URL, Slug, etc.)
- `TEXTAREA` - Multi-line text (can be validated as Rich Text, Markdown, JSON, etc.)
- `PASSWORD` - Password field (with built-in password validation)

#### Number Types
- `NUMBER` - Integer
- `DECIMAL` - Decimal/Float

#### Selection Types
- `RADIO` - Radio buttons
- `SELECT` - Dropdown select
- `MULTIPLE` - Multiple select
- `CHECKBOX` - Checkbox
- `COLOR` - Color picker

#### Date/Time Types
- `DATE` - Date only
- `TIME` - Time only
- `DATETIME` - Date and time

#### Boolean Types
- `BOOLEAN` - True/False

#### File Types
- `FILE` - File upload (can be validated as Image, Video, Audio, Document, etc.)

#### Location Types
- `LOCATION` - Location name
- `COORDINATES` - GPS coordinates

### 7.1. Validation System

Instead of having separate types for Email, URL, Image, Video, etc., you can use the validation system to apply specific validations to base types:

**Example:**
- Use `TEXT` type with `email` validation for email fields
- Use `TEXT` type with `url` validation for URL fields
- Use `FILE` type with `image` validation for image uploads
- Use `FILE` type with `video` validation for video uploads
- Use `TEXTAREA` type with `json` validation for JSON data

**Available Validations:**
- Text: `required`, `min_length`, `max_length`, `email`, `url`, `slug`, `password`, `regex`
- Number: `required`, `min`, `max`, `integer`, `decimal`
- File: `required`, `image`, `video`, `audio`, `document`, `max_file_size`, `allowed_mime_types`
- Format: `json`, `array`, `rich_text`, `markdown`
- Date: `required`, `date_format`, `after`, `before`

### 8. Database Optimization

The package uses an optimized database structure with separate columns for different data types:

- `value_text` - For text-based attributes (indexed)
- `value_number` - For integer values (indexed)
- `value_decimal` - For decimal values (indexed)
- `value_date` - For date values (indexed)
- `value_datetime` - For datetime values (indexed)
- `value_time` - For time values
- `value_boolean` - For boolean values (indexed)
- `value_json` - For complex data (JSON)

This structure allows for:
- **Fast searches** on indexed columns
- **Type-specific queries** (number ranges, date ranges, etc.)
- **Better performance** compared to storing everything as text

### 9. Real-World Examples

#### Example 1: E-commerce Product Search

```php
// Find products with specific criteria
$products = Product::findByEav(function ($query) {
    $query->whereTextIn('color', ['red', 'blue', 'green'])
          ->whereNumberBetween('price', 50, 200)
          ->whereBoolean('in_stock', true)
          ->whereDateBetween('created_at', '2024-01-01', '2024-12-31')
          ->orderByNumber('price', 'asc')
          ->limit(20);
});
```

#### Example 2: User Profile Filtering

```php
// Find users with specific attributes
$users = User::findByEav(function ($query) {
    $query->whereText('city', 'Tehran')
          ->whereNumber('age', '>=', 18)
          ->whereNumber('age', '<=', 65)
          ->whereBoolean('is_verified', true)
          ->orderByText('name', 'asc');
});
```

#### Example 3: Content Management

```php
// Find articles with specific tags and date range
$articles = Article::findByEav(function ($query) {
    $query->whereJsonContains('tags', 'value', 'laravel')
          ->whereDateBetween('published_at', '2024-01-01', '2024-12-31')
          ->whereBoolean('is_published', true)
          ->orderByDateTime('published_at', 'desc');
});
```

### 10. Using Repositories

```php
use Fiachehr\LaravelEav\Domain\Repositories\AttributeRepositoryInterface;

$attributeRepository = app(AttributeRepositoryInterface::class);

// Find attribute
$attribute = $attributeRepository->findById(1);
$attribute = $attributeRepository->findBySlug('color');
$attribute = $attributeRepository->findByLogicalId('uuid-here');

// Get all active attributes
$attributes = $attributeRepository->findActive();
```

### 11. Using Use Cases

```php
use Fiachehr\LaravelEav\Application\UseCases\CreateAttributeUseCase;
use Fiachehr\LaravelEav\Application\DTOs\CreateAttributeDTO;
use Fiachehr\LaravelEav\Domain\Enums\AttributeType;

$useCase = app(CreateAttributeUseCase::class);

$dto = CreateAttributeDTO::fromArray([
    'title' => 'Color',
    'slug' => 'color',
    'type' => AttributeType::COLOR->value,
    'values' => ['red', 'blue', 'green'],
    'validations' => ['required'],
    'is_active' => true,
    'language' => 'en',
]);

$attribute = $useCase->execute($dto);
```

## Testing

The package includes a comprehensive test suite with 126 tests covering all features.

### Running Tests

```bash
cd packages/laravel-eav
composer install
./vendor/bin/phpunit
```

### Test Coverage

- ✅ **Unit Tests**: Repositories, Services, Use Cases
- ✅ **Feature Tests**: Traits, Query Builder, Multilingual Search
- ✅ **126 tests** with **322 assertions**
- ✅ All CRUD operations
- ✅ Validation system
- ✅ Multilingual support
- ✅ Search and filtering
- ✅ All attribute types

For detailed test documentation, see [tests/README.md](tests/README.md).

## Configuration

Publish the config file:

```bash
php artisan vendor:publish --tag=laravel-eav-config
```

This will create a `config/laravel-eav.php` file where you can customize table names and cache settings.

## Migrations

Run the migrations to create the necessary tables:

```bash
php artisan migrate
```

The package includes the following migrations:
- `create_attributes_table` - Stores attribute definitions
- `create_attribute_groups_table` - Stores attribute groups
- `create_attribute_group_attributes_table` - Pivot table for groups and attributes
- `create_attributable_attributes_table` - Stores attribute values (optimized structure)
- `create_attributable_attribute_groups_table` - Pivot table for entities and groups
- `update_attributable_attributes_table` - Updates existing tables with new columns (if needed)
- `create_translations_table` - Stores translations for attributes and attribute groups (creates `eav_translations` table)

## Performance Tips

1. **Use Indexes**: The package automatically creates indexes on value columns for fast searches.

2. **Eager Loading**: Always eager load attributes when querying multiple entities:
   ```php
   $products = Product::with('eavAttributes')->get();
   ```

3. **Select Specific Attributes**: Only load the attributes you need:
   ```php
   $products = Product::with(['eavAttributes' => function ($q) {
       $q->whereIn('slug', ['color', 'price']);
   }])->get();
   ```

4. **Use Query Builder for Complex Searches**: For complex queries with multiple conditions, use the Query Builder instead of multiple scopes:
   ```php
   // Good - Single query
   $products = Product::findByEav(function ($q) {
       $q->whereText('color', 'red')
         ->whereNumber('price', '>', 100);
   });
   
   // Less efficient - Multiple queries
   $products = Product::whereEavAttribute('color', 'red')
       ->whereEavAttribute('price', '>', 100)
       ->get();
   ```

## Troubleshooting

### Attribute values not saving correctly

Make sure you're using the correct data type for the attribute. The package automatically stores values in the appropriate column based on the attribute type.

### Slow queries

- Check if indexes are created properly
- Use eager loading to avoid N+1 queries
- Consider caching frequently accessed attributes
- Use Query Builder for complex searches instead of multiple scopes

### Migration errors

If you're updating from an older version, make sure to run the update migration:
```bash
php artisan migrate
```

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## Changelog

### Version 1.0.0
- Initial release
- 20+ attribute types support
- Optimized database structure with separate columns for different data types
- Advanced search and filtering capabilities
- Query Builder for complex EAV queries
- Multilingual support with translation system
- Comprehensive validation system
- Clean architecture
- Full test coverage (126 tests, 322 assertions)

## Support

For issues and questions, please open an issue on GitHub.

## License

MIT

---

**Made with ❤️ by Fiachehr Pourmojib**
