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

fundingModule.factory('fundingEditorTrait', [function() {
  return {
    use: function ($scope) {
      const ts = CRM.ts('funding');

      function convertStringsToDates(formOrField) {
        // If we ensure that this function is called for every single field via
        // onStartEdit() we would not need to take care of forms, i.e.
        // $editables...
        let editables;
        if (formOrField.$editable) {
          editables = [formOrField.$editable];
        }
        else {
          editables = formOrField.$editables || [];
        }
        for (let editable of editables) {
          if (editable.attrs.editableDate) {
            if (formOrField.$editables) {
              formOrField.$data[editable.name] = new Date(formOrField.$data[editable.name]);
            }
            else {
              formOrField.$data = new Date(formOrField.$data);
              if (formOrField.$form.$editables) {
                formOrField.$form.$data[editable.name] = formOrField.$data;
              }
              else {
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
        }
        else {
          editables = formOrField.$editables || [];
        }
        for (let editable of editables) {
          if (editable.attrs.editableDate) {
            if (formOrField.$editables) {
              const date = formOrField.$data[editable.name];
              if (date instanceof Date) {
                formOrField.$data[editable.name] = date.toJSON().slice(0, 10);
              }
            }
            else {
              const date = formOrField.$data;
              if (date instanceof Date) {
                formOrField.$data = date.toJSON().slice(0, 10);
              }
              if (formOrField.$form.$editables) {
                formOrField.$form.$data[editable.name] = formOrField.$data;
              }
              else {
                formOrField.$form.$data = formOrField.$data;
              }
            }
          }
        }
      }

      $scope.errors = {};
      $scope.isChanged = false;
      $scope.editCount = 0;

      let originalData;
      let originalDataString;

      $scope.resetOriginalData = function () {
        originalData = _4.cloneDeep($scope.data);
        originalDataString = JSON.stringify(originalData);
        $scope.isChanged = false;
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
          // inputEl[0] might not be the actual field, but a wrapper around
          // one or multiple (e.g. in checklist).
          let fields;
          if (formOrField.$editable.inputEl[0].checkValidity) {
            fields = [formOrField.$editable.inputEl[0]];
          }
          else {
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
    }
  };
}]);
