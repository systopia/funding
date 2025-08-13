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

fundingModule.directive('fundingJfControlArray', [function() {
  return {
    restrict: 'E',
    scope: false,
    templateUrl: '~/crmFunding/jsonForms/jfControlArray.template.html',
    controller: ['$scope', function($scope) {
      function toJsonPointer(path) {
        return '/' + _4.join(_4.toPath(path), '/');
      }

      $scope.ts = CRM.ts('funding');

      $scope.elements = [];
      $scope.header = [];
      for (const element of $scope.uiSchema.options.elements) {
        if (!element.options ||
          element.options.type !== 'hidden' && element.options.type !== 'value'
        ) {
          $scope.elements.push(element);
          $scope.header.push(element.label);
        }
      }


      let errorPathPrefix = '';
      if ($scope.errorPathPrefix) {
        errorPathPrefix = $scope.errorPathPrefix;
        if (!errorPathPrefix.endsWith('.')) {
          errorPathPrefix += '.';
        }
      }

      $scope.errorsJsonPointer = toJsonPointer(errorPathPrefix + $scope.path);

      let data;
      $scope.$watch('data', (newData) => {
        if (newData !== data) {
          data = newData;
          $scope.value = _4.get(newData, $scope.path);
          if (undefined === $scope.value) {
            $scope.value = [];
            _4.set(newData, $scope.path, $scope.value);
          }
          $scope.addAllowed = $scope.editable && ($scope.propertySchema.maxItems == null || $scope.value.length < $scope.propertySchema.maxItems);
          $scope.removeAllowed = $scope.editable && ($scope.propertySchema.minItems == null || $scope.value.length > $scope.propertySchema.minItems);
        }
      });
    }],
  };
}]);
