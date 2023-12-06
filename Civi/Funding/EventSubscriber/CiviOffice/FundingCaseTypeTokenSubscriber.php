<?php
/*
 * Copyright (C) 2023 SYSTOPIA GmbH
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

namespace Civi\Funding\EventSubscriber\CiviOffice;

use Civi\Api4\FundingCaseType;
use Civi\Funding\DocumentRender\CiviOffice\AbstractCiviOfficeTokenSubscriber;
use Civi\Funding\DocumentRender\CiviOffice\CiviOfficeContextDataHolder;
use Civi\Funding\DocumentRender\Token\TokenNameExtractorInterface;
use Civi\Funding\DocumentRender\Token\TokenResolverInterface;
use Civi\Funding\Entity\AbstractEntity;
use Civi\Funding\FundingProgram\FundingCaseTypeManager;

/**
 * @phpstan-extends AbstractCiviOfficeTokenSubscriber<\Civi\Funding\Entity\FundingCaseTypeEntity>
 * @codeCoverageIgnore
 */
class FundingCaseTypeTokenSubscriber extends AbstractCiviOfficeTokenSubscriber {

  private FundingCaseTypeManager $fundingCaseTypeManager;

  /**
   * @phpstan-param TokenResolverInterface<\Civi\Funding\Entity\FundingCaseTypeEntity> $tokenResolver
   */
  public function __construct(
    FundingCaseTypeManager $fundingCaseTypeManager,
    CiviOfficeContextDataHolder $contextDataHolder,
    TokenResolverInterface $tokenResolver,
    TokenNameExtractorInterface $tokenNameExtractor
  ) {
    parent::__construct(
      $contextDataHolder,
      $tokenResolver,
      $tokenNameExtractor
    );
    $this->fundingCaseTypeManager = $fundingCaseTypeManager;
  }

  protected function getEntity(int $id): ?AbstractEntity {
    return $this->fundingCaseTypeManager->get($id);
  }

  protected function getApiEntityName(): string {
    return FundingCaseType::getEntityName();
  }

}
