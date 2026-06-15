/**

 * Assessment Builder — shared topic keys (always quiz + paper).

 */

(function (window) {

  'use strict';



  const sharedKeys = window.qpSelectedKeys || window.quizQbSelectedKeys || new Set();

  window.qpSelectedKeys = sharedKeys;

  window.quizQbSelectedKeys = sharedKeys;

  window.AB_BUILDER_MODE = 'both';

})(window);
