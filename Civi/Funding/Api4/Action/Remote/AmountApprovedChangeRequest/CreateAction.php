<?php
/*
 * Copyright (C) 2026 SYSTOPIA GmbH
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

namespace Civi\Funding\Api4\Action\Remote\AmountApprovedChangeRequest;

use Civi\Funding\Api4\Action\Remote\AbstractRemoteFundingAction;

/**
 * @method int getFundingCaseId()
 * @method $this setFundingCaseId(int $fundingCaseId)
 * @method float getAmountRequested()
 * @method $this setAmountRequested(float $amountRequested)
 * @method string getComment()
 * @method $this setComment(string $comment)
 */
final class CreateAction extends AbstractRemoteFundingAction {

  /**
   * @var int
   * @required
   */
  protected int $fundingCaseId;

  /**
   * @var float
   * @required
   */
  protected float $amountRequested;

  /**
   * @var string|null
   */
  protected ?string $comment = NULL;

}
