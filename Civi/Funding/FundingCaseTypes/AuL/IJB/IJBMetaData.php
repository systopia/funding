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

namespace Civi\Funding\FundingCaseTypes\AuL\IJB;

use Civi\Funding\FundingCaseType\MetaData\AbstractFundingCaseTypeMetaData;
use Civi\Funding\FundingCaseType\MetaData\CostItemType;
use Civi\Funding\FundingCaseType\MetaData\DefaultApplicationProcessActions;
use Civi\Funding\FundingCaseType\MetaData\DefaultApplicationProcessStatuses;
use Civi\Funding\FundingCaseType\MetaData\ResourcesItemType;
use Civi\Funding\FundingCaseType\MetaData\ReworkApplicationProcessStatuses;
use Civi\Funding\FundingCaseType\MetaData\ReworkApplicationProcessActions;

final class IJBMetaData extends AbstractFundingCaseTypeMetaData {

  public const NAME = IJBConstants::FUNDING_CASE_TYPE_NAME;

  /**
   * @phpstan-var array<string, CostItemType>
   */
  private ?array $costItemTypes = NULL;

  /**
   * @phpstan-var array<string, ResourcesItemType>
   */
  private ?array $resourcesItemTypes = NULL;

  public function getName(): string {
    return self::NAME;
  }

  /**
   * @inheritDoc
   */
  public function getApplicationProcessActions(): array {
    return DefaultApplicationProcessActions::getAll() + ReworkApplicationProcessActions::getAll();
  }

  /**
   * @inheritDoc
   */
  public function getApplicationProcessStatuses(): array {
    return DefaultApplicationProcessStatuses::getAll() + ReworkApplicationProcessStatuses::getAll();
  }

  /**
   * @inheritDoc
   */
  public function getCostItemTypes(): array {
    return $this->costItemTypes ??= [
      'unterkunftUndVerpflegung' => new CostItemType([
        'name' => 'unterkunftUndVerpflegung',
        'label' => 'Unterkunft und Verpflegung',
        'clearable' => TRUE,
      ]),
      'honorar' => new CostItemType([
        'name' => 'honorar',
        'label' => 'Honorar',
        'clearable' => TRUE,
      ]),
      'fahrtkosten/flug' => new CostItemType([
        'name' => 'fahrtkosten/intern',
        'label' => 'Fahrt-/Flugkosten inkl. Transfer zur Unterkunft',
        'clearable' => TRUE,
      ]),
      'fahrtkosten/anTeilnehmerErstattet' => new CostItemType([
        'name' => 'fahrtkosten/anTeilnehmerErstattet',
        'label' => 'An Teilnehmer*innen/Referent*innen erstattete Fahrtkosten',
        'clearable' => TRUE,
      ]),
      'programmkosten/programmkosten' => new CostItemType([
        'name' => 'programmkosten/programmkosten',
        'label' => 'Programmkosten',
        'clearable' => TRUE,
      ]),
      'programmkosten/arbeitsmaterial' => new CostItemType([
        'name' => 'programmkosten/arbeitsmaterial',
        'label' => 'Arbeitsmaterial',
        'clearable' => TRUE,
      ]),
      'programmkosten/fahrt' => new CostItemType([
        'name' => 'programmkosten/fahrt',
        'label' => 'Programmfahrtkosten',
        'clearable' => TRUE,
      ]),
      'sonstigeKosten' => new CostItemType([
        'name' => 'sonstigeKosten',
        'label' => 'Sonstige Kosten',
        'clearable' => TRUE,
      ]),
      'sonstigeAusgabe' => new CostItemType([
        'name' => 'sonstigeAusgabe',
        'label' => 'Sonstige Ausgabe',
        'clearable' => TRUE,
      ]),
      'zuschlagsrelevanteKosten/programmabsprachen' => new CostItemType([
        'name' => 'zuschlagsrelevanteKosten/programmabsprachen',
        'label' => 'Programmabsprachen (Telefon, Porto, Kopien, Internet etc.)',
        'clearable' => TRUE,
      ]),
      'zuschlagsrelevanteKosten/vorbereitungsmaterial' => new CostItemType([
        'name' => 'zuschlagsrelevanteKosten/vorbereitungsmaterial',
        'label' => 'Erstellung von Vorbereitungsmaterial',
        'clearable' => TRUE,
      ]),
      'zuschlagsrelevanteKosten/veroeffentlichungen' => new CostItemType([
        'name' => 'zuschlagsrelevanteKosten/veroeffentlichungen',
        'label' => <<<'EOD'
Veröffentlichungen, Publikationen, Videos, Fotos etc. als Dokumentation der Ergebnisse und für die Öffentlichkeitsarbeit
EOD,
        'clearable' => TRUE,
      ]),
      'zuschlagsrelevanteKosten/honorare' => new CostItemType([
        'name' => 'zuschlagsrelevanteKosten/honorare',
        'label' => 'Honorare für Vorträge, die der Vorbereitung der Gruppe dienen (nur im Inland)',
        'clearable' => TRUE,
      ]),
      'zuschlagsrelevanteKosten/fahrtkostenUndVerpflegung' => new CostItemType([
        'name' => 'zuschlagsrelevanteKosten/fahrtkostenUndVerpflegung',
        'label' => 'Fahrtkosten und Verpflegung, ggf. Übernachtung bei überregionaler TN-Zusammensetzung',
        'clearable' => TRUE,
      ]),
      'zuschlagsrelevanteKosten/reisekosten' => new CostItemType([
        'name' => 'zuschlagsrelevanteKosten/reisekosten',
        'label' => 'Reise-/Fahrtkosten für interne Koordination und Organisation der Vor- und Nachbereitung',
        'clearable' => TRUE,
      ]),
      'zuschlagsrelevanteKosten/miete' => new CostItemType([
        'name' => 'zuschlagsrelevanteKosten/miete',
        'label' => 'Raum-, Materialmiete (techn. Geräte, Beamer, Flipchart etc.)',
        'clearable' => TRUE,
      ]),
    ];
  }

