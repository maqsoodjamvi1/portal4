<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?= view('components/page_header', [
    'title' => 'Vocabulary Words List',
    'icon' => 'fas fa-list-ul',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'Vocabulary Words List', 'active' => true],
    ],
]) ?>


<section class="content">

  <!-- ====================== FILTER CARD ====================== -->
  <div class="card card-primary no-print">
    <div class="card-header">
      <h3 class="card-title">Filter By Class / Subject / Topics</h3>
    </div>

    <div class="card-body">
      <div class="row">
        <!-- Class -->
        <div class="form-group col-md-4">
          <label for="class_id">Class</label>
          <select id="class_id" class="form-control">
            <option value="">-- Select Class --</option>
            <?php foreach ($classes as $c): ?>
              <option value="<?= $c->class_id ?>"><?= esc($c->class_name) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Subject -->
        <div class="form-group col-md-4">
          <label for="subject_id">Subject</label>
          <select id="subject_id" class="form-control">
            <option value="">-- Select Subject --</option>
          </select>
        </div>

        <!-- Topics as checkboxes -->
        <div class="form-group col-md-4">
          <label>Topics (one or many)</label>
          <div id="topicsContainer"
               class="border rounded p-2"
               style="max-height: 170px; overflow-y:auto; background:#f9f9f9; font-size:13px;">
            <small class="text-muted">Select Class &amp; Subject to load topics.</small>
          </div>
        </div>
      </div>

      <div class="d-flex justify-content-between align-items-center mt-2">
        <div class="d-flex flex-wrap align-items-center" style="flex-wrap: wrap; gap: 10px;">
          <label class="me-2 mb-0"><strong>Show/Hide:</strong></label>
          
          <div class="form-check me-2">
            <input class="form-check-input column-toggle" type="checkbox" id="showPartOfSpeech" checked>
            <label class="form-check-label" for="showPartOfSpeech">POS</label>
          </div>

          <div class="form-check me-2">
            <input class="form-check-input column-toggle" type="checkbox" id="showEnglishMeaning" checked>
            <label class="form-check-label" for="showEnglishMeaning">EN</label>
          </div>

          <div class="form-check me-2">
            <input class="form-check-input column-toggle" type="checkbox" id="showUrduMeaning" checked>
            <label class="form-check-label" for="showUrduMeaning">UR</label>
          </div>

          <div class="form-check me-2">
            <input class="form-check-input column-toggle" type="checkbox" id="showExample" checked>
            <label class="form-check-label" for="showExample">Ex</label>
          </div>

          <div class="form-check me-2">
            <input class="form-check-input column-toggle" type="checkbox" id="showSynonyms" checked>
            <label class="form-check-label" for="showSynonyms">Syn</label>
          </div>

          <div class="form-check me-2">
            <input class="form-check-input column-toggle" type="checkbox" id="showAntonyms" checked>
            <label class="form-check-label" for="showAntonyms">Ant</label>
          </div>

          <div class="form-check me-2">
            <input class="form-check-input column-toggle" type="checkbox" id="showRelated" checked>
            <label class="form-check-label" for="showRelated">Rel</label>
          </div>

          <div class="form-check me-2">
            <input class="form-check-input column-toggle" type="checkbox" id="showSyllables" checked>
            <label class="form-check-label" for="showSyllables">Syll</label>
          </div>

          <div class="form-check me-2">
            <input class="form-check-input column-toggle" type="checkbox" id="showConfusing" checked>
            <label class="form-check-label" for="showConfusing">Con</label>
          </div>
        </div>

        <div>
          <button type="button" id="btnShowReport" class="btn btn-primary">
            <i class="fas fa-search"></i> Show Words
          </button>
          <button type="button" id="btnCopyAll" class="btn btn-success ms-2" style="display:none;">
            <i class="fas fa-copy"></i> Copy All Words
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- ====================== RESULTS ====================== -->
  <div id="resultsWrapper" style="display:none;">
    <!-- Summary Card -->
    <div class="card card-info">
      <div class="card-header">
        <h3 class="card-title">
          <i class="fas fa-chart-bar"></i> Summary
        </h3>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-3">
            <div class="info-box bg-light">
              <div class="info-box-content">
                <span class="info-box-text">Total Words</span>
                <span class="info-box-number" id="totalWords">0</span>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="info-box bg-light">
              <div class="info-box-content">
                <span class="info-box-text">Total Topics</span>
                <span class="info-box-number" id="totalTopics">0</span>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="info-box bg-light">
              <div class="info-box-content">
                <span class="info-box-text">Class & Subject</span>
                <span class="info-box-number" id="classSubjectInfo">-</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Words by Topic -->
    <div id="topicsWordsContainer">
      <!-- Dynamic content will be inserted here -->
    </div>
  </div>

