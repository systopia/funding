/*
 * Copyright (C) 2025 SYSTOPIA GmbH
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
 * This decorator adds the fundingClearingTasksDecorator directive if the
 * crmSearchTasksMenu component is used. fundingClearingTasksDecorator has
 * the same scope as crmSearchTasksMenu and so can modify its controller.
 */
fundingModule.config(['$provide', function($provide) {
  $provide.decorator('crmSearchTasksMenuDirective', ['$delegate', function ($delegate) {
    const templateUrl = $delegate[0].templateUrl;
    const template = $delegate[0].template;

    $delegate[0].template = (elem, attr) => {
      if (templateUrl) {
        const url = typeof templateUrl === 'function' ? templateUrl(elem, attr) : templateUrl;

        return '<ng-include src="\'' + url + '\'" funding-clearing-tasks-decorator></ng-include>';
      }

      return '<funding-clearing-tasks-decorator></funding-clearing-tasks-decorator>' +
        (typeof template === 'function' ? template(elem, attr) : template);
    };

    $delegate[0].templateUrl = undefined;

    return $delegate;
  }]);
}]);

fundingModule.directive('fundingClearingTasksDecorator', function() {
  return {
    restrict: 'AE',
    scope: false,
    template: function () {
      return '';
    },
    controller: ['$scope', 'fundingClearingProcessService', 'crmStatus',
      function ($scope, fundingClearingProcessService, crmStatus) {
        const ctrl = $scope.$ctrl;

        // See TaskManager in searchDisplayTasksTrait
        const taskManager = ctrl.taskManager;
        const entityName = taskManager.getEntityName();

        if (entityName !== 'FundingClearingProcess') {
          return;
        }

        const ts = CRM.ts('funding');

        let allowedActionsByClearing = {};

        let searchKitTasks;
        taskManager.getMetadata().then(() => {
          taskManager.entityInfo = taskManager.entityInfo || {};
          taskManager.entityInfo.title = ts('Clearing');
          taskManager.entityInfo.title_plural = ts('Clearings');
          searchKitTasks = taskManager.tasks;
        });

        function updateAvailableTasks() {
          if (ctrl.ids.length === 0) {
            taskManager.tasks = searchKitTasks;
            return;
          }

          let tasks = {};
          const firstActions = allowedActionsByClearing[ctrl.ids[0]] || {};
          for (const [actionName, {label, confirm}] of Object.entries(firstActions)) {
            tasks[actionName] = {
              name: actionName,
              title: label,
              confirm: confirm,
              _customTask: true,
            };
          }

          // Filter out tasks that are not available in all selected clearing
          // processes or have a different label.
          for (let i = 1; i < ctrl.ids.length; ++i) {
            const actions = allowedActionsByClearing[ctrl.ids[i]] || {};
            tasks = _4.pickBy(tasks, (task, actionName) => actions[actionName] && actions[actionName].label === task.title);
          }

          taskManager.tasks = searchKitTasks.concat(Object.values(tasks));
        }

        let lastIds;
        function updateTasks() {
          if (_4.isEqual(lastIds, ctrl.ids)) {
            return new Promise((resolve) => resolve([]));
          }

          lastIds = _4.clone(ctrl.ids);
          const idsToGetActions = [];
          ctrl.ids.forEach((id) => {
            if (!allowedActionsByClearing[id]) {
              idsToGetActions.push(id);
            }
          });

          if (idsToGetActions.length > 0) {
            taskManager.tasks = searchKitTasks;
            return fundingClearingProcessService.getAllowedActionsMultiple(idsToGetActions)
              .then((allowedActions) => _4.extend(allowedActionsByClearing, allowedActions))
              .then(updateAvailableTasks);
          } else {
            updateAvailableTasks();
            return new Promise((resolve) => resolve([]));
          }
        }

        // Only triggered if a new ID is selected, not un deselect.
        $scope.$watch('$ctrl.ids', () => updateTasks());

        taskManager.getMetadata = updateTasks;

        const parentDoTask = taskManager.doTask;
        taskManager.doTask = function(action, ids) {
          if (!action._customTask) {
            parentDoTask(action, ids);

            return;
          }

          if (!action.confirm || window.confirm(action.confirm)) {
            crmStatus(
              {},
              fundingClearingProcessService.applyActionMultiple(ids, action.name)
            ).then(() => {
              allowedActionsByClearing = _4.pickBy(allowedActionsByClearing,
                (actions, id) => !ids.includes(parseInt(id)));

              this.refreshAfterTask();

              const event = new CustomEvent('clearingSearchTaskExecuted', {
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

        const parentIsActionAllowed = ctrl.isActionAllowed;
        ctrl.isActionAllowed = function (action) {
          if (action._customTask) {
            return true;
          }

          return parentIsActionAllowed(action);
        };
      }
    ],
  };
});
