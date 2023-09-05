/*
 * Copyright (C) 2023 SYSTOPIA GmbH
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

/*
 * This decorator adds the fundingApplicationTasksDecorator directive if the
 * crmSearchTasks component is used. fundingApplicationTasksDecorator has the
 * same scope as crmSearchTasks and so can modify its controller.
 */
fundingModule.config(['$provide', function($provide) {
  $provide.decorator('crmSearchTasksDirective', ['$delegate', function ($delegate) {
    const templateUrl = $delegate[0].templateUrl;
    const template = $delegate[0].template;

    $delegate[0].template = (elem, attr) => {
      if (templateUrl) {
        const url = typeof templateUrl === 'function' ? templateUrl(elem, attr) : templateUrl;

        return '<ng-include src="\'' + url + '\'" funding-application-tasks-decorator></ng-include>';
      }

      return '<funding-application-tasks-decorator></funding-application-tasks-decorator>' +
        (typeof template === 'function' ? template(elem, attr) : template);
    };

    $delegate[0].templateUrl = undefined;

    return $delegate;
  }]);
}]);

fundingModule.directive('fundingApplicationTasksDecorator', function() {
  return {
    restrict: 'AE',
    scope: false,
    template: function () {
      return '';
    },
    controller: ['$scope', 'fundingApplicationProcessService', 'crmStatus',
      function ($scope, fundingApplicationProcessService, crmStatus) {
        const ctrl = $scope.$ctrl;
        const entityName = ctrl.entity;

        const allowedActionsByApplication = {};

        if (entityName !== 'FundingApplicationProcess') {
          return;
        }

        const ts = CRM.ts('funding');

        function updateAvailableTasks() {
          ctrl.tasks = [];
          const labels = [];
          for (const id of ctrl.ids) {
            const actions = allowedActionsByApplication[id] || {};
            for (const [actionName, {label, confirm}] of Object.entries(actions)) {
              // In case there are different actions with the same label only the first one is shown.
              if (!labels.includes(label)) {
                labels.push(label);
                ctrl.tasks.push({
                  name: actionName,
                  title: label,
                  confirm: confirm,
                });
              }
            }
          }
        }

        ctrl.entityInfo = {
          title: ts('Application'),
          title_plural: ts('Applications'),
        };

        ctrl.getTasks = function() {
          const idsToGetActions = [];
          ctrl.ids.forEach((id) => {
            if (!allowedActionsByApplication[id]) {
              idsToGetActions.push(id);
            }
          });

          if (idsToGetActions.length > 0) {
            ctrl.tasks = [];
            fundingApplicationProcessService.getAllowedActionsMultiple(idsToGetActions)
              .then((allowedActions) => _4.extend(allowedActionsByApplication, allowedActions))
              .then(updateAvailableTasks);
          } else {
            updateAvailableTasks();
          }
        };

        ctrl.isActionAllowed = function(action) {
          for (const id of ctrl.ids) {
            if (allowedActionsByApplication[id] && allowedActionsByApplication[id][action.name] &&
              allowedActionsByApplication[id][action.name].label !== action.title
            ) {
              return false;
            }
          }

          return true;
        };

        ctrl.doAction = function(action) {
          if (!ctrl.isActionAllowed(action)) {
            return;
          }

          if (!action.confirm || window.confirm(action.confirm)) {
            crmStatus(
              {},
              fundingApplicationProcessService.applyActionMultiple(ctrl.ids, action.name)
            ).then(ctrl.refresh);
          }
        };
      }
    ],
  };
});
