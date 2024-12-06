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

fundingModule.directive('fundingHihApplicationSidebar', function() {
  return {
    restrict: 'AE',
    scope: false,
    templateUrl: '~/crmFundingHiH/hihApplicationSidebar.template.html',
    controllerAs: '$ctrl',
    controller: ['$scope', 'crmApi4', 'crmStatus', function ($scope, crmApi4, crmStatus) {
      this.ts = CRM.ts('funding');

      $scope.prioritaetOptions = {};
        crmApi4('FundingApplicationProcess', 'getFields', {
        loadOptions: true,
        where: [['name', '=', 'bsh_funding_application_extra.prioritaet']],
        select: ["options"]
      }).then(function(fields) {
        $scope.prioritaetOptions = fields[0].options;
      });

      $scope.updateNdrBerichterstattung = function(data) {
        return crmStatus({}, crmApi4('FundingApplicationProcess', 'setNdrBerichterstattung', {
          id: $scope.applicationProcess.id,
          berichterstattung: data,
        }));
      };

      $scope.showNdrBerichterstattung = function() {
        if ($scope.applicationProcess['bsh_funding_application_extra.ndr_berichterstattung']) {
          return 'Ja';
        }

        if ($scope.applicationProcess['bsh_funding_application_extra.ndr_berichterstattung'] === false) {
          return 'Nein';
        }

        return 'unbekannt';
      };

      $scope.updatePrioritaet = function(data) {
        return crmStatus({}, crmApi4('FundingApplicationProcess', 'setBshPrioritaet', {
          id: $scope.applicationProcess.id,
          prioritaet: data,
        }));
      };

      $scope.showPrioritaet = function() {
        return $scope.prioritaetOptions[$scope.applicationProcess['bsh_funding_application_extra.prioritaet']] || 'nicht definiert';
      };
    }],
  };
});
