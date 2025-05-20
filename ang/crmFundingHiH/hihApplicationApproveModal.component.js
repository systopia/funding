/*
 * Copyright (C) 2025 SYSTOPIA GmbH
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

fundingHiHModule.directive('fundingHihApplicationApproveModal', function() {
  return {
    templateUrl: '~/crmFundingHiH/hihApplicationApproveModal.template.html',
    scope: false,
    controllerAs: '$ctrlApprove',
    controller: ['$scope', function ($scope) {
      const $ = CRM.$;
      const ctrl = this;
      let personalkostenBewilligt = null;
      let honorareBewilligt = null;
      let sachkostenBewilligt = null;
      let bewilligungskommentar = null;
      let $approveModal = null;
      let submitted;

      function resetApprovalValues() {
        const resetValue = function(key, value) {
          if (value === null) {
            delete $scope.data.kosten[key];
          }
          else {
            $scope.data.kosten[key] = value;
          }
        };

        resetValue('personalkostenBewilligt', personalkostenBewilligt);
        resetValue('honorareBewilligt', honorareBewilligt);
        resetValue('sachkostenBewilligt', sachkostenBewilligt);
        resetValue('bewilligungskommentar', bewilligungskommentar);
      }

      function validate() {
        if (!document.getElementById('honorareBewilligt').reportValidity()) {
          return new Promise((resolve) => resolve(false));
        }
        if (!document.getElementById('personalkostenBewilligt').reportValidity()) {
          return new Promise((resolve) => resolve(false));
        }
        if (!document.getElementById('sachkostenBewilligt').reportValidity()) {
          return new Promise((resolve) => resolve(false));
        }
        if (!document.getElementById('bewilligungskommentar').reportValidity()) {
          return new Promise((resolve) => resolve(false));
        }
        if (!ctrl.isApproveSumValid()) {
          return new Promise((resolve) => resolve(false));
        }

        return true;
      }

      function openDialog() {
        submitted = false;

        if ($approveModal === null) {
          $approveModal = $('#approve-modal');
          $approveModal.on('hidden.bs.modal', function () {
            if (!submitted) {
              resetApprovalValues();
            }
          });
        }

        $approveModal.modal({backdrop: 'static'});
      }

      $scope.approve = function () {
        ctrl.action = 'approve';
        $scope.data.kosten.personalkostenBewilligt = $scope.data.kosten.personalkostenSumme;
        $scope.data.kosten.honorareBewilligt = $scope.data.kosten.honorareSumme;
        $scope.data.kosten.sachkostenBewilligt = $scope.data.kosten.sachkosten.summe;
        openDialog();
      };

      $scope.approveUpdate = function () {
        ctrl.action = 'approve-update';
        personalkostenBewilligt = $scope.applicationProcess['bsh_funding_application_extra.amount_approved_personalkosten'];
        honorareBewilligt = $scope.applicationProcess['bsh_funding_application_extra.amount_approved_honorare'];
        sachkostenBewilligt = $scope.applicationProcess['bsh_funding_application_extra.amount_approved_sachkosten'];
        bewilligungskommentar = $scope.applicationProcess['bsh_funding_application_extra.approval_comment'];
        $scope.data.kosten.personalkostenBewilligt = personalkostenBewilligt;
        $scope.data.kosten.honorareBewilligt = honorareBewilligt;
        $scope.data.kosten.sachkostenBewilligt = sachkostenBewilligt;
        $scope.data.kosten.bewilligungsKommentar = bewilligungskommentar;
        $scope.data.recreateTransferContract = false;
        openDialog();
      };

      ctrl.approveSum = function () {
        return ($scope.data.kosten.personalkostenBewilligt + $scope.data.kosten.honorareBewilligt + $scope.data.kosten.sachkostenBewilligt).toFixed(2);
      };

      ctrl.isApproveSumValid = function () {
        if (ctrl.approveSum() <= 0) {
          return false;
        }

        if (ctrl.action === 'approve-update' && !$scope.data.recreateTransferContract) {
          if (Math.abs(ctrl.approveSum() - $scope.fundingCase.amount_approved) > Number.EPSILON) {
            return false;
          }
        }

        return true;
      };

      $scope.submitApprove = function() {
        const validationResult = validate();
        if (validationResult !== true) {
          return validationResult;
        }

        submitted = true;
        $approveModal.modal('hide');

        return $scope.submit(ctrl.action);
      };
    }],
  };
});
