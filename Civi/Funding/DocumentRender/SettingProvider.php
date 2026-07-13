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

namespace Civi\Funding\DocumentRender;

use Civi\Funding\DocumentRender\CiviOffice\CiviOfficeRendererOptions;

class SettingProvider {

  private CiviOfficeRendererOptions $options;

  public function __construct(CiviOfficeRendererOptions $options) {
    $this->options = $options;
  }

  protected function getSettingsValue(): ?string {
    /** @var ?string */
    return \Civi::settings()->get('funding_civioffice_renderer_uri');
  }

  /**
   * Get the configured renderer URI.
   *
   * @return string
   * @throws \CRM_Core_Exception
   */
  public function getCiviOfficeRendererUri(): string {
    return $this->getSettingsValue() ?? key($this->options->fetchOptions()) ?? '';
  }

}
