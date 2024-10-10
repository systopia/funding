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

fundingModule.directive('fundingDefaultApplicationSidebar', function() {
  return {
    restrict: 'AE',
    scope: false,
    templateUrl: '~/crmFunding/application/defaultApplicationSidebar.template.html',
    controllerAs: '$ctrl',
    controller: ['$scope', function ($scope) {
      this.ts = CRM.ts('funding');
      this.applicationProcessLabel = $scope.fundingCaseType.application_process_label || this.ts('Application');
    }],
  };
});
