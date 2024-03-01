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

fundingModule.directive('fundingClearingReviewers', function() {
  return {
    restrict: 'E',
    scope: false,
    templateUrl: '~/crmFunding/clearing/clearingReviewers.template.html',
    controller: ['$scope', 'crmStatus', 'fundingClearingProcessService',
      function($scope, crmStatus, fundingClearingProcessService) {
        $scope.ts = CRM.ts('funding');
        fundingClearingProcessService.getOptionLabels($scope.clearingProcess.id, 'reviewer_calc_contact_id')
          .then((options) => $scope.possibleReviewersCalculative = options);

        fundingClearingProcessService.getOptionLabels($scope.clearingProcess.id, 'reviewer_cont_contact_id')
          .then((options) => $scope.possibleReviewersContent = options);
      },
    ],
  };
});
