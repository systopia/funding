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

fundingModule.directive('fundingJfElement', ['$compile', function($compile) {
  return {
    restrict: 'E',
    require: { formCtrl: '^^fundingJfForm' },
    scope: {
      jsonSchema: '<',
      uiSchema: '<',
      data: '=',
      editable: '<?',
      errors: '<',
      noLabel: '<',
      errorPathPrefix: '@?',
    },
    template: '',
    link: function (scope, element) {
      /**
       * Copied from AngularJS. (The lodash analog behaves differently with
       * consecutive upper case letters.)
       * https://github.com/angular/angular.js/blob/47bf11ee94664367a26ed8c91b9b586d3dd420f5/src/Angular.js#L1893
       */
      const SNAKE_CASE_REGEXP = /[A-Z]/g;
      function snake_case(name, separator) {
        separator = separator || '_';
        return name.replace(SNAKE_CASE_REGEXP, function(letter, pos) {
          return (pos ? separator : '') + letter.toLowerCase();
        });
      }

      scope.$watch('uiSchema', function (uiSchema) {
        if (uiSchema.type) {
          const directiveName = 'fundingJf' + _4.upperFirst(uiSchema.type);
          const tagName = snake_case(directiveName, '-');
          const template = '<' + tagName + '></' + tagName + '>';
          angular.element(element[0]).html($compile(template)(scope));
        }
      });
    },
    controller: ['$scope', function ($scope) {
      if ($scope.errorPathPrefix === undefined) {
        $scope.errorPathPrefix = $scope.$parent.$eval('errorPathPrefix');
      }

      $scope.$parent.$watch('inserted',
        (inserted) => $scope.inserted = inserted);
      $scope.onStartEdit = $scope.$parent.$eval('onStartEdit');
      $scope.onBeforeSave = $scope.$parent.$eval('onBeforeSave');
      $scope.onAfterSave = $scope.$parent.$eval('onAfterSave');
      $scope.onCancelEdit = $scope.$parent.$eval('onCancelEdit');
      $scope.onEditFinished = $scope.$parent.$eval('onEditFinished');
      $scope.addTo = $scope.$parent.$eval('addTo');
      $scope.cancelInsertAt = $scope.$parent.$eval('cancelInsertAt');
      $scope.removeAt = $scope.$parent.$eval('removeAt');
    }],
  };
}]);
