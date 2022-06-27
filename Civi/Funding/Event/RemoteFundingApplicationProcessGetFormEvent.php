<?php
/**
 * Copyright (C) 2022 SYSTOPIA GmbH
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation in version 3.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types = 1);

namespace Civi\Funding\Event;

final class RemoteFundingApplicationProcessGetFormEvent extends AbstractRemoteFundingGetFormEvent {

  /**
   * @var array<string, mixed>
   */
  protected array $applicationProcess;

  /**
   * @var array<string, mixed>
   */
  protected array $fundingCase;

  /**
   * @var array<string, mixed>
   */
  protected array $fundingCaseType;

  /**
   * @return array<string, mixed>
   */
  public function getApplicationProcess(): array {
    return $this->applicationProcess;
  }

  /**
   * @return array<string, mixed>
   */
  public function getFundingCase(): array {
    return $this->fundingCase;
  }

  /**
   * @return array<string, mixed>
   */
  public function getFundingCaseType(): array {
    return $this->fundingCaseType;
  }

  protected function getRequiredParams(): array {
    return array_merge(parent::getRequiredParams(), [
      'applicationProcess',
      'fundingCase',
      'fundingCaseType',
    ]);
  }

}
