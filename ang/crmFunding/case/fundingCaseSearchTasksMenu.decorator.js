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

'use strict';

/*
 * This decorator adds the fundingCaseTasksDecorator directive if the
 * crmSearchTasksMenu component is used. fundingCaseTasksDecorator has
 * the same scope as crmSearchTasksMenu and so can modify its controller.
 */
fundingModule.config(['$provide', function($provide) {
  $provide.decorator('crmSearchTasksMenuDirective', ['$delegate', function ($delegate) {
    const templateUrl = $delegate[0].templateUrl;
    const template = $delegate[0].template;

    $delegate[0].template = (elem, attr) => {
      if (templateUrl) {
        const url = typeof templateUrl === 'function' ? templateUrl(elem, attr) : templateUrl;

        return '<ng-include src="\'' + url + '\'" funding-case-tasks-decorator></ng-include>';
      }

      return '<funding-case-tasks-decorator></funding-case-tasks-decorator>' +
        (typeof template === 'function' ? template(elem, attr) : template);
    };

    $delegate[0].templateUrl = undefined;

    return $delegate;
  }]);
}]);

fundingModule.directive('fundingCaseTasksDecorator', function() {
  return {
    restrict: 'AE',
    scope: false,
    template: function () {
      return '';
    },
    controller: ['$scope', 'fundingCaseService',
      function ($scope, fundingCaseService) {
        const ctrl = $scope.$ctrl;

        // See TaskManager in searchDisplayTasksTrait
        const taskManager = ctrl.taskManager;
        taskManager.getMetadata().then(() => {
          const entityName = taskManager.entityInfo.name;
          if (entityName !== 'FundingCase') {
            return;
          }

          let allowedTasksByCase = {};

          const searchKitTasks = taskManager.tasks;

          function updateAvailableTasks() {
            taskManager.tasks = Array.from(searchKitTasks);

            if (ctrl.ids.length === 0) {
              return;
            }

            let additionalTasks = allowedTasksByCase[ctrl.ids[0]] || {};

            // Filter out tasks that are not available in all selected funding
            // cases.
            for (let i = 1; i < ctrl.ids.length; ++i) {
              const caseTasks = allowedTasksByCase[ctrl.ids[i]] || {};
              additionalTasks = _4.pickBy(
                additionalTasks,
                (task, actionName) => caseTasks[actionName] && _4.isEqual(caseTasks[actionName], task)
              );
            }

            Object.values(additionalTasks).forEach((task) => {
              taskManager.tasks.push({number: '> 0', ...task});
            });
          }

          let lastIds;
          function updateTasks() {
            if (_4.isEqual(lastIds, ctrl.ids)) {
              return new Promise((resolve) => resolve([]));
            }

            lastIds = _4.clone(ctrl.ids);
            const idsToGetTasks = [];
            ctrl.ids.forEach((id) => {
              if (!allowedTasksByCase[id]) {
                idsToGetTasks.push(id);
              }
            });

            if (idsToGetTasks.length > 0) {
              taskManager.tasks = searchKitTasks;
              return fundingCaseService.getSearchTasks(idsToGetTasks)
                .then((tasksByCase) => _4.extend(allowedTasksByCase, tasksByCase))
                .then(updateAvailableTasks);
            }
            else {
              updateAvailableTasks();
              return new Promise((resolve) => resolve([]));
            }
          }

          // Only triggered if a new ID is selected, not un deselect.
          $scope.$watch('$ctrl.ids', () => updateTasks());

          // getMetadata is called every time the tasks menu is opened.
          const taskManagerGetMetaData = taskManager.getMetadata;
          taskManager.getMetadata = function () {
            updateTasks();

            return taskManagerGetMetaData();
          };
        });
      }
    ],
  };
});
