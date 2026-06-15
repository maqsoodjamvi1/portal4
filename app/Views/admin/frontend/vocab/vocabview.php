<?= $this->extend('frontend/layouts/master_portal') ?>

<?= $this->section('content') ?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <i class="fas fa-book-open me-2"></i>
                    <?= esc($title ?? 'Vocabulary Bank') ?>
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= base_url('student/dashboard') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item active">Vocabulary</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <!-- Parent Student Selector -->
        <?php if ($is_parent && !empty($children)): ?>
        <div class="row mb-3">
            <div class="col-12">
                <div class="card card-info">
                    <div class="card-header">
                        <h3 class="card-title">Select Student</h3>
                    </div>
                    <div class="card-body">
                        <div class="student-list d-flex flex-wrap">
                            <?php foreach ($children as $child): ?>
                            <div class="student-item mb-2 me-2">
                                <button class="btn btn-outline-primary student-select-btn <?= $active_student_id == $child['student_id'] ? 'active' : '' ?>"
                                        data-student-id="<?= $child['student_id'] ?>">
                                    <div class="d-flex align-items-center">
                                        <?php if (!empty($child['profile_photo'])): ?>
                                        <img src="<?= base_url('uploads/' . $child['profile_photo']) ?>" 
                                             class="img-circle me-2" 
                                             style="width: 30px; height: 30px; object-fit: cover;">
                                        <?php else: ?>
                                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" 
                                             style="width: 30px; height: 30px;">
                                            <i class="fas fa-user"></i>
                                        </div>
                                        <?php endif; ?>
                                        <div class="text-start">
                                            <strong><?= esc($child['first_name'] . ' ' . $child['last_name']) ?></strong><br>
                                            <small class="text-muted">
                                                <?= esc($child['class_name'] ?? '') ?> - 
                                                <?= esc($child['section_name'] ?? '') ?>
                                            </small>
                                        </div>
                                    </div>
                                </button>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Student Info Card -->
        <?php if (isset($student) && $student && $active_student_id > 0): ?>
        <div class="row mb-3">
            <div class="col-12">
                <div class="card card-primary">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-1">
                                    <i class="fas fa-user-graduate me-2"></i>
                                    <?= esc($student['first_name'] . ' ' . $student['last_name']) ?>
                                </h5>
                                <p class="mb-0 text-muted">
                                    <i class="fas fa-school me-1"></i>
                                    <?= esc($student['class_name'] ?? '') ?> - 
                                    <?= esc($student['section_name'] ?? '') ?> |
                                    <?= esc($student['campus_name'] ?? '') ?>
                                </p>
                            </div>
                            <div class="badge text-bg-light">
                                <i class="fas fa-id-card me-1"></i>
                                <?= esc($student['reg_no'] ?? '') ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Error Message -->
        <?php if (isset($error) && $error): ?>
        <div class="row mb-3">
            <div class="col-12">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?= esc($error) ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Main Content Area -->
        <div class="row">
            <div class="col-12">
                <!-- Loading Spinner -->
                <div id="loadingSpinner" class="text-center py-5" style="<?= ($active_student_id > 0 && empty($error)) ? '' : 'display: none;' ?>">
                    <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3 text-muted">Loading vocabulary...</p>
                </div>
                
                <!-- Vocabulary Content -->
                <div id="vocabularyContent" style="display: none;">
                    <!-- Topics Accordion -->
                    <div class="accordion" id="vocabAccordion">
                        <!-- Dynamic content will be loaded here -->
                    </div>
                    
                    <!-- Empty State -->
                    <div id="emptyState" class="text-center py-5" style="display: none;">
                        <div class="empty-state-icon mb-3">
                            <i class="fas fa-book fa-4x text-muted"></i>
                        </div>
                        <h4 class="text-muted">No Vocabulary Available</h4>
                        <p class="text-muted" id="emptyMessage">Your teacher hasn't added any vocabulary words yet.</p>
                        <a href="<?= base_url('student/dashboard') ?>" class="btn btn-primary mt-3">
                            <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
                        </a>
                    </div>
                </div>
                
                <!-- Select Student Prompt -->
                <div id="selectStudentPrompt" class="text-center py-5" style="<?= ($is_parent && $active_student_id == 0) ? '' : 'display: none;' ?>">
                    <div class="empty-state-icon mb-3">
                        <i class="fas fa-users fa-4x text-muted"></i>
                    </div>
                    <h4 class="text-muted">Select a Student</h4>
                    <p class="text-muted">Please select a student from the list above to view their vocabulary.</p>
                </div>
                
                <!-- Error Alert -->
                <div id="errorAlert" class="alert alert-danger alert-dismissible fade" style="display: none;" role="alert">
                    <span id="errorMessage"></span>
                    <button type="button" class="close" data-bs-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
     const activeStudentId = <?= $active_student_id ?? 0 ?>;
     // Debug current page info
    console.log('=== PAGE INFO ===');
    console.log('Current URL:', window.location.href);
    console.log('Pathname:', window.location.pathname);
    console.log('Origin:', window.location.origin);
    console.log('Student ID:', activeStudentId);
    console.log('=================');
    
    
    const loadingSpinner = document.getElementById('loadingSpinner');
    const vocabularyContent = document.getElementById('vocabularyContent');
    const vocabAccordion = document.getElementById('vocabAccordion');
    const emptyState = document.getElementById('emptyState');
    const emptyMessage = document.getElementById('emptyMessage');
    const selectStudentPrompt = document.getElementById('selectStudentPrompt');
    const errorAlert = document.getElementById('errorAlert');
    const errorMessage = document.getElementById('errorMessage');
    
    // Get student ID
  
    const isParent = <?= $is_parent ? 'true' : 'false' ?>;
    
    // Load vocabulary if student is selected
    if (activeStudentId > 0) {
        loadVocabulary();
    }
    
    // Student selection for parents
    document.querySelectorAll('.student-select-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const studentId = this.getAttribute('data-student-id');
            
            // Update active state
            document.querySelectorAll('.student-select-btn').forEach(b => {
                b.classList.remove('active');
            });
            this.classList.add('active');
            
            // Show loading
            vocabularyContent.style.display = 'none';
            emptyState.style.display = 'none';
            selectStudentPrompt.style.display = 'none';
            loadingSpinner.style.display = 'block';
            
            // Switch student via AJAX
            fetch(`<?= base_url('frontend/vocabbank/switchStudent') ?>/${activeStudentId}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Reload page to show new student data
                    window.location.reload();
                } else {
                    showError(data.message || 'Failed to switch student');
                    loadingSpinner.style.display = 'none';
                }
            })
            .catch(error => {
                console.error('Switch error:', error);
                showError('Network error. Please try again.');
                loadingSpinner.style.display = 'none';
            });
        });
    });
    
   // Update the loadVocabulary function to log the URL
function loadVocabulary() {
   
   const url = '/student/vocabbank/data';
      console.log('Using URL:', url);
        console.log('Full request URL:', window.location.origin + url);
        
    $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            data: {
                student_id: activeStudentId,
                debug: 1 // Add debug parameter
            },
            beforeSend: function() {
                $('#loadingSpinner').show();
                $('#vocabularyContent').hide();
                $('#errorAlert').hide();
                console.log('Sending AJAX request...');
            },
           success: function(data, status, xhr) {
    console.log('AJAX Success - Full response:', data);
    console.log('Response status:', data.status);
    $('#loadingSpinner').hide();
    
    if (data.status === 'ok') {
        console.log('Data has topics_by_subject?', !!data.topics_by_subject);
        console.log('Topics count:', data.summary?.total_topics || 0);
        console.log('Words count:', data.summary?.total_words || 0);
        
        if (data.topics_by_subject && Object.keys(data.topics_by_subject).length > 0) {
            console.log('Calling displayVocabulary...');
            displayVocabulary(data);
        } else if (data.topics && data.topics.length > 0) {
            // Fallback for old response format
            console.log('Using old format (topics array)');
            displayVocabularyOldFormat(data);
        } else {
            console.log('No topics found, showing empty message');
            $('#vocabularyContent').html(`
                <div class="alert alert-warning">
                    <i class="fas fa-info-circle"></i>
                    ${data.message || 'No vocabulary available.'}
                    ${data.summary ? `<br><small>Subjects: ${data.summary.all_subjects?.join(', ') || 'None'}</small>` : ''}
                </div>
            `).show();
        }
    } else if (data.status === 'error') {
        showError(data.msg || 'Error loading vocabulary');
        console.log('Error data:', data);
    } else if (data.status === 'debug') {
        // Show debug info
        console.log('Debug data:', data);
        $('#vocabularyContent').html(`
            <div class="alert alert-info">
                <h5>Debug Information</h5>
                <pre>${JSON.stringify(data, null, 2)}</pre>
            </div>
        `).show();
    } else {
        console.log('Unexpected response format:', data);
        showError('Unexpected response from server');
    }
},
            error: function(xhr, status, error) {
                $('#loadingSpinner').hide();
                
                console.log('=== AJAX ERROR ===');
                console.log('Status:', xhr.status);
                console.log('Error:', error);
                console.log('Response:', xhr.responseText);
                console.log('Requested URL:', xhr.responseURL);
                
                if (xhr.status === 404) {
                    showError(`404 Not Found. The server cannot find: ${xhr.responseURL}<br>
                              Make sure the route 'frontend/vocabbank/data' is defined in Routes.php`);
                } else {
                    showError('Error ' + xhr.status + ': ' + error);
                }
            }
        });
    }
    function renderVocabulary(topics, vocabulary, summary) {
        vocabAccordion.innerHTML = '';
        
        // Add summary card
        if (summary) {
            const summaryCard = document.createElement('div');
            summaryCard.className = 'card card-success mb-3';
            summaryCard.innerHTML = `
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-1"><i class="fas fa-chart-bar me-2"></i>Vocabulary Summary</h5>
                            <p class="mb-0">
                                <span class="badge text-bg-primary me-2">${summary.total_topics} Topics</span>
                                <span class="badge text-bg-success">${summary.total_words} Words</span>
                            </p>
                        </div>
                        <div class="text-end">
                            <small class="text-muted">
                                ${summary.class_name} ${summary.section_name} | 
                                ${summary.subject_name || ''}
                            </small>
                        </div>
                    </div>
                </div>
            `;
            vocabAccordion.appendChild(summaryCard);
        }
        
        // Add topics
        topics.forEach((topic, index) => {
            const topicId = topic.id;
            const words = vocabulary[topicId] || [];
            const hasWords = words.length > 0;
            
            const topicCard = createTopicCard(topic, words, index, hasWords);
            vocabAccordion.appendChild(topicCard);
        });
    }
    
    function displayVocabulary(data) {
    console.log('displayVocabulary called with data:', data);
    console.log('Has topics_by_subject?', !!data.topics_by_subject);
    console.log('Topics by subject keys:', data.topics_by_subject ? Object.keys(data.topics_by_subject) : 'none');
    
    let html = '';
    
    // Show summary
    if (data.summary) {
        console.log('Summary:', data.summary);
        html += `
            <div class="alert alert-success mb-4">
                <h5><i class="fas fa-user-graduate"></i> ${data.summary.student_name}</h5>
                <p class="mb-1">
                    <strong>${data.summary.class_name} ${data.summary.section_name}</strong> | 
                    ${data.summary.total_topics} topics, ${data.summary.total_words} words
                </p>
                <p class="mb-0">
                    <small>
                        <strong>Subjects:</strong> ${data.summary.all_subjects ? data.summary.all_subjects.join(', ') : 'Not specified'}
                        ${data.summary.subjects_with_vocabulary && data.summary.subjects_with_vocabulary.length > 0 ? 
                          `<br><strong>Subjects with vocabulary:</strong> ${data.summary.subjects_with_vocabulary.join(', ')}` : 
                          ''}
                    </small>
                </p>
            </div>
        `;
    }
    
    // Check if we have topics grouped by subject
    if (data.topics_by_subject && Object.keys(data.topics_by_subject).length > 0) {
        console.log('Processing topics_by_subject:', data.topics_by_subject);
        
        // Display topics grouped by subject
        Object.keys(data.topics_by_subject).forEach((subjectName, subjectIndex) => {
            const subjectTopics = data.topics_by_subject[subjectName];
            const totalWordsInSubject = subjectTopics.reduce((total, topic) => total + (topic.vocabulary?.length || 0), 0);
            
            console.log(`Subject ${subjectName}: ${subjectTopics.length} topics, ${totalWordsInSubject} words`);
            
            html += `
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-book me-2"></i>
                            ${subjectName}
                            <span class="badge text-bg-light float-end">
                                ${subjectTopics.length} topics, ${totalWordsInSubject} words
                            </span>
                        </h5>
                    </div>
                    <div class="card-body">
            `;
            
            subjectTopics.forEach((topic, topicIndex) => {
                const words = topic.vocabulary || [];
                
                html += `
                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <span class="badge text-bg-primary">${topicIndex + 1}</span>
                                ${topic.topic_name}
                                <span class="badge text-bg-secondary float-end">${words.length} words</span>
                            </h6>
                        </div>
                        <div class="card-body">
                `;
                
                if (words.length > 0) {
                    console.log(`Topic ${topic.topic_name}: ${words.length} words`);
                    words.forEach((word, idx) => {
                        html += `
                            <div class="border rounded p-3 mb-2">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <strong class="text-primary">${escapeHtml(word.word)}</strong>
                                        ${word.part_of_speech ? `<span class="badge bg-info ms-2">${escapeHtml(word.part_of_speech)}</span>` : ''}
                                    </div>
                                    <span class="badge bg-light text-dark">${idx + 1}</span>
                                </div>
                                ${word.meaning_en ? `<p class="mb-1"><small>EN:</small> ${escapeHtml(word.meaning_en)}</p>` : ''}
                                ${word.meaning_ur ? `<p class="mb-1"><small>UR:</small> ${escapeHtml(word.meaning_ur)}</p>` : ''}
                                ${word.example_sentence ? `<p class="mb-0 text-success"><small>Example:</small> ${escapeHtml(word.example_sentence)}</p>` : ''}
                            </div>
                        `;
                    });
                } else {
                    html += `<p class="text-muted text-center">No words in this topic</p>`;
                }
                
                html += `</div></div>`;
            });
            
            html += `</div></div>`;
        });
    } else if (data.student_subjects) {
        // Show which subjects have no vocabulary
        html += `<div class="alert alert-info">`;
        html += `<h5>Your Subjects:</h5>`;
        html += `<ul class="mb-0">`;
        data.student_subjects.forEach(subject => {
            html += `<li>${escapeHtml(subject.subject_name)}: ${subject.has_vocabulary ? '✅ Has vocabulary' : '❌ No vocabulary yet'}</li>`;
        });
        html += `</ul></div>`;
    } else {
        html += `<div class="alert alert-warning">No vocabulary available for any of your subjects.</div>`;
    }
    
    console.log('Generated HTML length:', html.length);
    $('#vocabularyContent').html(html).show();
    console.log('Content displayed');
}

// Make sure escapeHtml function exists
function escapeHtml(str) {
    if (typeof str !== 'string') return '';
    return str
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}
    
    function createTopicCard(topic, words, index, hasWords) {
        const collapseId = `collapse-${topic.id}`;
        
        const card = document.createElement('div');
        card.className = 'card mb-2';
        
        // Card header
        const cardHeader = document.createElement('div');
        cardHeader.className = 'card-header';
        cardHeader.id = `heading-${topic.id}`;
        cardHeader.style.cursor = 'pointer';
        cardHeader.setAttribute('data-bs-toggle', 'collapse');
        cardHeader.setAttribute('data-bs-target', `#${collapseId}`);
        cardHeader.setAttribute('aria-expanded', 'false');
        cardHeader.setAttribute('aria-controls', collapseId);
        
        const headerContent = document.createElement('div');
        headerContent.className = 'd-flex justify-content-between align-items-center';
        
        // Topic title with number
        const titleDiv = document.createElement('div');
        titleDiv.className = 'd-flex align-items-center';
        
        const topicNumber = document.createElement('span');
        topicNumber.className = 'badge text-bg-primary me-2';
        topicNumber.textContent = index + 1;
        
        const topicName = document.createElement('h5');
        topicName.className = 'mb-0';
        topicName.style.fontSize = '1rem';
        topicName.textContent = topic.topic_name || `Topic ${topic.id}`;
        
        titleDiv.appendChild(topicNumber);
        titleDiv.appendChild(topicName);
        
        // Word count and chevron
        const rightDiv = document.createElement('div');
        rightDiv.className = 'd-flex align-items-center';
        
        const wordCount = document.createElement('span');
        wordCount.className = 'badge text-bg-light text-dark me-3';
        wordCount.textContent = `${words.length} word${words.length !== 1 ? 's' : ''}`;
        
        const chevron = document.createElement('i');
        chevron.className = 'fas fa-chevron-down';
        
        rightDiv.appendChild(wordCount);
        rightDiv.appendChild(chevron);
        
        headerContent.appendChild(titleDiv);
        headerContent.appendChild(rightDiv);
        cardHeader.appendChild(headerContent);
        
        // Collapse body
        const collapseDiv = document.createElement('div');
        collapseDiv.id = collapseId;
        collapseDiv.className = 'collapse';
        collapseDiv.setAttribute('aria-labelledby', `heading-${topic.id}`);
        collapseDiv.setAttribute('data-parent', '#vocabAccordion');
        
        const cardBody = document.createElement('div');
        cardBody.className = 'card-body';
        
        if (hasWords) {
            const vocabList = createVocabularyList(words);
            cardBody.appendChild(vocabList);
        } else {
            const emptyMsg = document.createElement('div');
            emptyMsg.className = 'text-center py-4 text-muted';
            emptyMsg.innerHTML = `
                <i class="fas fa-book-open fa-2x mb-3"></i>
                <p class="mb-0">No vocabulary words for this topic yet.</p>
            `;
            cardBody.appendChild(emptyMsg);
        }
        
        collapseDiv.appendChild(cardBody);
        
        // Add chevron animation
        cardHeader.addEventListener('click', function() {
            const chevron = this.querySelector('.fa-chevron-down');
            if (chevron) {
                chevron.classList.toggle('fa-chevron-down');
                chevron.classList.toggle('fa-chevron-up');
            }
        });
        
        // Assemble card
        card.appendChild(cardHeader);
        card.appendChild(collapseDiv);
        
        return card;
    }
    
    function createVocabularyList(words) {
        const container = document.createElement('div');
        
        words.forEach((word, idx) => {
            const wordCard = document.createElement('div');
            wordCard.className = 'vocabulary-word-card mb-3 p-3 border rounded bg-white';
            
            let html = `
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="d-flex align-items-center">
                        <span class="badge text-bg-secondary me-2">${idx + 1}</span>
                        <h6 class="mb-0 text-primary fw-bold">${escapeHtml(word.word)}</h6>
                    </div>
            `;
            
            if (word.part_of_speech) {
                html += `<span class="badge text-bg-info">${escapeHtml(word.part_of_speech)}</span>`;
            }
            
            html += `</div>`;
            
            if (word.meaning_en) {
                html += `
                    <div class="row mb-2">
                        <div class="col-md-6">
                            <small class="text-muted d-block">English Meaning:</small>
                            <p class="mb-2">${escapeHtml(word.meaning_en)}</p>
                        </div>
                `;
            }
            
            if (word.meaning_ur) {
                html += `
                        <div class="col-md-6">
                            <small class="text-muted d-block">Urdu Meaning:</small>
                            <p class="mb-2">${escapeHtml(word.meaning_ur)}</p>
                        </div>
                `;
            }
            
            if (word.meaning_en || word.meaning_ur) {
                html += `</div>`;
            }
            
            if (word.example_sentence) {
                html += `
                    <div class="mb-2">
                        <small class="text-muted d-block">Example:</small>
                        <p class="mb-0 fst-italic text-success">${escapeHtml(word.example_sentence)}</p>
                    </div>
                `;
            }
            
            // Additional info
            let additionalInfo = [];
            if (word.synonyms) additionalInfo.push({label: 'Synonyms', value: word.synonyms, class: 'text-info'});
            if (word.antonyms) additionalInfo.push({label: 'Antonyms', value: word.antonyms, class: 'text-danger'});
            if (word.related_words) additionalInfo.push({label: 'Related Words', value: word.related_words, class: 'text-warning'});
            if (word.confusing_pair) additionalInfo.push({label: 'Confusing Pair', value: word.confusing_pair, class: 'text-muted'});
            if (word.syllables) additionalInfo.push({label: 'Syllables', value: word.syllables, class: 'badge text-bg-light'});
            if (word.difficulty_level) additionalInfo.push({label: 'Difficulty', value: word.difficulty_level, class: 'badge ' + (word.difficulty_level === 'Easy' ? 'text-bg-success' : word.difficulty_level === 'Medium' ? 'text-bg-warning' : 'text-bg-danger')});
            
            if (additionalInfo.length > 0) {
                html += `<div class="additional-info mt-3 pt-3 border-top">`;
                html += `<div class="row">`;
                
                additionalInfo.forEach(info => {
                    html += `
                        <div class="col-12 col-md-6 mb-2">
                            <small class="text-muted d-block">${info.label}:</small>
                            <div class="${info.class}">${escapeHtml(info.value)}</div>
                        </div>
                    `;
                });
                
                html += `</div></div>`;
            }
            
            wordCard.innerHTML = html;
            container.appendChild(wordCard);
        });
        
        return container;
    }
    
    function escapeHtml(str) {
        return String(str || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
    
    function showError(message) {
        errorMessage.textContent = message;
        errorAlert.style.display = 'block';
        errorAlert.classList.add('show');
    }
});
</script>

<style>
/* Custom styles for vocabulary page */
.student-select-btn.active {
    background-color: #007bff !important;
    color: white !important;
    border-color: #007bff !important;
}

.vocabulary-word-card {
    background: #fff;
    transition: all 0.3s ease;
}

.vocabulary-word-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.card-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
}

.card-header h5 {
    color: white;
}

.text-bg-primary {
    background: rgba(255,255,255,0.2);
    border: 1px solid white;
}

.additional-info {
    font-size: 0.9rem;
}

/* Mobile responsiveness */
@media (max-width: 768px) {
    .vocabulary-word-card {
        padding: 1rem !important;
    }
    
    .card-header h5 {
        font-size: 1rem;
    }
    
    .additional-info .row > div {
        width: 100% !important;
        max-width: 100% !important;
    }
    
    .student-list {
        flex-direction: column;
    }
    
    .student-item {
        margin-right: 0 !important;
        margin-bottom: 0.5rem;
    }
    
    .student-select-btn {
        width: 100%;
        text-align: left;
    }
}
</style>

<?= $this->endSection() ?>