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

use Civi\Api4\Activity;
use Civi\Api4\Generic\DAOCreateAction;
use Civi\Api4\Generic\Result;
use Civi\Funding\ActivityTypeNames;
use Webmozart\Assert\Assert;

final class CreateAction extends DAOCreateAction {

  public function __construct() {
    parent::__construct(Activity::getEntityName(), 'create');
  }

  public function _run(Result $result): void {
    Assert::keyExists($this->values, 'activity_type_id:name', 'activity_type_id:name is required');
    Assert::inArray(
      $this->values['activity_type_id:name'],
      ActivityTypeNames::getTasks(),
      'Invalid activity type name'
    );
    Assert::keyExists($this->values, 'source_record_id', 'source_record_id is required');

    parent::_run($result);
  }

}
