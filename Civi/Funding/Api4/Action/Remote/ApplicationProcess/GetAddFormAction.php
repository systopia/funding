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

namespace Civi\Funding\Api4\Action\Remote\ApplicationProcess;

use Civi\Api4\RemoteFundingApplicationProcess;
use Civi\Funding\Api4\Action\Remote\AbstractRemoteFundingAction;
use Civi\Funding\Api4\Action\Traits\FundingCaseIdParameterTrait;
use Civi\RemoteTools\ActionHandler\ActionHandlerInterface;

/**
 * @method int getCopyDataFromId()
 * @method $this setCopyDataFromId(int $copyDataFromId)
 */
final class GetAddFormAction extends AbstractRemoteFundingAction {

  use FundingCaseIdParameterTrait;

  /**
   * @var int ID of the application process to copy the initial data from.
   */
  protected ?int $copyDataFromId = NULL;

  public function __construct(ActionHandlerInterface $actionHandler = NULL) {
    parent::__construct(RemoteFundingApplicationProcess::getEntityName(), 'getAddForm', $actionHandler);
  }

}
