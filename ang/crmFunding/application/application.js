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

fundingModule.config(['$routeProvider', function($routeProvider) {
    $routeProvider.when('/funding/application/:applicationProcessId', {
      controller: 'fundingApplicationCtrl',
      controllerAs: '$ctrl',
      templateUrl: '~/crmFunding/application/application.template.html',
      resolve: {
        applicationProcess: ['$route', 'fundingApplicationProcessService', function($route, fundingApplicationProcessService) {
          return fundingApplicationProcessService.get($route.current.params.applicationProcessId);
        }],
        jsonSchema: ['$route', 'fundingApplicationProcessService', function($route, fundingApplicationProcessService) {
          return fundingApplicationProcessService.getJsonSchema($route.current.params.applicationProcessId);
        }],
      },
    });
  }]
);

fundingModule.controller('fundingApplicationCtrl', [
  '$scope', 'fundingApplicationProcessService', 'fundingApplicationProcessActivityService',
  'applicationProcess', 'jsonSchema',
  function($scope, fundingApplicationProcessService, fundingApplicationProcessActivityService,
           applicationProcess, jsonSchema) {
    const ts = $scope.ts = CRM.ts('funding');

    $scope.reviewStatusLabels = {
      null: ts('Undecided'),
      true: ts('Passed'),
      false: ts('Failed'),
    };

    $scope.statusLabels = {};
    fundingApplicationProcessService.getStatusLabels(applicationProcess.id)
        .then((labels) => $scope.statusLabels = labels);

    $scope.activities = {};
    $scope.loadActivities = function () {
      fundingApplicationProcessActivityService.get(applicationProcess.id)
          .then((result) => $scope.activities = result);
    };
    $scope.loadActivities();

    $scope.tab = 'application';
    $scope.applicationProcess = applicationProcess;
    $scope.jsonSchema = jsonSchema;

    $scope.canGoBack = History.length > 0;
  },
]);
