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
use Civi\Funding\FundingCaseType\MetaData\ResourcesItemType;

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
  public function getCostItemTypes(): array {
    return $this->costItemTypes ??= [
      'unterkunftUndVerpflegung' => new CostItemType('unterkunftUndVerpflegung', 'Unterkunft und Verpflegung'),
      'honorar' => new CostItemType('honorar', 'Honorar'),
      'fahrtkosten/flug' => new CostItemType('fahrtkosten/intern', 'Fahrt-/Flugkosten inkl. Transfer zur Unterkunft'),
      'fahrtkosten/anTeilnehmerErstattet' => new CostItemType(
        'fahrtkosten/anTeilnehmerErstattet',
        'An Teilnehmer*innen/Referent*innen erstattete Fahrtkosten'
      ),
      'programmkosten/programmkosten' => new CostItemType('programmkosten/programmkosten', 'Programmkosten'),
      'programmkosten/arbeitsmaterial' => new CostItemType('programmkosten/arbeitsmaterial', 'Arbeitsmaterial'),
      'programmkosten/fahrt' => new CostItemType('programmkosten/fahrt', 'Programmfahrtkosten'),
      'sonstigeKosten' => new CostItemType('sonstigeKosten', 'Sonstige Kosten'),
      'sonstigeAusgabe' => new CostItemType('sonstigeAusgabe', 'Sonstige Ausgabe'),
      'zuschlagsrelevanteKosten/programmabsprachen' => new CostItemType(
        'zuschlagsrelevanteKosten/programmabsprachen',
        'Programmabsprachen (Telefon, Porto, Kopien, Internet etc.)'
      ),
      'zuschlagsrelevanteKosten/veroeffentlichungen' => new CostItemType(
        'zuschlagsrelevanteKosten/veroeffentlichungen',
        <<<'EOD'
Veröffentlichungen, Publikationen, Videos, Fotos etc. als Dokumentation der Ergebnisse und für die Öffentlichkeitsarbeit
EOD
      ),
      'zuschlagsrelevanteKosten/honorare' => new CostItemType(
        'zuschlagsrelevanteKosten/honorare',
        'Honorare für Vorträge, die der Vorbereitung der Gruppe dienen (nur im Inland)'
      ),
      'zuschlagsrelevanteKosten/fahrtkostenUndVerpflegung' => new CostItemType(
        'zuschlagsrelevanteKosten/fahrtkostenUndVerpflegung',
        'Fahrtkosten und Verpflegung, ggf. Übernachtung bei überregionaler TN-Zusammensetzung'
      ),
      'zuschlagsrelevanteKosten/reisekosten' => new CostItemType(
        'zuschlagsrelevanteKosten/reisekosten',
        'Reise-/Fahrtkosten für interne Koordination und Organisation der Vor- und Nachbereitung'
      ),
      'zuschlagsrelevanteKosten/miete' => new CostItemType(
        'zuschlagsrelevanteKosten/miete',
      'Raum-, Materialmiete (techn. Geräte, Beamer, Flipchart etc.)'
      ),
    ];
  }

  /**
   * @inheritDoc
   */
  public function getResourcesItemTypes(): array {
    return $this->resourcesItemTypes ??= [
      'teilnehmerbeitraege' => new ResourcesItemType('teilnehmerbeitraege', 'Teilnehmer*innenbeiträge'),
      'eigenmittel' => new ResourcesItemType('eigenmittel', 'Eigenmittel'),
      'oeffentlicheMittel/europa' => new ResourcesItemType(
        'oeffentlicheMittel/europa',
        'Finanzierung durch Europa-Mittel'
      ),
      'oeffentlicheMittel/bundeslaender' => new ResourcesItemType(
        'oeffentlicheMittel/bundeslaender',
        'Finanzierung durch Bundesländer'
      ),
      'oeffentlicheMittel/staedteUndKreise' => new ResourcesItemType(
        'oeffentlicheMittel/staedteUndKreise',
        'Finanzierung durch Städte und Kreise'
      ),
      'sonstigeMittel' => new ResourcesItemType('sonstigeMittel', 'Sonstige Mittel'),
    ];
  }

}
