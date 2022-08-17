# Generate JSON Schema

The class `JsonSchema` and its subclasses can be used to generate a
[JSON Schema](https://json-schema.org/).

```php
$schema = new JsonSchemaObject([
'property1' => new JsonSchemaString(['minLength' => 10]),
'property2' => new JsonSchemaInteger(['minimum' => 20]),
], ['required' => ['property2']]);

// Accessing keywords:
$schema->getKeywordValue('type'); // 'object'
// JsonSchema object with keywords 'property1' and 'property2'
$schema->getKeywordValue('properties');

/*
 * [
 *   'type' => 'object',
 *   'properties' => JsonSchema(...),
 *   'required' => ['property2'],
 * ]
 */
$schema->getKeywords();

$schema->hasKeyword('properties'); // true
$schema->hasKeyword('description'); // false

$schema->addKeyword('description', 'example');

// Convert to another type:
$schemaAsStdClass = $schema->toStdClass();
$schemaAsArray = $schema->toArray();
$schemaJson = json_encode($schema);

// Create from an array: (Won't contain subclasses of JsonSchema unless the
// specified array already contains those objects.)
$schemaFromArray = JsonSchema::fromArray($schemaAsArray);
```

By subclassing reusable schemas can be created.
