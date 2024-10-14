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

use Civi\Api4\FundingApplicationProcess;
use Civi\Funding\ApplicationProcess\ApplicationProcessManager;
use Civi\Funding\DocumentRender\CiviOffice\AbstractCiviOfficeTokenSubscriber;
use Civi\Funding\DocumentRender\CiviOffice\CiviOfficeContextDataHolder;
use Civi\Funding\DocumentRender\Token\TokenNameExtractorInterface;
use Civi\Funding\DocumentRender\Token\TokenResolverInterface;
use Civi\Funding\Entity\AbstractEntity;
use Civi\Funding\Entity\ApplicationProcessEntity;

/**
 * @phpstan-extends AbstractCiviOfficeTokenSubscriber<\Civi\Funding\Entity\ApplicationProcessEntity>
 * @codeCoverageIgnore
 */
class ApplicationProcessTokenSubscriber extends AbstractCiviOfficeTokenSubscriber {

  private ApplicationProcessManager $applicationProcessManager;

  public static function getPriority(): int {
    return FundingCaseTokenSubscriber::getPriority() + 1;
  }

  /**
   * @phpstan-param TokenResolverInterface<\Civi\Funding\Entity\ApplicationProcessEntity> $tokenResolver
   */
  public function __construct(
    ApplicationProcessManager $applicationProcessManager,
    CiviOfficeContextDataHolder $contextDataHolder,
    TokenResolverInterface $tokenResolver,
    TokenNameExtractorInterface $tokenNameExtractor
  ) {
    parent::__construct(
      $contextDataHolder,
      $tokenResolver,
      $tokenNameExtractor
    );
    $this->applicationProcessManager = $applicationProcessManager;
  }

  protected function getEntity(int $id): ?AbstractEntity {
    return $this->applicationProcessManager->get($id);
  }

  protected function getApiEntityName(): string {
    return FundingApplicationProcess::getEntityName();
  }

  protected function getEntityClass(): string {
    return ApplicationProcessEntity::class;
  }

  /**
   * @inheritDoc
   */
  protected function getRelatedContextSchemas(): array {
    return ['fundingCaseId'];
  }

  /**
   * @inheritDoc
   */
  protected function getRelatedContextValues(AbstractEntity $entity): array {
    return ['fundingCaseId' => $entity->getFundingCaseId()];
  }

}
