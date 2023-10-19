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
use Civi\RemoteTools\JsonForms\Layout\JsonFormsGroup;

final class IJBZuschlagsrelevanteKostenUiSchema extends JsonFormsGroup {

  public function __construct(string $currency) {
    parent::__construct('Zuschlagsrelevante Kosten für Vor- und Nachbereitung und Qualifizierung der Maßnahme', [
      new JsonFormsControl(
        '#/properties/kosten/properties/zuschlagsrelevanteKosten/properties/programmabsprachen',
        'Programmabsprachen (Telefon, Porto, Kopien, Internet etc.) in ' . $currency
      ),
      new JsonFormsControl(
        '#/properties/kosten/properties/zuschlagsrelevanteKosten/properties/vorbereitungsmaterial',
        'Erstellung von Vorbereitungsmaterial in ' . $currency
      ),
      new JsonFormsControl(
        '#/properties/kosten/properties/zuschlagsrelevanteKosten/properties/veroeffentlichungen',
        'Veröffentlichungen, Publikationen, Videos, Fotos etc. als
        Dokumentation der Ergebnisse und für die Öffentlichkeitsarbeit in ' .
        $currency
      ),
      new JsonFormsControl(
        '#/properties/kosten/properties/zuschlagsrelevanteKosten/properties/honorare',
        'Honorare für Vorträge, die der Vorbereitung der Gruppe dienen (nur im Inland) in ' . $currency
      ),
      new JsonFormsControl(
        '#/properties/kosten/properties/zuschlagsrelevanteKosten/properties/fahrtkostenUndVerpflegung',
        'Fahrtkosten und Verpflegung, ggf. Übernachtung bei überregionaler TN-Zusammensetzung in ' . $currency
      ),
      new JsonFormsControl(
        '#/properties/kosten/properties/zuschlagsrelevanteKosten/properties/reisekosten',
        'Reise-/Fahrtkosten für interne Koordination und Organisation der Vor- und Nachbereitung in ' . $currency
      ),
      new JsonFormsControl(
        '#/properties/kosten/properties/zuschlagsrelevanteKosten/properties/miete',
        'Raum-, Materialmiete (techn. Geräte, Beamer, Flipchart etc.) in ' . $currency
      ),
      new JsonFormsControl(
        '#/properties/kosten/properties/zuschlagsrelevanteKostenGesamt',
        'Zuschlagsrelevante Kosten für Vor- und Nachbereitung und Qualifizierung gesamt in ' . $currency
      ),
    ]);
  }

}
