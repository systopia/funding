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
 * crmSearchTasksMenu component is used. fundingApplicationTasksDecorator has
 * the same scope as crmSearchTasksMenu and so can modify its controller.
 */
fundingModule.config(['$provide', function($provide) {
  $provide.decorator('crmSearchTasksMenuDirective', ['$delegate', function ($delegate) {
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

        // See TaskManager in searchDisplayTasksTrait
        const taskManager = ctrl.taskManager;
        const entityName = taskManager.getEntityName();

        if (entityName !== 'FundingApplicationProcess') {
          return;
        }

        const ts = CRM.ts('funding');

        let allowedActionsByApplication = {};

        taskManager.entityInfo = {
          title: ts('Application'),
          title_plural: ts('Applications'),
        };

        function updateAvailableTasks() {
          if (ctrl.ids.length === 0) {
            taskManager.tasks = [];
            return;
          }

          let tasks = {};
          const firstActions = allowedActionsByApplication[ctrl.ids[0]] || {};
          for (const [actionName, {label, confirm}] of Object.entries(firstActions)) {
            tasks[actionName] = {
              name: actionName,
              title: label,
              confirm: confirm,
            };
          }

          // Filter out tasks that are not available in all selected application
          // processes or have a different label.
          for (let i = 1; i < ctrl.ids.length; ++i) {
            const actions = allowedActionsByApplication[ctrl.ids[i]] || {};
            tasks = _4.pickBy(tasks, (task, actionName) => actions[actionName] && actions[actionName].label === task.title);
          }

          taskManager.tasks = Object.values(tasks);
        }

        let lastIds;
        function updateTasks() {
          if (_4.isEqual(lastIds, ctrl.ids)) {
            return;
          }

          lastIds = _4.clone(ctrl.ids);
          const idsToGetActions = [];
          ctrl.ids.forEach((id) => {
            if (!allowedActionsByApplication[id]) {
              idsToGetActions.push(id);
            }
          });

          if (idsToGetActions.length > 0) {
            taskManager.tasks = [];
            fundingApplicationProcessService.getAllowedActionsMultiple(idsToGetActions)
              .then((allowedActions) => _4.extend(allowedActionsByApplication, allowedActions))
              .then(updateAvailableTasks);
          } else {
            updateAvailableTasks();
          }
        }

        // Only triggered if a new ID is selected, not un deselect.
        $scope.$watch('$ctrl.ids', () => updateTasks());

        taskManager.getMetadata = () => {
          updateTasks();

          return new Promise((resolve) => resolve([]));
        };

        taskManager.doTask = function(action, ids) {
          if (!action.confirm || window.confirm(action.confirm)) {
            crmStatus(
              {},
              fundingApplicationProcessService.applyActionMultiple(ids, action.name)
            ).then(() => {
              allowedActionsByApplication = _4.pickBy(allowedActionsByApplication,
                (actions, id) => !ids.includes(parseInt(id)));

              this.refreshAfterTask();

              const event = new CustomEvent('applicationSearchTaskExecuted', {
                detail: {
                  entity: entityName,
                  ids: ids,
                  action: action.name,
                },
              });
              document.dispatchEvent(event);
            });
          }
        };

        // Only allowed actions are in the menu.
        ctrl.isActionAllowed = () => true;

      }
    ],
  };
});
