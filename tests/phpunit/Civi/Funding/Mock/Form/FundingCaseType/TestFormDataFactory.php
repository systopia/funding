<?php
/*
 * Copyright (C) 2022 SYSTOPIA GmbH
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

namespace Civi\Funding\Mock\Form\FundingCaseType;

use Civi\Funding\ApplicationProcess\ApplicationExternalFileManagerInterface;
use Civi\Funding\Entity\ApplicationProcessEntity;
use Civi\Funding\Entity\FundingCaseEntity;
use Civi\Funding\Form\ApplicationFormDataFactoryInterface;
use Webmozart\Assert\Assert;

final class TestFormDataFactory implements ApplicationFormDataFactoryInterface {

  private ApplicationExternalFileManagerInterface $externalFileManager;

  public static function getSupportedFundingCaseTypes(): array {
    return ['TestCaseType'];
  }

  public function __construct(ApplicationExternalFileManagerInterface $externalFileManager) {
    $this->externalFileManager = $externalFileManager;
  }

  /**
   * @inheritDoc
   */
  public function createFormData(ApplicationProcessEntity $applicationProcess, FundingCaseEntity $fundingCase): array {
    Assert::notNull($applicationProcess->getStartDate());
    Assert::notNull($applicationProcess->getEndDate());

    $data = [
      'title' => $applicationProcess->getTitle(),
      'shortDescription' => $applicationProcess->getShortDescription(),
      'recipient' => $fundingCase->getRecipientContactId(),
      'startDate' => $applicationProcess->getStartDate()->format('Y-m-d'),
      'endDate' => $applicationProcess->getEndDate()->format('Y-m-d'),
      'amountRequested' => $applicationProcess->getAmountRequested(),
      'resources' => $applicationProcess->getRequestData()['resources'],
    ];

    /** @var \Civi\Funding\Entity\ExternalFileEntity $file */
    $file = $this->externalFileManager->getFile('file', $applicationProcess->getId());
    $data['file'] = $file->getUri();

    return $data;
  }

}
