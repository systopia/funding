/*
 * Copyright (C) 2026 SYSTOPIA GmbH
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
 * This decorator adds the fundingChangeRequestTasksDecorator directive if the
 * crmSearchTasksMenu component is used. fundingChangeRequestTasksDecorator has
 * the same scope as crmSearchTasksMenu and so can modify its controller.
 */
fundingModule.config([
  '$provide', function ($provide) {
    $provide.decorator('crmSearchTasksMenuDirective', [
      '$delegate', function ($delegate) {
        const templateUrl = $delegate[0].templateUrl;
        const template = $delegate[0].template;

        $delegate[0].template = (elem, attr) => {
          if (templateUrl) {
            const url = typeof templateUrl === 'function' ? templateUrl(elem, attr) : templateUrl;
            return '<ng-include src="\'' + url + '\'" funding-change-request-tasks-decorator></ng-include>';
          }
          return '<funding-change-request-tasks-decorator></funding-change-request-tasks-decorator>' +
            (typeof template === 'function' ? template(elem, attr) : template);
        };
        $delegate[0].templateUrl = undefined;
        return $delegate;
      },
    ]);
  },
]);

fundingModule.directive('fundingChangeRequestTasksDecorator', function () {
  return {
    restrict: 'AE',
    scope: false,
    template: function () {
      return '';
    },
    controller: [
      '$scope', 'crmApi4', 'crmStatus',
      function ($scope, crmApi4, crmStatus) {
        const ctrl = $scope.$ctrl;
        const taskManager = ctrl.taskManager;

        taskManager.getMetadata().then(() => {
          const entityName = taskManager.entityInfo.name;
          if (entityName !== 'FundingAmountApprovedChangeRequest') {
            return;
          }

          const ts = CRM.ts('funding');
          taskManager.entityInfo.title = ts('Amount Approved Change Request');
          taskManager.entityInfo.title_plural = ts('Amount Approved Change Requests');

          let allowedActionsByChangeRequest = {};
          const searchKitTasks = taskManager.tasks;

          function updateAvailableTasks() {
            if (ctrl.ids.length === 0) {
              taskManager.tasks = searchKitTasks;
              return;
            }

            let tasks = {};
            const firstActions = allowedActionsByChangeRequest[ctrl.ids[0]] || {};
            for (const [actionName, {label, confirm}] of Object.entries(firstActions)) {
              tasks[actionName] = {
                name: actionName,
                title: label,
                confirm: confirm,
                _customTask: true,
              };
            }

            // Filter out tasks that are not available in all selected change requests
            // or have a different label.
            for (let i = 1; i < ctrl.ids.length; ++i) {
              const actions = allowedActionsByChangeRequest[ctrl.ids[i]] || {};
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
              if (!allowedActionsByChangeRequest[id]) {
                idsToGetActions.push(id);
              }
            });

            if (idsToGetActions.length > 0) {
              taskManager.tasks = searchKitTasks;
              return crmApi4('FundingAmountApprovedChangeRequest', 'get', {
                select: ['id', 'CAN_review'],
                where: [['id', 'IN', idsToGetActions]]
              }).then(function (changeRequests) {
                for (const req of changeRequests) {
                  if (req.CAN_review) {
                    allowedActionsByChangeRequest[req.id] = {
                      approve: {
                        label: ts('Approve'),
                        confirm: ts('Do you want to approve the selected change requests?'),
                      },
                      approvePartial: {
                        label: ts('Approve with change'),
                        confirm: ts('Do you want to approve the selected change requests with changes?'),
                      },
                      reject: {
                        label: ts('Reject'),
                        confirm: ts('Do you want to reject the selected change requests?'),
                      },
                    };
                  }
                  else {
                    allowedActionsByChangeRequest[req.id] = {};
                  }
                }

                updateAvailableTasks();
              });
            }
            else {
              updateAvailableTasks();
              return new Promise((resolve) => resolve([]));
            }
          }

          // Only triggered if a new ID is selected, not on deselect.
          $scope.$watch('$ctrl.ids', () => updateTasks());

          taskManager.getMetadata = updateTasks;

          const parentDoTask = taskManager.doTask;
          taskManager.doTask = function (action, ids) {
            if (!action._customTask) {
              parentDoTask(action, ids);

              return;
            }

            const actionName = action.name;
            const confirmMsg = action.confirm;

            if (!ids || ids.length === 0) {
              CRM.alert(ts('No change requests selected.'), ts('Notice'), 'warning');
              return;
            }

            if (actionName === 'approvePartial') {
              CRM.confirm({
                title: ts('Approve with change'),
                width: '400px',
                message: '<div class="form-group"><label for="change-request-approved-amount">' + ts('Approved amount') + '</label><input type="number" id="change-request-approved-amount" class="form-control" /></div>',
                options: { no: ts('Cancel'), yes: ts('Confirm') },
              }).on('crmConfirm:yes', function () {
                const amount = document.getElementById('change-request-approved-amount').value;
                executeTask(actionName, ids, {
                  amountApproved: amount,
                });
              });
            } else {
              const yesLabels = {
                approve: ts('Approve'),
                reject: ts('Reject'),
              };
              const yesLabel = yesLabels[actionName] || ts('Continue');

              CRM.confirm({
                message: confirmMsg,
                options: { no: ts('Cancel'), yes: yesLabel },
              }).on('crmConfirm:yes', function () {
                executeTask(actionName, ids, {});
              });
            }
          };

          function executeTask(actionName, ids, params) {
            const apiParams = Object.assign({ ids: ids }, params);
            crmStatus({}, crmApi4('FundingAmountApprovedChangeRequest', actionName, apiParams).then(() => {
              for (const id of ids) {
                allowedActionsByChangeRequest[id] = {};
              }
              taskManager.refreshAfterTask();

              const event = new CustomEvent('fundingAmountApprovedChangeRequestSearchTaskExecuted', {
                detail: {
                  entity: entityName,
                  ids: ids,
                  action: actionName,
                },
              });
              document.dispatchEvent(event);
            }));
          }

          const parentIsActionAllowed = ctrl.isActionAllowed;
          ctrl.isActionAllowed = function (action) {
            if (action._customTask) {
              return true;
            }

            return parentIsActionAllowed(action);
          };
        });
      },
    ],
  };
});
