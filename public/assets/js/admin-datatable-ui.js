(function(window, $) {
  "use strict";

  if (!$) {
    return;
  }

  function normalizeLabel(value) {
    return String(value || "")
      .replace(/\s+/g, " ")
      .replace(/[-_]+/g, " ")
      .trim();
  }

  function titleCase(value) {
    return normalizeLabel(value)
      .split(" ")
      .filter(Boolean)
      .map(function(part) {
        return part.charAt(0).toUpperCase() + part.slice(1);
      })
      .join(" ");
  }

  function getSearchTarget($wrapper) {
    var $table = $wrapper.find("table").first();
    var explicit = normalizeLabel($table.attr("data-sms-table-name"));
    if (explicit) {
      return explicit;
    }

    var title = normalizeLabel(
      $wrapper
        .closest(".card, .nav-tabs-custom, .card-tabs, .box, .panel")
        .find("> .card-header .card-title, > .card-header .box-title, > .panel-heading .panel-title")
        .first()
        .text()
    );

    if (title) {
      return title;
    }

    var tableId = normalizeLabel(($table.attr("id") || "").replace(/[-_]?datatable$/i, ""));
    return tableId || "records";
  }

  function enhanceDataTableWrapper(wrapper) {
    var $wrapper = $(wrapper);
    if (!$wrapper.length) {
      return;
    }

    $wrapper.addClass("sms-dt-shell");

    var $surface = $wrapper.closest(".card, .nav-tabs-custom, .card-tabs, .box, .panel");
    $surface.addClass("sms-list-surface");

    var $filter = $wrapper.find(".dataTables_filter").first();
    var $length = $wrapper.find(".dataTables_length").first();
    var $info = $wrapper.find(".dataTables_info").first();
    var $paginate = $wrapper.find(".dataTables_paginate").first();
    var target = titleCase(getSearchTarget($wrapper));

    if ($length.length) {
      $length.addClass("sms-dt-length");
    }

    if ($info.length) {
      $info.addClass("sms-dt-info");
    }

    if ($paginate.length) {
      $paginate.addClass("sms-dt-paginate");
    }

    if ($filter.length) {
      $filter.addClass("sms-dt-search");

      if (!$filter.find(".sms-dt-search-icon").length) {
        $filter.prepend('<span class="sms-dt-search-icon"><i class="fas fa-search"></i></span>');
      }

      var $input = $filter.find("input").first();
      if ($input.length) {
        if (!$input.attr("placeholder")) {
          $input.attr("placeholder", "Search " + target);
        }
        $input.attr("aria-label", "Search " + target);
      }
    }
  }

  function enhanceAllDataTables() {
    $(".dataTables_wrapper").each(function() {
      enhanceDataTableWrapper(this);
    });
  }

  $(enhanceAllDataTables);

  $(document).on("init.dt draw.dt", function(event, settings) {
    if (settings && settings.nTable) {
      enhanceDataTableWrapper($(settings.nTable).closest(".dataTables_wrapper"));
      return;
    }

    enhanceAllDataTables();
  });
})(window, window.jQuery);