  /**
   * @inheritDoc
   */
  public function getFundingCaseActions(): array {
    return [];
  }

  /**
   * @inheritDoc
   */
  public function getResourcesItemTypes(): array {
    return $this->resourcesItemTypes ??= [
      'teilnehmerbeitraege' => new ResourcesItemType([
        'name' => 'teilnehmerbeitraege',
        'label' => 'Teilnehmer*innenbeiträge',
        'clearable' => TRUE,
        'paymentPartyLabel' => 'Fördernde Stelle',
      ]),
      'eigenmittel' => new ResourcesItemType([
        'name' => 'eigenmittel',
        'label' => 'Eigenmittel',
        'clearable' => TRUE,
        'paymentPartyLabel' => 'Fördernde Stelle',
      ]),
      'oeffentlicheMittel/europa' => new ResourcesItemType([
        'name' => 'oeffentlicheMittel/europa',
        'label' => 'Finanzierung durch Europa-Mittel',
        'clearable' => TRUE,
        'paymentPartyLabel' => 'Fördernde Stelle',
      ]),
      'oeffentlicheMittel/bundeslaender' => new ResourcesItemType([
        'name' => 'oeffentlicheMittel/bundeslaender',
        'label' => 'Finanzierung durch Bundesländer',
        'clearable' => TRUE,
        'paymentPartyLabel' => 'Fördernde Stelle',
      ]),
      'oeffentlicheMittel/staedteUndKreise' => new ResourcesItemType([
        'name' => 'oeffentlicheMittel/staedteUndKreise',
        'label' => 'Finanzierung durch Städte und Kreise',
        'clearable' => TRUE,
        'paymentPartyLabel' => 'Fördernde Stelle',
      ]),
      'sonstigeMittel' => new ResourcesItemType([
        'name' => 'sonstigeMittel',
        'label' => 'Sonstige Mittel',
        'clearable' => TRUE,
        'paymentPartyLabel' => 'Fördernde Stelle',
      ]),
    ];
  }

}
