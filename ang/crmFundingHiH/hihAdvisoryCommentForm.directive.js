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

fundingHiHModule.directive('fundingHihAdvisoryCommentForm', function() {
  return {
    restrict: 'E',
    scope: {
      applicationProcess: '=',
      onCommentAdded: '&',
    },
    templateUrl: '~/crmFundingHiH/hihAdvisoryCommentForm.template.html',
    controllerAs: '$ctrl',
    controller: ['$scope', 'crmApi4', 'crmStatus', function ($scope, crmApi4, crmStatus) {
      $scope.text = '';
      $scope.addComment = function () {
        $scope.submitting = true;
        crmStatus({}, crmApi4('BshFundingAdvisoryComment', 'addComment', {
          applicationProcessId: $scope.applicationProcess.id,
          text: $scope.text.trim(),
        })).then(() => {
          $scope.text = '';
          $scope.onCommentAdded()();
        }).finally(() => {
          $scope.submitting = false;
        });
      };
    }],
  };
});
