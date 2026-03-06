<?php
/*
 * Copyright (C) 2026 SYSTOPIA GmbH
 *
 * This program is free software: you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License as published by the Free
 * Software Foundation, either version 3 of the License, or (at your option) any
 * later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types = 1);

namespace Civi\Funding\FundingCaseTypes\AuL\Personalkosten\Application\UiSchema;

use Civi\RemoteTools\JsonForms\Control\JsonFormsArray;
use Civi\RemoteTools\JsonForms\Control\JsonFormsHidden;
use Civi\RemoteTools\JsonForms\JsonFormsControl;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsCategory;

final class PersonalkostenDokumenteUiSchema extends JsonFormsCategory {

  public function __construct() {
    parent::__construct('Dokumente', [
      new JsonFormsArray(
        '#/properties/dokumente',
        'Dokumente',
        <<<EOD
<p>Hier können Dokumente hochgeladen werden.</p>
Das Formblatt Personalkostenplanung ist erforderlich.
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
