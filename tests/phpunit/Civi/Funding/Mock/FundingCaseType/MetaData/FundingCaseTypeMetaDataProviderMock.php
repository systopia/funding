<?php
/*
 * Copyright (C) 2025 SYSTOPIA GmbH
 *
 *  This program is free software: you can redistribute it and/or modify it under
 *  the terms of the GNU Affero General Public License as published by the Free
 *  Software Foundation, either version 3 of the License, or (at your option) any
 *  later version.
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

namespace Civi\Funding\Mock\FundingCaseType\MetaData;

use Civi\Funding\FundingCaseType\FundingCaseTypeMetaDataProviderInterface;
use Civi\Funding\FundingCaseType\MetaData\FundingCaseTypeMetaDataInterface;

final class FundingCaseTypeMetaDataProviderMock implements FundingCaseTypeMetaDataProviderInterface {

  /**
   * @phpstan-var array<string, FundingCaseTypeMetaDataInterface>
   */
  public array $metaDataList = [];

  public function __construct(FundingCaseTypeMetaDataInterface ...$metaData) {
    foreach ($metaData as $data) {
      $this->metaDataList[$data->getName()] = $data;
    }
  }

  public function get(string $name): FundingCaseTypeMetaDataInterface {
    return $this->metaDataList[$name];
  }

  /**
   * @inheritDoc
   */
  public function getAll(): iterable {
    return $this->metaDataList;
  }

  /**
   * @inheritDoc
   */
  public function getNames(): array {
    return array_keys($this->metaDataList);
  }

  public function has(string $name): bool {
    return isset($this->metaDataList[$name]);
  }

}
