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

namespace Civi\Funding\ApplicationProcess\Validator;

use Civi\API\Exception\UnauthorizedException;
use Civi\Funding\Entity\AbstractEntity;
use Civi\Funding\Entity\ApplicationProcessEntity;
use Civi\Funding\Entity\FundingCaseEntity;
use Civi\Funding\FundingCase\FundingCaseManager;
use Civi\Funding\Permission\FundingCase\FundingCaseContactsLoaderInterface;
use Civi\Funding\Validation\ConcreteEntityValidatorInterface;
use Civi\Funding\Validation\EntityValidationError;
use Civi\Funding\Validation\EntityValidationResult;
use CRM_Funding_ExtensionUtil as E;
use Webmozart\Assert\Assert;

/**
 * @implements ConcreteEntityValidatorInterface<ApplicationProcessEntity>
 */
final class ReviewerCalculativeContactValidator implements ConcreteEntityValidatorInterface {

  private FundingCaseContactsLoaderInterface $fundingCaseContactsLoader;

  private FundingCaseManager $fundingCaseManager;

  /**
   * @inheritDoc
   */
  public static function getEntityClass(): string {
    return ApplicationProcessEntity::class;
  }

  public function __construct(
    FundingCaseContactsLoaderInterface $fundingCaseContactsLoader,
    FundingCaseManager $fundingCaseManager
  ) {
    $this->fundingCaseContactsLoader = $fundingCaseContactsLoader;
    $this->fundingCaseManager = $fundingCaseManager;
  }

  /**
   * @inheritDoc
   *
   * @param \Civi\Funding\Entity\ApplicationProcessEntity $new
   * @param \Civi\Funding\Entity\ApplicationProcessEntity $current
   *
   * phpcs:disable Drupal.Commenting.FunctionComment.IncorrectTypeHint
   */
  public function validate(AbstractEntity $new, AbstractEntity $current): EntityValidationResult {
    if ($new->getReviewerCalculativeContactId() === $current->getReviewerCalculativeContactId()) {
      return EntityValidationResult::new();
    }

    $fundingCase = $this->fundingCaseManager->get($new->getFundingCaseId());
    Assert::notNull($fundingCase);
    $this->assertPermission($fundingCase);

    if (NULL === $new->getReviewerCalculativeContactId()) {
      return EntityValidationResult::new();
    }

    return $this->validateContactId($new->getReviewerCalculativeContactId(), $fundingCase);
  }

  /**
   * @inheritDoc
   *
   * @param \Civi\Funding\Entity\ApplicationProcessEntity $new
   */
  public function validateNew(AbstractEntity $new): EntityValidationResult {
    if (NULL === $new->getReviewerCalculativeContactId()) {
      return EntityValidationResult::new();
    }

    $fundingCase = $this->fundingCaseManager->get($new->getFundingCaseId());
    Assert::notNull($fundingCase);
    $this->assertPermission($fundingCase);

    return $this->validateContactId($new->getReviewerCalculativeContactId(), $fundingCase);
  }

  private function assertPermission(FundingCaseEntity $fundingCase): void {
    if (!$fundingCase->hasPermission('review_calculative')) {
      throw new UnauthorizedException(E::ts('Permission to change calculative reviewer is missing.'));
    }
  }

  private function validateContactId(int $contactId, FundingCaseEntity $fundingCase): EntityValidationResult {
    $possibleReviewers = $this->fundingCaseContactsLoader->getContactsWithPermission(
      $fundingCase,
      'review_calculative',
    );
    if (isset($possibleReviewers[$contactId])) {
      return EntityValidationResult::new();
    }

    return EntityValidationResult::new(
      EntityValidationError::new(
        'reviewer_calc_contact_id',
        E::ts('Contact %1 is not allowed as calculative reviewer.', [1 => $contactId]),
      )
    );
  }

}
