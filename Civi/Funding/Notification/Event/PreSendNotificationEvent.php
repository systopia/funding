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

namespace Civi\Funding\Notification\Event;

use Civi\Funding\Entity\ApplicationProcessEntity;
use Civi\Funding\Entity\ClearingProcessEntity;
use Civi\Funding\Entity\DrawdownEntity;
use Civi\Funding\Entity\FundingCaseEntity;
use Civi\Funding\Entity\FundingCaseTypeEntity;
use Civi\Funding\Entity\FundingProgramEntity;
use Civi\Funding\Entity\PayoutProcessEntity;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Allows to influence the notification to be sent, e.g. the contacts to be
 * notified.
 */
final class PreSendNotificationEvent extends Event {

  /**
   * @phpstan-var list<int>
   */
  private array $notificationContactIds;

  /**
   * @phpstan-var array<string, mixed>
   */
  private array $tokenContext;

  private ?string $workflowName;

  private string $workflowNamePostfix;

  /**
   * @phpstan-param list<int> $notificationContactIds
   * @phpstan-param array<string, mixed> $tokenContext
   */
  public function __construct(
    array $notificationContactIds,
    array $tokenContext,
    ?string $workflowName,
    string $workflowNamePostfix
  ) {
    $this->notificationContactIds = $notificationContactIds;
    $this->tokenContext = $tokenContext;
    $this->workflowName = $workflowName;
    $this->workflowNamePostfix = $workflowNamePostfix;
  }

  public function getApplicationProcess(): ?ApplicationProcessEntity {
    // @phpstan-ignore return.type
    return $this->tokenContext['fundingApplicationProcess'] ?? NULL;
  }

  public function getClearingProcess(): ?ClearingProcessEntity {
    // @phpstan-ignore return.type
    return $this->tokenContext['fundingClearingProcess'] ?? NULL;
  }

  public function getDrawdown(): ?DrawdownEntity {
    // @phpstan-ignore return.type
    return $this->tokenContext['fundingDrawdown'] ?? NULL;
  }

  public function getFundingCase(): FundingCaseEntity {
    // @phpstan-ignore return.type
    return $this->tokenContext['fundingCase'];
  }

  public function getFundingCaseType(): FundingCaseTypeEntity {
    // @phpstan-ignore return.type
    return $this->tokenContext['fundingCaseType'];
  }

  public function getFundingProgram(): FundingProgramEntity {
    // @phpstan-ignore return.type
    return $this->tokenContext['fundingProgram'];
  }

  public function getPayoutProcess(): ?PayoutProcessEntity {
    // @phpstan-ignore return.type
    return $this->tokenContext['fundingPayoutProcess'] ?? NULL;
  }

  /**
   * @phpstan-return list<int>
   */
  public function getNotificationContactIds(): array {
    return $this->notificationContactIds;
  }

  /**
   * @phpstan-param list<int> $notificationContactIds
   */
  public function setNotificationContactIds(array $notificationContactIds): self {
    $this->notificationContactIds = $notificationContactIds;

    return $this;
  }

  /**
   * @phpstan-return array<string, mixed>
   */
  public function getTokenContext(): array {
    return $this->tokenContext;
  }

  /**
   * @phpstan-return mixed
   */
  public function getTokenContextValue(string $key) {
    return $this->tokenContext[$key] ?? NULL;
  }

  /**
   * @param mixed $value
   */
  public function setTokenContextValue(string $key, $value): self {
    if (NULL === $value) {
      unset($this->tokenContext[$key]);
    }
    else {
      $this->tokenContext[$key] = $value;
    }

    return $this;
  }

  public function getWorkflowName(): ?string {
    return $this->workflowName;
  }

  /**
   * Sets the workflow name of a message template. If no workflow name is set,
   * no notification will be sent.
   */
  public function setWorkflowName(?string $workflowName): self {
    $this->workflowName = $workflowName;

    return $this;
  }

  public function getWorkflowNamePostfix(): string {
    return $this->workflowNamePostfix;
  }

}
