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

namespace Civi\Funding\FundingCaseTypes\BSH\HiHAktion\Application\UiSchema;

use Civi\RemoteTools\JsonForms\JsonFormsControl;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsGroup;

final class HiHRechtlichesGroup extends JsonFormsGroup {

  public function __construct(string $scopePrefix) {
    parent::__construct('', [
      new JsonFormsControl(
        "$scopePrefix/kinderschutzklausel",
        'Kinderschutz/Antidiskriminierungsklausel',
        <<<EOD
Die beteiligten Stiftungen wenden sich explizit gegen jede Form von physischer,
psychischer und sexualisierter Gewalt. Die antragstellende Organisation ist dem
Kinderschutz verpflichtet und macht dieses entsprechend kenntlich
(Flyer/Website/Satzung). Bitte ankreuzen.
EOD
      ),
      new JsonFormsGroup('Datenschutz', [
        new JsonFormsControl(
          "$scopePrefix/datenschutz",
          'Ich habe die Hinweise zum Datenschutz gelesen und stimme zu.',
          <<<EOD
Ich bin damit einverstanden, dass meine hier eingetragenen Daten sowie
hochgeladene Dokumente in der Datenbank der BürgerStiftung Hamburg
weiterverarbeitet, gespeichert und veröffentlicht werden und zum Zweck der
Begutachtung und Berichterstattung an den NDR, an die zuständige Bürgerstiftung
sowie die Beiratsmitglieder und den Verein Stiften für alle e.V. weitergeleitet
werden dürfen. Weitere Infos finden Sie in unseren Hinweisen zum Datenschutz
finden Sie in unseren Hinweisen zum Datenschutz.
EOD
        ),
      ]),
    ]);
  }

}
