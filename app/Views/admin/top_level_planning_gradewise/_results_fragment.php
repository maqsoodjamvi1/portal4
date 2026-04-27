<?php
// expects: $grouped_data
// optional: $show_updated_date (bool)
$show_updated_date = (bool)($show_updated_date ?? false);

if (!function_exists('contains_urdu')) {
  function contains_urdu(string $text): bool {
    // Arabic/Urdu ranges
    return (bool) preg_match('/[\x{0600}-\x{06FF}\x{0750}-\x{077F}\x{08A0}-\x{08FF}\x{FB50}-\x{FDFF}\x{FE70}-\x{FEFF}]/u', $text);
  }
}
?>

<style>
  /* ========================
     Jameel Urdu Nastaleeq
     ======================== */
  @font-face{
    font-family: 'JameelUrduNastaleeq';
    src:
      url('/assets/fonts/JameelUrduNastaleeq.woff2') format('woff2'),
      url('/assets/fonts/JameelUrduNastaleeq.woff')  format('woff'),
      url('/assets/fonts/JameelUrduNastaleeq.ttf')   format('truetype');
    font-weight: 400;
    font-style: normal;
    font-display: swap;
  }

  html, body{
    font-family: "Segoe UI", Roboto, Arial, sans-serif;
    font-size: 13px;
    line-height: 1.35;
    text-rendering: optimizeLegibility;
    -webkit-font-smoothing: antialiased;
  }

  :lang(ur), [lang="ur"], .urdu, .font-urdu{
    direction: rtl !important;
    text-align: right !important;
    unicode-bidi: isolate-override;
    font-family: 'JameelUrduNastaleeq','Noto Nastaliq Urdu',serif !important;
    line-height: 1.9;
  }

  #tlpTable td { vertical-align: top; }

  .tlp-subject-vertical { width:28px; padding:0 !important; text-align:center; }
  .tlp-subject-vertical .vtext{
    display:inline-block; writing-mode:vertical-rl; transform:rotate(180deg);
    white-space:nowrap; line-height:1; padding:.25rem .15rem;
  }

  .tlp-obj{
    display:block !important;
    white-space:normal !important;
    overflow:visible !important;
    text-overflow:clip !important;
    -webkit-line-clamp:unset !important;
    -webkit-box-orient:unset !important;
    max-height:none !important;
    overflow-wrap:anywhere;
    word-break:break-word;
  }
  .tlp-obj.urdu, .tlp-obj:lang(ur){
    direction: rtl !important;
    text-align: right !important;
    unicode-bidi: isolate-override;
    font-family: 'JameelUrduNastaleeq','Noto Nastaliq Urdu',serif !important;
    line-height: 1.9;
  }

  .tlp-date { font-size:.7rem; color:#6c757d; margin-top:.15rem; }

  tr.terms-row td{
    background:#f8f9fa;
    font-weight:600;
    vertical-align:middle;
  }

  thead.tlp-hidden-head { display:none; }
  .print-only { display:none; }

  @media print{
    #tlpTable { border-collapse: collapse !important; border: 1px solid #999 !important; }
    #tlpTable th, #tlpTable td { border: 1px solid #999 !important; }

    html, body{
      font-family: Cambria, "Times New Roman", Georgia, serif;
      font-size: 11pt;
      line-height: 1.35;
      -webkit-print-color-adjust: exact;
      print-color-adjust: exact;
      background:#fff !important;
    }

    :lang(ur), [lang="ur"], .urdu, .font-urdu,
    .tlp-obj.urdu, .tlp-obj:lang(ur){
      font-family: 'JameelUrduNastaleeq','Noto Nastaliq Urdu',serif !important;
      direction: rtl !important;
      text-align: right !important;
      unicode-bidi: isolate-override;
      font-size: 12pt;
      line-height: 1.9;
    }

    #tplhtmlresult, .table-responsive, .print-frame, .card, .card-body,
    .content, .content-wrapper{
      border:0 !important; outline:0 !important; box-shadow:none !important; background:#fff !important;
    }

    #tplhtmlresult .dataTables_wrapper .row:first-child,
    #tplhtmlresult .dataTables_wrapper .row:last-child,
    #tplhtmlresult .dataTables_filter,
    #tplhtmlresult .dt-buttons,
    #tplhtmlresult .dataTables_length,
    #tplhtmlresult .dataTables_info,
    #tplhtmlresult .dataTables_paginate,
    #tplhtmlresult input[type="search"]{
      display:none !important; visibility:hidden !important; height:0 !important; margin:0 !important; padding:0 !important;
    }

    .keep-together { break-inside: avoid !important; page-break-inside: avoid !important; }
    #tlpTable tr, #tlpTable td { break-inside: avoid !important; page-break-inside: avoid !important; }

    tr.terms-row { display:none !important; }
    .print-only { display:block !important; }

    .print-cth{
      font-weight:700;
      background:#e9f2ff;
      border:1px solid #999;
      margin:-2px -2px 4px -2px;
      padding:2px 4px;
    }

    .no-page-breaks tr.terms-row { display: table-row !important; }
    .no-page-breaks .print-only { display:none !important; }
  }
