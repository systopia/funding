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

'use strict';

fundingModule.config(['$routeProvider', function ($routeProvider) {
    $routeProvider.when('/funding/case/:id', {
      controller: 'fundingCaseCtrl',
      controllerAs: '$ctrl',
      templateUrl: '~/crmFunding/case/fundingCase.template.html',
      resolve: {
        fundingCase: ['$route', 'fundingCaseService', function ($route, fundingCaseService) {
          return fundingCaseService.get($route.current.params.id);
        }],
        statusLabels: ['$route', 'fundingCaseService', function ($route, fundingCaseService) {
          return fundingCaseService.getStatusLabels($route.current.params.id);
        }],
        applicationProcesses: ['$route', 'fundingApplicationProcessService', function ($route, fundingApplicationProcessService) {
          return fundingApplicationProcessService.getByFundingCaseId($route.current.params.id);
        }],
        payoutProcesses: ['$route', 'fundingPayoutProcessService', function ($route, fundingPayoutProcessService) {
          return fundingPayoutProcessService.getByFundingCaseId($route.current.params.id);
        }],
        possibleActions: ['$route', 'fundingCaseService', function ($route, fundingCaseService) {
          return fundingCaseService.getPossibleActions($route.current.params.id);
        }],
      },
    });
  }]
);

fundingModule.controller('fundingCaseCtrl', [
  '$scope', 'crmStatus', 'fundingProgramService', 'fundingCaseService', 'fundingContactService',
  'fundingCase', 'statusLabels', 'applicationProcesses', 'payoutProcesses', 'possibleActions',
  function ($scope, crmStatus, fundingProgramService, fundingCaseService, fundingContactService,
            fundingCase, statusLabels, applicationProcesses, payoutProcesses, possibleActions) {
    const $ = CRM.$;
    const ts = $scope.ts = CRM.ts('funding');

    function toCurrencySymbol(currencyName) {
      return new Intl.NumberFormat('en', {
        style: 'currency',
        currency: currencyName
      })
          .formatToParts(1)
          .find(part => part.type = 'currency').value;
    }

    document.addEventListener('applicationSearchTaskExecuted', (event) => {
      fundingCaseService.getPossibleActions(fundingCase.id).then(
        (possibleActions) => $scope.possibleActions = possibleActions
      );
    });

    fundingProgramService.get(fundingCase.funding_program_id).then(
        (result) => {
          $scope.fundingProgram = result;
          $scope.currencySymbol = toCurrencySymbol(result.currency);
        }
    );

    fundingContactService.get(fundingCase.recipient_contact_id).then(
        (contact) => $scope.recipientContact = contact
    );

    $scope.fundingCase = fundingCase;
    $scope.statusLabels = statusLabels;
    $scope.applicationProcesses = applicationProcesses;
    $scope.payoutProcesses = payoutProcesses;
    $scope.possibleActions = possibleActions;
    $scope.amountRequestedEligible = applicationProcesses
        .filter((applicationProcess) => applicationProcess.is_eligible)
        .reduce(
            (total, applicationProcess) => total + applicationProcess.amount_requested,
            0
        );

    function handleSetValue(field, value) {
      return function (result) {
        if (result[0] && _4.isEqual(result[0][field], value)) {
          $scope.fundingCase[field] = value;
          if (result[0].modificationDate) {
            $scope.fundingCase.modificationDate = result[0].modificationDate;
          }

          return true;
        }

        return false;
      };
    }

    function updateField (field, value) {
      if (value === undefined) {
        value = null;
      }

      return crmStatus({}, fundingCaseService.setValue($scope.fundingCase.id, field, value))
          .then(handleSetValue(field, value));
    }

    $scope.onBeforeSave = function (formOrField) {
      if (formOrField.$editable) {
        if (!formOrField.$editable.inputEl[0].checkValidity()) {
          return formOrField.$editable.inputEl[0].validationMessage || ts('Validation failed');
        }

        return updateField(formOrField.$editable.name, formOrField.$data);
      }
    };

    function onFundingCaseUpdate(fundingCase) {
      $scope.fundingCase = fundingCase;
      withOverlay(fundingCaseService.getPossibleActions(fundingCase.id)
          .then((possibleActions) => $scope.possibleActions = possibleActions));
    }

    $scope.approve = {
      amount: $scope.amountRequestedEligible,
    };
    let $approveModal = null;
    $scope.approvePrepare = function () {
      if ($approveModal === null) {
        $approveModal = $('#approve-modal');
      }
      $approveModal.modal({backdrop: 'static'});
    };
    $scope.approveSubmit = function () {
      if (!document.getElementById('approve-amount').reportValidity()) {
        return new Promise((resolve) => resolve(false));
      }

      $approveModal.modal('hide');
      return withOverlay(crmStatus({}, fundingCaseService.approve(fundingCase.id, $scope.approve.amount)
          .then(onFundingCaseUpdate)
      ));
    };

    $scope.recreateTransferContract = function () {
      withOverlay(crmStatus({}, fundingCaseService.recreateTransferContract(fundingCase.id)
          .then(onFundingCaseUpdate)
      ));
    };
  },
]);
