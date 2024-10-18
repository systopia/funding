<?php
declare(strict_types = 1);

namespace Civi\Funding\FundingCaseTypes\BSH\HiHAktion\FundingCase;

use Civi\Api4\Contact;
use Civi\Funding\Entity\FundingCaseEntity;
use Civi\Funding\Entity\FundingCaseTypeEntity;
use Civi\Funding\Entity\FundingProgramEntity;
use Civi\Funding\FundingCase\Recipients\PossibleRecipientsForChangeLoaderInterface;
use Civi\Funding\FundingCaseTypes\BSH\HiHAktion\Traits\HiHSupportedFundingCaseTypesTrait;
use Civi\RemoteTools\Api4\Api4Interface;
use Civi\RemoteTools\Api4\Query\Comparison;
use Civi\RemoteTools\Api4\Query\CompositeCondition;

final class HiHPossibleRecipientsForChangeLoader implements PossibleRecipientsForChangeLoaderInterface {

  use HiHSupportedFundingCaseTypesTrait;

  private Api4Interface $api4;

  public function __construct(Api4Interface $api4) {
    $this->api4 = $api4;
  }

  /**
   * @inheritDoc
   *
   * @throws \CRM_Core_Exception
   */
  public function getPossibleRecipients(
    FundingCaseEntity $fundingCase,
    FundingCaseTypeEntity $fundingCaseType,
    FundingProgramEntity $fundingProgram
  ): array {
    $separator = \CRM_Core_DAO::VALUE_SEPARATOR;

    return $this->api4->execute(Contact::getEntityName(), 'get', [
      'select' => ['id', 'display_name'],
      'where' => [
        CompositeCondition::new('OR',
          Comparison::new('contact_sub_type', 'LIKE', "%${separator}Buergerstiftung${separator}%"),
          Comparison::new('contact_sub_type', 'LIKE', "%${separator}Mittelempfaenger${separator}%"),
        )->toArray(),
      ],
    ])->indexBy('id')->column('display_name');
  }

}
