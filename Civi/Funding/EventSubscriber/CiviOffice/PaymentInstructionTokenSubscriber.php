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
use Civi\Token\AbstractTokenSubscriber;
use Civi\Token\TokenProcessor;
use Civi\Token\TokenRow;
use CRM_Funding_ExtensionUtil as E;

class PaymentInstructionTokenSubscriber extends AbstractTokenSubscriber {

  private CiviOfficeContextDataHolder $contextDataHolder;

  public static function getSubscribedEvents() {
    return [
      'civi.civioffice.tokenContext' => 'onCiviOfficeTokenContext',
    ] + parent::getSubscribedEvents();
  }

  public function __construct(CiviOfficeContextDataHolder $contextDataHolder) {
    parent::__construct('funding_payment_instruction', [
      'bank_account_reference' => E::ts('Bank Account Reference (e.g. IBAN)'),
      'bic' => E::ts('BIC (maybe empty)'),
    ]);
    $this->contextDataHolder = $contextDataHolder;
  }

  public function onCiviOfficeTokenContext(GenericHookEvent $event): void {
    if ('FundingPaymentInstruction' === $event->entity_type) {
      /**
       * @phpstan-var array{
       *   drawdown: \Civi\Funding\Entity\DrawdownEntity,
       *   bankAccount: \Civi\Funding\PayoutProcess\BankAccount,
       *   payoutProcess: \Civi\Funding\Entity\PayoutProcessEntity,
       *   fundingCase: \Civi\Funding\Entity\FundingCaseEntity,
       *   fundingCaseType: \Civi\Funding\Entity\FundingCaseTypeEntity,
       *   fundingProgram: \Civi\Funding\Entity\FundingProgramEntity,
       * } $data
       */
      $data = $this->contextDataHolder->getEntityData($event->entity_type, $event->entity_id);
      $event->context['fundingPaymentInstruction'] = [
        'bankAccount' => $data['bankAccount'],
      ];
      $event->context['contactId'] = $data['fundingCase']->getRecipientContactId();
      $event->context['fundingDrawdown'] = $data['drawdown'];
      $event->context['fundingPayoutProcess'] = $data['payoutProcess'];
    }
  }

  public function checkActive(TokenProcessor $processor): bool {
    return in_array('fundingPaymentInstruction', $processor->context['schema'] ?? [], TRUE)
      || [] !== $processor->getContextValues('fundingPaymentInstruction');
  }

  /**
   * @inheritDoc
   */
  public function evaluateToken(TokenRow $row, $entity, $field, $prefetch = NULL): void {
    /** @var \Civi\Funding\PayoutProcess\BankAccount $bankAccount */
    $bankAccount = $row->context['fundingPaymentInstruction']['bankAccount'];

    if ('bic' === $field) {
      $row->format('text/plain');
      $row->tokens($entity, $field, $bankAccount->getBic());
    }
    elseif ('bank_account_reference' === $field) {
      $row->format('text/plain');
      $row->tokens($entity, $field, $bankAccount->getReference());
    }
  }

}
