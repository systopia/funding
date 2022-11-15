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

namespace Civi\Funding\FundingProgram;

use Civi\Api4\FundingCaseType;
use Civi\Funding\Entity\FundingCaseTypeEntity;
use Civi\RemoteTools\Api4\Api4Interface;

/**
 * @phpstan-type fundingCaseTypeT array{
 *   id: int,
 *   title: string,
 *   name: string,
 *   properties: array<string, mixed>,
 * }
 */
class FundingCaseTypeManager {

  private Api4Interface $api4;

  /**
   * @phpstan-var array<string, ?int>
   */
  private array $nameIdMap = [];

  public function __construct(Api4Interface $api4) {
    $this->api4 = $api4;
  }

  public function get(int $id): ?FundingCaseTypeEntity {
    $action = FundingCaseType::get()->addWhere('id', '=', $id);

    /** @var fundingCaseTypeT|null $values */
    $values = $this->api4->executeAction($action)->first();

    return NULL === $values ? NULL : FundingCaseTypeEntity::fromArray($values);
  }

  public function getIdByName(string $name): ?int {
    if (!array_key_exists($name, $this->nameIdMap)) {
      $action = FundingCaseType::get()
        ->addSelect('id')
        ->addWhere('name', '=', $name);

      $result = $this->api4->executeAction($action);
      $this->nameIdMap[$name] = $result->first()['id'] ?? NULL;
    }

    return $this->nameIdMap[$name];
  }

}
