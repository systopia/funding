<?php
/*
 * Copyright (C) 2023 SYSTOPIA GmbH
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

namespace Civi\Funding\DocumentRender\Token;

use Civi\RemoteTools\Api4\Api4Interface;
use CRM_Funding_ExtensionUtil as E;

/**
 * @codeCoverageIgnore
 */
final class TokenNameExtractor implements TokenNameExtractorInterface {

  private Api4Interface $api4;

  public function __construct(Api4Interface $api4) {
    $this->api4 = $api4;
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function getTokenNames(string $entityName, string $entityClass): array {
    $tokenNames = [];
    $fields = $this->api4->execute(
      $entityName,
      'getFields',
      [
        'select' => ['name', 'label', 'serialize', 'suffixes'],
      ],
    );
    /** @phpstan-var array<string, array<string, scalar>|scalar[]|scalar|null> $field */
    foreach ($fields as $field) {
      /** @var string $name */
      $name = $field['name'];
      /** @var string $label */
      $label = $field['label'] ?? $name;
      $tokenNames[$name] = $label;

      if (0 !== ($field['serialize'] ?? 0)) {
        // Indicate that array value access is possible.
        $tokenNames[$name . '::'] = sprintf('%s (%s)', $label, E::ts('Array value access'));
      }
    }

    return $tokenNames;
  }

}
