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

fundingModule.directive('fundingClearingSidebar', function() {
  return {
    restrict: 'AE',
    scope: false,
    templateUrl: '~/crmFunding/clearing/clearingSidebar.template.html',
    controllerAs: '$ctrl',
    controller: ['$scope', function ($scope) {
      const ctrl = this;
      this.ts = CRM.ts('funding');

      $scope.$watch('activities', function (activities) {
        if (activities) {
          for (const activity of activities) {
            if (activity['activity_type_id:name'] === 'funding_application_comment_internal') {
              ctrl.lastInternalCommentActivity = activity;
              break;
            }
          }
        }
      });
    }],
  };
});
