<?php
declare(strict_types = 1);

namespace Civi\Funding\Form\JsonSchema;

use Civi\RemoteTools\Form\JsonSchema\JsonSchemaObject;
use Civi\RemoteTools\Form\JsonSchema\JsonSchemaString;
use Civi\RemoteTools\Form\JsonSchema\Util\JsonSchemaUtil;
use CRM_Funding_ExtensionUtil as E;

final class JsonSchemaComment extends JsonSchemaObject {

  public function __construct() {
    parent::__construct([
      'text' => new JsonSchemaString(),
      'type' => new JsonSchemaString([
        'oneOf' => JsonSchemaUtil::buildTitledOneOf([
          'internal' => E::ts('internal'),
          'external' => E::ts('external'),
        ]),
      ]),
    ], [
      'required' => ['text', 'type'],
    ]);
  }

}
