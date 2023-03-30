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

namespace Civi\Funding\Mock\DocumentRender;

use Civi\Funding\DocumentRender\DocumentRendererInterface;
use Civi\Funding\Util\TestFileUtil;

final class MockDocumentRenderer implements DocumentRendererInterface {

  /**
   * @inheritDoc
   */
  public function getMimeType(): string {
    return 'text/plain';
  }

  /**
   * @inheritDoc
   */
  public function render(string $templateFile, string $entityName, int $entityId, array $data = []): string {
    $tmpFile = TestFileUtil::createTempFile();
    file_put_contents($tmpFile, sprintf('Created by "%s"', __CLASS__));

    return $tmpFile;
  }

}
