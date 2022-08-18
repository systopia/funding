# Generate JSON Forms UI Schema

The class `JsonFormsElement` and its subclasses can be used to generate a
[JSON Forms UI Schema](https://jsonforms.io/).

```php
$uiSchema = new JsonFormsGroup('Group label', [
  new JsonFormsControl('#/properties/property1', 'Property1 label'),
  new JsonFormsControl('#/properties/property2', 'Property2 label'),
], 'Optional group description');

// Concert to another type
$uiSchemaAsStdClass = $uiSchema->toStdClass();
$uiSchemaAsArray = $uiSchema->toArray();
$uiSchemaJson = json_encode($uiSchema);
```

By subclassing reusable UI schemas can be created.
