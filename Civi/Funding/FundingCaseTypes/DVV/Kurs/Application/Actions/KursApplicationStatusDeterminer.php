<?php
/*
 * Copyright (C) 2025 SYSTOPIA GmbH
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

namespace Civi\Funding\FundingCaseTypes\DVV\Kurs\Application\Actions;

use Civi\Funding\ApplicationProcess\StatusDeterminer\AbstractApplicationProcessStatusDeterminerDecorator;
use Civi\Funding\ApplicationProcess\StatusDeterminer\ApplicationProcessStatusDeterminer;
use Civi\Funding\ApplicationProcess\StatusDeterminer\ReworkPossibleApplicationProcessStatusDeterminer;
use Civi\Funding\FundingCaseTypes\DVV\Kurs\Traits\KursSupportedFundingCaseTypesTrait;

final class KursApplicationStatusDeterminer extends AbstractApplicationProcessStatusDeterminerDecorator {

  use KursSupportedFundingCaseTypesTrait;

  private const STATUS_ACTION_STATUS_MAP = [
    NULL => [
      'save' => 'new',
      'apply' => 'review',
    ],
    'new' => [
      'save' => 'new',
      'apply' => 'review',
      'reject' => 'rejected',
    ],
    'review' => [
      'approve-calculative' => 'review',
      'reject-calculative' => 'review',
      'approve-content' => 'review',
      'reject-content' => 'review',
      'request-change' => 'draft',
      'approve' => 'eligible',
      'reject' => 'rejected',
      'update' => 'review',
      'add-comment' => 'review',
    ],
    'draft' => [
      'save' => 'draft',
      'apply' => 'review',
      'withdraw' => 'withdrawn',
      'review' => 'review',
      'add-comment' => 'draft',
      'reject' => 'rejected',
    ],
    'eligible' => [
      'withdraw' => 'withdrawn',
      'update' => 'eligible',
      'add-comment' => 'eligible',
    ],
    'complete' => [
      'withdraw' => 'withdrawn',
      'update' => 'complete',
      'add-comment' => 'complete',
    ],
  ];

  public function __construct() {
    parent::__construct(new ReworkPossibleApplicationProcessStatusDeterminer(
      new ApplicationProcessStatusDeterminer(self::STATUS_ACTION_STATUS_MAP)
    ));
  }

}
