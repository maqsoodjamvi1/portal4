<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<section class="content-header no-print">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1><i class="fas fa-book"></i> Vocabulary Report</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
          <li class="breadcrumb-item active">Vocabulary Report</li>
        </ol>
      </div>
    </div>
  </div>
</section>

<section class="content">

  <div class="card card-primary no-print">
    <div class="card-header">
      <h3 class="card-title">Filter By Class / Subject / Topics</h3>
    </div>

    <div class="card-body">
      <div class="row">
        <div class="form-group col-md-3">
          <label for="class_id">Class</label>
          <select id="class_id" class="form-control">
            <option value="">-- Select Class --</option>
            <?php foreach ($classes as $c): ?>
              <option value="<?= $c->class_id ?>"><?= esc($c->class_name) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group col-md-3">
          <label for="subject_id">Subject</label>
          <select id="subject_id" class="form-control">
            <option value="">-- Select Subject --</option>
          </select>
        </div>

        <div class="form-group col-md-3">
          <label>Topics (one or many)</label>
          <div id="topicsContainer" class="border rounded p-2" style="max-height: 170px; overflow-y:auto; background:#f9f9f9; font-size:13px;">
            <small class="text-muted">Select Class &amp; Subject to load topics.</small>
          </div>
        </div>

        <!-- Font Size Selection -->
        <div class="form-group col-md-3">
          <label for="fontSizeSelect">Font Size</label>
          <select id="fontSizeSelect" class="form-control">
            <option value="small">Small</option>
            <option value="medium" selected>Medium</option>
            <option value="large">Large</option>
          </select>
          <small class="text-muted">Adjust font size for report</small>
        </div>
      </div>

      <div class="d-flex justify-content-between align-items-center mt-2">
        <div class="d-flex flex-wrap align-items-center" style="flex-wrap: wrap; gap: 5px;">
          <label class="me-2 mb-0"><strong>Show/Hide:</strong></label>
          
          <div class="form-check me-2">
            <input class="form-check-input column-toggle" type="checkbox" id="toggle_en" data-field="meaning_en" checked>
            <label class="form-check-label" for="toggle_en">EN</label>
          </div>

          <div class="form-check me-2">
            <input class="form-check-input column-toggle" type="checkbox" id="toggle_ur" data-field="meaning_ur" checked>
            <label class="form-check-label" for="toggle_ur">UR</label>
          </div>

          <div class="form-check me-2">
            <input class="form-check-input column-toggle" type="checkbox" id="toggle_example" data-field="example" checked>
            <label class="form-check-label" for="toggle_example">Ex</label>
          </div>
          
          <div class="form-check me-2">
            <input class="form-check-input column-toggle" type="checkbox" id="toggle_pos" data-field="pos" checked>
            <label class="form-check-label" for="toggle_pos">POS</label>
          </div>

          <div class="form-check me-2">
            <input class="form-check-input column-toggle" type="checkbox" id="toggle_syllables" data-field="syllables" checked>
            <label class="form-check-label" for="toggle_syllables">Syll</label>
          </div>

          <div class="form-check me-2">
            <input class="form-check-input column-toggle" type="checkbox" id="toggle_synonyms" data-field="synonyms" checked>
            <label class="form-check-label" for="toggle_synonyms">Syn</label>
          </div>

          <div class="form-check me-2">
            <input class="form-check-input column-toggle" type="checkbox" id="toggle_antonyms" data-field="antonyms" checked>
            <label class="form-check-label" for="toggle_antonyms">Ant</label>
          </div>

          <div class="form-check me-2">
            <input class="form-check-input column-toggle" type="checkbox" id="toggle_related" data-field="related" checked>
            <label class="form-check-label" for="toggle_related">Rel</label>
          </div>

          <div class="form-check me-2">
            <input class="form-check-input column-toggle" type="checkbox" id="toggle_confusing" data-field="confusing" checked>
            <label class="form-check-label" for="toggle_confusing">Con</label>
          </div>
        </div>

        <div>
          <button type="button" id="btnShowReport" class="btn btn-primary">
            <i class="fas fa-search"></i> Show Report
          </button>
        </div>
      </div>
    </div>
  </div>

  <div id="vocabSummaryWrapper" style="display:none; text-align: center; margin-bottom: 30px;">
    <div class="row">
        <div class="col-12">
            <h1 style="font-weight: bold; font-size: 28pt; margin-bottom: 5px;">
                <span id="sumClassName"></span> - <span id="sumSubjectName"></span>
            </h1>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <h3 style="font-weight: bold; margin-bottom: 15px; text-transform: uppercase;">Parts of Speech Summary</h3>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-10">
            <table class="table table-bordered summary-table" style="border: 2px solid #000; width: 100%;">
                <thead>
                    <tr style="background-color: #eee !important; -webkit-print-color-adjust: exact;">
                        <th style="border: 1px solid #000; width: 20%; font-size: 14pt;">Total Words</th>
                        <th style="border: 1px solid #000; font-size: 14pt;">Breakdown by Category</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="border: 1px solid #000; font-size: 32pt; font-weight: bold; vertical-align: middle;">
                            <span id="sumTotalWords">0</span>
                        </td>
                        <td style="border: 1px solid #000; text-align: left; padding: 15px; vertical-align: middle;">
                            <div id="sumPosList" style="font-size: 13pt; line-height: 1.6;"></div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
  </div>

  <div id="vocabCardsWrapper" style="display:none;">
    <div id="vocabCardsContainer"></div>
    
    <div class="card-footer py-2 no-print mt-4">
      <small class="text-muted" id="cardCountText">0 words displayed</small>
      <div class="float-end">
        <button type="button" id="btnPrint" class="btn btn-dark btn-sm">
          <i class="fa fa-print"></i> Print Report
        </button>
      </div>
    </div>
  </div>

