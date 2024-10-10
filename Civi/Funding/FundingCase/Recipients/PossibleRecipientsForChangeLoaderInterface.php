<?php
declare(strict_types = 1);

namespace Civi\Funding\FundingCase\Recipients;

use Civi\Funding\Entity\FundingCaseEntity;
use Civi\Funding\Entity\FundingCaseTypeEntity;
use Civi\Funding\Entity\FundingProgramEntity;

/**
 * Loads the possible recipients when the recipient of an existing funding case
 * shall be changed via CiviCRM UI.
 */
interface PossibleRecipientsForChangeLoaderInterface {

  public const SERVICE_TAG = 'funding.case.possible_recipients_for_change_loader';

  /**
   * @phpstan-return array<int, string>
   *   Contact ID mapped to display name.
   */
  public function getPossibleRecipients(
    FundingCaseEntity $fundingCase,
    FundingCaseTypeEntity $fundingCaseType,
    FundingProgramEntity $fundingProgram
  ): array;

}
