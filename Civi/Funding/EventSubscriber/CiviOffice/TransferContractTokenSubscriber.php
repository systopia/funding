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

namespace Civi\Funding\EventSubscriber\CiviOffice;

use Civi\Core\Event\GenericHookEvent;
use Civi\Funding\DocumentRender\CiviOffice\CiviOfficeContextDataHolder;
use Civi\Funding\DocumentRender\Token\ValueConverter;
use Civi\Funding\Entity\ApplicationProcessEntity;
use Civi\Token\AbstractTokenSubscriber;
use Civi\Token\TokenProcessor;
use Civi\Token\TokenRow;
use CRM_Funding_ExtensionUtil as E;

class TransferContractTokenSubscriber extends AbstractTokenSubscriber {

  private CiviOfficeContextDataHolder $contextDataHolder;

  public static function getSubscribedEvents() {
    return [
      'civi.civioffice.tokenContext' => 'onCiviOfficeTokenContext',
    ] + parent::getSubscribedEvents();
  }

  public function __construct(CiviOfficeContextDataHolder $contextDataHolder) {
    parent::__construct('transfer_contract', [
      'eligible_application_list' => E::ts('List of Eligible Applications (identifier and title)'),
    ]);
    $this->contextDataHolder = $contextDataHolder;
  }

  public function onCiviOfficeTokenContext(GenericHookEvent $event): void {
    if ('TransferContract' === $event->entity_type) {
      /**
       * @phpstan-var array{
       *   fundingCase: \Civi\Funding\Entity\FundingCaseEntity,
       *   eligibleApplicationProcesses: array<\Civi\Funding\Entity\ApplicationProcessEntity>,
       * } $data
       */
      $data = $this->contextDataHolder->getEntityData($event->entity_type, $event->entity_id);
      $event->context['transferContract'] = [
        'eligibleApplicationProcesses' => $data['eligibleApplicationProcesses'],
      ];
      $event->context['contactId'] = $data['fundingCase']->getRecipientContactId();
    }
  }

  public function checkActive(TokenProcessor $processor): bool {
    return in_array('transferContract', $processor->context['schema'] ?? [], TRUE)
      || [] !== $processor->getContextValues('transferContract')
      || 'CRM_Civioffice_Page_Tokens' === ($processor->context['controller'] ?? NULL);
  }

  /**
   * @inheritDoc
   */
  public function evaluateToken(TokenRow $row, $entity, $field, $prefetch = NULL): void {
    if ('eligible_application_list' === $field) {
      // In case there's no eligible application process (i.e. re-creation of transfer contract on funding cas withdraw)
      // there should be an empty array, but it is NULL because \CRM_Utils_Array::extend() is used which sets NULL for
      // an empty array.
      $row->context['transferContract']['eligibleApplicationProcesses'] ??= [];
      /** @phpstan-var array<\Civi\Funding\Entity\ApplicationProcessEntity> $applicationProcesses */
      $applicationProcesses = $row->context['transferContract']['eligibleApplicationProcesses'];
      $titles = array_map(
        fn (ApplicationProcessEntity $applicationProcess) => $applicationProcess->getIdentifier() .
          ': ' . $applicationProcess->getTitle(),
        $applicationProcesses,
      );
      $resolvedToken = ValueConverter::toResolvedToken($titles);
      $row->format($resolvedToken->format);
      $row->tokens($entity, $field, $resolvedToken->value);
    }
  }

}
