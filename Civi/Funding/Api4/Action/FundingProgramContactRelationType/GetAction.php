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

namespace Civi\Funding\Api4\Action\FundingProgramContactRelationType;

use Civi\Api4\FundingProgramContactRelationType;
use Civi\Api4\Generic\AbstractGetAction;
use Civi\Api4\Generic\Result;
use Civi\Api4\Generic\Traits\ArrayQueryActionTrait;
use Civi\Funding\Contact\Relation\RelationTypeContainerInterface;

final class GetAction extends AbstractGetAction {

  use ArrayQueryActionTrait;

  private RelationTypeContainerInterface $relationTypeContainer;

  public function __construct(RelationTypeContainerInterface $relationTypeContainer) {
    parent::__construct(FundingProgramContactRelationType::getEntityName(), 'get');
    $this->relationTypeContainer = $relationTypeContainer;
  }

  /**
   * @inheritDoc
   */
  public function _run(Result $result): void {
    $types = [];
    foreach ($this->relationTypeContainer->getRelationTypes() as $type) {
      $types[] = $type->toArray();
    }

    $types = $this->sortArray($types);
    $types = $this->limitArray($types);
    $types = $this->selectArray($types);
    $result->exchangeArray($types);
  }

}
