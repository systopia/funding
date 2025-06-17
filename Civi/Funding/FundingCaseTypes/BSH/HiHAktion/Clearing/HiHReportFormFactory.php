<?php
/*
 * Copyright (C) 2025 SYSTOPIA GmbH
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

declare(strict_types = 1);

namespace Civi\Funding\FundingCaseTypes\BSH\HiHAktion\Clearing;

use Civi\Funding\ClearingProcess\Form\ReportForm;
use Civi\Funding\ClearingProcess\Form\ReportFormFactoryInterface;
use Civi\Funding\ClearingProcess\Form\ReportFormInterface;
use Civi\Funding\Entity\ClearingProcessEntityBundle;
use Civi\Funding\FundingCaseTypes\BSH\HiHAktion\Traits\HiHSupportedFundingCaseTypesTrait;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsGroup;
use Civi\RemoteTools\JsonSchema\JsonSchema;
use Civi\RemoteTools\JsonSchema\JsonSchemaObject;
use Civi\RemoteTools\JsonSchema\JsonSchemaString;

final class HiHReportFormFactory implements ReportFormFactoryInterface {

  use HiHSupportedFundingCaseTypesTrait;

  /**
   * @inheritDoc
   */
  public function createReportForm(ClearingProcessEntityBundle $clearingProcessBundle): ReportFormInterface {
    return new ReportForm(
      new JsonSchemaObject([
        'reportData' => new JsonSchemaObject([
          'personalkostenKommentar' => new JsonSchemaString(['minLength' => 1]),
        ], [
          'required' => ['personalkostenKommentar'],
          '$limitValidation' => new JsonSchemaObject([
            '_action' => JsonSchema::fromArray(['enum' => ['save']]),
          ]),
        ]),
      ]),
      new JsonFormsGroup('Sachbericht', []));
  }

}
