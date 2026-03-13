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

(function() {
  const FUNDING_CASE_EXTRA_FIELDS = ['amount_cleared', 'amount_admitted'];

  fundingModule.config(['$routeProvider', function ($routeProvider) {
      $routeProvider.when('/funding/case/:id', {
        controller: 'fundingCaseCtrl',
        controllerAs: '$ctrl',
        templateUrl: '~/crmFunding/case/fundingCase.template.html',
        resolve: {
          fundingCase: ['$route', 'fundingCaseService', function ($route, fundingCaseService) {
            return fundingCaseService.get($route.current.params.id, FUNDING_CASE_EXTRA_FIELDS);
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
    '$scope', 'crmStatus', 'fundingCurrencyFilter', 'fundingProgramService', 'fundingCaseService',
    'fundingApplicationProcessService', 'fundingCaseTypeService', 'fundingContactService',
    'fundingCase', 'statusLabels', 'applicationProcesses', 'payoutProcesses', 'possibleActions',
    function ($scope, crmStatus, fundingCurrencyFilter, fundingProgramService, fundingCaseService,
              fundingApplicationProcessService, fundingCaseTypeService, fundingContactService,
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

      function updateAmountEligible() {
        $scope.amountEligible = $scope.applicationProcesses
          .reduce(
            (total, applicationProcess) => total + applicationProcess.amount_eligible,
            0
          );
        $scope.approve = {
          amount: $scope.amountEligible,
        };
      }

      function updateApplicationProcesses() {
        fundingApplicationProcessService.getByFundingCaseId($scope.fundingCase.id).then(function (applicationProcesses) {
          $scope.applicationProcesses = applicationProcesses;
          updateAmountEligible();
        });
      }

      function onFundingCaseUpdate(fundingCase) {
        $scope.fundingCase = fundingCase;
        withOverlay(fundingCaseService.getPossibleActions(fundingCase.id)
          .then((possibleActions) => {
            $scope.possibleActions = possibleActions;
            updateApplicationProcesses();
          }));
      }

      document.addEventListener('applicationSearchTaskExecuted', () => {
        withOverlay(fundingCaseService.get($scope.fundingCase.id, FUNDING_CASE_EXTRA_FIELDS).then(onFundingCaseUpdate));
      });

      fundingProgramService.get(fundingCase.funding_program_id).then(
        (result) => {
          $scope.fundingProgram = result;
          $scope.currencySymbol = toCurrencySymbol(result.currency);
        }
      );

      fundingCaseTypeService.get(fundingCase.funding_case_type_id).then(
        (fundingCaseType) => {
          $scope.fundingCaseType = fundingCaseType;
        }
      );

      fundingContactService.get(fundingCase.recipient_contact_id).then(
        (contact) => $scope.recipientContact = contact
      );

      $scope.fundingCurrencyFilter = fundingCurrencyFilter;

      $scope.fundingCase = fundingCase;
      $scope.statusLabels = statusLabels;
      $scope.applicationProcesses = applicationProcesses;
      $scope.payoutProcesses = payoutProcesses;
      $scope.possibleActions = possibleActions;
      updateAmountEligible();

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

      function updateField(field, value) {
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

      let $approveModal = null;
      $scope.approvePrepare = function () {
        if ($scope.fundingProgram.budget !== null) {
          fundingProgramService.getAmountApproved(fundingCase.funding_program_id).then((amountApproved) => {
            $scope.availableBudget = $scope.fundingProgram.budget - amountApproved;
          });
        }

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

      $scope.updateAmountApproved = {
        amount: $scope.fundingCase.amount_approved,
      };
      let $updateAmountApprovedModal = null;
      $scope.updateAmountApprovedPrepare = function () {
        if ($scope.fundingCaseType.properties.amountApprovedNonAdjustable) {
          $scope.updateAmountApproved.amount = $scope.amountEligible;
        }

        if ($scope.fundingProgram.budget !== null) {
          fundingProgramService.getAmountApproved(fundingCase.funding_program_id).then((amountApproved) => {
            $scope.availableBudget = $scope.fundingProgram.budget - amountApproved + $scope.fundingCase.amount_approved;
          });
        }

        if ($updateAmountApprovedModal === null) {
          $updateAmountApprovedModal = $('#update-amount-approved-modal');
        }
        $updateAmountApprovedModal.modal({backdrop: 'static'});
      };
      $scope.updateAmountApprovedSubmit = function () {
        if (!document.getElementById('amount-approved').reportValidity()) {
          return new Promise((resolve) => resolve(false));
        }

        $updateAmountApprovedModal.modal('hide');
        if ($scope.fundingCase.amount_approved === $scope.updateAmountApproved.amount) {
          return new Promise((resolve) => resolve(true));
        }

        return withOverlay(crmStatus({}, fundingCaseService.updateAmountApproved(fundingCase.id, $scope.updateAmountApproved.amount)
          .then(onFundingCaseUpdate)
        ));
      };

      $scope.finishClearing = function () {
        CRM.confirm({
          message: ts(
            'This will close the funding case. A final payment instruction will be created, \
            if the available funding wasn\'t paid out completely, yet. Otherwise, a payback \
            claim will be created. Maybe you want to update the amount approved before.'),
          'width': '600px',
        }).on('crmConfirm:yes', function () {
          return withOverlay(crmStatus({}, fundingCaseService.finishClearing(fundingCase.id)
            .then(() => window.location.reload())
          ));
        });
      };

      $scope.reject = function () {
        CRM.confirm({
          message: ts('The funding case with all its applications will be rejected. Do you want to continue?'),
        }).on('crmConfirm:yes', function () {
          return withOverlay(crmStatus({}, fundingCaseService.reject(fundingCase.id)
            .then(() => window.location.reload())
          ));
        });
      };

      $scope.recreateTransferContract = function () {
        withOverlay(crmStatus({}, fundingCaseService.recreateTransferContract(fundingCase.id)
          .then(onFundingCaseUpdate)
        ));
      };
    },
  ]);
})();
