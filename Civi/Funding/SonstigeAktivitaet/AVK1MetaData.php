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

namespace Civi\Funding\SonstigeAktivitaet;

use Civi\Funding\FundingCaseType\MetaData\AbstractFundingCaseTypeMetaData;
use Civi\Funding\FundingCaseType\MetaData\CostItemType;
use Civi\Funding\FundingCaseType\MetaData\ResourcesItemType;

final class AVK1MetaData extends AbstractFundingCaseTypeMetaData {

  public const NAME = AVK1Constants::FUNDING_CASE_TYPE_NAME;

  public function getName(): string {
    return self::NAME;
  }

  /**
   * @phpstan-var array<string, CostItemType>
   */
  private ?array $costItemTypes = NULL;

  /**
   * @phpstan-var array<string, ResourcesItemType>
   */
  private ?array $resourcesItemTypes = NULL;

  /**
   * @inheritDoc
   */
  public function getCostItemTypes(): array {
    return $this->costItemTypes ??= [
      'unterkunftUndVerpflegung' => new CostItemType('unterkunftUndVerpflegung', 'Unterkunft und Verpflegung'),
      'honorar' => new CostItemType('honorar', 'Honorar'),
      'fahrtkosten/intern' => new CostItemType('fahrtkosten/intern', 'Fahrtkosten'),
      'fahrtkosten/anTeilnehmerErstattet' => new CostItemType(
        'fahrtkosten/anTeilnehmerErstattet',
        'An Teilnehmer*innen/Referent*innen erstattete Fahrtkosten'
      ),
      'sachkosten/ausstattung' => new CostItemType('sachkosten/ausstattung', 'Sachkosten'),
      'sonstigeAusgabe' => new CostItemType('sonstigeAusgabe', 'Sonstige Ausgabe'),
      'versicherung/teilnehmer' => new CostItemType(
        'versicherung/teilnehmer',
        'Kosten der Versicherung der Teilnehmer*innen'
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
