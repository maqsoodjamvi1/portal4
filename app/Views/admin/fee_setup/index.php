<?php $uiNeedsDataTables = false; ?>
<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<link href="https://gitcdn.github.io/bootstrap-toggle/2.2.2/css/bootstrap-toggle.min.css" rel="stylesheet">
<link rel="stylesheet" href="<?= base_url('assets/css/school_setup_wizard.css') ?>?v=1">
<link rel="stylesheet" href="<?= base_url('assets/css/fee_setup.css') ?>?v=6">

<?= view('components/page_header', [
    'title' => 'Fee Configuration',
    'icon' => 'fas fa-sliders-h',
    'subtitle' => 'Configure fee types and class-wise fee amounts for challans.',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Fee Configuration', 'active' => true],
    ],
]) ?>

<section class="content">
  <div class="container-fluid setup-wizard-page fee-setup-page">
    <?= view('admin/partials/setup_step_context', ['setup_step_id' => 'fee']) ?>
    <?php
    $navTabs = [];
    $step = 1;
    if (!empty($can_types)) {
        $navTabs[] = ['id' => 'types', 'step' => $step++, 'label' => 'Fee Types', 'icon' => 'fa-tags', 'hint' => 'Fee heads'];
    }
    if (!empty($can_amounts)) {
        $navTabs[] = ['id' => 'amounts', 'step' => $step++, 'label' => 'Fee Structure', 'icon' => 'fa-table', 'hint' => 'Class amounts'];
    }

    $tabIds = array_column($navTabs, 'id');
    $activeIndex = array_search($active_tab, $tabIds, true);
    if ($activeIndex === false) {
        $activeIndex = 0;
    }

    $feeWizardNav = static function (string $tabId) use ($tabIds): array {
        $idx = array_search($tabId, $tabIds, true);
        if ($idx === false) {
            return ['wizard_prev_tab' => '', 'wizard_next_tab' => '', 'wizard_is_last' => false];
        }
        $count = count($tabIds);

        return [
            'wizard_prev_tab' => $idx > 0 ? $tabIds[$idx - 1] : '',
            'wizard_next_tab' => ($idx < $count - 1) ? $tabIds[$idx + 1] : '',
            'wizard_is_last' => $idx === $count - 1,
        ];
    };

    foreach ($navTabs as $i => &$tab) {
        $tab['href'] = base_url('admin/fee_setup?tab=' . $tab['id']);
    }
    unset($tab);
    ?>

    <div class="setup-wizard-shell fee-setup-shell">
      <?= view('admin/partials/setup_wizard_nav', [
          'steps' => $navTabs,
          'active_step' => $active_tab,
          'mode' => 'link',
          'total_steps' => count($navTabs),
      ]) ?>

      <div class="setup-wizard-body fee-setup-body">
        <?php if ($can_types) : ?>
        <div class="setup-wizard-pane fee-setup-pane <?= $active_tab === 'types' ? 'is-visible' : '' ?>" id="tab-types" role="tabpanel">
          <?= view('admin/fee_setup/_tab_types', array_merge([
            'fee_types' => $fee_types ?? [],
            'active_type_count' => $active_type_count ?? 0,
            'type_count' => $type_count ?? count($fee_types ?? []),
            'can_amounts' => !empty($can_amounts),
            'monthly_fee_locked' => $monthly_fee_locked ?? false,
          ], $feeWizardNav('types'))) ?>
        </div>
        <?php endif; ?>

        <?php if ($can_amounts) : ?>
        <div class="setup-wizard-pane fee-setup-pane <?= $active_tab === 'amounts' ? 'is-visible' : '' ?>" id="tab-amounts" role="tabpanel">
          <?= view('admin/fee_setup/_tab_amounts', array_merge([
            'classesinfo' => $classesinfo ?? [],
            'session_id' => $session_id ?? null,
            'current_academic_sessioninfo' => $current_academic_sessioninfo ?? null,
            'prev_fees' => $prev_fees ?? [],
            'current_fees' => $current_fees ?? [],
            'is_first_time' => $is_first_time ?? false,
            'fee_type_info' => $fee_type_info ?? [],
            'amount_ids' => $amount_ids ?? [],
            'max_fee' => $max_fee ?? null,
            'campus_flags' => $campus_flags ?? null,
            'fee_flag' => $fee_flag ?? 0,
            'has_session' => $has_session ?? false,
            'has_fee_types' => $has_fee_types ?? false,
          ], $feeWizardNav('amounts'))) ?>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<script src="https://gitcdn.github.io/bootstrap-toggle/2.2.2/js/bootstrap-toggle.min.js"></script>
<script>
$(function () {
  var activeTab = <?= json_encode($active_tab) ?>;
  var tabOrder = <?= json_encode($tabIds) ?>;
  var navigationLock = false;

  function tabUrl(tab) {
    return <?= json_encode(base_url('admin/fee_setup')) ?> + '?tab=' + encodeURIComponent(tab);
  }

  function setNavigationBusy(busy) {
    var $links = $('.setup-wizard-nav__link, .fee-setup-nav__link');
    var $buttons = $('.setup-wizard-prev, .setup-wizard-next, .setup-wizard-finish');
    $buttons.prop('disabled', busy);
    $links.toggleClass('is-disabled', busy).attr('aria-disabled', busy ? 'true' : 'false');
    if (busy) {
      $('.setup-wizard-next, .setup-wizard-prev').prepend('<span class="wizard-nav-spinner fa fa-spinner fa-spin me-1"></span>');
    } else {
      $('.wizard-nav-spinner').remove();
    }
  }

  function saveCurrentFeeTab(options) {
    options = options || {};
    if (activeTab === 'types' && typeof window.saveFeeTypesData === 'function') {
      return window.saveFeeTypesData(options);
    }
    if (activeTab === 'amounts' && typeof window.saveFeeAmountsData === 'function') {
      return window.saveFeeAmountsData(options);
    }
    return $.when();
  }

  function navigateFeeTab(targetTab, finish) {
    if (navigationLock) {
      return;
    }
    navigationLock = true;
    setNavigationBusy(true);

    saveCurrentFeeTab({ silent: true })
      .done(function () {
        if (finish) {
          window.location.href = <?= json_encode(base_url('admin/getting-started')) ?>;
        } else if (targetTab) {
          window.location.href = tabUrl(targetTab);
        }
      })
      .always(function () {
        navigationLock = false;
        setNavigationBusy(false);
      });
  }

  $(document).on('click', '.setup-wizard-nav__link, .fee-setup-nav__link', function (e) {
    e.preventDefault();
    var tab = $(this).data('wizard-tab');
    if (!tab || tab === activeTab) {
      return;
    }
    navigateFeeTab(tab);
  });

  $(document).on('click', '.setup-wizard-next', function () {
    var tab = $(this).data('wizard-tab');
    if (tab) {
      navigateFeeTab(tab);
    }
  });

  $(document).on('click', '.setup-wizard-prev', function () {
    var tab = $(this).data('wizard-tab');
    if (tab) {
      navigateFeeTab(tab);
    }
  });

  $(document).on('click', '.setup-wizard-finish', function () {
    navigateFeeTab(null, true);
  });
});
</script>

<?= $this->endSection() ?>
