/*
 * Copyright (C) 2026 SYSTOPIA GmbH
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

fundingModule.directive('fundingJsonDiff', [
  'fundingApplicationSnapshotService',
  function (fundingApplicationSnapshotService) {
    return {
      restrict: 'E',
      scope: {
        leftData: '=',
        rightData: '=',
        leftLabel: '@',
        rightLabel: '@',
      },
      templateUrl: '~/crmFunding/application/fundingJsonDiff.template.html',
      link: function (scope) {
        scope.ts = CRM.ts('funding');
        scope.$watchGroup(['leftData', 'rightData'], function () {
          if (scope.leftData && scope.rightData) {
            const diffResult = fundingApplicationSnapshotService.calculateChanges(scope.leftData, scope.rightData);
            scope.leftDiff = diffResult.snapshotDiff;
            scope.rightDiff = diffResult.currentDiff;
            scope.hasDifferences = diffResult.hasDifferences;
          }
        });
      },
    };
  },
]);
