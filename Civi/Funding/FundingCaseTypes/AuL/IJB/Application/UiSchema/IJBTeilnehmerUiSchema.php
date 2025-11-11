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

namespace Civi\Funding\FundingCaseTypes\AuL\IJB\Application\UiSchema;

use Civi\RemoteTools\JsonForms\JsonFormsControl;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsCategory;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsGroup;

final class IJBTeilnehmerUiSchema extends JsonFormsCategory {

  /**
   * @param bool $report TRUE if used for report.
   */
  public function __construct(string $scopePrefix, bool $report = FALSE) {
    $teilnehmerDeutschlandElements = [
      new JsonFormsControl(
        "$scopePrefix/deutschland/properties/gesamt",
        'Gesamtanzahl der Teilnehmer*innen (inkl. Team)',
      ),
      new JsonFormsControl("$scopePrefix/deutschland/properties/weiblich", 'davon weiblich'),
      new JsonFormsControl("$scopePrefix/deutschland/properties/divers", 'davon divers'),
      new JsonFormsControl("$scopePrefix/deutschland/properties/unter27", 'davon U27'),
      new JsonFormsControl(
        "$scopePrefix/deutschland/properties/inJugendhilfeEhrenamtlichTaetig",
        'davon in der Kinder- und Jugendhilfe (Multiplikator*innen-Seminare) ehrenamtlich tätig',
      ),
      new JsonFormsControl(
        "$scopePrefix/deutschland/properties/inJugendhilfeHauptamtlichTaetig",
        'davon in der Kinder- und Jugendhilfe (Multiplikator*innen-Seminare) hauptamtlich tätig',
      ),
      new JsonFormsControl(
        "$scopePrefix/deutschland/properties/referenten",
        'davon Referent*innen, Leitungs- und Begleitpersonen (Team)',
      ),
    ];
    if ($report) {
      $teilnehmerDeutschlandElements[] = new JsonFormsControl(
        "$scopePrefix/deutschland/properties/mitFahrtkosten",
        'davon Personen, bei denen tatsächlich Fahrtkosten angefallen sind',
      );
    }

    parent::__construct('Teilnehmer*innen', [
      new JsonFormsGroup('Teilnehmer*innen aus Deutschland', $teilnehmerDeutschlandElements),
      new JsonFormsGroup('Teilnehmer*innen aus dem Partnerland', [
        new JsonFormsControl(
          "$scopePrefix/partnerland/properties/gesamt",
          'Gesamtanzahl der Teilnehmer*innen (inkl. Team)',
        ),
        new JsonFormsControl("$scopePrefix/partnerland/properties/weiblich", 'davon weiblich'),
        new JsonFormsControl("$scopePrefix/partnerland/properties/divers", 'davon divers'),
        new JsonFormsControl("$scopePrefix/partnerland/properties/unter27", 'davon U27'),
        new JsonFormsControl(
          "$scopePrefix/partnerland/properties/inJugendhilfeEhrenamtlichTaetig",
          'davon in der Kinder- und Jugendhilfe (Multiplikator*innen-Seminare) ehrenamtlich tätig',
        ),
        new JsonFormsControl(
          "$scopePrefix/partnerland/properties/inJugendhilfeHauptamtlichTaetig",
          'davon in der Kinder- und Jugendhilfe (Multiplikator*innen-Seminare) hauptamtlich tätig',
        ),
        new JsonFormsControl(
          "$scopePrefix/partnerland/properties/referenten",
          'davon Referent*innen, Leitungs- und Begleitpersonen (Team)',
        ),
      ]),
      new JsonFormsControl("$scopePrefix/teilnehmertage", 'Teilnehmendentage'),
    ]);
  }

}
