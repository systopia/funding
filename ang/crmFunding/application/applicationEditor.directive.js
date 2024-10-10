/*
 * Copyright (C) 2022 SYSTOPIA GmbH
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

fundingModule.directive('fundingApplicationEditor', ['$compile', function($compile) {
  return {
    restrict: 'E',
    scope: {
      applicationProcess: '=',
      // Buttons are not shown initially if JSON schema is loaded in controller.
      form: '=',
      statusOptions: '=',
      reviewStatusLabels: '=',
      onPostSubmit: '&',
    },
    template: '',
    // Insert the editor tag for the current funding case type.
    link: function (scope, element) {
      const unwatch = scope.$watch('fundingCaseType', function (fundingCaseType) {
        if (fundingCaseType) {
          const tagName = _4.get(fundingCaseType, 'properties.applicationEditorTagName', 'funding-default-application-editor');
          const template = '<' + tagName + '></' + tagName + '>';
          element.append($compile(template)(scope));
          window.setTimeout(fixHeights, 100);
          unwatch();
        }
      });
    },
    controller: ['$scope', 'crmStatus', 'fundingContactService', 'fundingCaseService',
      'fundingCaseTypeService', 'fundingProgramService', 'fundingApplicationProcessService',
      async function($scope, crmStatus, fundingContactService, fundingCaseService,
                     fundingCaseTypeService, fundingProgramService, fundingApplicationProcessService) {
        function convertStringsToDates(formOrField) {
          // If we ensure that this function is called for every single field via
          // onStartEdit() we would not need to take care of forms, i.e.
          // $editables...
          let editables;
          if (formOrField.$editable) {
            editables = [formOrField.$editable];
          } else {
            editables = formOrField.$editables || [];
          }
          for (let editable of editables) {
            if (editable.attrs.editableDate) {
              if (formOrField.$editables) {
                formOrField.$data[editable.name] = new Date(formOrField.$data[editable.name]);
              } else {
                formOrField.$data = new Date(formOrField.$data);
                if (formOrField.$form.$editables) {
                  formOrField.$form.$data[editable.name] = formOrField.$data;
                } else {
                  formOrField.$form.$data = formOrField.$data;
                }
              }
            }
          }
        }

        function convertDatesToStrings(formOrField) {
          // If we ensure that this function is called for every single field via
          // onBeforeSave() or onCancelEdit() we would not need to take care of
          // forms, i.e. $editables...
          let editables;
          if (formOrField.$editable) {
            editables = [formOrField.$editable];
          } else {
            editables = formOrField.$editables || [];
          }
          for (let editable of editables) {
            if (editable.attrs.editableDate) {
              if (formOrField.$editables) {
                const date = formOrField.$data[editable.name];
                if (date instanceof Date) {
                  formOrField.$data[editable.name] = date.toJSON().slice(0, 10);
                }
              } else {
                const date = formOrField.$data;
                if (date instanceof Date) {
                  formOrField.$data = date.toJSON().slice(0, 10);
                }
                if (formOrField.$form.$editables) {
                  formOrField.$form.$data[editable.name] = formOrField.$data;
                } else {
                  formOrField.$form.$data = formOrField.$data;
                }
              }
            }
          }
        }

        const $ = CRM.$;
        const ts = $scope.ts = CRM.ts('funding');

        fundingCaseService.get($scope.applicationProcess.funding_case_id).then(function (fundingCase) {
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

        $scope.errors = {};
        $scope.comment = {text: null};
        $scope.isChanged = false;
        $scope.editCount = 0;

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

        function reloadApplicationProcess() {
          return fundingApplicationProcessService.get($scope.applicationProcess.id).then(
              (applicationProcess) => $scope.applicationProcess = applicationProcess
          );
        }

        function reloadJsonSchema() {
          return fundingApplicationProcessService.getJsonSchema($scope.applicationProcess.id).then(
              (jsonSchema) => $scope.jsonSchema = jsonSchema
          );
        }

        $scope.jsonSchema = $scope.form.jsonSchema;
        $scope.uiSchema = $scope.form.uiSchema;
        $scope.uiSchema.label = null;
        $scope.data = $scope.form.data;
        let originalData = _4.cloneDeep($scope.data);
        let originalDataString = JSON.stringify(originalData);

        function handleSetValue(field, value) {
          return function (result) {
            if (result[0] && _4.isEqual(result[0][field], value)) {
              $scope.applicationProcess[field] = value;

              return true;
            }

            return false;
          };
        }

        $scope.updateApplicationProcessField = function (field, value) {
          if ($scope.applicationProcess[field] === value) {
            return;
          }

          if (value === undefined) {
            value = null;
          }

          return crmStatus({}, fundingApplicationProcessService.setValue($scope.applicationProcess.id, field, value))
            .then(handleSetValue(field, value));
        };

        $scope.setReviewerCalculative = function (contactId) {
          return $scope.updateApplicationProcessField('reviewer_calc_contact_id', contactId);
        };

        $scope.setReviewerContent = function (contactId) {
          return $scope.updateApplicationProcessField('reviewer_cont_contact_id', contactId);
        };

        $scope.hasPermission = function (permission) {
          return $scope.permissions && $scope.permissions.includes(permission);
        };

        $scope.hasReviewCalculativePermission = function () {
          return $scope.hasPermission('review_calculative');
        };

        $scope.hasReviewContentPermission = function () {
          return $scope.hasPermission('review_content');
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
        $scope.performAction = function (action, label, withComment) {
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

        $scope.reset = function () {
          $scope.data = _4.cloneDeep(originalData);
          $scope.comment = {text: null};
          $scope.isChanged = false;
          $scope.errors = {};
        };

        $scope.onStartEdit = function (formOrField) {
          convertStringsToDates(formOrField);
          $scope.editCount++;
        };

        $scope.onEditFinished = function () {
          $scope.editCount--;
        };

        $scope.onBeforeSave = function (formOrField) {
          if (formOrField.$editable) {
            // inputEl[0] might not be the actual field, but a wrapper around
            // one or multiple (e.g. in checklist).
            let fields;
            if (formOrField.$editable.inputEl[0].checkValidity) {
              fields = [formOrField.$editable.inputEl[0]];
            } else {
              fields = formOrField.$editable.inputEl[0].querySelectorAll('input, textarea').values();
            }
            for (const field of fields) {
              if (!field.checkValidity()) {
                return field.validationMessage || ts('Validation failed');
              }
            }
          }

          convertDatesToStrings(formOrField);
        };

        $scope.onAfterSave = function (formOrField) {
          $scope.isChanged = $scope.isChanged || JSON.stringify($scope.data) !== originalDataString;
          // don't validate single fields that are part of multi field form
          if (!formOrField.$form || 1 === formOrField.$form.$editables.length) {
            $scope.validate();
          }
        };

        $scope.onCancelEdit = function (formOrField) {
          convertDatesToStrings(formOrField);
        };

        $scope.addTo = function (path, initialData = {}) {
          $scope.inserted = _4.cloneDeep(initialData);
          let array = _4.get($scope.data, path);
          if (!array) {
            array = [];
            _4.set($scope.data, path, array);
          }
          array.push($scope.inserted);
        };

        $scope.cancelInsertAt = function (path, index, form) {
          // Call $hide() before form is removed to have correct editCount
          form.$hide();
          const array = _4.get($scope.data, path);
          _4.pullAt(array, index);
        };

        $scope.removeAt = function (path, index) {
          $scope.isChanged = true;
          const array = _4.get($scope.data, path);
          _4.pullAt(array, index);
          $scope.validate();
        };

        $scope.remove = function (path) {
          $scope.isChanged = true;
          _4.unset($scope.data, path);
          $scope.validate();
        };

        $scope.validate = function () {
          const data = angular.extend({}, $scope.data, {_action: 'update'});
          return fundingApplicationProcessService.validateForm($scope.applicationProcess.id, data).then(function (result) {
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

          return fundingApplicationProcessService.submitForm($scope.applicationProcess.id, data).then(function (result) {
            if (result.data) {
              $scope.data = result.data;
            }
            $scope.errors = result.errors;

            if (_4.isEmpty(result.errors)) {
              $scope.comment = {text: null};
              withOverlay(reloadApplicationProcess());
              withOverlay(reloadJsonSchema());
              if ($scope.onPostSubmit) {
                $scope.$eval($scope.onPostSubmit);
                //$scope.onPostSubmit(action);
              }
              originalData = _4.cloneDeep($scope.data);
              originalDataString = JSON.stringify(originalData);
              $scope.isChanged = false;

              return true;
            }

            return false;
          }).finally(() => disableOverlay());
        };
      },
    ],
  };
}]);
