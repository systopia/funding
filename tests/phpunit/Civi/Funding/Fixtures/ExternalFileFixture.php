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

namespace Civi\Funding\Fixtures;

use Civi\Api4\ExternalFile;
use Civi\Funding\Entity\ExternalFileEntity;

final class ExternalFileFixture {

  /**
   * @phpstan-param array<string, mixed> $values
   */
  public static function addFixture(array $values = []): ExternalFileEntity {
    $action = ExternalFile::create(FALSE)
      ->setValues($values + [
        'source' => 'https://example.org/test.txt',
        'extension' => 'funding',
      ]);

    // @phpstan-ignore-next-line
    return ExternalFileEntity::fromArray($action->execute()->single());
  }

}
