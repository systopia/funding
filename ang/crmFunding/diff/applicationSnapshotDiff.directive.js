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

/**
 * Directive for displaying a dialog with the difference between a snapshot and the current data.
 */
fundingModule.directive('fundingApplicationSnapshotDiff', [
  function () {
    return {
      restrict: 'AE',
      templateUrl: '~/crmFunding/diff/applicationSnapshotDiff.template.html',
      link: function (scope, element) {
        const dialog = element.find('dialog')[0] || element[0];
        if (dialog && typeof dialog.showModal === 'function') {
          dialog.showModal();
          // ensure scope and element cleanup:
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
