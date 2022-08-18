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

namespace Civi\RemoteTools\Util;

use Webmozart\Assert\Assert;

final class JsonConverter {

  /**
   * @return array<string, mixed>
   *
   * @throws \JsonException
   */
  public static function toArray(\stdClass $data): array {
    $result = \json_decode(\json_encode($data, JSON_THROW_ON_ERROR), TRUE);
    Assert::isArray($result);

    return $result;
  }

  /**
   * @param array<string, mixed> $data
   *
   * @throws \JsonException
   */
  public static function toStdClass(array $data): \stdClass {
    $result = \json_decode(\json_encode($data, JSON_THROW_ON_ERROR));
    Assert::isInstanceOf($result, \stdClass::class);

    return $result;
  }

}
