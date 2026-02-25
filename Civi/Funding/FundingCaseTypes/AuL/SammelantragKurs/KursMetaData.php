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
use Civi\Funding\FundingCaseType\MetaData\ApplicationProcessAction;
use Civi\Funding\FundingCaseType\MetaData\CostItemType;
use Civi\Funding\FundingCaseType\MetaData\DefaultApplicationProcessActions;
use Civi\Funding\FundingCaseType\MetaData\DefaultApplicationProcessStatuses;
use Civi\Funding\FundingCaseType\MetaData\FundingCaseAction;
use Civi\Funding\FundingCaseType\MetaData\ResourcesItemType;
use Civi\Funding\FundingCaseType\MetaData\ReworkApplicationProcessActions;
use Civi\Funding\FundingCaseType\MetaData\ReworkApplicationProcessStatuses;
use CRM_Funding_ExtensionUtil as E;

final class KursMetaData extends AbstractFundingCaseTypeMetaData {

  public const NAME = KursConstants::FUNDING_CASE_TYPE_NAME;

  /**
   * @var non-empty-array<string, ApplicationProcessAction>|null
   */
  private ?array $applicationProcessActions = NULL;

  /**
   * @phpstan-var array<string, CostItemType>
   */
  private ?array $costItemTypes = NULL;

  /**
   * @var non-empty-array<string, FundingCaseAction>|null
   */
  private ?array $fundingCaseActions = NULL;

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
    return $this->applicationProcessActions ??= [
      'save&new' => new ApplicationProcessAction([
        'name' => 'save&new',
        'label' => 'Speichern und neu',
      ]),
      'save&copy' => new ApplicationProcessAction([
        'name' => 'save&copy',
        'label' => 'Speichern und kopieren',
      ]),
      'save' => new ApplicationProcessAction([
        'name' => 'save',
        'label' => 'Speichern',
      ]),
      'modify' => new ApplicationProcessAction([
        'name' => 'modify',
        'label' => 'Bearbeiten',
      ]),
      'withdraw' => new ApplicationProcessAction([
        'name' => 'withdraw',
        'label' => 'Zurückziehen',
        'confirmMessage' => 'Möchten Sie diesen Kurs wirklich zurückziehen?',
      ]),
      'delete' => new ApplicationProcessAction([
        'name' => 'delete',
        'label' => 'Löschen',
        'confirmMessage' => 'Möchten Sie diesen Kurs wirklich löschen?',
        'delete' => TRUE,
      ]),
      'review' => DefaultApplicationProcessActions::review(),
      'approve-calculative' => DefaultApplicationProcessActions::approveCalculative(),
      'reject-calculative' => DefaultApplicationProcessActions::rejectCalculative(),
      'approve-content' => DefaultApplicationProcessActions::approveContent(),
      'reject-content' => DefaultApplicationProcessActions::rejectContent(),
      'request-change' => DefaultApplicationProcessActions::requestChange(),
      'approve' => DefaultApplicationProcessActions::approve(),
      'reject' => DefaultApplicationProcessActions::reject(),
      'reject-change' => ReworkApplicationProcessActions::rejectChange(),
      'reopen' => new ApplicationProcessAction([
        'name' => 'reopen',
        'label' => E::ts('Reopen'),
        'batchPossible' => TRUE,
      ]),
    ];
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
  public function getFundingCaseActions(): array {
    return $this->fundingCaseActions ??= [
      'save' => new FundingCaseAction(['name' => 'save', 'label' => 'Speichern']),
      'apply' => new FundingCaseAction(['name' => 'apply', 'label' => 'Beantragen']),
      'withdraw' => new FundingCaseAction([
        'name' => 'withdraw',
        'label' => 'Zurückziehen',
        'confirmMessage' => 'Wollen Sie diesen Sammelantrag wirklich zurückziehen?',
      ]),
      'delete' => new FundingCaseAction([
        'name' => 'delete',
        'label' => 'Löschen',
        'confirmMessage' => 'Wollen Sie diesen Sammelantrag wirklich löschen?',
      ]),
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
