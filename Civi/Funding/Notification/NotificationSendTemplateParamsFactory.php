<?php
/*
 * Copyright (C) 2024 SYSTOPIA GmbH
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

namespace Civi\Funding\Notification;

use Civi\Api4\Contact;
use Civi\RemoteTools\Api4\Api4Interface;

class NotificationSendTemplateParamsFactory {

  private Api4Interface $api4;

  public function __construct(Api4Interface $api4) {
    $this->api4 = $api4;
  }

  /**
   * @phpstan-param array<string, mixed> $tokenContext
   *
   * @phpstan-return array<string, mixed>
   *
   * @throws \CRM_Core_Exception
   */
  public function createSendTemplateParams(
    int $notificationContactId,
    string $workflowName,
    ?string $fromName,
    string $fromEmail,
    array $tokenContext
  ): ?array {
    /** @phpstan-var array{display_name: string, 'email.email': string}|null $contact */
    $contact = $this->api4->execute(Contact::getEntityName(), 'get', [
      'select' => [
        'display_name',
        'email.email',
      ],
      'where' => [
        ['id', '=', $notificationContactId],
        ['do_not_email', '=', FALSE],
      ],
      'join' => [
        ['Email AS email', 'INNER', ['email.contact_id', '=', 'id'], ['email.is_primary', '=', TRUE]],
      ],
    ])->first();

    if (NULL === $contact) {
      return NULL;
    }

    $tokenContext['contactId'] = $notificationContactId;

    return [
      'workflow' => $workflowName,
      'from' => ($fromName ?? '') . ' <' . $fromEmail . '>',
      'toName' => $contact['display_name'],
      'toEmail' => $contact['email.email'],
      'tokenContext' => $tokenContext,
    ];
  }

}
