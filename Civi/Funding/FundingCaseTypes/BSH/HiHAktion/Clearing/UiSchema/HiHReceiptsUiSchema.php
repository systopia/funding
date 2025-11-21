<?php
/*
 * Copyright (C) 2025 SYSTOPIA GmbH
 *
 *  This program is free software: you can redistribute it and/or modify it under
 *  the terms of the GNU Affero General Public License as published by the Free
 *  Software Foundation, either version 3 of the License, or (at your option) any
 *  later version.
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

namespace Civi\Funding\FundingCaseTypes\BSH\HiHAktion\Clearing\UiSchema;

use Civi\Funding\Entity\ClearingProcessEntityBundle;
use Civi\RemoteTools\JsonForms\Control\JsonFormsArray;
use Civi\RemoteTools\JsonForms\Control\JsonFormsHidden;
use Civi\RemoteTools\JsonForms\JsonFormsControl;
use Civi\RemoteTools\JsonForms\JsonFormsMarkup;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsGroup;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsTable;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsTableRow;

/**
 * @phpstan-type applicationCostItemsByTypeT array<string,
 *   array<string, \Civi\Funding\Entity\ApplicationCostItemEntity>>
 *   Mapping of cost item types to cost items indexed by identifier.
 */
final class HiHReceiptsUiSchema extends JsonFormsGroup {

  /**
   * @phpstan-param applicationCostItemsByTypeT $applicationCostItemsByType
   */
  public function __construct(
    array $applicationCostItemsByType,
    ClearingProcessEntityBundle $clearingProcessBundle,
  ) {
    $currency = $clearingProcessBundle->getFundingProgram()->getCurrency();
    $reportDataScopePrefix = '#/properties/reportData/properties';
    $costItemsScopePrefix = '#/properties/costItems/properties';
    $sachkostenScopePrefix = "$costItemsScopePrefix/sachkosten/properties/records/properties";

    $numberFormatter = \NumberFormatter::create('de_DE', \NumberFormatter::DECIMAL);
    $numberFormatter->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, 2);

