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

namespace Civi\Funding\Api4\Action\FundingClearingProcess;

use Civi\Api4\FundingClearingProcess;
use Civi\Api4\Generic\AbstractAction;
use Civi\RemoteTools\ActionHandler\ActionHandlerInterface;
use Civi\RemoteTools\Api4\Action\Traits\ActionHandlerRunTrait;

/**
 * @method int getClearingProcessId()
 * @method $this setClearingProcessId(int $clearingProcessId)
 * @method int getReviewerContactId()
 * @method $this setReviewerContactId(int $reviewerContactId)
 */
class SetContentReviewerAction extends AbstractAction {

  use ActionHandlerRunTrait;

  /**
   * @var int
   * @required
   */
  protected ?int $clearingProcessId = NULL;

  /**
   * @var int
   * @required
   */
  protected ?int $reviewerContactId = NULL;

  public function __construct(?ActionHandlerInterface $actionHandler = NULL) {
    parent::__construct(FundingClearingProcess::getEntityName(), 'setContentReviewer');
    $this->setActionHandler($actionHandler);
  }

}
