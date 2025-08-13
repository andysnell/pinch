# Enum Helper Functions

This module provides utility functions for working with PHP enums, particularly for extracting values from backed enums and working with attributes on enum cases.

## Functions

### `enum_values(...$enum): array`

Extracts the values from a variadic list of backed enums.

**Parameters:**

- `...$enum` (`\BackedEnum`): One or more backed enum cases

**Returns:**

- `array<int|string>`: An array containing the values of the provided enum cases

**Example:**

```php
use function PhoneBurner\Pinch\Enum\enum_values;

enum Status: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Pending = 'pending';
}

$values = enum_values(Status::Active, Status::Pending);
// Result: ['active', 'pending']

// Works with integer-backed enums too
enum Priority: int
{
    case Low = 1;
    case Medium = 2;
    case High = 3;
}

$priorities = enum_values(Priority::Low, Priority::High);
// Result: [1, 3]
```

### `case_attr_find($case, $name = null, $use_instanceof = false): array`

Finds all attributes on an enum case, with optional filtering by attribute type.

**Parameters:**

- `$case` (`\UnitEnum`): The enum case to inspect
- `$name` (`class-string<T>|null`): Optional class name to filter attributes (default: `null`)
- `$use_instanceof` (`bool`): Whether to use instanceof matching for inheritance (default: `false`)

**Returns:**

- `array`: Array of attribute instances. If `$name` is provided, returns typed array of that attribute type

**Example:**

```php
use function PhoneBurner\Pinch\Enum\case_attr_find;

#[\Attribute]
class Description
{
    public function __construct(public readonly string $text) {}
}

#[\Attribute]
class Category
{
    public function __construct(public readonly string $name) {}
}

enum Status: string
{
    #[Description('User is currently active')]
    #[Category('user-status')]
    case Active = 'active';

    #[Description('User account is suspended')]
    case Inactive = 'inactive';
}

// Get all attributes on the case
$all_attributes = case_attr_find(Status::Active);
// Result: [Description instance, Category instance]

// Get only Description attributes
$descriptions = case_attr_find(Status::Active, Description::class);
// Result: [Description instance]

// Get no attributes (none of this type)
$empty = case_attr_find(Status::Inactive, Category::class);
// Result: []
```

### `case_attr_first($case, $name = null, $use_instanceof = false): object|null`

Finds the first attribute on an enum case, with optional filtering by attribute type.

**Parameters:**

- `$case` (`\UnitEnum`): The enum case to inspect
- `$name` (`class-string<T>|null`): Optional class name to filter attributes (default: `null`)
- `$use_instanceof` (`bool`): Whether to use instanceof matching for inheritance (default: `false`)

**Returns:**

- `object|T|null`: The first matching attribute instance, or `null` if none found

**Example:**

```php
use function PhoneBurner\Pinch\Enum\case_attr_first;

// Using the same enums from the previous example

// Get first attribute of any type
$first_attr = case_attr_first(Status::Active);
// Result: Description instance (first one defined)

// Get first Description attribute
$description = case_attr_first(Status::Active, Description::class);
// Result: Description instance with text "User is currently active"

// Get first Category attribute
$category = case_attr_first(Status::Active, Category::class);
// Result: Category instance with name "user-status"

// No matching attribute
$not_found = case_attr_first(Status::Inactive, Category::class);
// Result: null
```

### `case_attr_fetch($case, $name, $use_instanceof = false): object`

Fetches the first attribute of a specific type from an enum case, throwing an exception if not found.

**Parameters:**

- `$case` (`\UnitEnum`): The enum case to inspect
- `$name` (`class-string<T>`): The class name of the attribute to find (required)
- `$use_instanceof` (`bool`): Whether to use instanceof matching for inheritance (default: `false`)

**Returns:**

- `T&object`: The first matching attribute instance

**Throws:**

- `\LogicException`: If no attribute of the specified type is found

**Example:**

```php
use function PhoneBurner\Pinch\Enum\case_attr_fetch;

// Using the same enums from previous examples

// Successfully fetch an existing attribute
$description = case_attr_fetch(Status::Active, Description::class);
// Result: Description instance with text "User is currently active"

// Attempt to fetch a non-existent attribute
try {
    $category = case_attr_fetch(Status::Inactive, Category::class);
} catch (\LogicException $e) {
    echo $e->getMessage();
    // Output: "Attribute Category Not Found for Enum Case Status::Inactive"
}
```

## Common Use Cases

### Working with Enum Values

The `enum_values()` function is particularly useful when you need to extract multiple enum values for database queries, API responses, or validation:

```php
// Build a WHERE IN clause for database query
$statuses = enum_values(Status::Active, Status::Pending);
$sql = "SELECT * FROM users WHERE status IN (" . implode(',', array_fill(0, count($statuses), '?')) . ")";

// Create a validation rule
$allowed_values = enum_values(...Status::cases());
```

### Attribute-Driven Enum Behavior

The attribute functions enable powerful metadata-driven programming patterns:

```php
#[\Attribute]
class DisplayLabel
{
    public function __construct(public readonly string $label) {}
}

#[\Attribute]
class Color
{
    public function __construct(public readonly string $hex) {}
}

enum Priority: int
{
    #[DisplayLabel('Low Priority')]
    #[Color('#00ff00')]
    case Low = 1;

    #[DisplayLabel('High Priority')]
    #[Color('#ff0000')]
    case High = 3;
}

// Create a display helper
function getDisplayLabel(Priority $priority): string
{
    $label = case_attr_first($priority, DisplayLabel::class);
    return $label?->label ?? $priority->name;
}

function getColorHex(Priority $priority): string
{
    return case_attr_fetch($priority, Color::class)->hex;
}

echo getDisplayLabel(Priority::Low);  // "Low Priority"
echo getColorHex(Priority::High);     // "#ff0000"
```

### Inheritance with `$use_instanceof`

When working with attribute inheritance, use the `$use_instanceof` parameter:

```php
#[\Attribute]
abstract class BaseMetadata {}

#[\Attribute]
class SpecificMetadata extends BaseMetadata
{
    public function __construct(public readonly string $data) {}
}

enum Example
{
    #[SpecificMetadata('test')]
    case Item;
}

// Find by exact class
$exact = case_attr_find(Example::Item, SpecificMetadata::class);
// Result: [SpecificMetadata instance]

// Find by inheritance
$inherited = case_attr_find(Example::Item, BaseMetadata::class, use_instanceof: true);
// Result: [SpecificMetadata instance] (matches because SpecificMetadata extends BaseMetadata)
```

## Type Safety

All functions are fully typed with PHPStan generics and conditional return types:

- Return types change based on whether a specific attribute class is requested
- Template types ensure you get back the correct attribute type
- Null unions indicate when functions might return `null`

This provides excellent IDE support and static analysis capabilities while maintaining runtime type safety.
