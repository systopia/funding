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

fundingModule.factory('fundingApplicationDiffService', [
  function () {
    /**
     * Escapes HTML special characters in a string.
     *
     * @param unsafe
     * @returns {string}
     */
    const escapeHtml = (unsafe) => {
      return (unsafe || '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
    };

    /**
     * Formats a single line of the diff.
     *
     * @param {string} line
     * @param {string} className
     * @returns {string}
     */
    const formatLineHtml = (line, className) => {
      return `<div class="${className}">${escapeHtml(line) || '&nbsp;'}</div>`;
    };

    /**
     * Formats the diff lines between snapshot and current JSON.
     *
     * @param snapshotJSON
     * @param currentJSON
     * @returns {Object}
     */
    const formatDiffLines = (snapshotJSON, currentJSON) => {
      const snapshotParts = snapshotJSON.split('\n');
      const currentParts = currentJSON.split('\n');

      const diff = calculateDiff(snapshotParts, currentParts);

      let snapshotResult = '', currentResult = '';
      diff.forEach(part => {
        if (part.type === 'equal') {
          snapshotResult += formatLineHtml(part.a, '');
          currentResult += formatLineHtml(part.b, '');
        } else if (part.type === 'add') {
          snapshotResult += formatLineHtml('', '');
          currentResult += formatLineHtml(part.b, 'funding-diff-line-added');
        } else if (part.type === 'remove') {
          snapshotResult += formatLineHtml(part.a, 'funding-diff-line-removed');
          currentResult += formatLineHtml('', '');
        } else if (part.type === 'change') {
          snapshotResult += formatLineHtml(part.a, 'funding-diff-line-removed');
          currentResult += formatLineHtml(part.b, 'funding-diff-line-added');
        }
      });

      return {
        snapshotResult: snapshotResult,
        currentResult: currentResult,
      };
    };

    const calculateDiff = (aParts, bParts) => {
      const table = Array.from({ length: aParts.length + 1 }, () => Array(bParts.length + 1).fill(0));
      for (let i = 1; i <= aParts.length; i++) {
        for (let j = 1; j <= bParts.length; j++) {
          if (aParts[i - 1] === bParts[j - 1]) {
            table[i][j] = table[i - 1][j - 1] + 1;
          } else {
            table[i][j] = Math.max(table[i - 1][j], table[i][j - 1]);
          }
        }
      }

      let i = aParts.length, j = bParts.length;
      const diff = [];
      while (i > 0 || j > 0) {
        if (i > 0 && j > 0 && aParts[i - 1] === bParts[j - 1]) {
          diff.unshift({ type: 'equal', a: aParts[i - 1], b: bParts[j - 1] });
          i--; j--;
        } else if (j > 0 && (i === 0 || table[i][j - 1] >= table[i - 1][j])) {
          diff.unshift({ type: 'add', a: null, b: bParts[j - 1] });
          j--;
        } else {
          diff.unshift({ type: 'remove', a: aParts[i - 1], b: null });
          i--;
        }
      }

      const mergedDiff = [];
      for (let k = 0; k < diff.length; k++) {
        if (diff[k].type === 'remove' && diff[k + 1] && diff[k + 1].type === 'add') {
          mergedDiff.push({ type: 'change', a: diff[k].a, b: diff[k + 1].b });
          k++;
        } else {
          mergedDiff.push(diff[k]);
        }
      }
      return mergedDiff;
    };

    /**
     * Normalizes the value for comparison in diff.
     *
     * @param {*} val
     * @returns {*}
     */
    const normalizeValue = (val) => {
      if (isEmpty(val)) {
        return '';
      }

      if (typeof val !== 'object') {
        return typeof val === 'string' ? val.trim() : val;
      }

      if (Array.isArray(val)) {
        return normalizeArray(val);
      }

      return normalizeObject(val);
    };

    /**
     * Checks if a value is empty or null.
     *
     * @param {*} val
     * @returns {boolean}
     */
    const isEmpty = (val) => {
      return val === null || val === undefined || val === '' || (typeof val === 'string' && val.trim() === '');
    };

    const normalizeArray = (arr) => {
      const normalized = arr.map(normalizeValue).filter(v => v !== '');
      if (normalized.length === 0) {
        return '';
      }
      // Sort simple arrays for consistent comparison
      if (normalized.every(v => typeof v !== 'object')) {
        normalized.sort();
      }
      return normalized;
    };

    const normalizeObject = (obj) => {
      const sortedObj = {};
      const keys = Object.keys(obj).sort();
      let hasVisibleProps = false;

      keys.forEach(k => {
        if (k.startsWith('_') || k === 'id' || k === 'identifier') {
          return;
        }
        const v = normalizeValue(obj[k]);
        if (v !== '') {
          sortedObj[k] = v;
          hasVisibleProps = true;
        }
      });
      return hasVisibleProps ? sortedObj : '';
    };

    return {
      normalizeValue: normalizeValue,
      formatDiffLines: formatDiffLines,
    };
  },
]);
