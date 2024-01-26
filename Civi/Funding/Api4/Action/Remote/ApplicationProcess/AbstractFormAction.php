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

namespace Civi\Funding\Api4\Action\Remote\ApplicationProcess;

use Civi\Api4\RemoteFundingApplicationProcess;
use Civi\Core\CiviEventDispatcherInterface;
use Civi\Funding\Api4\Action\Remote\AbstractRemoteFundingActionLegacy;
use Civi\Funding\Api4\Action\Remote\Traits\RemoteFundingActionContactIdRequiredTrait;
use Civi\Funding\ApplicationProcess\ApplicationProcessBundleLoader;
use Civi\Funding\Event\Remote\FundingEvents;
use CRM_Funding_ExtensionUtil as E;
use Webmozart\Assert\Assert;

abstract class AbstractFormAction extends AbstractRemoteFundingActionLegacy {

  use RemoteFundingActionContactIdRequiredTrait;

  protected ApplicationProcessBundleLoader $_applicationProcessBundleLoader;

  public function __construct(
    string $actionName,
    ApplicationProcessBundleLoader $applicationProcessBundleLoader,
    CiviEventDispatcherInterface $eventDispatcher
  ) {
    parent::__construct(RemoteFundingApplicationProcess::getEntityName(), $actionName);
    $this->_applicationProcessBundleLoader = $applicationProcessBundleLoader;
    $this->_eventDispatcher = $eventDispatcher;
    $this->_authorizeRequestEventName = FundingEvents::REQUEST_AUTHORIZE_EVENT_NAME;
    $this->_initRequestEventName = FundingEvents::REQUEST_INIT_EVENT_NAME;
  }

  /**
   * @phpstan-return array<string, mixed>
   *
   * @throws \CRM_Core_Exception
   */
  protected function createEventParams(int $applicationProcessId): array {
    Assert::notNull($this->remoteContactId);

    $applicationProcessBundle = $this->_applicationProcessBundleLoader->get($applicationProcessId);
    Assert::notNull(
      $applicationProcessBundle,
      E::ts('Application process with ID "%1" not found', [1 => $applicationProcessId])
    );

    return $this->getExtraParams() + [
      'applicationProcessBundle' => $applicationProcessBundle,
    ];
  }

}
