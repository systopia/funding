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

namespace Civi\Funding\DocumentRender;

interface DocumentRendererInterface {

  /**
   * @return string Mime type of rendered files.
   */
  public function getMimeType(): string;

  /**
   * @phpstan-param array<string, mixed> $data
   *
   * @return string Path to rendered file.
   *
   * @throws \CRM_Core_Exception
   */
  public function render(string $templateFile, string $entityName, int $entityId, array $data = []): string;

}
