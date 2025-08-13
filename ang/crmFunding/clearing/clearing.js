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

fundingModule.config(['$routeProvider', function($routeProvider) {
    $routeProvider.when('/funding/clearing/:clearingProcessId', {
      controller: 'fundingClearingCtrl',
      controllerAs: '$ctrl',
      templateUrl: '~/crmFunding/clearing/clearing.template.html',
      resolve: {
        clearingProcess: ['$route', 'fundingClearingProcessService', function($route, fundingClearingProcessService) {
          return fundingClearingProcessService.get($route.current.params.clearingProcessId, ['amount_cleared', 'currency']);
        }],
        form: ['$route', 'fundingClearingProcessService', function($route, fundingClearingProcessService) {
          return fundingClearingProcessService.getForm($route.current.params.clearingProcessId);
        }],
      },
    });
  }]
);

fundingModule.controller('fundingClearingCtrl', [
  '$scope', 'fundingClearingProcessService', 'fundingApplicationProcessActivityService',
  'fundingApplicationProcessService',
  'clearingProcess', 'form',
  function($scope, fundingClearingProcessService,
           fundingApplicationProcessActivityService, fundingApplicationProcessService,
           clearingProcess, form) {
    const ts = $scope.ts = CRM.ts('funding');

    $scope.$watch('tab', () => window.setTimeout(fixHeights, 100));

    $scope.reviewStatusLabels = {
      null: ts('Undecided'),
      true: ts('Passed'),
      false: ts('Failed'),
    };

    $scope.applicationStatusOptions = {};
    fundingApplicationProcessService.getStatusOptions({id: clearingProcess.application_process_id})
      .then((options) => $scope.applicationStatusOptions = options);

    $scope.clearingStatusOptions = {};
    fundingClearingProcessService.getStatusOptions()
      .then((options) => $scope.clearingStatusOptions = options);

    $scope.activities = [];
    $scope.loadActivities = function () {
      fundingApplicationProcessActivityService.get(clearingProcess.application_process_id)
        .then((result) => $scope.activities = result);
    };
    $scope.loadActivities();

    $scope.tab = 'clearing';
    $scope.clearingProcess = clearingProcess;
    $scope.form = form;
  },
]);
