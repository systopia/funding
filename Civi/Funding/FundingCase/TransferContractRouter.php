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

namespace Civi\Funding\FundingCase;

use Civi\Funding\Session\FundingSessionInterface;
use Civi\Funding\Util\UrlGenerator;

/**
 * @codeCoverageIgnore
 */
class TransferContractRouter {

  private FundingCaseManager $fundingCaseManager;

  private FundingSessionInterface $session;

  private UrlGenerator $urlGenerator;

  public function __construct(
    FundingCaseManager $fundingCaseManager,
    FundingSessionInterface $session,
    UrlGenerator $urlGenerator
  ) {
    $this->fundingCaseManager = $fundingCaseManager;
    $this->session = $session;
    $this->urlGenerator = $urlGenerator;
  }

  /**
   * @return string|null
   *   The URI to the transfer contract of the given funding case, or NULL if no
   *   transfer contract exists, yet.
   *
   * @throws \CRM_Core_Exception
   */
  public function generate(int $fundingCaseId): ?string {
    if (!$this->fundingCaseManager->hasTransferContract($fundingCaseId)) {
      return NULL;
    }

    if ($this->session->isRemote()) {
      $path = 'civicrm/funding/remote/transfer-contract/download';
    }
    else {
      $path = 'civicrm/funding/transfer-contract/download';
    }

    return $this->urlGenerator->generate($path, ['fundingCaseId' => (string) $fundingCaseId]);
  }

}
