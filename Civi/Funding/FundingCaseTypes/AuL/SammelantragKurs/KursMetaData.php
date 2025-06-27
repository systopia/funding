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

namespace Civi\Funding\FundingCaseTypes\AuL\SammelantragKurs;

use Civi\Funding\FundingCaseType\MetaData\AbstractFundingCaseTypeMetaData;
use Civi\Funding\FundingCaseType\MetaData\CostItemType;
use Civi\Funding\FundingCaseType\MetaData\DefaultApplicationProcessStatuses;
use Civi\Funding\FundingCaseType\MetaData\ResourcesItemType;
use Civi\Funding\FundingCaseType\MetaData\ReworkApplicationProcessStatuses;

final class KursMetaData extends AbstractFundingCaseTypeMetaData {

  public const NAME = KursConstants::FUNDING_CASE_TYPE_NAME;

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
  public function getApplicationProcessStatuses(): array {
    return DefaultApplicationProcessStatuses::getAll() + ReworkApplicationProcessStatuses::getAll();
  }

  /**
   * @inheritDoc
   */
  public function getCostItemTypes(): array {
    return $this->costItemTypes ??= [
      'teilnehmerkosten' => new CostItemType('teilnehmerkosten', 'Teilnehmerkosten'),
      'fahrtkosten' => new CostItemType('fahrtkosten', 'Fahrtkosten'),
      'honorarkosten' => new CostItemType('honorarkosten', 'Honorarkosten'),
      'sonstigeAusgaben' => new CostItemType('sonstigeAusgaben', 'Sonstige Ausgaben'),
    ];
  }

  /**
   * @inheritDoc
   */
  public function getResourcesItemTypes(): array {
    return $this->resourcesItemTypes ??= [
      'teilnehmerbeitraege' => new ResourcesItemType('teilnehmerbeitraege', 'Teilnehmer*innenbeiträge'),
      'eigenmittel' => new ResourcesItemType('eigenmittel', 'Eigenmittel'),
      'oeffentlicheMittel/europa' => new ResourcesItemType('oeffentlicheMittel/europa', 'Öffentliche Mittel'),
      'oeffentlicheMittel/bundeslaender' => new ResourcesItemType(
        'oeffentlicheMittel/bundeslaender',
        'Öffentliche Mittel'
      ),
      'oeffentlicheMittel/staedteUndKreise' => new ResourcesItemType(
        'oeffentlicheMittel/staedteUndKreise',
        'Öffentliche Mittel'
      ),
      'sonstigeMittel' => new ResourcesItemType('sonstigeMittel', 'Sonstige Mittel'),
    ];
  }

}
