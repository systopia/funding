<?php
/*
 * Copyright (C) 2026 SYSTOPIA GmbH
 *
 * This program is free software: you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License as published by the Free
 * Software Foundation, either version 3 of the License, or (at your option) any
 * later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types = 1);

namespace Civi\Funding\Api4\Action\FundingCase;

use Civi\Api4\Generic\AbstractAction;
use Civi\Funding\Api4\Action\Traits\IdsParameterTrait;
use Civi\RemoteTools\Api4\Action\Traits\ActionHandlerRunTrait;

/**
 * @method int getAmountPercent()
 * @method $this setAmountPercent(int $amountPercent)
 */
final class CreateDrawdownsAction extends AbstractAction {

  use ActionHandlerRunTrait;

  use IdsParameterTrait;

  /**
   * @var int
   * @required
   */
  protected ?int $amountPercent = NULL;

  public function __construct() {
    parent::__construct('FundingCase', 'createDrawdowns');
  }

}
