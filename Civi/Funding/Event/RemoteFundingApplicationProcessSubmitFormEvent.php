<?php
declare(strict_types = 1);

namespace Civi\Funding\Event;

final class RemoteFundingApplicationProcessSubmitFormEvent extends AbstractRemoteFundingSubmitFormEvent {

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
