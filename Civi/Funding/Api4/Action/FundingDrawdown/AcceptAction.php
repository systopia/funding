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
use Civi\Funding\PayoutProcess\DrawdownManager;
use Civi\Funding\Session\FundingSessionInterface;
use Webmozart\Assert\Assert;

/**
 * @method $this setId(int $int)
 */
final class AcceptAction extends AbstractAction {

  /**
   * @var int
   * @required
   */
  protected ?int $id = NULL;

  private DrawdownManager $drawdownManager;

  private FundingSessionInterface $fundingSession;

  public function __construct(DrawdownManager $drawdownManager, FundingSessionInterface $fundingSession) {
    parent::__construct(FundingDrawdown::_getEntityName(), 'accept');
    $this->drawdownManager = $drawdownManager;
    $this->fundingSession = $fundingSession;
  }

  /**
   * @inheritDoc
   */
  public function _run(Result $result): void {
    Assert::notNull($this->id);
    $drawdown = $this->drawdownManager->get($this->id);
    Assert::notNull($drawdown, sprintf('Drawdown with ID "%d" not found', $this->id));
    $this->drawdownManager->accept($drawdown, $this->fundingSession->getContactId());

    $result->exchangeArray([$drawdown->toArray()]);
  }

}
