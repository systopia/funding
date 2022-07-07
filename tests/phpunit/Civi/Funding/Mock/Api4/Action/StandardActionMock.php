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

namespace Civi\Funding\Mock\Api4\Action;

use Civi\Api4\Generic\AbstractAction;
use Civi\Api4\Generic\Result;

class StandardActionMock extends AbstractAction {

  protected Result $_result;

  public function __construct(string $entityName = 'TestEntity', string $actionName = 'get') {
    parent::__construct($entityName, $actionName);
    $this->_result = new Result();
  }

  /**
   * @inheritDoc
   */
  public function _run(Result $result): void {
    $result->rowCount = $this->_result->rowCount;
    $result->exchangeArray($this->_result->getArrayCopy());
  }

  public function setResult(Result $result): self {
    $this->_result = $result;

    return $this;
  }

}
