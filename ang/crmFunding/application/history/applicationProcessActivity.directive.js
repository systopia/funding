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

fundingModule.directive('fundingApplicationProcessActivity', [function() {
  return {
    restrict: 'E',
    scope: {
      activity: '=',
      statusLabels: '=',
      reviewStatusLabels: '=',
    },
    templateUrl: '~/crmFunding/application/history/applicationProcessActivity.template.html',
    controller: function($scope) {
      $scope.ts = CRM.ts('funding');

      function getActivityTemplateUrl(activity) {
        switch (activity['activity_type_id:name']) {
          case 'funding_application_status_change':
            return '~/crmFunding/application/history/activities/statusChange.template.html';
          case 'funding_application_create':
            return '~/crmFunding/application/history/activities/create.template.html';
          case 'funding_application_comment':
            return '~/crmFunding/application/history/activities/comment.template.html';
          case 'funding_application_review_status_change':
            return '~/crmFunding/application/history/activities/reviewStatusChange.template.html';
          default:
            return null;
        }
      }

      $scope.templateUrl = getActivityTemplateUrl($scope.activity);
    },
  };
}]);