</section>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {

  const classSelect      = document.getElementById('class_id');
  const subjectSelect    = document.getElementById('subject_id');
  const topicsContainer  = document.getElementById('topicsContainer');
  const btnShow          = document.getElementById('btnShowReport');
  const btnCopyAll       = document.getElementById('btnCopyAll');
  const resultsWrapper   = document.getElementById('resultsWrapper');
  const topicsWordsContainer = document.getElementById('topicsWordsContainer');
  const totalWordsSpan   = document.getElementById('totalWords');
  const totalTopicsSpan  = document.getElementById('totalTopics');
  const classSubjectInfo = document.getElementById('classSubjectInfo');
  
  // Column toggle elements
  const showPartOfSpeech = document.getElementById('showPartOfSpeech');
  const showEnglishMeaning = document.getElementById('showEnglishMeaning');
  const showUrduMeaning = document.getElementById('showUrduMeaning');
  const showExample = document.getElementById('showExample');
  const showSynonyms = document.getElementById('showSynonyms');
  const showAntonyms = document.getElementById('showAntonyms');
  const showRelated = document.getElementById('showRelated');
  const showSyllables = document.getElementById('showSyllables');
  const showConfusing = document.getElementById('showConfusing');

  let currentData = [];
  let currentRawData = [];

  function resetSubjects() {
    subjectSelect.innerHTML = '<option value="">-- Select Subject --</option>';
  }

  function resetTopicsUI() {
    topicsContainer.innerHTML = '<small class="text-muted">Select Class &amp; Subject to load topics.</small>';
  }

  function resetResults() {
    resultsWrapper.style.display = 'none';
    topicsWordsContainer.innerHTML = '';
    btnCopyAll.style.display = 'none';
    currentData = [];
    currentRawData = [];
  }

  // =============== LOAD SUBJECTS ON CLASS CHANGE ==================
  classSelect.addEventListener('change', function () {
    const cid = this.value;
    resetSubjects();
    resetTopicsUI();
    resetResults();
    if (!cid) return;

    fetch('<?= base_url('admin/question-bank/subjects') ?>?class_id=' + encodeURIComponent(cid))
      .then(r => r.json())
      .then(d => {
        const subjects = Array.isArray(d) ? d : (d.subjects || []);
        resetSubjects();
        subjects.forEach(s => {
          const o = document.createElement('option');
          o.value = s.subject_id;
          o.textContent = s.subject_name || s.subject_short_name || ('Subject ' + s.subject_id);
          subjectSelect.appendChild(o);
        });
      })
      .catch(err => {
        console.error('Words List: load subjects error', err);
        resetSubjects();
      });
  });

  // =============== LOAD TOPICS ON SUBJECT CHANGE ==============
  subjectSelect.addEventListener('change', function () {
    const cid = classSelect.value;
    const sid = this.value;
    resetTopicsUI();
    resetResults();
    if (!cid || !sid) return;

    topicsContainer.innerHTML = '<small class="text-muted">Loading topics...</small>';

    fetch('<?= base_url('admin/vocab-bank/topics') ?>?class_id=' + encodeURIComponent(cid) + '&subject_id=' + encodeURIComponent(sid))
      .then(r => r.json())
      .then(d => {
        const topics = Array.isArray(d) ? d : (d.topics || []);
        if (!topics.length) {
          topicsContainer.innerHTML = '<small class="text-danger">No topics found.</small>';
          return;
        }
        topicsContainer.innerHTML = '';
        topics.forEach(t => {
          const id   = t.id;
          const name = t.topic_name || ('Topic ' + id);
          const wrapper = document.createElement('div');
          wrapper.className = 'form-check';
          wrapper.innerHTML = `
            <input class="form-check-input topic-checkbox" type="checkbox" value="${id}" id="topic_${id}">
            <label class="form-check-label" for="topic_${id}">${escapeHtml(name)}</label>
          `;
          topicsContainer.appendChild(wrapper);
        });
      })
      .catch(err => {
        console.error('Words List: load topics error', err);
        topicsContainer.innerHTML = '<small class="text-danger">Error loading topics.</small>';
      });
  });

  // =============== SHOW REPORT BUTTON ==================
  btnShow.addEventListener('click', function () {
    const cid = classSelect.value;
    const sid = subjectSelect.value;

    const topicCheckboxes = document.querySelectorAll('.topic-checkbox:checked');
    const topicIds = [];
    const topicNames = [];

    topicCheckboxes.forEach(cb => {
      const id = cb.value;
      const label = cb.closest('.form-check').querySelector('label');
      const name = label ? label.textContent.trim() : ('Topic ' + id);
      
      topicIds.push(id);
      topicNames.push(name);
    });

    if (!cid || !sid) {
      Swal.fire('Error', 'Please select Class and Subject.', 'error');
      return;
    }
    if (!topicIds.length) {
      Swal.fire('Error', 'Please select at least one Topic.', 'error');
      return;
    }

    resetResults();

    const url = '<?= base_url('admin/vocab-bank/report-data') ?>'
      + '?class_id='   + encodeURIComponent(cid)
      + '&subject_id=' + encodeURIComponent(sid)
      + '&topic_ids='  + encodeURIComponent(topicIds.join(','));

    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
      .then(r => r.json())
      .then(data => {
        if (!data || data.status !== 'ok') {
          Swal.fire('Error', data && data.msg ? data.msg : 'Failed to load words.', 'error');
          return;
        }

        let items = Array.isArray(data.items) ? data.items : [];
        currentRawData = items;
        
        // Group items by topic
        const groupedByTopic = {};
        items.forEach(item => {
          const topicId = item.topic_id || 'unknown';
          const topicName = topicNames[topicIds.indexOf(topicId)] || ('Topic ' + topicId);
          
          if (!groupedByTopic[topicName]) {
            groupedByTopic[topicName] = [];
          }
          groupedByTopic[topicName].push(item);
        });
        
        currentData = groupedByTopic;

        // Update summary
        const classText = classSelect.options[classSelect.selectedIndex]
                          ? classSelect.options[classSelect.selectedIndex].text
                          : (data.header.class_name || '');
        const subjectText = subjectSelect.options[subjectSelect.selectedIndex]
                            ? subjectSelect.options[subjectSelect.selectedIndex].text
                            : (data.header.subject_name || '');
        
        totalWordsSpan.textContent = items.length;
        totalTopicsSpan.textContent = Object.keys(groupedByTopic).length;
        classSubjectInfo.textContent = `${classText} | ${subjectText}`;
        
        // Generate display
        generateWordsDisplay(groupedByTopic);
        resultsWrapper.style.display = 'block';
        btnCopyAll.style.display = 'inline-block';
      })
      .catch(err => {
        console.error('Words List: fetch error', err);
        Swal.fire('Error', 'Error loading words.', 'error');
      });
  });

  // =============== GENERATE WORDS DISPLAY - EACH RECORD ON NEW LINE ==================
  function generateWordsDisplay(groupedData) {
    topicsWordsContainer.innerHTML = '';
    
    const topicNames = Object.keys(groupedData);
    
    if (topicNames.length === 0) {
      topicsWordsContainer.innerHTML = '<div class="alert alert-warning">No words found.</div>';
      return;
    }
    
    const showPos = showPartOfSpeech.checked;
    const showEn = showEnglishMeaning.checked;
    const showUr = showUrduMeaning.checked;
    const showEx = showExample.checked;
    const showSyn = showSynonyms.checked;
    const showAnt = showAntonyms.checked;
    const showRel = showRelated.checked;
    const showSyll = showSyllables.checked;
    const showConf = showConfusing.checked;
    
    topicNames.forEach((topicName, index) => {
      const items = groupedData[topicName];
      
      // Create topic card
      const topicCard = document.createElement('div');
      topicCard.className = 'card mb-4 topic-word-card';
      
      let recordsHtml = `
        <div class="card-header">
          <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
              <span class="badge text-bg-primary me-2">${index + 1}</span>
              ${escapeHtml(topicName)}
              <span class="badge text-bg-info ms-2">${items.length} words</span>
            </h5>
            <button class="btn btn-sm btn-outline-success copy-topic-btn" data-topic="${escapeHtml(topicName)}">
              <i class="fas fa-copy"></i> Copy Words
            </button>
          </div>
        </div>
        <div class="card-body">
          <div class="records-container">
      `;
      
      // Build each word as a separate record with S.No
      items.forEach((item, idx) => {
        let recordHtml = `<div class="word-record">`;
        recordHtml += `<div class="record-sno">${idx + 1}.</div>`;
        recordHtml += `<div class="record-content">`;
        recordHtml += `<span class="word-title"><strong>${escapeHtml(item.word)}</strong></span>`;
        
        if (showPos && item.part_of_speech) {
          recordHtml += ` <span class="pos-text">(${escapeHtml(item.part_of_speech)})</span>`;
        }
        
        // Collect all detail lines
        let details = [];
        
        if (showEn && item.meaning_en) {
          details.push(`<strong>EN:</strong> ${escapeHtml(item.meaning_en)}`);
        }
        
        if (showUr && item.meaning_ur) {
          details.push(`<strong>UR:</strong> ${escapeHtml(item.meaning_ur)}`);
        }
        
        if (showEx && item.example_sentence) {
          details.push(`<strong>Ex:</strong> ${escapeHtml(item.example_sentence)}`);
        }
        
        if (showSyn && item.synonyms) {
          details.push(`<strong>Syn:</strong> ${escapeHtml(item.synonyms)}`);
        }
        
        if (showAnt && item.antonyms) {
          details.push(`<strong>Ant:</strong> ${escapeHtml(item.antonyms)}`);
        }
        
        if (showRel && item.related_words) {
          details.push(`<strong>Rel:</strong> ${escapeHtml(item.related_words)}`);
        }
        
        if (showSyll && item.syllables) {
          details.push(`<strong>Syll:</strong> ${escapeHtml(item.syllables)}`);
        }
        
        if (showConf && item.confusing_pair) {
          let confusingText = `<strong>Con:</strong> ${escapeHtml(item.confusing_pair)}`;
          if (item.confusing_pair_difference) {
            confusingText += ` (${escapeHtml(item.confusing_pair_difference)})`;
          }
          details.push(confusingText);
        }
        
        if (details.length > 0) {
          recordHtml += `<div class="record-details">${details.join(' | ')}</div>`;
        }
        
        recordHtml += `</div></div>`;
        recordsHtml += recordHtml;
      });
      
      recordsHtml += `
          </div>
        </div>
      `;
      
      topicCard.innerHTML = recordsHtml;
      topicsWordsContainer.appendChild(topicCard);
    });
    
    // Add event listeners to copy buttons
    document.querySelectorAll('.copy-topic-btn').forEach(btn => {
      btn.addEventListener('click', function(e) {
        e.stopPropagation();
        const topicName = this.getAttribute('data-topic');
        const topicCard = this.closest('.topic-word-card');
        const records = topicCard.querySelectorAll('.word-record');
        
        const showPos = showPartOfSpeech.checked;
        const showEn = showEnglishMeaning.checked;
        const showUr = showUrduMeaning.checked;
        const showEx = showExample.checked;
        const showSyn = showSynonyms.checked;
        const showAnt = showAntonyms.checked;
        const showRel = showRelated.checked;
        const showSyll = showSyllables.checked;
        const showConf = showConfusing.checked;
        
        let wordTexts = [];
        records.forEach(record => {
          const sno = record.querySelector('.record-sno')?.textContent || '';
          const word = record.querySelector('.word-title')?.textContent || '';
          const pos = record.querySelector('.pos-text')?.textContent || '';
          
          let details = [];
          const detailsDiv = record.querySelector('.record-details');
          if (detailsDiv) {
            const detailText = detailsDiv.textContent || '';
            const parts = detailText.split(' | ');
            details = parts;
          }
          
          let fullText = `${sno} ${word}`;
          if (pos) fullText += ` ${pos}`;
          if (details.length > 0) {
            fullText += ` [${details.join(' | ')}]`;
          }
          wordTexts.push(fullText);
        });
        
        copyToClipboard(wordTexts.join('\n'), topicName);
      });
    });
  }
  
  // =============== COPY ALL WORDS ==================
  btnCopyAll.addEventListener('click', function() {
    if (!currentData || Object.keys(currentData).length === 0) {
      Swal.fire('Error', 'No words to copy.', 'error');
      return;
    }
    
    const showPos = showPartOfSpeech.checked;
    const showEn = showEnglishMeaning.checked;
    const showUr = showUrduMeaning.checked;
    const showEx = showExample.checked;
    const showSyn = showSynonyms.checked;
    const showAnt = showAntonyms.checked;
    const showRel = showRelated.checked;
    const showSyll = showSyllables.checked;
    const showConf = showConfusing.checked;
    
    let allWords = [];
    const topicNames = Object.keys(currentData);
    
    topicNames.forEach(topicName => {
      const items = currentData[topicName];
      allWords.push(`=== ${topicName} ===`);
      
      items.forEach((item, idx) => {
        let entry = `${idx + 1}. ${item.word}`;
        const parts = [];
        
        if (showPos && item.part_of_speech) {
          parts.push(`(${item.part_of_speech})`);
        }
        
        if (showEn && item.meaning_en) {
          parts.push(`EN: ${item.meaning_en}`);
        }
        
        if (showUr && item.meaning_ur) {
          parts.push(`UR: ${item.meaning_ur}`);
        }
        
        if (showEx && item.example_sentence) {
          parts.push(`Ex: ${item.example_sentence}`);
        }
        
        if (showSyn && item.synonyms) {
          parts.push(`Syn: ${item.synonyms}`);
        }
        
        if (showAnt && item.antonyms) {
          parts.push(`Ant: ${item.antonyms}`);
        }
        
        if (showRel && item.related_words) {
          parts.push(`Rel: ${item.related_words}`);
        }
        
        if (showSyll && item.syllables) {
          parts.push(`Syll: ${item.syllables}`);
        }
        
        if (showConf && item.confusing_pair) {
          let confusingText = `Con: ${item.confusing_pair}`;
          if (item.confusing_pair_difference) {
            confusingText += ` (${item.confusing_pair_difference})`;
          }
          parts.push(confusingText);
        }
        
        if (parts.length > 0) {
          entry += ` [${parts.join(' | ')}]`;
        }
        
        allWords.push(entry);
      });
      
      allWords.push(''); // Empty line between topics
    });
    
    const fullText = allWords.join('\n');
    copyToClipboard(fullText, 'All Topics');
  });
  
  // =============== COPY TO CLIPBOARD FUNCTION ==================
  function copyToClipboard(text, label) {
    const textarea = document.createElement('textarea');
    textarea.value = text;
    textarea.style.position = 'fixed';
    textarea.style.opacity = '0';
    document.body.appendChild(textarea);
    textarea.select();
    
    try {
      const successful = document.execCommand('copy');
      if (successful) {
        Swal.fire({
          icon: 'success',
          title: 'Copied!',
          text: `${label} words copied to clipboard.`,
          toast: true,
          timer: 2000,
          showConfirmButton: false
        });
      } else {
        throw new Error('Copy failed');
      }
    } catch (err) {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'Failed to copy to clipboard. Please try again.',
        timer: 2000
      });
    } finally {
      document.body.removeChild(textarea);
    }
  }
  
  // =============== TOGGLE ALL FILTERS ==================
  function refreshDisplay() {
    if (currentData && Object.keys(currentData).length > 0) {
      generateWordsDisplay(currentData);
    }
  }
  
  // Add event listeners to all toggle checkboxes
  showPartOfSpeech.addEventListener('change', refreshDisplay);
  showEnglishMeaning.addEventListener('change', refreshDisplay);
  showUrduMeaning.addEventListener('change', refreshDisplay);
  showExample.addEventListener('change', refreshDisplay);
  showSynonyms.addEventListener('change', refreshDisplay);
  showAntonyms.addEventListener('change', refreshDisplay);
  showRelated.addEventListener('change', refreshDisplay);
  showSyllables.addEventListener('change', refreshDisplay);
  showConfusing.addEventListener('change', refreshDisplay);
  
  // Simple HTML escape
  function escapeHtml(str) {
    return String(str || '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }
  
});
</script>

