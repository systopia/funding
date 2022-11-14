<?php
/*
 * Copyright (C) 2022 SYSTOPIA GmbH
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

namespace Civi\Funding\Form;

use Opis\JsonSchema\Errors\ValidationError;
use Opis\JsonSchema\Info\DataInfo;
use Opis\JsonSchema\Info\SchemaInfo;
use Opis\JsonSchema\Schemas\EmptySchema;

final class ValidationErrorFactory {

  /**
   * @phpstan-param array<int|string> $path
   */
  public static function createValidationError(
    array $path = ['foo'],
    string $data = 'bar',
    string $message = 'Invalid value',
    string $keyword = 'test'
  ): ValidationError {
    return new ValidationError(
      $keyword,
      new EmptySchema(new SchemaInfo(FALSE, NULL)),
      new DataInfo($data, 'string', NULL, $path),
      $message
    );
  }

}
