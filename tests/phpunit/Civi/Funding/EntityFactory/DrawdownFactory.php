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

namespace Civi\Funding\EntityFactory;

use Civi\Funding\Entity\DrawdownEntity;

/**
 * @phpstan-type drawdownT array{
 *   id?: int,
 *   payout_process_id?: int,
 *   status?: string,
 *   creation_date?: string,
 *   amount?: float,
 *   acception_date?: ?string,
 *   requester_contact_id?: int,
 *   reviewer_contact_id?: ?int,
 * }
 */
final class DrawdownFactory {

  public const DEFAULT_ID = 7;

  /**
   * @phpstan-param drawdownT $values
   */
  public static function create(array $values = []): DrawdownEntity {
    return DrawdownEntity::fromArray($values + [
      'id' => self::DEFAULT_ID,
      'payout_process_id' => PayoutProcessFactory::DEFAULT_ID,
      'status' => 'new',
      'creation_date' => '2023-04-04 04:04:04',
      'amount' => 1.23,
      'acception_date' => NULL,
      'requester_contact_id' => 1,
      'reviewer_contact_id' => NULL,
    ]);
  }

}
