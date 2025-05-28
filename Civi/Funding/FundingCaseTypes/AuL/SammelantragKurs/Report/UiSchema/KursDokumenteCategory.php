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

namespace Civi\Funding\FundingCaseTypes\AuL\SammelantragKurs\Report\UiSchema;

use Civi\RemoteTools\JsonForms\Control\JsonFormsArray;
use Civi\RemoteTools\JsonForms\JsonFormsControl;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsCategory;

final class KursDokumenteCategory extends JsonFormsCategory {

  public function __construct(string $scopePrefix) {
    parent::__construct('Dokumente', [
      new JsonFormsArray(
        "$scopePrefix/dateien",
        'Dokumente',
        <<<'EOD'
<p>Hier können weitere Dokumente wie Nachweise zur Öffentlichkeitsarbeit,
Veröffentlichungen, Ergebnisse, Routennachweise, das durchgeführte Programm
(wenn von geplantem Programm abweichend) etc. hochgeladen werden.</p>
Eine Teilnehmendenliste ist erforderlich. Das durchgeführte Programm ist
erforderlich. Die Unterschriftsseite Verwendungsnachweis ist erforderlich.
EOD,
        [
          new JsonFormsControl('#/properties/datei', 'Datei', NULL, ['format' => 'file']),
          new JsonFormsControl('#/properties/beschreibung', 'Beschreibung'),
        ],
        [
          'addButtonLabel' => 'Dokument hinzufügen',
          'removeButtonLabel' => 'Dokument entfernen',
        ]),
    ]);
  }

}
