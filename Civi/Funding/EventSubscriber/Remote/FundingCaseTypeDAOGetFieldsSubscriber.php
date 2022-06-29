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

namespace Civi\Funding\EventSubscriber\Remote;

use Civi\Funding\Event\Remote\FundingGetFieldsEvent;
use Civi\Funding\EventSubscriber\Remote\Traits\FundingCaseTypeSubscriberTrait;
use Civi\RemoteTools\EventSubscriber\AbstractRemoteDAOGetFieldsSubscriber;

final class FundingCaseTypeDAOGetFieldsSubscriber extends AbstractRemoteDAOGetFieldsSubscriber {

  use FundingCaseTypeSubscriberTrait;

  protected const DAO_ENTITY_NAME = 'FundingCaseType';

  protected const ENTITY_NAME = 'RemoteFundingCaseType';

  protected const EVENT_CLASS = FundingGetFieldsEvent::class;

}
