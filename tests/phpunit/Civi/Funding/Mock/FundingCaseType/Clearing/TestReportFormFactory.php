<?php
/*
 * Copyright (C) 2024 SYSTOPIA GmbH
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

namespace Civi\Funding\Mock\FundingCaseType\Clearing;

use Civi\Funding\ClearingProcess\Form\ReportForm;
use Civi\Funding\ClearingProcess\Form\ReportFormFactoryInterface;
use Civi\Funding\ClearingProcess\Form\ReportFormInterface;
use Civi\Funding\Entity\ClearingProcessEntityBundle;
use Civi\Funding\Entity\FundingCaseTypeEntity;
use Civi\Funding\Entity\FundingProgramEntity;
use Civi\Funding\Mock\FundingCaseType\Traits\TestSupportedFundingCaseTypesTrait;
use Civi\RemoteTools\JsonForms\JsonFormsControl;
use Civi\RemoteTools\JsonForms\JsonFormsMarkup;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsGroup;
use Civi\RemoteTools\JsonSchema\JsonSchemaObject;
use Civi\RemoteTools\JsonSchema\JsonSchemaString;

final class TestReportFormFactory implements ReportFormFactoryInterface {

  use TestSupportedFundingCaseTypesTrait;

  public function createReportForm(ClearingProcessEntityBundle $clearingProcessBundle): ReportFormInterface {
    return $this->doCreateReportForm();
  }

  public function createReportFormForTranslation(
    FundingProgramEntity $fundingProgram,
    FundingCaseTypeEntity $fundingCaseType
  ): ReportFormInterface {
    return $this->doCreateReportForm();
  }

  private function doCreateReportForm(): ReportFormInterface {
    $jsonSchema = new JsonSchemaObject([
      'reportData' => new JsonSchemaObject([
        'foo' => new JsonSchemaString(),
        'file' => new JsonSchemaString([
          'format' => 'uri',
          '$tag' => 'externalFile',
        ]),
      ]),
    ]);

    $uiSchema = new JsonFormsGroup('Report', [
      new JsonFormsControl('#/properties/reportData/properties/foo', 'Foo'),
    ]);

    $receiptsPrependUiSchema = new JsonFormsMarkup('test');
    $receiptsAppendUiSchema = new JsonFormsMarkup('test');

    return new ReportForm($jsonSchema, $uiSchema, $receiptsPrependUiSchema, $receiptsAppendUiSchema);
  }

}
