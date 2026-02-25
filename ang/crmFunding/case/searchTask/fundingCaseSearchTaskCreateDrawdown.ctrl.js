/*
 * Copyright (C) 2026 SYSTOPIA GmbH
 *
 * This program is free software: you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License as published by the Free
 * Software Foundation, either version 3 of the License, or (at your option) any
 * later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

(function(angular, $, _) {
  "use strict";

  angular.module('crmFunding').controller('fundingCaseSearchTaskCreateDrawdown', function($scope, crmApi4, dialogService) {
    const ts = $scope.ts = CRM.ts('funding');
    const model = $scope.model;
    const ctrl = this;

    // HTML in AngularJS expressions is not possible, thus we define the string here.
    $scope.amountPercentHelp = ts('The amount of each created drawdown is: <code>This value × amount approved ÷ 100</code>. In case that exceeds the amount available, the amount available will be used.');

    this.cancel = function() {
      dialogService.cancel('crmSearchTask');
    };

    this.createDrawdowns = function() {
      if (ctrl.run) {
        return;
      }

      ctrl.run = true;
      $('.ui-dialog-titlebar button').hide();

      crmApi4('FundingCase', 'createDrawdowns', {
        ids: model.ids,
        amountPercent: $scope.amountPercent,
      }).then(() => {
        dialogService.close('crmSearchTask');
      }, (failure) => {
        if (failure.error_message) {
          CRM.alert(ts(
            'An error occurred while attempting to create drawdowns: %1',
            {1: failure.error_message}
          ), ts('Error'), 'error');
        }
        else {
          CRM.alert(ts(
            'An error occurred while attempting to create drawdowns.'
          ), ts('Error'), 'error');
        }
        dialogService.close('crmSearchTask');
      });
    };
  });
})(angular, CRM.$, CRM._);
