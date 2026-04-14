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
  '$sce',
  function (crmApi4, fundingApplicationProcessService, $compile, $rootScope, $sce) {
    const ts = CRM.ts('funding');

    /**
     * Escapes HTML special characters in a string.
     *
     * @param unsafe
     * @returns {string}
     */
    const escapeHtml = (unsafe) => {
      return (unsafe || '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
    };

    /**
     * Formats the diff lines between snapshot and current JSON.
     *
     * @param snapshotJSON
     * @param currentJSON
     * @returns {string}
     */
    const formatDiffLines = (snapshotJSON, currentJSON) => {
      const snapshotLines = snapshotJSON.split('\n');
      const currentLines = currentJSON.split('\n');
      const maxLines = Math.max(snapshotLines.length, currentLines.length);
      let snapshotResult = '', currentResult = '';

      for (let i = 0; i < maxLines; i++) {
        const snapshotLine = snapshotLines[i] || '';
        const currentLine = currentLines[i] || '';
        const isDiff = snapshotLine !== currentLine;
        const snapshotClass = isDiff ? 'funding-diff-line-removed' : '';
        const currentClass = isDiff ? 'funding-diff-line-added' : '';

        if (i < snapshotLines.length) {
          snapshotResult += `<div class="${snapshotClass}">${escapeHtml(snapshotLine) || '&nbsp;'}</div>`;
        }
        if (i < currentLines.length) {
          currentResult += `<div class="${currentClass}">${escapeHtml(currentLine) || '&nbsp;'}</div>`;
        }
      }
      return [snapshotResult, currentResult];
    };

    /**
     * Normalizes the value for comparison in diff.
     *
     * @param val
     * @returns {string}
     */
    const normalizeValue = (val) => {
      if (val === null || val === undefined || val === '' || (typeof val === 'string' && val.trim() === '')) {
        return '';
      }
      if (typeof val === 'string') {
        return val.trim();
      }
      if (typeof val !== 'object') {
        return val;
      }
      if (Array.isArray(val)) {
        const normalizedArray = val.map(normalizeValue).filter(v => v !== '');
        if (normalizedArray.length === 0) {
          return '';
        }
        if (normalizedArray.every(v => typeof v !== 'object')) {
          normalizedArray.sort();
        }
        return normalizedArray;
      }
      const sortedObj = {};
      const keys = Object.keys(val).sort();
      let hasVisibleProps = false;
      keys.forEach(k => {
        if (k.startsWith('_')) {
          return;
        }
        if (k === 'id' || k === 'identifier') {
          return;
        }
        const v = normalizeValue(val[k]);
        if (v !== '') {
          sortedObj[k] = v;
          hasVisibleProps = true;
        }
      });
      return hasVisibleProps ? sortedObj : '';
    };

    /**
     * Prepares combined data for snapshot comparison.
     *
     * @param requestData
     * @param costItems
     * @returns {{}}
     */
    const prepareCombinedData = (requestData, costItems) => {
      const data = {};
      Object.keys(requestData || {}).forEach(key => {
        data[key] = requestData[key];
      });
      (costItems || []).forEach(item => {
        data[item.type] = item;
      });
      return data;
    };

    /**
     * Calculates changes between snapshot and current data.
     *
     * @param snapshotData
     * @param currentData
     * @returns {[]}
     */
    const calculateChanges = (snapshotData, currentData) => {
      const allKeys = new Set([...Object.keys(snapshotData), ...Object.keys(currentData)]);
      const sortedKeys = Array.from(allKeys)
        .filter(key => !key.startsWith('_'))
        .sort();

      const changes = [];
      sortedKeys.forEach(key => {
        const snapshotJSON = JSON.stringify(normalizeValue(snapshotData[key]), null, 2);
        const currentJSON = JSON.stringify(normalizeValue(currentData[key]), null, 2);

        if (snapshotJSON !== currentJSON) {
          const [snapshotDiff, currentDiff] = formatDiffLines(snapshotJSON, currentJSON);
          changes.push({
            key: key,
            snapshotDiff: $sce.trustAsHtml(snapshotDiff),
            currentDiff: $sce.trustAsHtml(currentDiff),
          });
        }
      });
      return changes;
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
        select: ['request_data', 'cost_items', 'creation_date'],
      }).then(result => result[0]);

      const currentPromise = fundingApplicationProcessService.getFormData(applicationProcessId);
      const currentCostsPromise = crmApi4('FundingApplicationCostItem', 'get', {
        where: [['application_process_id', '=', applicationProcessId]],
      });

      return Promise.all([snapshotPromise, currentPromise, currentCostsPromise])
        // destructuring the result
        .then(([snapshot, current, currentCosts]) => {
          const snapshotData = prepareCombinedData(snapshot.request_data, snapshot.cost_items);
          const currentData = prepareCombinedData(current, currentCosts);

          const changes = calculateChanges(snapshotData, currentData);

          const scope = $rootScope.$new();
          scope.snapshot = snapshot;
          scope.changes = changes; //ng-repeat
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
            console.log('closing diff dialog');
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
    };
  },
]);

/**
 * Directive for displaying a dialog with the difference between a snapshot and the current data.
 */
fundingModule.directive('fundingApplicationSnapshotDiff', [
  function () {
    return {
      restrict: 'AE',
      templateUrl: '~/crmFunding/application/applicationSnapshotDiff.template.html',
      link: function (scope, element) {
        const dialog = element.find('dialog')[0] || element[0];
        if (dialog && typeof dialog.showModal === 'function') {
          dialog.showModal();
          // ensure scope and element cleanpu:
          dialog.addEventListener('close', function () {
            if (typeof scope.close === 'function') {
              scope.close();
            }
          });
        }
      },
    };
  },
]);
