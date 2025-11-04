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

use Civi\Funding\ClearingProcess\ClearingProcessPermissions;
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

    parent::__construct('Projektkosten', [
      new JsonFormsGroup('Personalkosten', [
        new JsonFormsTable(['Position', 'Beantragter Betrag', 'Bewilligter Betrag', "Ausgaben in $currency"], [
          new JsonFormsTableRow([
            new JsonFormsMarkup('Personalkosten'),
            new JsonFormsMarkup($this->getAmountSum(
              $applicationCostItemsByType['personalkosten'] ?? []) . " $currency"
            ),
            new JsonFormsMarkup(
              $applicationCostItemsByType['bewilligt']['personalkostenBewilligt']->getAmount() . " $currency"
            ),
            new JsonFormsControl("$costItemsScopePrefix/personalkosten/properties/amountRecordedTotal", ''),
          ]),
        ]),
        new JsonFormsArray(
          "$costItemsScopePrefix/personalkosten/properties/records",
          '',
          NULL,
          $this->addAmountAdmittedField([
            new JsonFormsHidden('#/properties/_id', ['internal' => TRUE]),
            new JsonFormsControl('#/properties/properties/properties/posten', 'Posten'),
            new JsonFormsControl('#/properties/properties/properties/wochenstunden', 'Wochenstunden'),
            new JsonFormsControl(
              '#/properties/properties/properties/monatlichesArbeitgeberbrutto',
              "Monatliches Arbeitgeberbrutto in $currency (Anteil Projekt)"
            ),
            new JsonFormsControl('#/properties/properties/properties/monate', 'Monate'),
            new JsonFormsControl('#/properties/amount', "Summe in $currency"),
          ], '#/properties', $clearingProcessBundle),
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
        new JsonFormsTable(['Position', 'Beantragter Betrag', 'Bewilligter Betrag', "Ausgaben in $currency"], [
          new JsonFormsTableRow([
            new JsonFormsMarkup('Honorare'),
            new JsonFormsMarkup($this->getAmountSum(
                $applicationCostItemsByType['honorar'] ?? []) . " $currency"
            ),
            new JsonFormsMarkup(
              $applicationCostItemsByType['bewilligt']['honorareBewilligt']->getAmount() . " $currency"
            ),
            new JsonFormsControl("$costItemsScopePrefix/honorare/properties/amountRecordedTotal", ''),
          ]),
        ]),
        new JsonFormsArray(
          "$costItemsScopePrefix/honorare/properties/records",
          '',
          NULL,
          $this->addAmountAdmittedField([
            new JsonFormsHidden('#/properties/_id', ['internal' => TRUE]),
            new JsonFormsControl('#/properties/properties/properties/posten', 'Posten'),
            new JsonFormsControl('#/properties/properties/properties/berechnungsgrundlage', 'Berechnungsgrundlage'),
            new JsonFormsControl(
              '#/properties/properties/properties/verguetung',
              "Verguetung in $currency"
            ),
            new JsonFormsControl('#/properties/properties/properties/dauer', 'Dauer'),
            new JsonFormsControl('#/properties/amount', "Summe in $currency"),
          ], '#/properties', $clearingProcessBundle),
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
        new JsonFormsTable(['Position', 'Beantragter Betrag', 'Bewilligter Betrag', "Ausgaben in $currency"], [
          new JsonFormsTableRow([
            new JsonFormsMarkup('Sachkosten'),
            new JsonFormsMarkup($this->getAmountSum(
                $applicationCostItemsByType['sachkosten'] ?? []) . " $currency"
            ),
            new JsonFormsMarkup(
              $applicationCostItemsByType['bewilligt']['sachkostenBewilligt']->getAmount() . " $currency"
            ),
            new JsonFormsControl('#/properties/sachkostenAmountRecordedTotal', ''),
          ]),
        ]),
        new JsonFormsHidden("$sachkostenScopePrefix/materialien/properties/_id", ['internal' => TRUE]),
        ...$this->addAmountAdmittedField([
          new JsonFormsControl(
            "$sachkostenScopePrefix/materialien/properties/amount",
            "Projektbezogene Materialien in $currency",
            'z.B. für Veranstaltungen, Workshops, Verbrauchsmaterial',
            ['descriptionDisplay' => 'before']
          ),
        ], "$sachkostenScopePrefix/materialien/properties", $clearingProcessBundle),
        new JsonFormsHidden("$sachkostenScopePrefix/ehrenamtspauschalen/properties/_id", ['internal' => TRUE]),
        ...$this->addAmountAdmittedField([
          new JsonFormsControl(
            "$sachkostenScopePrefix/ehrenamtspauschalen/properties/amount",
            "Ehrenamts-/Übungsleiterpauschalen in $currency",
            NULL,
            ['descriptionDisplay' => 'before']
          ),
        ], "$sachkostenScopePrefix/ehrenamtspauschalen/properties", $clearingProcessBundle),
        new JsonFormsHidden("$sachkostenScopePrefix/verpflegung/properties/_id", ['internal' => TRUE]),
        ...$this->addAmountAdmittedField([
          new JsonFormsControl(
            "$sachkostenScopePrefix/verpflegung/properties/amount",
            "Verpflegung/Catering in $currency",
            'z.B. für Teilnehmer:innen von Angeboten',
            ['descriptionDisplay' => 'before']
          ),
        ], "$sachkostenScopePrefix/verpflegung/properties", $clearingProcessBundle),
        new JsonFormsHidden("$sachkostenScopePrefix/fahrtkosten/properties/_id", ['internal' => TRUE]),
        ...$this->addAmountAdmittedField([
          new JsonFormsControl(
            "$sachkostenScopePrefix/fahrtkosten/properties/amount",
            "Fahrtkosten in $currency",
            'z.B. für Ausflüge',
            ['descriptionDisplay' => 'before']
          ),
        ], "$sachkostenScopePrefix/fahrtkosten/properties", $clearingProcessBundle),
        new JsonFormsHidden("$sachkostenScopePrefix/investitionen/properties/_id", ['internal' => TRUE]),
        ...$this->addAmountAdmittedField([
          new JsonFormsControl(
            "$sachkostenScopePrefix/investitionen/properties/amount",
            "Projektbezogene Investitionen in $currency",
            'z.B. Möbel, Laptop, Software, Fahrradrikscha',
            ['descriptionDisplay' => 'before']
          ),
        ], "$sachkostenScopePrefix/investitionen/properties", $clearingProcessBundle),
        new JsonFormsHidden("$sachkostenScopePrefix/mieten/properties/_id", ['internal' => TRUE]),
        ...$this->addAmountAdmittedField([
          new JsonFormsControl(
            "$sachkostenScopePrefix/mieten/properties/amount",
            "Projektbezogene Mieten in $currency",
            'z.B. für Veranstaltungen',
            ['descriptionDisplay' => 'before']
          ),
        ], "$sachkostenScopePrefix/mieten/properties", $clearingProcessBundle),
        new JsonFormsArray(
          "$costItemsScopePrefix/sachkostenSonstige/properties/records",
          'Sonstige projektbezogene Sachkosten',
          'z.B. Eintrittsgelder für den Besuch von Veranstaltungen, Telefonkosten, Bürobedarf oder IT-Support',
          $this->addAmountAdmittedField([
            new JsonFormsHidden('#/properties/_id', ['internal' => TRUE]),
            new JsonFormsControl('#/properties/properties/properties/bezeichnung', 'Bezeichnung'),
            new JsonFormsControl('#/properties/amount', "Summe in $currency"),
          ], '#/properties', $clearingProcessBundle),
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
          ['Beantragter Betrag', 'Bewilligter Betrag', "Ausgaben in $currency"],
          [
            new JsonFormsTableRow([
              new JsonFormsMarkup("{$clearingProcessBundle->getApplicationProcess()->getAmountRequested()} $currency"),
              new JsonFormsMarkup("{$clearingProcessBundle->getFundingCase()->getAmountApproved()} $currency"),
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

  /**
   * @phpstan-param list<JsonFormsControl> $fields
   *
   * @phpstan-return list<JsonFormsControl>
   */
  private function addAmountAdmittedField(
    array $fields,
    string $scopePrefix,
    ClearingProcessEntityBundle $clearingProcessBundle
  ): array {
    if ($clearingProcessBundle->getFundingCase()->hasPermission(ClearingProcessPermissions::REVIEW_CALCULATIVE)) {
      $fields[] = new JsonFormsControl(
        "$scopePrefix/amountAdmitted",
        'Anerkannter Betrag in ' . $clearingProcessBundle->getFundingProgram()->getCurrency()
      );
    }

    return $fields;
  }

}
