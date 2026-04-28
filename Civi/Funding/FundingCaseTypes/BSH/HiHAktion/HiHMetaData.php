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
use Civi\Funding\FundingCaseType\MetaData\ApplicationProcessAction;
use Civi\Funding\FundingCaseType\MetaData\ApplicationProcessStatus;
use Civi\Funding\FundingCaseType\MetaData\CostItemType;
use Civi\Funding\FundingCaseType\MetaData\DefaultApplicationProcessActions;
use Civi\Funding\FundingCaseType\MetaData\DefaultApplicationProcessStatuses;

final class HiHMetaData extends AbstractFundingCaseTypeMetaData {

  public const NAME = HiHConstants::FUNDING_CASE_TYPE_NAME;

  /**
   * @var non-empty-array<string, ApplicationProcessAction>|null
   */
  private ?array $applicationProcessActions = NULL;

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
  public function getApplicationProcessActions(): array {
    return $this->applicationProcessActions ??= [
      // Applicant actions.
      'save' => DefaultApplicationProcessActions::save(),
      'modify' => DefaultApplicationProcessActions::modify(),
      'apply' => DefaultApplicationProcessActions::apply(),
      'withdraw' => DefaultApplicationProcessActions::withdraw(),
      'delete' => DefaultApplicationProcessActions::delete(),
      // Reviewer actions.
      'review' => DefaultApplicationProcessActions::review(),
      'release' => new ApplicationProcessAction([
        'name' => 'release',
        'label' => 'Für Beirat freigeben',
        'batchPossible' => TRUE,
      ]),
      'request-change' => DefaultApplicationProcessActions::requestChange(),
      'reject' => DefaultApplicationProcessActions::reject(),
      // Admin actions.
      're-apply' => new ApplicationProcessAction([
        'name' => 're-apply',
        'label' => 'Zurück zu "beantragt"',
        'batchPossible' => TRUE,
      ]),
      're-release' => new ApplicationProcessAction([
        'name' => 're-release',
        'label' => 'Erneut für Beirat freigeben',
        'batchPossible' => TRUE,
      ]),
      'approve' => DefaultApplicationProcessActions::approve('Bewilligen'),
      'approve-update' => new ApplicationProcessAction([
        'name' => 'approve-update',
        'label' => 'Bewilligung aktualisieren',
        'batchPossible' => TRUE,
      ]),
    ];
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
      'personalkosten' => new CostItemType([
        'name' => 'personalkosten',
        'label' => 'Personalkosten',
        'clearable' => FALSE,
      ]),
      'honorar' => new CostItemType([
        'name' => 'honorar',
        'label' => 'Honorar',
        'clearable' => FALSE,
      ]),
      'sachkosten.materialien' => new CostItemType([
        'name' => 'sachkosten.materialien',
        'label' => 'Sachkosten (Materialien)',
        'clearable' => FALSE,
      ]),
      'sachkosten.ehrenamtspauschalen' => new CostItemType([
        'name' => 'sachkosten.ehrenamtspauschalen',
        'label' => 'Sachkosten (Ehrenamtspauschalen)',
        'clearable' => FALSE,
      ]),
      'sachkosten.verpflegung' => new CostItemType([
        'name' => 'sachkosten.verpflegung',
        'label' => 'Sachkosten (Verpflegung)',
        'clearable' => FALSE,
      ]),
      'sachkosten.fahrtkosten' => new CostItemType([
        'name' => 'sachkosten.fahrtkosten',
        'label' => 'Sachkosten (Fahrtkosten)',
        'clearable' => FALSE,
      ]),
      'sachkosten.oeffentlichkeitsarbeit' => new CostItemType([
        'name' => 'sachkosten.oeffentlichkeitsarbeit',
        'label' => 'Sachkosten (Öffentlichkeitsarbeit)',
        'clearable' => FALSE,
      ]),
      'sachkosten.investitionen' => new CostItemType([
        'name' => 'sachkosten.investitionen',
        'label' => 'Sachkosten (Investitionen)',
        'clearable' => FALSE,
      ]),
      'sachkosten.mieten' => new CostItemType([
        'name' => 'sachkosten.mieten',
        'label' => 'Sachkosten (Mieten)',
        'clearable' => FALSE,
      ]),
      'sachkosten.sonstige' => new CostItemType([
        'name' => 'sachkosten.sonstige',
        'label' => 'Sachkosten (Sonstige)',
        'clearable' => FALSE,
      ]),
      // Special type used in JSON schema only for admins only.
      'bewilligt' => new CostItemType([
        'name' => 'bewilligt',
        'label' => 'Bewilligt',
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
