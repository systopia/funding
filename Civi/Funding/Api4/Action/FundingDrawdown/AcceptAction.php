<?php
/*
 * Copyright (C) 2023 SYSTOPIA GmbH
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
use Civi\Funding\Api4\Action\Traits\DrawdownManagerTrait;
use Civi\Funding\Api4\Action\Traits\RequestContextTrait;
use Civi\Funding\PayoutProcess\DrawdownManager;
use Civi\RemoteTools\Api4\Action\Traits\IdParameterTrait;
use Civi\RemoteTools\RequestContext\RequestContextInterface;
use Webmozart\Assert\Assert;

class AcceptAction extends AbstractAction {

  use IdParameterTrait;

  use DrawdownManagerTrait;

  use RequestContextTrait;

  public function __construct(
    ?DrawdownManager $drawdownManager = NULL,
    ?RequestContextInterface $requestContext = NULL
  ) {
    parent::__construct(FundingDrawdown::getEntityName(), 'accept');
    $this->_drawdownManager = $drawdownManager;
    $this->_requestContext = $requestContext;
  }

  /**
   * @inheritDoc
   */
  public function _run(Result $result): void {
    $drawdownBundle = $this->getDrawdownManager()->getBundle($this->getId());
    Assert::notNull($drawdownBundle, sprintf('Drawdown with ID "%d" not found', $this->getId()));
    $this->getDrawdownManager()->accept($drawdownBundle, $this->getRequestContext()->getContactId());

    $result->exchangeArray([$drawdownBundle->getDrawdown()->toArray()]);
  }

}
