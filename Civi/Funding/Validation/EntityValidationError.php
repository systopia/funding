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

namespace Civi\Funding\Validation;

/**
 * @codeCoverageIgnore
 */
final class EntityValidationError {

  public ?string $field;

  public string $message;

  /**
   * @param string|null $field Null, if the error is not related to a specific
   *   field.
   */
  public static function new(?string $field, string $message): self {
    return new self($field, $message);
  }

  /**
   * @param string|null $field Null, if the error is not related to a specific
   *   field.
   */
  public function __construct(?string $field, string $message) {
    $this->field = $field;
    $this->message = $message;
  }

}
