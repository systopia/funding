<?php
declare(strict_types = 1);

namespace Civi\Funding\FundingCase\Recipients;

use Civi\Funding\Entity\FundingCaseEntity;
use Civi\Funding\Entity\FundingCaseTypeEntity;
use Civi\Funding\Entity\FundingProgramEntity;
use Psr\Container\ContainerInterface;

final class PossibleRecipientsForChangeLoaderCollector implements PossibleRecipientsForChangeLoaderInterface {

  private ContainerInterface $recipientsLoaders;

  private FallbackPossibleRecipientsForChangeLoader $fallbackRecipientsLoader;

  /**
   * @param \Psr\Container\ContainerInterface $recipientsLoaders
   *   Recipient loaders with funding case type name as ID.
   */
  public function __construct(
    ContainerInterface $recipientsLoaders,
    FallbackPossibleRecipientsForChangeLoader $fallbackRecipientsLoader
  ) {
    $this->recipientsLoaders = $recipientsLoaders;
    $this->fallbackRecipientsLoader = $fallbackRecipientsLoader;
  }

  /**
   * @inheritDoc
   */
  public function getPossibleRecipients(
    FundingCaseEntity $fundingCase,
    FundingCaseTypeEntity $fundingCaseType,
    FundingProgramEntity $fundingProgram
  ): array {
    return $this->getRecipientsLoader($fundingCaseType->getName())->getPossibleRecipients(
      $fundingCase,
      $fundingCaseType,
      $fundingProgram
    );
  }

  private function getRecipientsLoader(string $fundingCaseTypeName): PossibleRecipientsForChangeLoaderInterface {
    if ($this->recipientsLoaders->has($fundingCaseTypeName)) {
      // @phpstan-ignore return.type
      return $this->recipientsLoaders->get($fundingCaseTypeName);
    }

    return $this->fallbackRecipientsLoader;
  }

}
