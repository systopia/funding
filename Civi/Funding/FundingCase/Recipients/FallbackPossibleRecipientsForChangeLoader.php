<?php
declare(strict_types = 1);

namespace Civi\Funding\FundingCase\Recipients;

use Civi\Funding\Contact\PossibleRecipientsLoaderInterface;
use Civi\Funding\Entity\FundingCaseBundle;
use Civi\Funding\FundingCaseType\FallbackFundingCaseTypeServiceInterface;
use Civi\RemoteTools\RequestContext\RequestContextInterface;

// phpcs:ignore Generic.Files.LineLength.TooLong
final class FallbackPossibleRecipientsForChangeLoader implements PossibleRecipientsForChangeLoaderInterface, FallbackFundingCaseTypeServiceInterface {

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
  public function getPossibleRecipients(FundingCaseBundle $fundingCaseBundle): array {
    // In this fallback implementation we use the possible recipients loader
    // used when creating a new funding case. If we have no result with the
    // current users contact ID we use the funding case's creation contact ID.
    $contacts = $this->possibleRecipientsLoader->getPossibleRecipients(
      $this->requestContext->getContactId(),
      $fundingCaseBundle->getFundingProgram()
    );

    return [] !== $contacts ? $contacts
      : $this->possibleRecipientsLoader->getPossibleRecipients(
        $fundingCaseBundle->getFundingCase()->getCreationContactId(),
        $fundingCaseBundle->getFundingProgram()
      );
  }

}
