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

/**
 * @codeCoverageIgnore
 */
final class ResolvedToken {

  /**
   * @phpstan-var string|\DateTime|\Brick\Money\Money
   * Either a string or a value that is handled by TokenProcessor.
   */
  public $value;

  /**
   * @phpstan-var 'text/plain'|'text/html'
   */
  public string $format;

  /**
   * @phpstan-param string|\DateTime|\Brick\Money\Money $value
   *   Either a string or a value that is handled by TokenProcessor.
   * @phpstan-param 'text/plain'|'text/html' $format
   */
  public function __construct($value, string $format) {
    $this->value = $value;
    $this->format = $format;
  }

}
