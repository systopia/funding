<?php
/*
 * Copyright (C) 2022 SYSTOPIA GmbH
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

namespace Civi\Funding\ApplicationProcess\StatusDeterminer;

final class DefaultApplicationProcessStatusDeterminer extends ApplicationProcessStatusDeterminer {

  private const STATUS_ACTION_STATUS_MAP = [
    NULL => [
      'save' => 'new',
      'apply' => 'applied',
    ],
    'new' => [
      'save' => 'new',
      'apply' => 'applied',
      'update' => 'new',
    ],
    'applied' => [
      'modify' => 'draft',
      'withdraw' => 'withdrawn',
      'review' => 'review',
      'update' => 'applied',
    ],
    'review' => [
      'set-calculative-review-result' => 'review',
      'set-content-review-result' => 'review',
      'request-change' => 'draft',
      'approve' => 'approved',
      'reject' => 'rejected',
      'update' => 'review',
    ],
    'draft' => [
      'save' => 'draft',
      'apply' => 'applied',
      'withdraw' => 'withdrawn',
      'update' => 'draft',
    ],
  ];

  public function __construct() {
    parent::__construct(self::STATUS_ACTION_STATUS_MAP);
  }

}
