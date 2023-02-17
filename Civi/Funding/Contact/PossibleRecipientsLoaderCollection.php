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

namespace Civi\Funding\Contact;

use Civi\Funding\Entity\FundingProgramEntity;

final class PossibleRecipientsLoaderCollection implements PossibleRecipientsLoaderInterface {

  /**
   * @phpstan-var iterable<PossibleRecipientsLoaderInterface>
   */
  private iterable $possibleRecipientsLoaders;

  /**
   * @phpstan-param iterable<PossibleRecipientsLoaderInterface> $possibleRecipientsLoaders
   */
  public function __construct(iterable $possibleRecipientsLoaders) {
    $this->possibleRecipientsLoaders = $possibleRecipientsLoaders;
  }

  /**
   * @inheritDoc
   */
  public function getPossibleRecipients(int $contactId, FundingProgramEntity $fundingProgram): array {
    $possibleRecipients = [];
    foreach ($this->possibleRecipientsLoaders as $possibleRecipientsLoader) {
      $possibleRecipients += $possibleRecipientsLoader->getPossibleRecipients($contactId, $fundingProgram);
    }

    return $possibleRecipients;
  }

}
