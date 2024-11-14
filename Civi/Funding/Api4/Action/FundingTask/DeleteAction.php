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
use Civi\Api4\Generic\DAODeleteAction;

final class DeleteAction extends DAODeleteAction {

  public function __construct() {
    parent::__construct(Activity::getEntityName(), 'delete');
  }

  /**
   * @phpstan-param list<array{id: int}> $items
   *
   * @phpstan-return list<array{id: int}>
   *
   * @throws \CRM_Core_Exception
   */
  protected function deleteObjects($items): array {
    foreach ($items as $item) {
      if (0 === FundingTask::get(FALSE)
        ->addSelect('id')
        ->addWhere('id', '=', $item['id'])
        ->execute()
        ->count()
      ) {
        throw new UnauthorizedException(sprintf('Cannot delete task with ID %d', $item['id']));
      }
    }

    return parent::deleteObjects($items);
  }

}
