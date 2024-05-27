/*
 * Copyright (C) 2024 SYSTOPIA GmbH
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

fundingModule.directive('fundingClearingEditor', [function() {
  return {
    restrict: 'E',
    scope: {
      clearingProcess: '=',
      // Buttons are not shown initially if JSON schema is loaded in controller.
      form: '=',
      statusOptions: '=',
      reviewStatusLabels: '=',
      onPostSubmit: '&',
    },
    templateUrl: '~/crmFunding/clearing/clearingEditor.template.html',
    // Insert the editor tag for the current funding case type.
    link: function () {
      window.setTimeout(fixHeights, 100);
    },
    controller: ['$scope', 'crmStatus', 'fundingContactService', 'fundingCaseService',
      'fundingCaseTypeService', 'fundingProgramService', 'fundingApplicationProcessService',
      'fundingClearingProcessService', 'fundingEditorTrait',
      function($scope, crmStatus, fundingContactService, fundingCaseService,
                     fundingCaseTypeService, fundingProgramService, fundingApplicationProcessService,
                     fundingClearingProcessService, fundingEditorTrait) {
        fundingEditorTrait.use($scope);

        this.$scope = $scope;

        const $ = CRM.$;
        const ts = $scope.ts = CRM.ts('funding');

        fundingApplicationProcessService.get($scope.clearingProcess.application_process_id).then((applicationProcess) => {
          $scope.applicationProcess = applicationProcess;
          fundingCaseService.get(applicationProcess.funding_case_id).then((fundingCase) => {
            $scope.permissions = fundingCase.permissions;
            fundingProgramService.get(fundingCase.funding_program_id).then(
              (fundingProgram) => $scope.currency = fundingProgram.currency
            );
            fundingContactService.get(fundingCase.recipient_contact_id).then(
              (contact) => $scope.recipientContact = contact
            );
            fundingCaseTypeService.get(fundingCase.funding_case_type_id).then(
              (fundingCaseType) => $scope.fundingCaseType = fundingCaseType
            );
          });
        });

        $scope.comment = {text: null};

        const decoratedReset = $scope.reset;
        $scope.reset = function () {
          decoratedReset();
          $scope.comment = {text: null};
        };

        $scope.jsonSchema = $scope.form.jsonSchema;
        $scope.uiSchema = $scope.form.uiSchema;
        $scope.uiSchema.label = null;
        $scope.data = $scope.form.data;
        $scope.resetOriginalData();

        $scope.isActionAllowed = function (action) {
          return $scope.jsonSchema.properties._action.enum.includes(action);
        };

        $scope.isAnyActionAllowed = function (...actions) {
          for (const action of actions) {
            if ($scope.isActionAllowed(action)) {
              return true;
            }
          }

          return false;
        };

        $scope.isActionDisabled = function (action) {
          return $scope.editCount > 0 ||
            !fundingIsEmpty($scope.errors) ||
            $scope.isChanged && action !== 'update';
        };

        $scope.isEditAllowed = function () {
          return $scope.isActionAllowed('update');
        };

        function reloadClearingProcess() {
          return fundingClearingProcessService.get($scope.clearingProcess.id).then(
              (clearingProcess) => $scope.clearingProcess = clearingProcess
          );
        }

        function reloadForm() {
          return fundingClearingProcessService.getForm($scope.clearingProcess.id).then(
            (form) => {
              $scope.form = form;
              $scope.jsonSchema = form.jsonSchema;
              $scope.uiSchema = $scope.form.uiSchema;
              $scope.uiSchema.label = null;
              $scope.data = form.data;
              $scope.resetOriginalData();
            }
          );
        }

        $scope.setReviewerCalculative = function (contactId) {
          return crmStatus({}, fundingClearingProcessService.setCalculativeReviewer($scope.clearingProcess.id, contactId))
            .then(() => $scope.clearingProcess.reviewer_calc_contact_id = contactId);
        };

        $scope.setReviewerContent = function (contactId) {
          return crmStatus({}, fundingClearingProcessService.setContentReviewer($scope.clearingProcess.id, contactId))
            .then(() => $scope.clearingProcess.reviewer_cont_contact_id = contactId);
        };

        $scope.hasPermission = function (permission) {
          return $scope.permissions && $scope.permissions.includes(permission);
        };

        $scope.hasReviewCalculativePermission = function () {
          return $scope.hasPermission('review_clearing_calculative');
        };

        $scope.hasReviewContentPermission = function () {
          return $scope.hasPermission('review_clearing_content');
        };

        $scope.startReviewCalculative = function () {
          if ($scope.isActionAllowed('review')) {
            $scope.submit('review').then(() => $scope.setReviewerCalculative(CRM.config.cid));
          } else {
            $scope.setReviewerCalculative(CRM.config.cid);
          }
        };

        $scope.startReviewContent = function () {
          if ($scope.isActionAllowed('review')) {
            $scope.submit('review').then(() => $scope.setReviewerContent(CRM.config.cid));
          } else {
            $scope.setReviewerContent(CRM.config.cid);
          }
        };

        let $submitModal = null;
        $scope.performAction = function (action, label, withComment, modalSubmitButtonLabel) {
          if (withComment) {
            const commentRequired = withComment === 'required';
            if ($submitModal === null) {
              $submitModal = $('#submit-modal');
              $submitModal.on('hidden.bs.modal', function () {
                // comment will be cleared if not submitted or on successful submit
                if (!$scope.submitModal.submitted) {
                  $scope.comment.text = null;
                }
              });
            }
            $scope.submitModal = {
              action,
              title: label,
              submitButtonLabel: modalSubmitButtonLabel,
              commentRequired,
              submitted: false,
            };
            $submitModal.modal({backdrop: 'static'});
          } else {
            $scope.submit(action);
          }
        };

        $scope.modalSubmit = function () {
          if (!document.getElementById('commentText').reportValidity()) {
            return new Promise((resolve) => resolve(false));
          }
          if (!document.getElementById('commentType').reportValidity()) {
            return new Promise((resolve) => resolve(false));
          }

          $scope.submitModal.submitted = true;
          $submitModal.modal('hide');
          return $scope.submit($scope.submitModal.action);
        };

        $scope.validate = function () {
          const data = angular.extend({}, $scope.data, {_action: 'update'});
          return fundingClearingProcessService.validateForm($scope.clearingProcess.id, data).then(function (result) {
            if (result.data) {
              $scope.data = result.data;
            }
            $scope.errors = result.errors;

            return _4.isEmpty(result.errors);
          });
        };

        $scope.submit = function (action = 'update') {
          if ($scope.isActionDisabled(action)) {
            // Should not happen
            window.alert(ts('The chosen action is disabled. Please report this issue.'));

            return new Promise((resolve) => resolve(false));
          }

          enableOverlay();
          const data = angular.extend({}, $scope.data, {_action: action});
          if ($scope.comment.text) {
            data.comment = $scope.comment;
          }

          return fundingClearingProcessService.submitForm($scope.clearingProcess.id, data).then(function (result) {
            $scope.errors = result.errors;
            if (result.data) {
              $scope.data = result.data;
            }

            if (_4.isEmpty(result.errors)) {
              $scope.comment = {text: null};
              withOverlay(reloadClearingProcess());
              withOverlay(reloadForm());
              if ($scope.onPostSubmit) {
                $scope.$eval($scope.onPostSubmit);
              }

              return true;
            }

            return false;
          }).finally(() => disableOverlay());
        };
      },
    ],
  };
}]);
