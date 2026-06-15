(function ($) {
  "use strict";

  function initReportSelects(scope) {
    var $scope = scope ? $(scope) : $(document);
    $scope.find(".report-select2").each(function () {
      var $el = $(this);
      if ($.fn.select2 && !$el.hasClass("select2-hidden-accessible")) {
        $el.select2({ width: "100%" });
      }
    });
  }

  $(function () {
    initReportSelects(document);
  });

  window.ReportUI = {
    initReportSelects: initReportSelects
  };
})(jQuery);
