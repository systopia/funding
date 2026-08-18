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

use Civi\Funding\ApplicationProcess\StatusDeterminer\AbstractApplicationProcessStatusDeterminer;
use Civi\Funding\Entity\FullApplicationProcessStatus;
use Civi\Funding\FundingCaseTypes\AdB\SammelantragKurs\Traits\KursSupportedFundingCaseTypesTrait;

final class KursApplicationStatusDeterminer extends AbstractApplicationProcessStatusDeterminer {

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
    parent::__construct(self::STATUS_ACTION_STATUS_MAP);
  }

  protected function getIsReviewCalculative(FullApplicationProcessStatus $currentStatus, string $action): ?bool {
    if ('approve-calculative' === $action) {
      return TRUE;
    }

    if ('reject-calculative' === $action) {
      return FALSE;
    }

    return $currentStatus->getIsReviewCalculative();
  }

  protected function getIsReviewContent(FullApplicationProcessStatus $currentStatus, string $action): ?bool {
    if ('approve-content' === $action) {
      return TRUE;
    }

    if ('reject-content' === $action) {
      return FALSE;
    }

    return $currentStatus->getIsReviewContent();
  }

  public function getStatusOnClearingProcessStarted(FullApplicationProcessStatus $currentStatus
  ): FullApplicationProcessStatus {
    return new FullApplicationProcessStatus(
      'complete',
      $currentStatus->getIsReviewCalculative(),
      $currentStatus->getIsReviewContent()
    );
  }

}
