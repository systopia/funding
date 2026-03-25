<?php
/*
 * Copyright (C) 2026 SYSTOPIA GmbH
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

declare(strict_types=1);

namespace Civi\Funding\FundingCaseTypes\AuL\Personalkosten\Clearing;

use Civi\Funding\ApplicationProcess\ApplicationCostItemManager;
use Civi\Funding\ClearingProcess\Form\ReportForm;
use Civi\Funding\ClearingProcess\Form\ReportFormFactoryInterface;
use Civi\Funding\ClearingProcess\Form\ReportFormInterface;
use Civi\Funding\Entity\ApplicationCostItemEntity;
use Civi\Funding\Entity\ClearingProcessEntityBundle;
use Civi\Funding\Entity\FundingCaseTypeEntity;
use Civi\Funding\Entity\FundingProgramEntity;
use Civi\Funding\FundingCaseTypes\AuL\Personalkosten\Application\Data\PersonalkostenDokumenteFactory;
use Civi\Funding\FundingCaseTypes\AuL\Personalkosten\Clearing\JsonSchema\PersonalkostenClearingCostItemsJsonSchema;
use Civi\Funding\FundingCaseTypes\AuL\Personalkosten\Clearing\JsonSchema\PersonalkostenReportDataJsonSchema;
use Civi\Funding\FundingCaseTypes\AuL\Personalkosten\Clearing\UiSchema\PersonalkostenClearingDokumenteUiSchema;
use Civi\Funding\FundingCaseTypes\AuL\Personalkosten\Traits\PersonalkostenSupportedFundingCaseTypesTrait;
use Civi\RemoteTools\JsonForms\JsonFormsControl;
use Civi\RemoteTools\JsonForms\JsonFormsMarkup;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsCategorization;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsCategory;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsGroup;

final class PersonalkostenReportFormFactory implements ReportFormFactoryInterface {

  use PersonalkostenSupportedFundingCaseTypesTrait;

  private ApplicationCostItemManager $applicationCostItemManager;

  private PersonalkostenDokumenteFactory $dokumenteFactory;

  public function __construct(ApplicationCostItemManager $applicationCostItemManager, PersonalkostenDokumenteFactory $dokumenteFactory) {
    $this->applicationCostItemManager = $applicationCostItemManager;
    $this->dokumenteFactory = $dokumenteFactory;
  }

  /**
   * @inheritDoc
   */
  public function createReportForm(ClearingProcessEntityBundle $clearingProcessBundle): ReportFormInterface {
    return $this->doCreateReportForm($clearingProcessBundle->getFundingProgram());
  }

  /**
   * @inheritDoc
   */
  public function createReportFormForTranslation(
    FundingProgramEntity $fundingProgram,
    FundingCaseTypeEntity $fundingCaseType
  ): ReportFormInterface {
    return $this->doCreateReportForm($fundingProgram);
  }

  public function doCreateReportForm(
    FundingProgramEntity $fundingProgram,
  ): ReportFormInterface {
    $scopePrefix = '#/properties/reportData/properties';

    return new ReportForm(
      new PersonalkostenReportDataJsonSchema(
        $fundingProgram->getStartDate(),
        $fundingProgram->getEndDate(),
      ),
      new JsonFormsCategorization([
        new JsonFormsCategory('Grunddaten', [
          new JsonFormsGroup('Infrastrukturstelle von', [
            new JsonFormsControl("$scopePrefix/internerBezeichner", 'Interner Bezeichner'),
            new JsonFormsControl("$scopePrefix/name", 'Name'),
            new JsonFormsControl("$scopePrefix/vorname", 'Vorname'),
            new JsonFormsControl("$scopePrefix/tarifUndEingruppierung", 'Tarif und Eingruppierung'),
            new JsonFormsControl("$scopePrefix/beginn", 'Beschäftigungszeitraum von'),
            new JsonFormsControl("$scopePrefix/ende", 'Beschäftigungszeitraum bis'),
            new JsonFormsControl("$scopePrefix/personalkostenBeantragt", 'Personalkostenförderung beantragt'),
            new JsonFormsControl("$scopePrefix/sachkostenpauschale", 'Sachkostenpauschale'),
            new JsonFormsControl("$scopePrefix/titel", 'Titel'),
            new JsonFormsControl("$scopePrefix/kurzbeschreibung", 'Kurzbeschreibung'),
          ]),
          new PersonalkostenClearingDokumenteUiSchema("$scopePrefix/dokumente"),
        ]),
      ]),
    );
  }

}
