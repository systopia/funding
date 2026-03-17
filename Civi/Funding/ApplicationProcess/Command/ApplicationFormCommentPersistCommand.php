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

namespace Civi\Funding\ApplicationProcess\Command;

use Civi\Funding\Entity\ApplicationProcessEntityBundle;
use Civi\Funding\Entity\Traits\ApplicationProcessEntityBundleTrait;
use Civi\Funding\Form\Application\ValidatedApplicationDataInterface;
use Webmozart\Assert\Assert;

final class ApplicationFormCommentPersistCommand {

  use ApplicationProcessEntityBundleTrait;

  private ValidatedApplicationDataInterface $validatedData;

  public function __construct(
    ApplicationProcessEntityBundle $applicationProcessBundle,
    ValidatedApplicationDataInterface $validatedData
  ) {
    $this->applicationProcessBundle = $applicationProcessBundle;
    $this->validatedData = $validatedData;
  }

  public function getValidatedData(): ValidatedApplicationDataInterface {
    return $this->validatedData;
  }

  public function getCommentText(): string {
    Assert::notNull($this->validatedData->getComment());

    return $this->validatedData->getComment()['text'];
  }

  public function getCommentType(): string {
    Assert::notNull($this->validatedData->getComment());

    return $this->validatedData->getComment()['type'];
  }

}
