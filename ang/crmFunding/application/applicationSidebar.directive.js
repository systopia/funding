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

fundingModule.directive('fundingApplicationSidebar', ['$compile', function($compile) {
  return {
    restrict: 'AE',
    scope: false,
    template: '',
    // Insert the sidebar tag for the current funding case type.
    link: function (scope, element) {
      const unwatch = scope.$watch('fundingCaseType', function (fundingCaseType) {
        if (fundingCaseType) {
          const tagName = _4.get(fundingCaseType, 'properties.applicationReviewSidebarTagName', 'funding-default-application-sidebar');
          const template = '<' + tagName + '></' + tagName + '>';
          element.append($compile(template)(scope));
          unwatch();
        }
      });
    },
  };
}]);
