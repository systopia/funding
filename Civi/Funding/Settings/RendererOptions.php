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

namespace Civi\Funding\Settings;

use Civi;

class RendererOptions {

  /**
   * @return string[]
   */
  public static function getOptions(): array {
    $typeContainerClassname = '\Civi\Civioffice\DocumentRendererTypeContainer';
    if (class_exists($typeContainerClassname) && Civi::container()->has($typeContainerClassname)) {
      return $typeContainerClassname::getInstance()->getTitles();
    }
    $civiOfficeConfigurationClassname = '\CRM_Civioffice_Configuration';
    if (class_exists($civiOfficeConfigurationClassname) && Civi::container()->has($civiOfficeConfigurationClassname)) {
      return $civiOfficeConfigurationClassname::getDocumentRendererTypes();
    }
    return ['unoconv-local' => 'Local Unoconv'];
  }

}
