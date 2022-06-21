<?php
declare(strict_types = 1);

namespace Civi\Funding\Event;

final class RemoteFundingCaseSubmitNewApplicationFormEvent extends AbstractRemoteFundingSubmitFormEvent {

  /**
   * @var array<string, mixed>&array{id: int}
   */
  protected array $fundingCaseType;

  /**
   * @var array<string, mixed>&array{id: int}
   */
  protected array $fundingProgram;

  /**
   * @return array<string, mixed>&array{id: int}
   */
  public function getFundingCaseType(): array {
    return $this->fundingCaseType;
  }

  /**
   * @return array<string, mixed>&array{id: int}
   */
  public function getFundingProgram(): array {
    return $this->fundingProgram;
  }

  protected function getRequiredParams(): array {
    return array_merge(parent::getRequiredParams(), [
      'fundingCaseType',
      'fundingProgram',
    ]);
  }

}