</style>

<?php if (empty($grouped_data)): ?>
  <div class="alert alert-info m-0">No data found with the selected filters.</div>
<?php else: ?>
  <?php
    // Gather all terms (keep column order stable)
    $all_terms = [];
    foreach ($grouped_data as $cid => $cdata) {
      foreach (($cdata['terms'] ?? []) as $tid => $tname) {
        $all_terms[$tid] = $tname;
      }
    }
    $classKeys = array_keys($grouped_data);

    // Precompute the first [subject] that has text for each [class × term]
    $firstCellFor = []; // [$class_id][$term_id] = $subject_id
    foreach ($classKeys as $class_id) {
      $class_data = $grouped_data[$class_id] ?? [];
      foreach ($all_terms as $term_id => $tname) {
        $firstCellFor[$class_id][$term_id] = null;
        foreach (($class_data['subjects'] ?? []) as $subject_id => $subject_data) {
          if (!empty($subject_data['objectives'][$term_id]['text'])) {
            $firstCellFor[$class_id][$term_id] = $subject_id;
            break;
          }
        }
      }
    }
  ?>

  <div class="table-responsive">
    <table id="tlpTable" class="table table-sm table-bordered table-striped table-hover table-compact">
      <thead class="tlp-hidden-head">
        <tr>
          <th>Class</th>
          <th>Subject</th>
          <?php foreach ($all_terms as $tid => $tname): ?>
            <th><?= esc($tname) ?></th>
          <?php endforeach; ?>
        </tr>
      </thead>

      <tbody>
        <?php foreach ($classKeys as $class_id): ?>
          <?php
            $class_data = $grouped_data[$class_id] ?? [];
            $className  = $class_data['class_name'] ?? 'N/A';
          ?>

          <!-- Terms header row (visible on screen) -->
          <tr class="terms-row">
            <td class="d-none"><?= esc($className) ?></td>
            <td>Subject</td>
            <?php foreach ($all_terms as $tid => $tname): ?>
              <td><?= esc($tname) ?></td>
            <?php endforeach; ?>
          </tr>

          <?php foreach (($class_data['subjects'] ?? []) as $subject_id => $subject_data): ?>
            <tr>
              <td class="d-none"><?= esc($className) ?></td>

              <td class="tlp-subject-vertical">
                <?php
                  $label = $subject_data['subject_short_name'] ?? ($subject_data['subject_name'] ?? '');
                ?>
                <div class="vtext"><?= esc($label) ?></div>
              </td>

              <?php foreach ($all_terms as $term_id => $tname): ?>
                <?php if (isset($subject_data['objectives'][$term_id])): ?>
                  <?php
                    $obj          = $subject_data['objectives'][$term_id];
                    $text         = (string)($obj['text'] ?? '');
                    $display_date = $obj['updated_date'] ?? ($obj['created_date'] ?? 'N/A');
                    if ($display_date !== 'N/A') $display_date = date('d M Y', strtotime($display_date));

                    // Urdu detection: actual script or known Urdu-like subject codes
                    $subShort = strtolower((string)($subject_data['subject_short_name'] ?? ''));
                    $looksUrduSubject = in_array($subShort, ['urdu','urd','nazra','nazra/ik','isl','islamiat','islamiyat'], true);
                    $isUrdu = contains_urdu($text) || $looksUrduSubject;

                    $urduClass = $isUrdu ? ' urdu font-urdu' : '';
                    $langAttr  = $isUrdu ? ' lang="ur" dir="rtl"' : '';

                    // <<< FIX: compute this before using >>>
                    $printHeadingHere = ($firstCellFor[$class_id][$term_id] === $subject_id && !empty($text));
                  ?>
                  <td<?= $langAttr ?> title="<?= esc(strip_tags($text)) ?>">
                    <div class="keep-together">
                      <?php if ($printHeadingHere): ?>
                        <div class="print-only print-cth"><?= esc($className) ?> — <?= esc($tname) ?></div>
                      <?php endif; ?>

                      <div class="tlp-obj<?= $urduClass ?>">
                        <?= nl2br(esc($text)) ?>
                      </div>

                      <?php if ($show_updated_date): ?>
                        <div class="tlp-date<?= $isUrdu ? ' urdu' : '' ?>">Updated: <?= esc($display_date) ?></div>
                      <?php endif; ?>

                      <div class="no-print mt-1" style="white-space:nowrap;">
                        <a href="<?= site_url('admin/top_level_planning_gradewise/edit/' . ($obj['tlp_id'] ?? '')) ?>" class="btn btn-xxs btn-primary" title="Edit"><i class="fas fa-edit"></i></a>
                        <a href="<?= site_url('admin/top_level_planning_gradewise/delete/' . ($obj['tlp_id'] ?? '')) ?>" class="btn btn-xxs btn-danger" title="Delete" onclick="return confirm('Are you sure?')"><i class="fas fa-trash"></i></a>
                      </div>
                    </div>
                  </td>
                <?php else: ?>
                  <td class="text-muted">—</td>
                <?php endif; ?>
              <?php endforeach; ?>
            </tr>
          <?php endforeach; ?>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>
