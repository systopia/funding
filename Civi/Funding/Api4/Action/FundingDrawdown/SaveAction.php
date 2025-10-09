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

namespace Civi\Funding\Api4\Action\FundingDrawdown;

use Civi\Api4\FundingDrawdown;
use Civi\Api4\Generic\DAOSaveAction;
use Civi\Funding\Api4\Action\Traits\ActionRecordValidationTrait;
use Civi\Funding\Validation\EntityValidatorInterface;
use Civi\RemoteTools\Api4\Api4Interface;

final class SaveAction extends DAOSaveAction {

  use ActionRecordValidationTrait;

  /**
   * @phpstan-param EntityValidatorInterface<\Civi\Funding\Entity\DrawdownEntity> $entityValidator
   */
  public function __construct(
    ?Api4Interface $api4 = NULL,
    ?EntityValidatorInterface $entityValidator = NULL
  ) {
    parent::__construct(FundingDrawdown::getEntityName(), 'save');
    $this->_api4 = $api4;
    $this->_entityValidator = $entityValidator;
  }

}
