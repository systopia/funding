<?php
/*
 * Copyright (C) 2026 SYSTOPIA GmbH
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

namespace Civi\Funding\DocumentRender\CiviOffice;

use Civi\RemoteTools\Api4\Api4Interface;

class CiviOfficeRendererOptions {

  private Api4Interface $api4;

  public function __construct(Api4Interface $api4) {
    $this->api4 = $api4;
  }

  /**
   * Get options for the CiviOffice renderer URI setting.
   *
   * @return array<string, string>
   * @throws \CRM_Core_Exception
   */
  public function fetchOptions(): array {
    $civiofficeRenderers = $this->api4->execute('CiviofficeRenderer', 'get', [
      'where' => [
        ['is_active', '=', TRUE],
      ],
      'checkPermissions' => TRUE,
    ]);

    $options = [];
    /** @var array<string, string> $renderer */
    foreach ($civiofficeRenderers as $renderer) {
      $options[$renderer['uri']] = $renderer['name'];
    }
    return $options;
  }

  /**
   * Get options for the CiviOffice renderer URI setting.
   *
   * @return array<string, string>
   */
  public static function getOptions(): array {
    // @phpstan-ignore method.nonObject
    return \Civi::service(self::class)->fetchOptions();
  }

}
