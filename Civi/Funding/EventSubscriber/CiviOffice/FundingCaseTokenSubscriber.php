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

use Civi\Api4\FundingCase;
use Civi\Funding\DocumentRender\CiviOffice\AbstractCiviOfficeTokenSubscriber;
use Civi\Funding\DocumentRender\CiviOffice\CiviOfficeContextDataHolder;
use Civi\Funding\DocumentRender\Token\TokenNameExtractorInterface;
use Civi\Funding\DocumentRender\Token\TokenResolverInterface;
use Civi\Funding\Entity\AbstractEntity;
use Civi\Funding\FundingCase\FundingCaseManager;

/**
 * @phpstan-extends AbstractCiviOfficeTokenSubscriber<\Civi\Funding\Entity\FundingCaseEntity>
 * @codeCoverageIgnore
 */
class FundingCaseTokenSubscriber extends AbstractCiviOfficeTokenSubscriber {

  private FundingCaseManager $fundingCaseManager;

  public static function getPriority(): int {
    return max(
      FundingCaseTypeTokenSubscriber::getPriority() + 1,
      FundingProgramTokenSubscriber::getPriority() + 1,
    );
  }

  /**
   * @phpstan-param TokenResolverInterface<\Civi\Funding\Entity\FundingCaseEntity> $tokenResolver
   */
  public function __construct(
    FundingCaseManager $fundingCaseManager,
    CiviOfficeContextDataHolder $contextDataHolder,
    TokenResolverInterface $tokenResolver,
    TokenNameExtractorInterface $tokenNameExtractor
  ) {
    parent::__construct(
      $contextDataHolder,
      $tokenResolver,
      $tokenNameExtractor
    );
    $this->fundingCaseManager = $fundingCaseManager;
  }

  protected function getApiEntityName(): string {
    return FundingCase::getEntityName();
  }

  /**
   * @inheritDoc
   */
  protected function getRelatedContextSchemas(): array {
    return [
      'fundingCaseTypeId',
      'fundingProgramId',
      'contactId',
    ];
  }

  /**
   * @inheritDoc
   */
  protected function getRelatedContextValues(AbstractEntity $entity): array {
    return [
      'fundingCaseTypeId' => $entity->getFundingCaseTypeId(),
      'fundingProgramId' => $entity->getFundingProgramId(),
      'contactId' => $entity->getRecipientContactId(),
    ];
  }

  protected function loadEntity(int $id): ?AbstractEntity {
    return $this->fundingCaseManager->get($id);
  }

}
