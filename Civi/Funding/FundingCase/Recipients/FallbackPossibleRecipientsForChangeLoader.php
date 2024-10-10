<?php
declare(strict_types = 1);

namespace Civi\Funding\FundingCase\Recipients;

use Civi\Funding\Contact\PossibleRecipientsLoaderInterface;
use Civi\Funding\Entity\FundingCaseEntity;
use Civi\Funding\Entity\FundingCaseTypeEntity;
use Civi\Funding\Entity\FundingProgramEntity;
use Civi\RemoteTools\RequestContext\RequestContextInterface;

final class FallbackPossibleRecipientsForChangeLoader implements PossibleRecipientsForChangeLoaderInterface {

  private PossibleRecipientsLoaderInterface $possibleRecipientsLoader;

  private RequestContextInterface $requestContext;

  public function __construct(
    PossibleRecipientsLoaderInterface $possibleRecipientsLoader,
    RequestContextInterface $requestContext
  ) {
    $this->possibleRecipientsLoader = $possibleRecipientsLoader;
    $this->requestContext = $requestContext;
  }

  /**
   * @inheritDoc
   */
  public function getPossibleRecipients(
    FundingCaseEntity $fundingCase,
    FundingCaseTypeEntity $fundingCaseType,
    FundingProgramEntity $fundingProgram
  ): array {
    // In this fallback implementation we use the possible recipients loader
    // used when creating a new funding case. If we have no result with the
    // current users contact ID we use the funding case's creation contact ID.
    $contacts = $this->possibleRecipientsLoader->getPossibleRecipients(
      $this->requestContext->getContactId(),
      $fundingProgram
    );

    return [] !== $contacts ? $contacts
      : $this->possibleRecipientsLoader->getPossibleRecipients(
        $fundingCase->getCreationContactId(),
        $fundingProgram
      );
  }

}
