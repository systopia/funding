/*
 * Copyright (C) 2023 SYSTOPIA GmbH
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

/**
 * This directive allows to dynamically change a font awesome icon.
 */
fundingModule.directive('fundingFa', function() {
  return {
    restrict: 'E',
    transclude: true,
    scope: {
      // icon name with "fa-" prefix.
      icon: '=',
    },
    templateUrl: '~/crmFunding/fontAwesome/fa.template.html',
    link: function (scope) {
      // This toggles scope.show on icon change which makes the template re-render.
      scope.$watch('show', function (newValue) {
        if (newValue === false) {
          scope.show = !!scope.icon;
        }
      });
      scope.$watch('icon', function (newValue) {
        scope.show = newValue ? false : undefined;
      });
    },
  };
});