    parent::__construct('Projektkosten', [
      new JsonFormsGroup('Personalkosten', [
        new JsonFormsTable([
          'Position',
          "Beantragter Betrag in $currency",
          "Bewilligter Betrag in $currency",
          "Ausgaben in $currency",
        ], [
          new JsonFormsTableRow([
            new JsonFormsMarkup('Personalkosten'),
            new JsonFormsMarkup((string) $numberFormatter->format($this->getAmountSum(
              $applicationCostItemsByType['personalkosten'] ?? [])
            )),
            new JsonFormsMarkup((string) $numberFormatter->format(
              $applicationCostItemsByType['bewilligt']['personalkostenBewilligt']->getAmount()
            )),
            new JsonFormsControl("$costItemsScopePrefix/personalkosten/properties/amountRecordedTotal", ''),
          ]),
        ]),
        new JsonFormsArray(
          "$costItemsScopePrefix/personalkosten/properties/records",
          '',
          NULL,
          [
            new JsonFormsHidden('#/properties/_id', ['internal' => TRUE]),
            new JsonFormsControl('#/properties/properties/properties/posten', 'Posten'),
            new JsonFormsControl('#/properties/properties/properties/wochenstunden', 'Wochenstunden'),
            new JsonFormsControl(
              '#/properties/properties/properties/monatlichesArbeitgeberbrutto',
              "Monatliches Arbeitgeberbrutto in $currency (Anteil Projekt)"
            ),
            new JsonFormsControl('#/properties/properties/properties/monate', 'Monate'),
            new JsonFormsControl('#/properties/amount', "Summe in $currency"),
          ],
          [
            'addButtonLabel' => 'Personalkosten hinzufügen',
            'removeButtonLabel' => 'Entfernen',
          ]
        ),
        new JsonFormsControl(
          "$reportDataScopePrefix/personalkostenKommentar",
          'Kommentar zu den Personalkosten',
          NULL,
          ['multi' => TRUE]
        ),
      ]),

      new JsonFormsGroup('Honorare', [
        new JsonFormsTable([
          'Position',
          "Beantragter Betrag in $currency",
          "Bewilligter Betrag in $currency",
          "Ausgaben in $currency",
        ], [
          new JsonFormsTableRow([
            new JsonFormsMarkup('Honorare'),
            new JsonFormsMarkup((string) $numberFormatter->format($this->getAmountSum(
                $applicationCostItemsByType['honorar'] ?? [])
            )),
            new JsonFormsMarkup((string) $numberFormatter->format(
              $applicationCostItemsByType['bewilligt']['honorareBewilligt']->getAmount()
            )),
            new JsonFormsControl("$costItemsScopePrefix/honorare/properties/amountRecordedTotal", ''),
          ]),
        ]),
        new JsonFormsArray(
          "$costItemsScopePrefix/honorare/properties/records",
          '',
          NULL,
          [
            new JsonFormsHidden('#/properties/_id', ['internal' => TRUE]),
            new JsonFormsControl('#/properties/properties/properties/posten', 'Posten'),
            new JsonFormsControl('#/properties/properties/properties/berechnungsgrundlage', 'Berechnungsgrundlage'),
            new JsonFormsControl(
              '#/properties/properties/properties/verguetung',
              "Vergütung in $currency"
            ),
            new JsonFormsControl('#/properties/properties/properties/dauer', 'Dauer'),
            new JsonFormsControl('#/properties/amount', "Summe in $currency"),
          ],
          [
            'addButtonLabel' => 'Honorar hinzufügen',
            'removeButtonLabel' => 'Entfernen',
          ]
        ),
        new JsonFormsControl(
          "$reportDataScopePrefix/honorareKommentar",
          'Kommentar zu den Honoraren',
          NULL,
          ['multi' => TRUE]),
      ]),

      new JsonFormsGroup('Projektbezogene Sachkosten', [
        new JsonFormsTable(['Position',
          "Beantragter Betrag in $currency",
          "Bewilligter Betrag in $currency",
          "Ausgaben in $currency",
        ], [
          new JsonFormsTableRow([
            new JsonFormsMarkup('Sachkosten'),
            new JsonFormsMarkup((string) $numberFormatter->format($this->getAmountSum(
                $applicationCostItemsByType['sachkosten'] ?? [])
            )),
            new JsonFormsMarkup((string) $numberFormatter->format(
              $applicationCostItemsByType['bewilligt']['sachkostenBewilligt']->getAmount()
            )),
            new JsonFormsControl('#/properties/sachkostenAmountRecordedTotal', ''),
          ]),
        ]),
        new JsonFormsHidden("$sachkostenScopePrefix/materialien/properties/_id", ['internal' => TRUE]),
        new JsonFormsControl(
          "$sachkostenScopePrefix/materialien/properties/amount",
          "Projektbezogene Materialien in $currency",
          'z.B. für Veranstaltungen, Workshops, Verbrauchsmaterial',
          ['descriptionDisplay' => 'before']
        ),
        new JsonFormsHidden("$sachkostenScopePrefix/ehrenamtspauschalen/properties/_id", ['internal' => TRUE]),
        new JsonFormsControl(
          "$sachkostenScopePrefix/ehrenamtspauschalen/properties/amount",
          "Ehrenamts-/Übungsleiterpauschalen in $currency",
          NULL,
          ['descriptionDisplay' => 'before']
        ),
        new JsonFormsHidden("$sachkostenScopePrefix/verpflegung/properties/_id", ['internal' => TRUE]),
        new JsonFormsControl(
          "$sachkostenScopePrefix/verpflegung/properties/amount",
          "Verpflegung/Catering in $currency",
          'z.B. für Teilnehmer:innen von Angeboten',
          ['descriptionDisplay' => 'before']
        ),
        new JsonFormsHidden("$sachkostenScopePrefix/fahrtkosten/properties/_id", ['internal' => TRUE]),
        new JsonFormsControl(
          "$sachkostenScopePrefix/fahrtkosten/properties/amount",
          "Fahrtkosten in $currency",
          'z.B. für Ausflüge',
          ['descriptionDisplay' => 'before']
        ),
        new JsonFormsHidden("$sachkostenScopePrefix/investitionen/properties/_id", ['internal' => TRUE]),
        new JsonFormsControl(
          "$sachkostenScopePrefix/investitionen/properties/amount",
          "Projektbezogene Investitionen in $currency",
          'z.B. Möbel, Laptop, Software, Fahrradrikscha',
          ['descriptionDisplay' => 'before']
        ),
        new JsonFormsHidden("$sachkostenScopePrefix/mieten/properties/_id", ['internal' => TRUE]),
        new JsonFormsControl(
          "$sachkostenScopePrefix/mieten/properties/amount",
          "Projektbezogene Mieten in $currency",
          'z.B. für Veranstaltungen',
          ['descriptionDisplay' => 'before']
        ),
        new JsonFormsArray(
          "$costItemsScopePrefix/sachkostenSonstige/properties/records",
          'Sonstige projektbezogene Sachkosten',
          'z.B. Eintrittsgelder für den Besuch von Veranstaltungen, Telefonkosten, Bürobedarf oder IT-Support',
          [
            new JsonFormsHidden('#/properties/_id', ['internal' => TRUE]),
            new JsonFormsControl('#/properties/properties/properties/bezeichnung', 'Bezeichnung'),
            new JsonFormsControl('#/properties/amount', "Summe in $currency"),
          ],
          [
            'addButtonLabel' => 'Sonstige hinzufügen',
            'removeButtonLabel' => 'Entfernen',
          ]
        ),
        new JsonFormsControl(
          "$costItemsScopePrefix/sachkostenSonstige/properties/amountRecordedTotal",
          "Summe sonstige Sachkosten in $currency",
        ),
        new JsonFormsControl(
          "$reportDataScopePrefix/sachkostenKommentar",
          'Kommentar zu den Sachkosten',
          NULL,
          ['multi' => TRUE]
        ),
      ]),

      new JsonFormsGroup('Insgesamt', [
        new JsonFormsTable(
          ["Beantragter Betrag in $currency", "Bewilligter Betrag in $currency", "Ausgaben in $currency"],
          [
            new JsonFormsTableRow([
              new JsonFormsMarkup((string) $numberFormatter->format(
                $clearingProcessBundle->getApplicationProcess()->getAmountRequested()
              )),
              new JsonFormsMarkup((string) $numberFormatter->format(
                $clearingProcessBundle->getFundingCase()->getAmountApproved() ?? 0
              )),
              new JsonFormsControl('#/properties/ausgaben', ''),
            ]),
          ]
        ),
      ]),
    ]);
  }

  /**
   * @param array<\Civi\Funding\Entity\ApplicationCostItemEntity> $applicationCostItems
   */
  private function getAmountSum(array $applicationCostItems): float {
    $sum = 0.0;
    foreach ($applicationCostItems as $applicationCostItem) {
      $sum += $applicationCostItem->getAmount();
    }

    return $sum;
  }

}
