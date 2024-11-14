<?php
/*
 * Copyright (C) 2024 SYSTOPIA GmbH
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as published by
 *  the Free Software Foundation in version 3.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types = 1);

namespace Civi\Funding\Api4\ActionHandler;

use Civi\RemoteTools\ActionHandler\ActionHandlerInterface;
use Civi\RemoteTools\Api4\Action\AbstractRemoteGetFieldsAction;
use Civi\RemoteTools\Api4\Api4Interface;

abstract class AbstractRemoteFundingGetFieldsActionHandler implements ActionHandlerInterface {

  private Api4Interface $api4;

  /**
   * @phpstan-var array<string, array<string, array<array<string, scalar|null>>>>
   *   entity name => field name => [options]
   */
  private array $options = [];

  public function __construct(Api4Interface $api4) {
    $this->api4 = $api4;
  }

  /**
   * @phpstan-return list<array<string, mixed>>
   *
   * @throws \CRM_Core_Exception
   */
  public function getFields(AbstractRemoteGetFieldsAction $action): array {
    /** @phpstan-var array<string, array<string, mixed>> $remoteFields */
    $remoteFields = $this->api4->execute($this->getEntityName(), 'getFields', [
      'loadOptions' => $action->getLoadOptions(),
      'action' => $action->getAction(),
      'values' => $action->getValues(),
      'language' => $action->getLanguage(),
      'select' => $action->getSelect(),
      'where' => $action->getWhere(),
      'orderBy' => $action->getOrderBy(),
      'limit' => $action->getLimit(),
      'offset' => $action->getOffset(),
    ])->indexBy('name')->getArrayCopy();

    foreach ($remoteFields as $fieldName => $field) {
      if (($field['options'] ?? FALSE) !== FALSE && is_array($field['suffixes'] ?? NULL)) {
        foreach ($field['suffixes'] as $suffix) {
          if (
            is_array($field['options'])
            && [] !== $field['options']
            && (!is_array($field['options'][0]) || !array_key_exists($suffix, $field['options'][0]))
          ) {
            // "loadOptions" was set to TRUE or the suffix was not part of "loadOptions".
            // @phpstan-ignore argument.type
            $field['options'] = $this->getEntityFieldOptions($field['entity'], $fieldName);
          }

          $remoteFields[$fieldName . ':' . $suffix] ??= $this->createField($field, $fieldName, $suffix);
        }
      }
    }

    return array_values($remoteFields);
  }

  /**
   * @return string The non-remote entity name.
   */
  abstract protected function getEntityName(): string;

  /**
   * @phpstan-param array<string, mixed> $field
   *
   * @phpstan-return array<string, mixed>
   *
   * phpcs:disable Generic.Metrics.CyclomaticComplexity.TooHigh
   */
  private function createField(array $field, string $fieldName, string $suffix): array {
    // phpcs:enable
    /** @var string $title */
    $title = $field['title'] ?? $fieldName;
    /** @var string $label */
    $label = $field['label'] ?? $title;
    /** @var string $description */
    $description = $field['description'] ?? $label;

    return [
      'name' => $fieldName . ':' . $suffix,
      'type' => 'Option',
      'entity' => $field['entity'] ?? NULL,
      'data_type' => 'String',
      'nullable' => $field['nullable'] ?? TRUE,
      'readonly' => $field['readonly'] ?? TRUE,
      'permission' => $field['permission'] ?? NULL,
      'fk_entity' => $field['fk_entity'] ?? NULL,
      // @phpstan-ignore argument.type
      'options' => $this->getSuffixFieldOptions($field['options'], $suffix),
      'title' => sprintf('%s [%s]', $title, $suffix),
      'label' => sprintf('%s [%s]', $label, $suffix),
      'description' => sprintf('%s [%s:%s]', $description, $fieldName, $suffix),
      'help_pre' => $field['help_pre'] ?? NULL,
      'help_post' => $field['help_post'] ?? NULL,
      'custom_field_id' => $field['custom_field_id'] ?? NULL,
      'custom_group_id' => $field['custom_group_id'] ?? NULL,
    ];
  }

  /**
   * @throws \CRM_Core_Exception
   *
   * @phpstan-return array<array<string, scalar|null>>
   */
  private function getEntityFieldOptions(string $entityName, string $fieldName): array {
    $entityOptions = $this->options[$entityName] ??= $this->api4->execute($entityName, 'getFields', [
      'select' => ['name', 'options'],
      'where' => [['options', '!=', FALSE]],
      'loadOptions' => [
        'id',
        'name',
        'label',
        'abbr',
        'description',
        'color',
        'icon',
      ],
    ])->indexBy('name')
      ->column('options');

    return $entityOptions[$fieldName] ?? [];
  }

  /**
   * @param bool|array $options
   * @phpstan-param true|array<array<string, scalar|null>> $options
   *
   * @return bool|array
   * @phpstan-return true|array<int|string, scalar>
   */
  private function getSuffixFieldOptions($options, string $suffix) {
    if (!is_array($options)) {
      return TRUE;
    }

    return array_filter(
      array_column($options, $suffix, $suffix),
      fn ($value) => NULL !== $value,
    );

  }

}
