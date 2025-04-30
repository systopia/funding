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

namespace Civi\Funding\FundingCaseTypes\BSH\HiHAktion;

use Civi\Funding\FundingCaseType\MetaData\AbstractFundingCaseTypeMetaData;
use Civi\Funding\FundingCaseType\MetaData\CostItemType;

final class HiHMetaData extends AbstractFundingCaseTypeMetaData {

  public const NAME = HiHConstants::FUNDING_CASE_TYPE_NAME;

  /**
   * @phpstan-var array<string, CostItemType>
   */
  private ?array $costItemTypes = NULL;

  public function getName(): string {
    return self::NAME;
  }

  /**
   * @inheritDoc
   */
  public function getCostItemTypes(): array {
    return $this->costItemTypes ??= [
      'personalkosten' => new CostItemType('personalkosten', 'Personalkosten'),
      'honorar' => new CostItemType('honorar', 'Honorar'),
      'sachkosten.materialien' => new CostItemType('sachkosten.materialien', 'Sachkosten (Materialien)'),
      'sachkosten.ehrenamtspauschalen' => new CostItemType(
        'sachkosten.ehrenamtspauschalen', 'Sachkosten (Ehrenamtspauschalen)'
      ),
      'sachkosten.verpflegung' => new CostItemType('sachkosten.verpflegung', 'Sachkosten (Verpflegung)'),
      'sachkosten.fahrtkosten' => new CostItemType('sachkosten.fahrtkosten', 'Sachkosten (Fahrtkosten)'),
      'sachkosten.oeffentlichkeitsarbeit' => new CostItemType(
        'sachkosten.oeffentlichkeitsarbeit', 'Sachkosten (Öffentlichkeitsarbeit)'
      ),
      'sachkosten.investitionen' => new CostItemType('sachkosten.investitionen', 'Sachkosten (Investitionen)'),
      'sachkosten.mieten' => new CostItemType('sachkosten.mieten', 'Sachkosten (Mieten)'),
      'sachkosten.sonstige' => new CostItemType('sachkosten.sonstige', 'Sachkosten (Sonstige)'),
    ];
  }

  /**
   * @inheritDoc
   */
  public function getResourcesItemTypes(): array {
    return [];
  }

}
