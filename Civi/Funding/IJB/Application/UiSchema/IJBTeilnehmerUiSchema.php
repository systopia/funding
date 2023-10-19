<?php
/*
 * Copyright (C) 2023 SYSTOPIA GmbH
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

namespace Civi\Funding\IJB\Application\UiSchema;

use Civi\RemoteTools\JsonForms\JsonFormsControl;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsCategory;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsGroup;

final class IJBTeilnehmerUiSchema extends JsonFormsCategory {

  public function __construct() {
    parent::__construct('Teilnehmer*innen', [
      new JsonFormsGroup('Teilnehmer*innen aus Deutschland', [
        new JsonFormsControl(
          '#/properties/teilnehmer/properties/deutschland/properties/gesamt',
          'Gesamtanzahl der Teilnehmer*innen (inkl. Team)',
        ),
        new JsonFormsControl('#/properties/teilnehmer/properties/deutschland/properties/weiblich', 'davon weiblich'),
        new JsonFormsControl('#/properties/teilnehmer/properties/deutschland/properties/divers', 'davon divers'),
        new JsonFormsControl('#/properties/teilnehmer/properties/deutschland/properties/unter27', 'davon U27'),
        new JsonFormsControl(
          '#/properties/teilnehmer/properties/deutschland/properties/inJugendhilfeTaetig',
          'davon in der Kinder- und Jugendhilfe (Multiplikator*innen-Seminare) tätig',
        ),
        new JsonFormsControl(
          '#/properties/teilnehmer/properties/deutschland/properties/referenten',
          'davon Referent*innen, Leitungs- und Begleitpersonen (Team)',
        ),
      ]),
      new JsonFormsGroup('Teilnehmer*innen aus dem Partnerland', [
        new JsonFormsControl(
          '#/properties/teilnehmer/properties/partnerland/properties/gesamt',
          'Gesamtanzahl der Teilnehmer*innen (inkl. Team)',
        ),
        new JsonFormsControl('#/properties/teilnehmer/properties/partnerland/properties/weiblich', 'davon weiblich'),
        new JsonFormsControl('#/properties/teilnehmer/properties/partnerland/properties/divers', 'davon divers'),
        new JsonFormsControl('#/properties/teilnehmer/properties/partnerland/properties/unter27', 'davon U27'),
        new JsonFormsControl(
          '#/properties/teilnehmer/properties/partnerland/properties/inJugendhilfeTaetig',
          'davon in der Kinder- und Jugendhilfe (Multiplikator*innen-Seminare) tätig',
        ),
        new JsonFormsControl(
          '#/properties/teilnehmer/properties/partnerland/properties/referenten',
          'davon Referent*innen, Leitungs- und Begleitpersonen (Team)',
        ),
      ]),
      new JsonFormsControl('#/properties/teilnehmer/properties/teilnehmertage', 'Teilnehmendentage'),
    ]);
  }

}
