/*
 * Copyright (C) 2026 SYSTOPIA GmbH
 *
 * This program is free software: you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License as published by the Free
 * Software Foundation, either version 3 of the License, or (at your option) any
 * later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

'use strict';

fundingDvvModule.directive('fundingDvvApplicationSidebar', function() {
  return {
    restrict: 'AE',
    scope: false,
    templateUrl: '~/crmFundingDvv/dvvApplicationSidebar.template.html',
    controllerAs: '$ctrl',
    controller: ['$scope', function ($scope) {
      const ctrl = this;
      this.ts = CRM.ts('funding');
      this.applicationProcessLabel = $scope.fundingCaseType.application_process_label || this.ts('Application');

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
