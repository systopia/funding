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

namespace Civi\Funding\Api4\Action\Remote\Drawdown;

use Civi\Funding\Api4\Action\Remote\AbstractRemoteFundingAction;

/**
 * @method float getAmount()
 * @method $this setAmount(float $amount)
 * @method int getPayoutProcessId()
 * @method $this setPayoutProcessId(int $payoutProcessId)
 */
final class CreateAction extends AbstractRemoteFundingAction {

  /**
   * @var int
   * @required
   */
  protected ?int $payoutProcessId = NULL;

  /**
   * @var float
   * @required
   */
  protected ?float $amount = NULL;

}
