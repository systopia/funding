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

fundingModule.directive('fundingApplicationReviewers', function() {
  return {
    restrict: 'E',
    scope: false,
    templateUrl: '~/crmFunding/application/applicationReviewers.template.html',
    controller: ['$scope', 'crmStatus', 'fundingApplicationProcessService',
      function($scope, crmStatus, fundingApplicationProcessService) {
        $scope.ts = CRM.ts('funding');
        fundingApplicationProcessService.getOptionLabels($scope.applicationProcess.id, 'reviewer_calc_contact_id')
          .then((options) => $scope.possibleReviewersCalculative = options);

        fundingApplicationProcessService.getOptionLabels($scope.applicationProcess.id, 'reviewer_cont_contact_id')
          .then((options) => $scope.possibleReviewersContent = options);
      },
    ],
  };
});
