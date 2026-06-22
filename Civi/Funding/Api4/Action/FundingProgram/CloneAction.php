<?php
/*
 * Copyright (C) 2026 SYSTOPIA GmbH
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

namespace Civi\Funding\Api4\Action\FundingProgram;

use Civi\Api4\FundingProgram;
use Civi\Api4\Generic\AbstractBatchAction;
use Civi\Api4\Generic\Result;
use Civi\Funding\Api4\Action\Traits\Api4Trait;
use Civi\Funding\Entity\FundingProgramEntity;
use Civi\Funding\FundingProgram\Api4\ActionHandler\CloneHandler;
use Civi\RemoteTools\Api4\Action\Traits\ActionHandlerRunTrait;
use Civi\RemoteTools\Api4\Api4Interface;

/**
 * Clones a FundingProgram including related settings.
 *
 * @method $this setValues(array<string, mixed> $values)
 * @method array<string, mixed> getValues()
 */
class CloneAction extends AbstractBatchAction {

  use ActionHandlerRunTrait;

  use Api4Trait;

  /**
   * @var array
   * @phpstan-var array<string, mixed>
   */
  protected array $values = [];

  public function __construct(?Api4Interface $api4 = NULL) {
    parent::__construct(FundingProgram::getEntityName(), 'clone');
    $this->_api4 = $api4;
  }

  /**
   * @return string[]
   */
  protected function getSelect(): array {
    return ['*', 'custom.*'];
  }

  /**
   * @param \Civi\Api4\Generic\Result $result
   */
  public function _run(Result $result): void {
    $handler = new CloneHandler($this->getApi4());
    foreach ($this->getBatchRecords() as $record) {
      $sourceFundingProgramEntity = FundingProgramEntity::fromArray($record);
      $targetFundingProgramData = $handler->prepareTargetFundingProgramData(
        $sourceFundingProgramEntity,
        $this->getValues()
      );
      $result[] = $handler->executeClone(
        $sourceFundingProgramEntity->getId(),
        $targetFundingProgramData,
        $this->getCheckPermissions()
      )->toArray();
    }
  }

}
