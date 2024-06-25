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

namespace Civi\Funding\SammelantragKurs\Report\UiSchema;

use Civi\RemoteTools\JsonForms\JsonFormsControl;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsCategory;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsGroup;

final class KursZusammenFassungCategory extends JsonFormsCategory {

  public function __construct(string $reportDataScopePrefix, string $currency) {
    parent::__construct('Zusammenfassung', [
      new JsonFormsControl(
        "$reportDataScopePrefix/grunddaten/properties/programmtage",
        'Veranstaltungstage',
        NULL,
        ['readonly' => TRUE]
      ),
      new JsonFormsControl(
        "$reportDataScopePrefix/grunddaten/properties/teilnehmer/properties/gesamt",
        'Zahl der Teilnehmenden',
        NULL,
        ['readonly' => TRUE]
      ),
      new JsonFormsControl(
        "$reportDataScopePrefix/grunddaten/properties/teilnehmertage",
        'Teilnahmetage',
        NULL,
        ['readonly' => TRUE]
      ),
      new JsonFormsGroup('Ausgaben', [
        new JsonFormsGroup('Übernachtung und Verpflegung', [
          new JsonFormsControl(
            '#/properties/costItemsByType/properties/amountRecorded_teilnehmerkosten',
            "Erfasster Betrag in $currency"
          ),
          new JsonFormsControl(
            '#/properties/costItemsByType/properties/amountAdmitted_teilnehmerkosten',
            "Anerkannter Betrag in $currency"
          ),
        ]),
        new JsonFormsGroup('Fahrtkosten', [
          new JsonFormsControl(
            '#/properties/costItemsByType/properties/amountRecorded_fahrtkosten',
            "Erfasster Betrag in $currency"
          ),
          new JsonFormsControl(
            '#/properties/costItemsByType/properties/amountAdmitted_fahrtkosten',
            "Anerkannter Betrag in $currency"
          ),
        ]),
        new JsonFormsGroup('Honorarkosten', [
          new JsonFormsControl(
            '#/properties/costItemsByType/properties/amountRecorded_honorarkosten',
            "Erfasster Betrag in $currency"
          ),
          new JsonFormsControl(
            '#/properties/costItemsByType/properties/amountAdmitted_honorarkosten',
            "Anerkannter Betrag in $currency"
          ),
        ]),
        new JsonFormsGroup('Sonstige Ausgaben', [
          new JsonFormsControl(
            '#/properties/costItemsByType/properties/amountRecorded_sonstigeAusgaben',
            "Erfasster Betrag in $currency"
          ),
          new JsonFormsControl(
            '#/properties/costItemsByType/properties/amountAdmitted_sonstigeAusgaben',
            "Anerkannter Betrag in $currency"
          ),
        ]),
        new JsonFormsGroup('Gesamtkosten', [
          new JsonFormsControl(
            '#/properties/costItemsAmountRecorded',
            "Erfasster Betrag in $currency"
          ),
          new JsonFormsControl(
            '#/properties/costItemsAmountAdmitted',
            "Anerkannter Betrag in $currency"
          ),
        ]),
      ]),
      new JsonFormsGroup('Einnahmen', [
        new JsonFormsGroup('Teilnahmebeiträge', [
          new JsonFormsControl(
            '#/properties/resourcesItemsByType/properties/amountRecorded_teilnehmerbeitraege',
            "Erfasster Betrag in $currency"
          ),
          new JsonFormsControl(
            '#/properties/resourcesItemsByType/properties/amountAdmitted_teilnehmerbeitraege',
            "Anerkannter Betrag in $currency"
          ),
        ]),
        new JsonFormsGroup('Eigenmittel', [
          new JsonFormsControl(
            '#/properties/resourcesItemsByType/properties/amountRecorded_eigenmittel',
            "Erfasster Betrag in $currency"
          ),
          new JsonFormsControl(
            '#/properties/resourcesItemsByType/properties/amountAdmitted_eigenmittel',
            "Anerkannter Betrag in $currency"
          ),
        ]),
        new JsonFormsGroup('Öffentliche Mittel', [
          new JsonFormsControl(
            '#/properties/resourcesItemsByType/properties/amountRecorded_oeffentlicheMittel',
            "Erfasster Betrag in $currency"
          ),
          new JsonFormsControl(
            '#/properties/resourcesItemsByType/properties/amountAdmitted_oeffentlicheMittel',
            "Anerkannter Betrag in $currency"
          ),
        ]),
        new JsonFormsGroup('Sonstige Mittel', [
          new JsonFormsControl(
            "$reportDataScopePrefix/zusammenfassung/properties/amountRecorded_sonstigeMittel",
            "Erfasster Betrag in $currency"
          ),
          new JsonFormsControl(
            "$reportDataScopePrefix/zusammenfassung/properties/amountAdmitted_sonstigeMittel",
            "Anerkannter Betrag in $currency"
          ),
        ]),
        new JsonFormsGroup('KJP-Festbetragsförderung Teilnahmetage', [
          new JsonFormsControl(
            "$reportDataScopePrefix/zusammenfassung/properties/foerderungTeilnahmetage",
            "Betrag in $currency"
          ),
        ]),
        new JsonFormsGroup('KJP-Festbetragsförderung Honorare', [
          new JsonFormsControl(
            "$reportDataScopePrefix/zusammenfassung/properties/foerderungHonorare",
            "Betrag in $currency"
          ),
        ]),
        new JsonFormsGroup('KJP-Festbetragsförderung Fahrtkosten', [
          new JsonFormsControl(
            "$reportDataScopePrefix/zusammenfassung/properties/foerderungFahrtkosten",
            "Betrag in $currency"
          ),
        ]),
        new JsonFormsGroup('Summe KJP-Förderung', [
          new JsonFormsControl(
            "$reportDataScopePrefix/foerderung/properties/summe",
            "Betrag in $currency"
          ),
        ]),
        new JsonFormsGroup('Gesamteinnahmen', [
          new JsonFormsControl(
            '#/properties/resourcesItemsAmountRecorded',
            "Erfasster Betrag in $currency"
          ),
          new JsonFormsControl(
            '#/properties/resourcesItemsAmountAdmitted',
            "Anerkannter Betrag in $currency"
          ),
        ]),
      ]),
    ]);
  }

}
