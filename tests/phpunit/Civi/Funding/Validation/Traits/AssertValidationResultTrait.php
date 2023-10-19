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

namespace Civi\Funding\Validation\Traits;

use Opis\JsonSchema\Errors\ErrorFormatter;
use Opis\JsonSchema\ValidationResult;

trait AssertValidationResultTrait {

  public static function assertValidationValid(ValidationResult $result): void {
    if (NULL !== $result->error()) {
      $errorFormatter = new ErrorFormatter();
      // If there's an error, we use this assertion so the error is displayed.
      static::assertSame([], $errorFormatter->formatKeyed($result->error()));
    }

    static::assertTrue($result->isValid());
  }

}
