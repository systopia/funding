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
          taskManager.entityInfo.title = ts('Change Request');
          taskManager.entityInfo.title_plural = ts('Change Requests');

          const searchKitTasks = taskManager.tasks;

          function updateAvailableTasks() {
            const definedTasks = {
              approve: {
                name: 'approve',
                title: ts('Approve'),
                confirm: ts('Do you want to approve the selected change requests?'),
                number: '>= 0',
                _isChangeRequestTask: true,
              },
              approvePartial: {
                name: 'approvePartial',
                title: ts('Approve with change'),
                confirm: ts('Do you want to approve the selected change requests with changes?'),
                number: '>= 0',
                _isChangeRequestTask: true,
              },
              reject: {
                name: 'reject',
                title: ts('Reject'),
                confirm: ts('Do you want to reject the selected change requests?'),
                number: '>= 0',
                _isChangeRequestTask: true,
              },
            };

            taskManager.tasks = searchKitTasks.map(task => {
              const taskName = task.name || task;
              if (definedTasks[taskName]) {
                const custom = definedTasks[taskName];
                delete definedTasks[taskName];
                return Object.assign({}, task, custom);
              }
              return task;
            }).concat(Object.values(definedTasks));
          }

          taskManager.doTask = function (action, ids) {
            const actionName = typeof action === 'string' ? action : (action.name || action);
            if (!action._isChangeRequestTask && !['approve', 'approvePartial', 'reject'].includes(actionName)) {
              return Object.getPrototypeOf(taskManager).doTask.call(taskManager, action, ids);
            }

            const name = action._isChangeRequestTask ? action.name : actionName;
            const confirmMsg = action.confirm || (
              name === 'approve' ? ts('Do you want to approve the selected change requests?') :
                name === 'approvePartial' ? ts('Do you want to approve the selected change requests with changes?') :
                  ts('Do you want to reject the selected change requests?')
            );

            const run = function (targetIds) {
              if (targetIds.length === 0) {
                CRM.alert(ts('No change requests selected or found.'), ts('Notice'), 'warning');
                return;
              }

              if (name === 'approvePartial') {
                CRM.confirm({
                  title: ts('Approve with change'),
                  width: '400px',
                  message: '<div class="form-group"><label style="display: block; margin-bottom: 5px;">' + ts('Approved amount') + '</label><input type="number" id="change-request-approved-amount" class="form-control" style="width: 100%; border: 1px solid #ccc !important; box-shadow: none !important;" /></div>',
                  options: { no: ts('Cancel'), yes: ts('Confirm') },
                }).on('crmConfirm:yes', function () {
                  const amount = document.getElementById('change-request-approved-amount').value;
                  executeTask(name, targetIds, {
                    amount_approved: amount,
                    amount_accepted: amount,
                    amountApproved: amount,
                    amount: amount,
                  });
                });
              } else {
                const yesLabels = {
                  approve: ts('Approve'),
                  reject: ts('Reject'),
                };
                const yesLabel = yesLabels[name] || ts('Continue');
                CRM.confirm({
                  message: confirmMsg,
                  options: { no: ts('Cancel'), yes: yesLabel },
                }).on('crmConfirm:yes', function () {
                  executeTask(name, targetIds, {});
                });
              }
            };

            if (!ids || ids.length === 0) {
              const params = taskManager.getApiParams();
              params.return = 'id';
              crmApi4('SearchDisplay', 'run', params).then(function (allIds) {
                run(_.toArray(allIds));
              });
            } else {
              run(ids);
            }
          };

          function executeTask(actionName, ids, params) {
            const apiParams = Object.assign({ ids: ids }, params);
            crmStatus({}, crmApi4('FundingAmountApprovedChangeRequest', actionName, apiParams).then(() => {
              taskManager.refreshAfterTask();
            }));
          }

          const parentIsActionAllowed = ctrl.isActionAllowed;
          ctrl.isActionAllowed = function (action) {
            if (action._isChangeRequestTask || ['approve', 'approvePartial', 'reject'].includes(action.name || action)) {
              return true;
            }
            return parentIsActionAllowed(action);
          };

          updateAvailableTasks();
        });
      },
    ],
  };
});
