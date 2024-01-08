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

namespace Civi\Funding\Event\ApplicationProcess;

use Civi\Funding\ApplicationProcess\Command\AbstractApplicationFormSubmitResult;
use Civi\Funding\Entity\ApplicationProcessEntity;
use Civi\Funding\Entity\ApplicationProcessEntityBundle;
use Civi\Funding\Form\Application\ValidatedApplicationDataInterface;

final class ApplicationFormSubmitSuccessEvent extends AbstractApplicationEvent {

  private ?ApplicationProcessEntity $previousApplicationProcess;

  private AbstractApplicationFormSubmitResult $result;

  /**
   * @phpstan-var array<string, mixed>
   */
  private array $submittedData;

  /**
   * @phpstan-param array<string, mixed> $submittedData
   */
  public function __construct(
    int $contactId,
    ApplicationProcessEntityBundle $applicationProcessBundle,
    array $submittedData,
    AbstractApplicationFormSubmitResult $result,
    ?ApplicationProcessEntity $previousApplicationProcess
  ) {
    parent::__construct($contactId, $applicationProcessBundle);
    $this->submittedData = $submittedData;
    $this->result = $result;
    $this->previousApplicationProcess = $previousApplicationProcess;
  }

  public function getAction(): string {
    return $this->getValidatedData()->getAction();
  }

  public function getPreviousApplicationProcess(): ?ApplicationProcessEntity {
    return $this->previousApplicationProcess;
  }

  public  function getResult(): AbstractApplicationFormSubmitResult {
    return $this->result;
  }

  /**
   * The returned data might not contain the actual application data if the
   * requested action resulted in a restore of a previous snapshot.
   *
   * @phpstan-return array<string, mixed>
   */
  public function getSubmittedData(): array {
    return $this->submittedData;
  }

  /**
   * The returned data might not contain the actual application data if the
   * requested action resulted in a restore of a previous snapshot.
   */
  public function getValidatedData(): ValidatedApplicationDataInterface {
    return $this->result->getValidatedData();
  }

}
