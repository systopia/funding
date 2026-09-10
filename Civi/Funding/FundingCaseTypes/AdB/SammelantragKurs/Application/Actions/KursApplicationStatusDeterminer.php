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

namespace Civi\Funding\FundingCaseTypes\AdB\SammelantragKurs\Application\Actions;

use Civi\Funding\ApplicationProcess\StatusDeterminer\ApplicationProcessStatusDeterminer;
use Civi\Funding\Entity\FullApplicationProcessStatus;
use Civi\Funding\FundingCaseTypes\AdB\SammelantragKurs\Traits\KursSupportedFundingCaseTypesTrait;

final class KursApplicationStatusDeterminer extends ApplicationProcessStatusDeterminer {

  use KursSupportedFundingCaseTypesTrait;

  private const STATUS_ACTION_STATUS_MAP = [
    NULL => [
      'save' => 'eligible',
    ],
    'eligible' => [
      'withdraw' => 'withdrawn',
      'modify' => 'rework',
      'update' => 'eligible',
      'add-comment' => 'eligible',
    ],
    'complete' => [
      'withdraw' => 'withdrawn',
      'update' => 'complete',
      'add-comment' => 'complete',
    ],
    'rework' => [
      'save' => 'eligible',
      'withdraw-change' => 'eligible',
      'add-comment' => 'rework',
    ],
    'withdrawn' => [
      'reopen' => '@previous',
      'add-comment' => 'withdrawn',
    ],
  ];

  public function __construct() {
    parent::__construct(
      self::STATUS_ACTION_STATUS_MAP,
      [
        'approve-calculative' => TRUE,
        'reject-calculative' => FALSE,
      ],
      [
        'approve-content' => TRUE,
        'reject-content' => FALSE,
      ]
    );
  }

}
