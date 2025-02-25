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
 * This decorator adds the fundingDrawdownTasksDecorator directive if the
 * crmSearchTasksMenu component is used. fundingDrawdownTasksDecorator has
 * the same scope as crmSearchTasksMenu and so can modify its controller.
 */
fundingModule.config(['$provide', function($provide) {
  $provide.decorator('crmSearchTasksMenuDirective', ['$delegate', function ($delegate) {
    const templateUrl = $delegate[0].templateUrl;
    const template = $delegate[0].template;

    $delegate[0].template = (elem, attr) => {
      if (templateUrl) {
        const url = typeof templateUrl === 'function' ? templateUrl(elem, attr) : templateUrl;

        return '<ng-include src="\'' + url + '\'" funding-drawdown-tasks-decorator></ng-include>';
      }

      return '<funding-drawdown-tasks-decorator></funding-drawdown-tasks-decorator>' +
        (typeof template === 'function' ? template(elem, attr) : template);
    };

    $delegate[0].templateUrl = undefined;

    return $delegate;
  }]);
}]);

fundingModule.directive('fundingDrawdownTasksDecorator', function() {
  return {
    restrict: 'AE',
    scope: false,
    template: function () {
      return '';
    },
    controller: ['$scope', 'crmApi4', 'crmStatus',
      function ($scope, crmApi4, crmStatus) {
        const ctrl = $scope.$ctrl;

        // See TaskManager in searchDisplayTasksTrait
        const taskManager = ctrl.taskManager;
        const entityName = taskManager.getEntityName();

        if (entityName !== 'FundingDrawdown') {
          return;
        }

        const ts = CRM.ts('funding');

        let allowedActionsByDrawdown = {};

        let searchKitTasks;
        taskManager.getMetadata().then(() => {
          taskManager.entityInfo = {
            title: ts('Drawdown'),
            title_plural: ts('Drawdowns'),
          };

          searchKitTasks = taskManager.tasks;
        });

        function updateAvailableTasks() {
          if (ctrl.ids.length === 0) {
            taskManager.tasks = searchKitTasks;
            return;
          }

          let tasks = {};
          const firstActions = allowedActionsByDrawdown[ctrl.ids[0]] || {};
          for (const [actionName, {label, confirm}] of Object.entries(firstActions)) {
            tasks[actionName] = {
              name: actionName,
              title: label,
              confirm: confirm,
              _customTask: true,
            };
          }

          // Filter out tasks that are not available in all selected drawdowns
          // have a different label.
          for (let i = 1; i < ctrl.ids.length; ++i) {
            const actions = allowedActionsByDrawdown[ctrl.ids[i]] || {};
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
            if (!allowedActionsByDrawdown[id]) {
              idsToGetActions.push(id);
            }
          });

          if (idsToGetActions.length > 0) {
            taskManager.tasks = searchKitTasks;
            crmApi4('FundingDrawdown', 'get', {
              select: ['id', 'CAN_review'],
              where: [['id', 'IN', idsToGetActions]]
            }).then(function (drawdowns) {
              for (const drawdown of drawdowns) {
                if (drawdown.CAN_review) {
                  allowedActionsByDrawdown[drawdown.id] = {
                    accept: {label: ts('Accept'), confirm: ts('Do you want to accept the selected drawdowns?')},
                    reject: {label: ts('Reject'), confirm: ts('Do you want to reject the selected drawdowns?')},
                  };
                }
                else {
                  allowedActionsByDrawdown[drawdown.id] = {};
                }
              }

              updateAvailableTasks();
            });
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

          CRM.confirm({message: action.confirm}).on('crmConfirm:yes', function() {
            crmStatus({}, crmApi4('FundingDrawdown', action.name + 'Multiple', {ids}).then(() => {
              for (const id of ids) {
                allowedActionsByDrawdown[id] = {};
              }

              if (action.name === 'accept') {
                window.open(CRM.url('civicrm/funding/drawdown-document/download', {drawdownIds: ids.join(',')}));
              }

              taskManager.refreshAfterTask();

              const event = new CustomEvent('drawdownSearchTaskExecuted', {
                detail: {
                  entity: entityName,
                  ids: ids,
                  action: action.name,
                },
              });
              document.dispatchEvent(event);
            }));
          });
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