</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const classSelect = document.getElementById('class_id');
  const subjectSelect = document.getElementById('subject_id');
  const topicsContainer = document.getElementById('topicsContainer');
  const btnShow = document.getElementById('btnShowReport');
  const wrapSummary = document.getElementById('vocabSummaryWrapper');
  const sumTotal = document.getElementById('sumTotalWords');
  const sumClassName = document.getElementById('sumClassName');
  const sumSubjectName = document.getElementById('sumSubjectName');
  const sumPosList = document.getElementById('sumPosList');
  const wrapCards = document.getElementById('vocabCardsWrapper');
  const cardsContainer = document.getElementById('vocabCardsContainer');
  const cardCountText = document.getElementById('cardCountText');
  const btnPrint = document.getElementById('btnPrint');
  const fontSizeSelect = document.getElementById('fontSizeSelect');
  const columnToggles = document.querySelectorAll('.column-toggle');

  let currentGroupedData = null;

  // Font size configuration
  const fontSizes = {
    small: {
      cardBox: '11pt',
      cardLine: '10pt',
      word: '12pt',
      pos: '10pt',
      syllables: '9pt',
      example: '10pt',
      topicHeader: '16pt',
      summaryTitle: '22pt',
      summaryTable: '11pt',
      posList: '11pt'
    },
    medium: {
      cardBox: '14pt',
      cardLine: '12pt',
      word: '15pt',
      pos: '12pt',
      syllables: '11pt',
      example: '12pt',
      topicHeader: '22pt',
      summaryTitle: '28pt',
      summaryTable: '14pt',
      posList: '13pt'
    },
    large: {
      cardBox: '17pt',
      cardLine: '15pt',
      word: '18pt',
      pos: '15pt',
      syllables: '14pt',
      example: '14pt',
      topicHeader: '28pt',
      summaryTitle: '34pt',
      summaryTable: '17pt',
      posList: '16pt'
    }
  };

  function applyFontSize() {
    const selectedSize = fontSizeSelect.value;
    const sizes = fontSizes[selectedSize];
    
    const cardBoxes = document.querySelectorAll('.card-box');
    cardBoxes.forEach(box => {
      box.style.fontSize = sizes.cardBox;
    });
    
    const cardLines = document.querySelectorAll('.card-line');
    cardLines.forEach(line => {
      line.style.fontSize = sizes.cardLine;
    });
    
    const words = document.querySelectorAll('.word-text');
    words.forEach(word => {
      word.style.fontSize = sizes.word;
    });
    
    const posElements = document.querySelectorAll('.pos-text');
    posElements.forEach(pos => {
      pos.style.fontSize = sizes.pos;
    });
    
    const syllablesElements = document.querySelectorAll('.syllables-text');
    syllablesElements.forEach(syll => {
      syll.style.fontSize = sizes.syllables;
    });
    
    const exampleElements = document.querySelectorAll('.example-line');
    exampleElements.forEach(example => {
      example.style.fontSize = sizes.example;
    });
    
    const topicHeaders = document.querySelectorAll('.topic-header h2');
    topicHeaders.forEach(header => {
      header.style.fontSize = sizes.topicHeader;
    });
    
    const summaryTitle = document.querySelector('#vocabSummaryWrapper h1');
    if (summaryTitle) {
      summaryTitle.style.fontSize = sizes.summaryTitle;
    }
    
    const totalWordsSpan = document.getElementById('sumTotalWords');
    if (totalWordsSpan) {
      totalWordsSpan.style.fontSize = selectedSize === 'small' ? '24pt' : (selectedSize === 'medium' ? '32pt' : '40pt');
    }
    
    const posListDiv = document.getElementById('sumPosList');
    if (posListDiv) {
      posListDiv.style.fontSize = sizes.posList;
    }
  }

  function applyColumnVisibility() {
    if (!currentGroupedData) return;
    
    const showEn = document.getElementById('toggle_en').checked;
    const showUr = document.getElementById('toggle_ur').checked;
    const showExample = document.getElementById('toggle_example').checked;
    const showPos = document.getElementById('toggle_pos').checked;
    const showSyllables = document.getElementById('toggle_syllables').checked;
    const showSynonyms = document.getElementById('toggle_synonyms').checked;
    const showAntonyms = document.getElementById('toggle_antonyms').checked;
    const showRelated = document.getElementById('toggle_related').checked;
    const showConfusing = document.getElementById('toggle_confusing').checked;
    
    generateCards(currentGroupedData, {
      showEn, showUr, showExample, showPos, showSyllables,
      showSynonyms, showAntonyms, showRelated, showConfusing
    });
  }

  function resetReport() {
    wrapSummary.style.display = 'none';
    wrapCards.style.display = 'none';
    cardsContainer.innerHTML = '';
    currentGroupedData = null;
  }

  classSelect.addEventListener('change', function () {
    const cid = this.value;
    subjectSelect.innerHTML = '<option value="">-- Select Subject --</option>';
    topicsContainer.innerHTML = '<small class="text-muted">Select Subject...</small>';
    resetReport();
    if (!cid) return;
    fetch('<?= base_url('admin/question-bank/subjects') ?>?class_id=' + cid)
      .then(r => r.json()).then(d => {
        const subjects = Array.isArray(d) ? d : (d.subjects || []);
        subjects.forEach(s => {
          const o = document.createElement('option');
          o.value = s.subject_id;
          o.textContent = s.subject_name || s.subject_short_name;
          subjectSelect.appendChild(o);
        });
      });
  });

  subjectSelect.addEventListener('change', function () {
    const cid = classSelect.value;
    const sid = this.value;
    topicsContainer.innerHTML = '';
    resetReport();
    if (!cid || !sid) return;
    fetch('<?= base_url('admin/vocab-bank/topics') ?>?class_id=' + cid + '&subject_id=' + sid)
      .then(r => r.json()).then(d => {
        const topics = Array.isArray(d) ? d : (d.topics || []);
        topics.forEach(t => {
          const wrapper = document.createElement('div');
          wrapper.className = 'form-check';
          wrapper.innerHTML = `<input class="form-check-input topic-checkbox" type="checkbox" value="${t.id}" id="t${t.id}"><label class="form-check-label" for="t${t.id}">${escapeHtml(t.topic_name)}</label>`;
          topicsContainer.appendChild(wrapper);
        });
      });
  });

  btnShow.addEventListener('click', function () {
    const cid = classSelect.value;
    const sid = subjectSelect.value;
    const topicCheckboxes = document.querySelectorAll('.topic-checkbox:checked');
    const topicIds = Array.from(topicCheckboxes).map(cb => cb.value);
    const topicMap = {};
    topicCheckboxes.forEach(cb => { topicMap[cb.value] = cb.nextElementSibling.textContent; });

    if (!cid || !sid || !topicIds.length) { alert('Select Class, Subject and Topics'); return; }

    const url = '<?= base_url('admin/vocab-bank/report-data') ?>?class_id='+cid+'&subject_id='+sid+'&topic_ids='+topicIds.join(',');

    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
      .then(r => r.json()).then(data => {
        if (data.status !== 'ok') return;

        sumTotal.textContent = data.header.total_words;
        sumClassName.textContent = classSelect.options[classSelect.selectedIndex].text;
        sumSubjectName.textContent = subjectSelect.options[subjectSelect.selectedIndex].text;

        sumPosList.innerHTML = Object.entries(data.part_of_speech_counts)
          .map(([pos, count]) => `<span style="margin-right:15px;"><b>${escapeHtml(pos)}:</b> ${count}</span>`).join(' ');

        const grouped = {};
        data.items.forEach(item => {
          const name = topicMap[item.topic_id] || 'General';
          if (!grouped[name]) grouped[name] = [];
          grouped[name].push(item);
        });
        
        currentGroupedData = grouped;
        
        const showEn = document.getElementById('toggle_en').checked;
        const showUr = document.getElementById('toggle_ur').checked;
        const showExample = document.getElementById('toggle_example').checked;
        const showPos = document.getElementById('toggle_pos').checked;
        const showSyllables = document.getElementById('toggle_syllables').checked;
        const showSynonyms = document.getElementById('toggle_synonyms').checked;
        const showAntonyms = document.getElementById('toggle_antonyms').checked;
        const showRelated = document.getElementById('toggle_related').checked;
        const showConfusing = document.getElementById('toggle_confusing').checked;
        
        generateCards(grouped, {
          showEn, showUr, showExample, showPos, showSyllables,
          showSynonyms, showAntonyms, showRelated, showConfusing
        });
        wrapSummary.style.display = 'block';
        wrapCards.style.display = 'block';
        applyFontSize();
      });
  });

  function generateCards(groupedData, visibility) {
    cardsContainer.innerHTML = '';
    let total = 0;
    
    const showEn = visibility.showEn;
    const showUr = visibility.showUr;
    const showExample = visibility.showExample;
    const showPos = visibility.showPos;
    const showSyllables = visibility.showSyllables;
    const showSynonyms = visibility.showSynonyms;
    const showAntonyms = visibility.showAntonyms;
    const showRelated = visibility.showRelated;
    const showConfusing = visibility.showConfusing;
    
    Object.keys(groupedData).forEach((topicName, index) => {
      const items = groupedData[topicName];
      total += items.length;

      const header = document.createElement('div');
      header.className = 'topic-header text-center';
      header.innerHTML = `<h2 class="mt-4 mb-3" style="font-weight:bold; text-decoration: underline;">${escapeHtml(topicName)}</h2>`;
      cardsContainer.appendChild(header);

      const grid = document.createElement('div');
      grid.className = 'topic-cards-container';
      
      items.forEach((item, i) => {
        const card = document.createElement('div');
        card.className = 'vocab-card';
        
        let cardHtml = `<div class="card-box">`;
        
        // Row 1: Word + POS + Syllables (all on same line)
        cardHtml += `<div class="card-line">`;
        cardHtml += `<b class="word-text">${i+1}. ${escapeHtml(item.word)}</b>`;
        if (showPos && item.part_of_speech) {
          cardHtml += ` <i class="pos-text">(${escapeHtml(item.part_of_speech)})</i>`;
        }
        if (showSyllables && item.syllables) {
          cardHtml += ` <span class="syllables-text">[${escapeHtml(item.syllables)}]</span>`;
        }
        cardHtml += `</div>`;
        
        // Row 2: EN and UR on same row (if both visible)
        const hasEn = showEn && item.meaning_en;
        const hasUr = showUr && item.meaning_ur;
        if (hasEn || hasUr) {
          cardHtml += `<div class="card-line">`;
          if (hasEn && hasUr) {
            cardHtml += `<span><b>EN:</b> ${escapeHtml(item.meaning_en)}</span> &nbsp;|&nbsp; <span><b>UR:</b> ${escapeHtml(item.meaning_ur)}</span>`;
          } else if (hasEn) {
            cardHtml += `<span><b>EN:</b> ${escapeHtml(item.meaning_en)}</span>`;
          } else if (hasUr) {
            cardHtml += `<span><b>UR:</b> ${escapeHtml(item.meaning_ur)}</span>`;
          }
          cardHtml += `</div>`;
        }
        
        // Row 3: Synonyms, Antonyms, and Related Words all on same row
        const hasSyn = showSynonyms && item.synonyms;
        const hasAnt = showAntonyms && item.antonyms;
        const hasRel = showRelated && item.related_words;
        
        if (hasSyn || hasAnt || hasRel) {
          cardHtml += `<div class="card-line">`;
          let parts = [];
          if (hasSyn) parts.push(`<span><b>Syn:</b> ${escapeHtml(item.synonyms)}</span>`);
          if (hasAnt) parts.push(`<span><b>Ant:</b> ${escapeHtml(item.antonyms)}</span>`);
          if (hasRel) parts.push(`<span><b>Rel:</b> ${escapeHtml(item.related_words)}</span>`);
          cardHtml += parts.join(' &nbsp;|&nbsp; ');
          cardHtml += `</div>`;
        }
        
        // Row 4: Confusing Pair
        if (showConfusing && item.confusing_pair) {
          cardHtml += `<div class="card-line"><b>Con:</b> ${escapeHtml(item.confusing_pair)}`;
          if (item.confusing_pair_difference) {
            cardHtml += ` (${escapeHtml(item.confusing_pair_difference)})`;
          }
          cardHtml += `</div>`;
        }
        
        // Last Row: Example Sentence (font size 2pt smaller)
        if (showExample && item.example_sentence) {
          cardHtml += `<div class="card-line example-line"><b>Ex:</b> ${escapeHtml(item.example_sentence)}</div>`;
        }
        
        cardHtml += `</div>`;
        card.innerHTML = cardHtml;
        grid.appendChild(card);
      });
      cardsContainer.appendChild(grid);
    });
    cardCountText.textContent = total + " words displayed";
  }

  // Column toggle listeners
  columnToggles.forEach(toggle => {
    toggle.addEventListener('change', function() {
      if (currentGroupedData) {
        applyColumnVisibility();
        applyFontSize();
      }
    });
  });

  // Font size change listener
  fontSizeSelect.addEventListener('change', function() {
    applyFontSize();
  });

  btnPrint.addEventListener('click', () => window.print());
});

