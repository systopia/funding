<?php
/*
 * Copyright (C) 2024 SYSTOPIA GmbH
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

namespace Civi\Funding\Api4\Action\FundingTask;

use Civi\API\Exception\UnauthorizedException;
use Civi\Api4\Activity;
use Civi\Api4\FundingTask;
use Civi\Api4\Generic\DAOUpdateAction;
use Civi\Funding\Api4\Util\WhereUtil;

final class UpdateAction extends DAOUpdateAction {

  public function __construct() {
    parent::__construct(Activity::getEntityName(), 'update');
  }

  protected function validateValues(): void {
    parent::validateValues();

    $id = $this->values['id'] ?? WhereUtil::getInt($this->getWhere(), 'id');
    if (NULL === $id) {
      throw new \InvalidArgumentException('id is required');
    }

    if (0 === FundingTask::get(FALSE)
      ->addSelect('id')
      ->addWhere('id', '=', $id)
      ->execute()
      ->count()
    ) {
      throw new UnauthorizedException('Cannot update task with ID %d', $id);
    }
  }

}
