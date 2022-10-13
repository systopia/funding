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

use Civi\Api4\Contact;
use Civi\Funding\Entity\FundingCaseEntity;
use Civi\RemoteTools\Api4\Api4Interface;
use CRM_Funding_ExtensionUtil as E;

final class FundingCaseRecipientLoader implements FundingCaseRecipientLoaderInterface {

  private Api4Interface $api4;

  public function __construct(Api4Interface $api4) {
    $this->api4 = $api4;
  }

  /**
   * @inheritDoc
   */
  public function getRecipient(FundingCaseEntity $fundingCase): array {
    $action = Contact::get()->addWhere('id', '=', $fundingCase->getRecipientContactId());
    /** @phpstan-var array<string, mixed> $contact */
    $contact = $this->api4->executeAction($action)->first();

    // @todo Do we have to take care of display_name set to NULL? See DefaultPossibleRecipientsLoader
    /** @var string $displayName */
    $displayName = $contact['display_name'] ?? E::ts('Unknown');

    return [$fundingCase->getRecipientContactId() => $displayName];
  }

}
