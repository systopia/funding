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

namespace Civi\Funding\PayoutProcess\Api4\ActionHandler\RemoteFundingDrawdown;

use Civi\Funding\Api4\Action\Remote\Drawdown\CreateAction;
use Civi\Funding\PayoutProcess\DrawdownManager;
use Civi\Funding\PayoutProcess\PayoutProcessManager;
use Civi\RemoteTools\ActionHandler\ActionHandlerInterface;
use Webmozart\Assert\Assert;

/**
 * @phpstan-import-type drawdownT from \Civi\Funding\Entity\DrawdownEntity
 */
final class RemoteCreateActionHandler implements ActionHandlerInterface {

  public const ENTITY_NAME = 'RemoteFundingDrawdown';

  private DrawdownManager $drawdownManager;

  private PayoutProcessManager $payoutProcessManager;

  public function __construct(DrawdownManager $drawdownManager, PayoutProcessManager $payoutProcessManager) {
    $this->drawdownManager = $drawdownManager;
    $this->payoutProcessManager = $payoutProcessManager;
  }

  /**
   * @phpstan-return array{drawdownT}
   *
   * @throws \CRM_Core_Exception
   */
  public function create(CreateAction $action): array {
    $payoutProcessBundle = $this->payoutProcessManager->getBundle($action->getPayoutProcessId());
    Assert::notNull(
      $payoutProcessBundle,
      sprintf('Payout process with ID %d not found', $action->getPayoutProcessId())
    );
    $drawdown = $this->drawdownManager->createNew(
      $payoutProcessBundle,
      $action->getAmount(),
      $action->getResolvedContactId()
    );

    return [$drawdown->toArray()];
  }

}
