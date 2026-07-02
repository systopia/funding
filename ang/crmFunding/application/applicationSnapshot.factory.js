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

fundingModule.factory('fundingApplicationSnapshotService', [
  'crmApi4',
  'fundingApplicationProcessService',
  '$compile',
  '$rootScope',
  'fundingDiffService',
  function (crmApi4, fundingApplicationProcessService, $compile, $rootScope, fundingDiffService) {
    const ts = CRM.ts('funding');

    /**
     * Prepares combined data for snapshot comparison.
     *
     * @param requestData
     * @returns {Object}
     */
    const prepareCombinedData = (requestData) => {
      const data = {};
      Object.keys(requestData || {}).forEach(key => {
        data[key] = requestData[key];
      });
      return data;
    };

    /**
     * Calculates changes between snapshot and current data as a single JSON diff.
     *
     * @param snapshotData
     * @param currentData
     * @returns {Object}
     */
    const calculateChanges = (snapshotData, currentData) => {
      const diffResult = fundingDiffService.calculateChanges(snapshotData, currentData);
      return {
        snapshotDiff: diffResult.leftDiff,
        currentDiff: diffResult.rightDiff,
        hasDifferences: diffResult.hasDifferences,
      };
    };

    /**
     * Opens a dialog showing the difference between a snapshot and the current data.
     *
     * @param {number} applicationProcessId
     * @param {number} snapshotId
     */
    function openDiffDialog(applicationProcessId, snapshotId) {
      const snapshotPromise = crmApi4('FundingApplicationSnapshot', 'get', {
        where: [['id', '=', snapshotId]],
        select: ['request_data', 'creation_date'],
      }).then(result => result[0]);

      const currentPromise = fundingApplicationProcessService.getFormData(applicationProcessId);

      return Promise.all([snapshotPromise, currentPromise])
        // destructuring the result
        .then(([applicationSnapshot, currentApplication]) => {
          const snapshotData = prepareCombinedData(applicationSnapshot.request_data);
          const currentData = prepareCombinedData(currentApplication);

          const scope = $rootScope.$new();
          scope.snapshotData = snapshotData;
          scope.currentData = currentData;
          const diffResult = calculateChanges(snapshotData, currentData);
          scope.hasDifferences = diffResult.hasDifferences;
          scope.snapshot = applicationSnapshot;
          scope.ts = ts;

          // make sure any dialog modal is removed before creating a new one
          let dialogNode = document.getElementById('funding-diff-dialog');
          if (dialogNode) {
            angular.element(dialogNode).remove();
          }

          const element = $compile('<div funding-application-snapshot-diff></div>')(scope);
          angular.element(document.body).append(element);

          // remove the dialog on close
          scope.close = () => {
            const dialog = document.getElementById('funding-diff-dialog');
            if (dialog) {
              dialog.close();
            }
            scope.$destroy();
            element.remove();
          };
        });
    }

    return {
      openDiffDialog: openDiffDialog,
      calculateChanges: calculateChanges,
    };
  },
]);