<style>
/* Words List Styling */
.topic-word-card {
  border-start: 4px solid #007bff;
  transition: all 0.3s ease;
  page-break-inside: avoid;
  margin-bottom: 20px;
}

.topic-word-card:hover {
  box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.topic-word-card .card-header {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border-bottom: none;
}

.topic-word-card .card-header h5 {
  color: white;
}

.topic-word-card .text-bg-primary {
  background: white;
  color: #667eea;
  font-size: 14px;
  padding: 5px 10px;
}

.topic-word-card .text-bg-info {
  background: rgba(255,255,255,0.3);
  color: white;
}

.records-container {
  background: #f8f9fa !important;
  border: 1px solid #e9ecef;
  padding: 10px;
}

.word-record {
  display: flex;
  margin-bottom: 12px;
  padding-bottom: 8px;
  border-bottom: 1px solid #dee2e6;
}

.word-record:last-child {
  border-bottom: none;
  margin-bottom: 0;
  padding-bottom: 0;
}

.record-sno {
  font-weight: bold;
  font-size: 14px;
  color: #007bff;
  min-width: 35px;
  margin-right: 10px;
}

.record-content {
  flex: 1;
}

.word-title {
  font-size: 15px;
  color: #333;
}

.pos-text {
  font-style: italic;
  color: #6c757d;
  font-size: 13px;
}

.record-details {
  margin-top: 5px;
  font-size: 13px;
  color: #555;
  line-height: 1.5;
}

.record-details strong {
  color: #333;
  font-weight: 700;
}

.copy-topic-btn {
  transition: all 0.3s ease;
}

.copy-topic-btn:hover {
  transform: scale(1.05);
}

/* Summary Info Boxes */
.info-box {
  box-shadow: 0 0 1px rgba(0,0,0,0.125), 0 1px 3px rgba(0,0,0,0.2);
  border-radius: 0.25rem;
  background: #fff;
  display: flex;
  padding: 0.5rem;
  margin-bottom: 0;
}

.info-box-content {
  flex: 1;
  padding: 0.5rem 0.5rem 0.5rem 0;
}

.info-box-text {
  text-transform: uppercase;
  font-weight: 600;
  font-size: 0.75rem;
  color: #6c757d;
  margin-bottom: 0.25rem;
}

.info-box-number {
  font-size: 1.5rem;
  font-weight: 700;
  color: #007bff;
  margin-bottom: 0;
}

/* Print styles */
@media print {
  .no-print {
    display: none !important;
  }
  
  .topic-word-card {
    break-inside: avoid;
    page-break-inside: avoid;
    border-start: 2px solid #000;
  }
  
  .topic-word-card .card-header {
    background: #f0f0f0 !important;
    color: #000 !important;
    border-bottom: 1px solid #000;
  }
  
  .topic-word-card .card-header h5 {
    color: #000 !important;
  }
  
  .records-container {
    background: white !important;
    border: 1px solid #ddd;
  }
  
  .record-sno {
    color: #000;
  }
  
  .record-details strong {
    color: #000;
  }
  
  .info-box {
    border: 1px solid #ddd;
  }
  
  .info-box-number {
    color: #000;
  }
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .topic-word-card .card-header {
    flex-direction: column;
    align-items: flex-start !important;
  }
  
  .copy-topic-btn {
    margin-top: 10px;
    width: 100%;
  }
  
  .word-record {
    flex-direction: column;
  }
  
  .record-sno {
    margin-bottom: 5px;
  }
  
  .record-details {
    font-size: 12px;
  }
}
</style>

<?= $this->endSection() ?>