<?php
declare(strict_types = 1);

namespace Civi\Funding\Event;

final class RemoteFundingCaseGetNewApplicationFormEvent extends AbstractRemoteFundingGetFormEvent {

  /**
   * @var array<string, mixed>&array{id: int}
   */
  protected array $fundingProgram;

  /**
   * @var array<string, mixed>&array{id: int}
   */
  protected array $fundingCaseType;

  /**
   * @return array<string, mixed>&array{id: int}
   */
  public function getFundingProgram(): array {
    return $this->fundingProgram;
  }

  /**
   * @return array<string, mixed>&array{id: int}
   */
  public function getFundingCaseType(): array {
    return $this->fundingCaseType;
  }

  protected function getRequiredParams(): array {
    return array_merge(parent::getRequiredParams(), [
      'fundingProgram',
      'fundingCaseType',
    ]);
  }

}
