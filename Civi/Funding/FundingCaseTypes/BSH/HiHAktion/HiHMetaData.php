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
use Civi\Funding\FundingCaseType\MetaData\ApplicationProcessStatus;
use Civi\Funding\FundingCaseType\MetaData\CostItemType;
use Civi\Funding\FundingCaseType\MetaData\DefaultApplicationProcessStatuses;

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
  public function getApplicationProcessStatuses(): array {
    return [
      'advisory' => new ApplicationProcessStatus([
        'name' => 'advisory',
        'label' => 'Beirat',
        'icon' => 'fa-eye',
        'eligible' => TRUE,
        'clearingAvailable' => FALSE,
      ]),
      'applied' => DefaultApplicationProcessStatuses::applied(),
      'approved' => new ApplicationProcessStatus([
        'name' => 'approved',
        'label' => 'bewilligt',
        'icon' => 'fa-check-circle-o',
        'iconColor' => '#56ab41',
        'eligible' => TRUE,
      ]),
      'approved_partial' => new ApplicationProcessStatus([
        'name' => 'approved_partial',
        'label' => 'teilbewilligt',
        'icon' => 'fa-check-circle-o',
        'iconColor' => '#56ab41',
        'eligible' => TRUE,
      ]),
      'complete' => DefaultApplicationProcessStatuses::complete(),
      'draft' => DefaultApplicationProcessStatuses::draft(),
      'new' => DefaultApplicationProcessStatuses::new(),
      'rejected' => DefaultApplicationProcessStatuses::rejected('rejected', 'sachlich abgelehnt'),
      'rejected_after_advisory' => DefaultApplicationProcessStatuses::rejected(
        'rejected_after_advisory', 'abgelehnt nach Beirat'
      ),
      'review' => DefaultApplicationProcessStatuses::review(),
    ];
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

  /**
   * @inheritDoc
   */
  public function isGeneralClearingAdmitAllowed(): bool {
    return TRUE;
  }

  /**
   * @inheritDoc
   */
  public function isFinalDrawdownAcceptedByDefault(): bool {
    return FALSE;
  }

}
