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

fundingModule.directive('fundingApplicationProcessHistory', [function() {
  return {
    restrict: 'E',
    scope: {
      activities: '=',
      statusLabels: '=',
      reviewStatusLabels: '=',
    },
    templateUrl: '~/crmFunding/application/history/applicationProcessHistory.template.html',
    controller: function($scope) {
      $scope.ts = CRM.ts('funding');

      $scope.commentsHidden = false;
      $scope.workflowActivitiesHidden = false;

      $scope.toggleComments = function() {
        $scope.commentsHidden = !$scope.commentsHidden;
      };

      $scope.toggleWorkflowActivities = function () {
        $scope.workflowActivitiesHidden = !$scope.workflowActivitiesHidden;
      };

      $scope.isActivityHidden = function (activity) {
        switch (activity['activity_type_id:name']) {
          case 'funding_application_status_change':
            return $scope.workflowActivitiesHidden;
          case 'funding_application_review_status_change':
            return $scope.workflowActivitiesHidden;
          case 'funding_application_create':
            return $scope.workflowActivitiesHidden;
          case 'funding_application_comment_external':
          case 'funding_application_comment_internal':
            return $scope.commentsHidden;
          default:
            return false;
        }
      };
    },
  };
}]);
