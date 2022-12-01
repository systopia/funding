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

fundingModule.config(['$routeProvider', function($routeProvider) {
    $routeProvider.when('/funding/application/:applicationProcessId', {
      controller: 'fundingApplicationCtrl',
      controllerAs: '$ctrl',
      templateUrl: '~/crmFunding/application/application.template.html',
      resolve: {
        applicationProcess: ['$route', 'fundingApplicationProcessService', function($route, fundingApplicationProcessService) {
          return fundingApplicationProcessService.get($route.current.params.applicationProcessId);
        }],
        formData: ['$route', 'fundingApplicationProcessService', function($route, fundingApplicationProcessService) {
          return fundingApplicationProcessService.getFormData($route.current.params.applicationProcessId);
        }],
        jsonSchema: ['$route', 'fundingApplicationProcessService', function($route, fundingApplicationProcessService) {
          return fundingApplicationProcessService.getJsonSchema($route.current.params.applicationProcessId);
        }],
      },
    });
  }]
);

fundingModule.controller('fundingApplicationCtrl', [
  '$scope', 'crmStatus', 'fundingContactService', 'fundingCaseService', 'fundingProgramService', 'fundingApplicationProcessService',
  'applicationProcess', 'formData', 'jsonSchema',
  function($scope, crmStatus, fundingContactService, fundingCaseService, fundingProgramService, fundingApplicationProcessService,
           applicationProcess, formData, jsonSchema) {
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
            formOrField.$form.$data = formOrField.$data = new Date(formOrField.$data);
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
            formOrField.$data[editable.name] = date.toJSON().slice(0, 10);
          } else {
            const date = formOrField.$data;
            formOrField.$form.$data = formOrField.$data = date.toJSON().slice(0, 10);
          }
        }
      }
    }

    const ts = $scope.ts = CRM.ts('funding');

    fundingCaseService.get(applicationProcess.funding_case_id).then(function (fundingCase) {
      $scope.permissions = fundingCase.permissions;
      fundingProgramService.get(fundingCase.funding_program_id).then(
        (fundingProgram) => $scope.currency = fundingProgram.currency
      );
      fundingContactService.get(fundingCase.recipient_contact_id).then(
          (contact) => $scope.recipientContact = contact
      );
    });

    $scope.applicationProcess = applicationProcess;
    $scope.data = formData;
    $scope.jsonSchema = jsonSchema;
    const originalData = _4.cloneDeep(formData);
    const originalDataString = JSON.stringify(originalData);

    $scope.reviewStatusList = {
      null: ts('Undecided'),
      true: ts('Accepted'),
      false: ts('Rejected'),
    };

    $scope.errors = {};
    $scope.isChanged = false;
    $scope.editCount = 0;

    function reloadApplicationProcess() {
      fundingApplicationProcessService.get($scope.applicationProcess.id).then(
          (applicationProcess) => $scope.applicationProcess = applicationProcess
      );
    }

    function reloadJsonSchema() {
      fundingApplicationProcessService.getJsonSchema($scope.applicationProcess.id).then(
          (jsonSchema) => $scope.jsonSchema = jsonSchema
      );
    }

    function handleSetValue(field, value) {
      return function (result) {
        if (result[0] && _4.isEqual(result[0][field], value)) {
          $scope.applicationProcess[field] = value;
          reloadJsonSchema();

          return true;
        }

        return false;
      };
    }

    $scope.isActionAllowed = function (action) {
      return $scope.jsonSchema.properties.action.enum.includes(action);
    };

    $scope.isActionDisabled = function (action) {
      return $scope.editCount > 0 ||
          !fundingIsEmpty($scope.errors) ||
          ($scope.isChanged && action !== 'update');
    };

    $scope.isEditAllowed = function () {
      return $scope.isActionAllowed('update');
    };

    $scope.performAction = function (action) {
      return $scope.submit(action);
    };

    $scope.setReviewCalculative = function (reviewCalculative) {
      return $scope.updateApplicationProcessField('is_review_calculative', reviewCalculative);
    };

    $scope.setReviewContent = function (reviewContent) {
      return $scope.updateApplicationProcessField('is_review_content', reviewContent);
    };

    $scope.updateApplicationProcessField = function (field, value) {
      if (value === undefined) {
        value = null;
      }

      return crmStatus({}, fundingApplicationProcessService.setValue(applicationProcess.id, field, value))
          .then(handleSetValue(field, value));
    };

    $scope.reset = function () {
      $scope.data = _4.cloneDeep(originalData);
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
        if (!formOrField.$editable.inputEl[0].checkValidity()) {
          return formOrField.$editable.inputEl[0].validationMessage || ts('Validation failed');
        }
      }

      convertDatesToStrings(formOrField);
    };

    $scope.onAfterSave = function (formOrField) {
      $scope.isChanged = $scope.isChanged || JSON.stringify($scope.data) !== originalDataString;
      // don't validate single fields that are part of multi field form
      if (!formOrField.$form || _4.isEqual(formOrField.$data, formOrField.$form.$data)) {
        $scope.validate();
      }
    };

    $scope.onCancelEdit = function (formOrField) {
      convertDatesToStrings(formOrField);
    };

    $scope.addTo = function (path, initialData = {}) {
      $scope.inserted = _4.cloneDeep(initialData);
      const array = _4.get($scope.data, path);
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
    };

    $scope.remove = function (path) {
      $scope.isChanged = true;
      _4.unset($scope.data, path);
    };

    $scope.validate = function () {
      const data = angular.extend({}, $scope.data, {action: 'update'});
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
        window.alert(ts('The chosen action is disabled. Please report this issue'));

        return new Promise((resolve) => resolve(false));
      }

      const data = angular.extend({}, $scope.data, {action});
      return fundingApplicationProcessService.submitForm($scope.applicationProcess.id, data).then(function (result) {
        if (result.data) {
          $scope.data = result.data;
        }
        $scope.errors = result.errors;

        if (_4.isEmpty(result.errors)) {
          reloadApplicationProcess();
          reloadJsonSchema();
          $scope.isChanged = false;

          return true;
        }

        return false;
      });
    };
  }
]);