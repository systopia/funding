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

namespace Civi\Funding\ClearingProcess\Api4\ActionHandler;

use Civi\API\Exception\UnauthorizedException;
use Civi\Api4\FundingClearingProcess;
use Civi\Funding\Api4\Action\FundingClearingProcess\SetContentReviewerAction;
use Civi\Funding\ClearingProcess\ClearingProcessBundleLoader;
use Civi\Funding\ClearingProcess\ClearingProcessPermissions;
use Civi\Funding\Entity\FundingCaseEntity;
use Civi\Funding\Permission\FundingCase\FundingCaseContactsLoaderInterface;
use Civi\RemoteTools\ActionHandler\ActionHandlerInterface;
use Civi\RemoteTools\Api4\Api4Interface;
use CRM_Funding_ExtensionUtil as E;
use Webmozart\Assert\Assert;

final class SetContentReviewerActionHandler implements ActionHandlerInterface {

  public const ENTITY_NAME = 'FundingClearingProcess';

  private Api4Interface $api4;

  private ClearingProcessBundleLoader $clearingProcessBundleLoader;

  private FundingCaseContactsLoaderInterface $fundingCaseContactsLoader;

  public function __construct(
    Api4Interface $api4,
    ClearingProcessBundleLoader $clearingProcessBundleLoader,
    FundingCaseContactsLoaderInterface $fundingCaseContactsLoader
  ) {
    $this->api4 = $api4;
    $this->clearingProcessBundleLoader = $clearingProcessBundleLoader;
    $this->fundingCaseContactsLoader = $fundingCaseContactsLoader;
  }

  /**
   * @phpstan-return array{}
   *
   * @throws \CRM_Core_Exception
   */
  public function setContentReviewer(SetContentReviewerAction $action): array {
    $clearingProcessBundle = $this->clearingProcessBundleLoader->get($action->getClearingProcessId());
    Assert::notNull(
      $clearingProcessBundle,
      sprintf('Clearing process with ID %d not found', $action->getClearingProcessId())
    );

    if (!ClearingProcessPermissions::hasReviewContentPermission(
      $clearingProcessBundle->getFundingCase()->getPermissions()
    )) {
      throw new UnauthorizedException(E::ts('Permission to change content reviewer is missing.'));
    }

    $this->validateContactId($action->getReviewerContactId(), $clearingProcessBundle->getFundingCase());

    $this->api4->updateEntity(
      FundingClearingProcess::getEntityName(),
      $clearingProcessBundle->getClearingProcess()->getId(),
      ['reviewer_cont_contact_id' => $action->getReviewerContactId()]
    );

    return [];
  }

  private function validateContactId(int $contactId, FundingCaseEntity $fundingCase): void {
    $possibleReviewers = $this->fundingCaseContactsLoader->getContactsWithAnyPermission(
      $fundingCase,
      [ClearingProcessPermissions::REVIEW_CONTENT],
    );

    if (!isset($possibleReviewers[$contactId])) {
      throw new \InvalidArgumentException(
        E::ts('Contact %1 is not allowed as content reviewer.', [1 => $contactId]),
      );
    }
  }

}