function escapeHtml(str) {
  return String(str || '').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":"&#039;"}[m]));
}
</script>

<style>
/* ================= GLOBAL & PRINT ================= */
body { font-family: "Times New Roman", serif; color: #000; background: #fff; }

.topic-cards-container {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  margin-bottom: 20px;
  page-break-inside: auto;
}

.vocab-card {
  width: calc(33.333% - 7px);
  page-break-inside: avoid;
  break-inside: avoid;
  margin-bottom: 5px;
}

.card-box {
  border: 1px solid #000;
  padding: 8px;
  height: 100%;
  line-height: 1.3;
}

.card-line {
  margin-bottom: 4px;
}

.card-line:last-child {
  margin-bottom: 0;
}

.example-line {
  margin-top: 2px;
  border-top: 1px dashed #ccc;
  padding-top: 3px;
}

.syllables-text {
  color: #555;
}

.topic-header { 
  page-break-after: avoid; 
  break-after: avoid; 
}

@media print {
  @page { size: A4 portrait; margin: 12mm; }
  .no-print { display: none !important; }
  .content { padding: 0 !important; }
  .table-bordered th, .table-bordered td { border: 1px solid #000 !important; }
  body { font-size: 16pt; }
  #vocabSummaryWrapper { page-break-after: avoid; }
  .syllables-text { color: #000; }
  .example-line {
    border-top: 1px dashed #000;
  }
}

/* Custom Table borders for UI */
.summary-table th, .summary-table td { border: 1px solid #000 !important; vertical-align: middle; }

@media print, screen {
    .content-wrapper, 
    .main-sidebar, 
    .main-footer,
    .content {
        border-end: none !important;
        border-start: none !important;
        border-bottom: none !important;
    }

    .fa-fire, 
    .fas.fa-fire, 
    [class*="fa-fire"],
    .main-footer i {
        display: none !important;
    }

    .main-footer {
        display: none !important;
        border-top: none !important;
    }
}

@media print {
    body, .wrapper {
        border: none !important;
    }
    .content-wrapper {
        background-color: #white !important;
        border: none !important;
    }
}
</style>

<?= $this->endSection() ?>