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

use Civi\RemoteTools\JsonForms\JsonFormsControl;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsCategory;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsGroup;

final class PersonalkostenGrunddatenUiSchema extends JsonFormsCategory {

  public function __construct(string $scopePrefix, string $currency) {
    $elements = [
      new JsonFormsControl("$scopePrefix/internerBezeichner", 'Interner Bezeichner'),
      new JsonFormsControl("$scopePrefix/name", 'Name'),
      new JsonFormsControl("$scopePrefix/vorname", 'Vorname'),
      new JsonFormsControl("$scopePrefix/tarifUndEingruppierung", 'Tarif und Eingruppierung'),
      new JsonFormsControl("$scopePrefix/beginn", 'Beschäftigungszeitraum von'),
      new JsonFormsControl("$scopePrefix/ende", 'Beschäftigungszeitraum bis'),
      new JsonFormsControl(
        "$scopePrefix/personalkostenTatsaechlich",
        "Tatsächliche Personalkosten, auf welche die Förderung beantragt wird in $currency",
        'Hier bitte das tatsächliche AG-Brutto angeben.',
      ),
      new JsonFormsControl(
        "$scopePrefix/personalkostenBeantragt",
        "Beantragte Personalkostenförderung in $currency",
        'Hier bitte den Betrag angeben, der gefördert werden soll'
      ),
      new JsonFormsControl(
        "$scopePrefix/sachkostenpauschale",
        "Sachkostenpauschale in $currency",
        <<<EOD
        Die Höhe der Sachkostenpauschale wird in jedem Jahr neu festgelegt; in
        der Regel kann sie nur anteilig gefördert werden.
        EOD
      ),
      new JsonFormsControl("$scopePrefix/beantragterZuschuss", "Beantragter Zuschuss in $currency"),
    ];

    parent::__construct('Grunddaten', [new JsonFormsGroup('Infrastrukturstelle von', $elements)]);
  }

}
