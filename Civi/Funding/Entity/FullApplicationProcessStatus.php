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

namespace Civi\Funding\Entity;

final class FullApplicationProcessStatus {

  private string $status;

  private ?bool $isReviewCalculative;

  private ?bool $isReviewContent;

  public function __construct(string $status, ?bool $isReviewCalculative, ?bool $isReviewContent) {
    $this->status = $status;
    $this->isReviewCalculative = $isReviewCalculative;
    $this->isReviewContent = $isReviewContent;
  }

  public function getStatus(): string {
    return $this->status;
  }

  public function getIsReviewCalculative(): ?bool {
    return $this->isReviewCalculative;
  }

  public function getIsReviewContent(): ?bool {
    return $this->isReviewContent;
  }

}
