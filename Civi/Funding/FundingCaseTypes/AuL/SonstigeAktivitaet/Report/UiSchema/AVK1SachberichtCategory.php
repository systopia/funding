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

namespace Civi\Funding\FundingCaseTypes\AuL\SonstigeAktivitaet\Report\UiSchema;

use Civi\RemoteTools\JsonForms\JsonFormsControl;
use Civi\RemoteTools\JsonForms\JsonFormsRule;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsCategory;
use Civi\RemoteTools\JsonSchema\JsonSchema;

final class AVK1SachberichtCategory extends JsonFormsCategory {

  public function __construct(string $scopePrefix) {
    parent::__construct('Sachbericht', [
      new JsonFormsControl(
        "$scopePrefix/durchgefuehrt",
        'Die Maßnahme wurde durchgeführt',
        NULL,
        ['format' => 'radio']
      ),
      new JsonFormsControl("$scopePrefix/aenderungen", '', NULL, ['multi' => TRUE], [
        'rule' => new JsonFormsRule(
          'ENABLE',
          "$scopePrefix/durchgefuehrt",
          JsonSchema::fromArray(['const' => 'geaendert'])
        ),
      ]),
      new JsonFormsControl(
        "$scopePrefix/thematischeSchwerpunkte",
        'Welche thematischen Schwerpunkte hatte die Veranstaltung?',
        NULL,
        ['multi' => TRUE],
      ),
      new JsonFormsControl(
        "$scopePrefix/methoden",
        'Inwiefern und mit welchen Methoden wurden die  inhaltlichen Ziele erreicht?',
        NULL,
        ['multi' => TRUE]
      ),
      new JsonFormsControl(
        "$scopePrefix/zielgruppe",
        'Welche Zielgruppe wurde mit der Veranstaltung erreicht (Zusammensetzung, Alter)?',
        NULL,
        ['multi' => TRUE]
      ),
      new JsonFormsControl(
        "$scopePrefix/sonstiges",
        'Besondere Vorkommnisse, Schlussfolgerungen oder sonstige Hinweise',
        NULL,
        ['multi' => TRUE]
      ),
    ]);
  }

}
