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

use Civi\RemoteTools\JsonForms\Control\JsonFormsArray;
use Civi\RemoteTools\JsonForms\Control\JsonFormsHidden;
use Civi\RemoteTools\JsonForms\JsonFormsControl;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsCategory;

final class IJBProjektunterlagenUiSchema extends JsonFormsCategory {

  public function __construct() {
    parent::__construct('Projektunterlagen', [
      new JsonFormsArray(
        '#/properties/projektunterlagen',
        'Dokumente',
        <<<EOD
<p>Bitte hier einen Screenshot der Berechnung der Reiseentfernung und die geplante
Programmübersicht (ein tageweise gegliedertes und aussagekräftiges
Begegnungsprogramm/Programmentwurf bzw. eine Darstellung des Verlaufs/der
vorgesehen Aktivitäten der geplanten Maßnahme, aus der hervorgehen soll,
inwieweit die Programmpunkte gemeinsam – deutsche und ausländische Gruppe –
durchgeführt werden) und ggf. weitere Unterlagen hochladen.</p>
Die Unterschriftsseite Antrag ist erforderlich.
EOD,
        [
          new JsonFormsHidden('#/properties/_identifier'),
          new JsonFormsControl('#/properties/datei', 'Datei', NULL, ['format' => 'file']),
          new JsonFormsControl('#/properties/beschreibung', 'Beschreibung'),
        ],
        [
          'addButtonLabel' => 'Dokument hinzufügen',
          'removeButtonLabel' => 'Dokument entfernen',
        ]
      ),
    ]);
  }

}
