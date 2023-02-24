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
use Civi\Funding\Api4\Util\ContactUtil;
use Civi\Funding\Entity\FundingCaseEntity;
use Civi\RemoteTools\Api4\Api4Interface;

final class FundingCaseRecipientLoader implements FundingCaseRecipientLoaderInterface {

  private Api4Interface $api4;

  public function __construct(Api4Interface $api4) {
    $this->api4 = $api4;
  }

  /**
   * @inheritDoc
   */
  public function getRecipient(FundingCaseEntity $fundingCase): array {
    $action = Contact::get(FALSE)
      ->addSelect('id', 'display_name')
      ->addWhere('id', '=', $fundingCase->getRecipientContactId());
    /** @phpstan-var array{id: int, display_name: ?string} $contact */
    $contact = $this->api4->executeAction($action)->first();

    return [$fundingCase->getRecipientContactId() => ContactUtil::getDisplayName($contact)];
  }

}
