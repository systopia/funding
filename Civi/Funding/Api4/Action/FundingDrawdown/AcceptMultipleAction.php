<?php
/*
 * Copyright (C) 2025 SYSTOPIA GmbH
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
use Civi\Api4\Generic\AbstractAction;
use Civi\Api4\Generic\Result;
use Civi\Funding\Api4\Action\Traits\IdsParameterTrait;

class AcceptMultipleAction extends AbstractAction {

  use IdsParameterTrait;

  public function __construct() {
    parent::__construct(FundingDrawdown::getEntityName(), 'acceptMultiple');
  }

  /**
   * @inheritDoc
   * @throws \CRM_Core_Exception
   */
  public function _run(Result $result): void {
    foreach ($this->getIds() as $id) {
      $result[$id] = FundingDrawdown::accept()
        ->setId($id)
        ->execute()
        ->single();
    }
  }

}
