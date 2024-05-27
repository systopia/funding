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

fundingModule.directive('fundingJfMarkup', ['$compile', function($compile) {
  return {
    restrict: 'E',
    scope: false,
    template: '',
    link: function (scope, element) {
      const unwatch = scope.$watch('uiSchema', function (uiSchema) {
        if (uiSchema.content) {
          element.append(uiSchema.content);
          unwatch();
        }
      });
    },
    controller: ['$scope', function ($scope) {
      const foo = 'bar';
    }],
  };
}]);
