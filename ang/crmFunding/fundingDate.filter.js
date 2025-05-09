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

fundingModule.filter('fundingDate', ['dateFilter', function (dateFilter) {
  const ts = CRM.ts('funding');

  return function (value, format = ts('yyyy-MM-dd hh:mm:ss')) {
    if (value === undefined || value === null || value === '') {
      return ts('empty');
    }

    const date = new Date(value);
    return dateFilter(date, format);
  };
}]);
