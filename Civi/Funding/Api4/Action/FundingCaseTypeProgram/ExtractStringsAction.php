<?php
/*
 * Copyright (C) 2025 SYSTOPIA GmbH
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

namespace Civi\Funding\Api4\Action\FundingCaseTypeProgram;

use Civi\Api4\Generic\AbstractAction;
use Civi\RemoteTools\Api4\Action\Traits\ActionHandlerRunTrait;

/**
 * @phpstan-import-type whereT from \Civi\Funding\Api4\Util\WhereUtil
 *
 * @phpstan-method whereT getWhere()
 * @phpstan-method $this setWhere(whereT $where)
 */
final class ExtractStringsAction extends AbstractAction {

  use ActionHandlerRunTrait;

  /**
   * @var array
   * @phpstan-var whereT
   */
  protected array $where = [];

}
