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

fundingModule.component('fundingApplication', {
  restrict: 'E',
  bindings: {
    id: '=',
    tab: '=',
  },
  templateUrl: '~/crmFunding/application/application.template.html',
  controller: ['$scope', 'fundingApplicationProcessService', 'fundingApplicationProcessActivityService',
    'fundingCaseService', 'fundingCaseTypeService',
    'fundingProgramService', 'fundingClearingProcessService', function ($scope, fundingApplicationProcessService, fundingApplicationProcessActivityService, fundingCaseService, fundingCaseTypeService, fundingProgramService, fundingClearingProcessService) {
      const ts = $scope.ts = CRM.ts('funding');

      $scope.$watch('tab', () => window.setTimeout(fixHeights, 100));

      function reloadApplicationProcess() {
        return fundingApplicationProcessService.get($scope.applicationProcess.id).then(
          (applicationProcess) => $scope.applicationProcess = applicationProcess
        );
      }

      function reloadApplicationProcessJsonSchema() {
        return fundingApplicationProcessService.getJsonSchema($scope.applicationProcess.id).then(
          (jsonSchema) => $scope.applicationForm.jsonSchema = jsonSchema
        );
      }

      function reloadClearingProcess() {
        return fundingClearingProcessService.get($scope.clearingProcess.id, ['amount_cleared', 'currency']).then(
          (clearingProcess) => {
            $scope.clearingProcess = clearingProcess;
          }
        );
      }

      function reloadClearingForm() {
        return fundingClearingProcessService.getForm($scope.clearingProcess.id).then(
          (form) => {
            $scope.clearingForm = form;
          }
        );
      }

      function reloadFundingCase() {
        return fundingCaseService.get($scope.fundingCase.id).then(
          (fundingCase) => $scope.fundingCase = fundingCase
        );
      }

      const ctrl = this;
      ctrl.$onInit = () => {
        const id = $scope.$ctrl.id;
        $scope.statusOptions = {};
        fundingApplicationProcessService.get(id).then(function (applicationProcess) {
          $scope.applicationProcess = applicationProcess;

          fundingApplicationProcessService.getStatusOptions({
            id: id,
            fundingCaseId: applicationProcess.funding_case_id
          })
            .then((options) => $scope.statusOptions = options);

          fundingCaseService.get(applicationProcess.funding_case_id).then(function (fundingCase) {
            $scope.fundingCase = fundingCase;
            fundingCaseTypeService.get(fundingCase.funding_case_type_id).then(
              (fundingCaseType) => $scope.fundingCaseType = fundingCaseType
            );

            fundingProgramService.get(fundingCase.funding_program_id).then(function (fundingProgram) {
              $scope.fundingProgram = fundingProgram;
            });
          });
        });

        fundingApplicationProcessService.getForm(id).then(function (form) {
          $scope.applicationForm = form;
        });

        fundingClearingProcessService.getByApplicationProcessId(id)
          .then((clearingProcess) => {
            $scope.clearingProcess = clearingProcess;
            if (clearingProcess.status !== 'not-started') {
              reloadClearingForm();
            }
          });

        $scope.hasPermission = function (permission) {
          return $scope.fundingCase && $scope.fundingCase.permissions.includes(permission);
        };

        $scope.reviewStatusLabels = {
          null: ts('Undecided'),
          true: ts('Passed'),
          false: ts('Failed'),
        };

        $scope.clearingStatusOptions = {};
        fundingClearingProcessService.getStatusOptions()
          .then((options) => $scope.clearingStatusOptions = options);

        $scope.activities = [];
        $scope.loadActivities = function () {
          fundingApplicationProcessActivityService.get(id)
            .then((result) => $scope.activities = result);
        };
        $scope.loadActivities();

        $scope.onPostSubmit = function () {
          withOverlay(reloadApplicationProcess());
          withOverlay(reloadApplicationProcessJsonSchema());
          withOverlay(reloadClearingProcess().then(() => {
            if ($scope.clearingProcess.status !== 'not-started') {
              withOverlay(reloadClearingForm());
            }
          }));
          withOverlay(reloadFundingCase());
          $scope.loadActivities();
        };

        $scope.tab = $scope.$ctrl.tab || 'application';
      };
    },
  ],
});
