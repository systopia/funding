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

fundingModule.directive('fundingApplicationPage', ['$compile', function($compile) {
  return {
    restrict: 'E',
    scope: false,
    template: '',
    // Insert the page tag for the current funding case type.
    link: function (scope, element) {
      const unwatch = scope.$watch('fundingCaseType', function (fundingCaseType) {
        if (fundingCaseType) {
          const tagName = _4.get(fundingCaseType, 'properties.applicationPageTagName', 'funding-default-application-page');
          const template = '<' + tagName + '></' + tagName + '>';
          element.append($compile(template)(scope));
          window.setTimeout(fixHeights, 100);
          unwatch();
        }
      });
    },
  };
}]);
