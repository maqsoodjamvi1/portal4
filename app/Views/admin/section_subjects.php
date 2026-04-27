<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<!-- Toastr for notifications -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

<style>
.class-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 10px;
    padding: 15px;
    margin-bottom: 15px;
    cursor: pointer;
    transition: transform 0.2s;
}
.class-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}
.class-card.active {
    border: 3px solid #ffc107;
}
.section-card {
    background: white;
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}
.subject-item {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 10px;
    margin-bottom: 10px;
    transition: all 0.2s;
}
.subject-item.assigned {
    background: #d4edda;
    border-color: #c3e6cb;
}
.subject-checkbox {
    margin-right: 10px;
    transform: scale(1.2);
}
.progress-bar-custom {
    height: 10px;
    background: #e9ecef;
    border-radius: 5px;
    overflow: hidden;
    margin: 10px 0;
}
.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #28a745, #20c997);
    border-radius: 5px;
    transition: width 0.3s;
}
</style>

<!-- Page Header -->
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1><i class="fas fa-book mr-2"></i> Section Subjects</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="<?= site_url('admin/dashboard') ?>">Dashboard</a></li>
          <li class="breadcrumb-item active">Section Subjects</li>
        </ol>
      </div>
    </div>
  </div>
</section>

<!-- Main Content -->
<section class="content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-body">
            <!-- Loader -->
            <div id="loader" class="text-center py-5">
              <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                <span class="sr-only">Loading...</span>
              </div>
              <p class="mt-2">Loading section subjects...</p>
            </div>

            <!-- Content Area -->
            <div id="contentArea" style="display: none;">
              <!-- Filters -->
              <div class="row mb-3">
                <div class="col-md-6">
                  <input type="text" class="form-control" id="searchSubjects" placeholder="Search subjects...">
                </div>
                <div class="col-md-6">
                  <select class="form-control" id="filterStatus">
                    <option value="all">All Subjects</option>
                    <option value="assigned">Assigned Only</option>
                    <option value="unassigned">Unassigned Only</option>
                  </select>
                </div>
              </div>

              <div class="row">
                <!-- Classes Sidebar -->
                <div class="col-md-3">
                  <h5>Classes</h5>
                  <div id="classesList"></div>
                </div>

                <!-- Sections Content -->
                <div class="col-md-9">
                  <div id="selectedClassInfo" class="mb-3"></div>
                  <div id="sectionsContainer"></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<script>
let appData = {
    classes: [],
    subjects: [],
    assignments: {},
    currentClassIndex: 0,
    searchTerm: '',
    filterStatus: 'all'
};

$(document).ready(function() {
    loadData();
});

function loadData() {
    $('#loader').show();
    $('#contentArea').hide();
    
    $.ajax({
        url: "<?= base_url('admin/section_subjects/getData') ?>",
        type: "GET",
        dataType: "json",
        success: function(response) {
            console.log('Data loaded:', response);
            
            if (response.status === 'success') {
                appData.classes = response.data.classes || [];
                appData.subjects = response.data.subjects || [];
                appData.assignments = response.data.assignments || {};
                
                renderClasses();
                
                if (appData.classes.length > 0) {
                    selectClass(0);
                }
                
                $('#loader').hide();
                $('#contentArea').show();
            } else {
                showError(response.message || 'Failed to load data');
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', {xhr, status, error});
            showError('Network error: ' + (xhr.statusText || 'Unknown error'));
        }
    });
}

function showError(message) {
    $('#loader').hide();
    $('#contentArea').html(`
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            ${message}
            <button class="btn btn-sm btn-outline-danger ml-3" onclick="location.reload()">
                <i class="fas fa-redo mr-1"></i> Retry
            </button>
        </div>
    `).show();
}

function renderClasses() {
    let html = '';
    
    appData.classes.forEach((classData, index) => {
        const classInfo = classData.class;
        const sectionCount = classData.sections.length;
        
        html += `
            <div class="class-card ${index === appData.currentClassIndex ? 'active' : ''}" 
                 onclick="selectClass(${index})">
                <h5 class="mb-1">${escapeHtml(classInfo.class_short_name || classInfo.class_name)}</h5>
                <small>${sectionCount} section${sectionCount > 1 ? 's' : ''}</small>
            </div>
        `;
    });
    
    $('#classesList').html(html);
}

function selectClass(index) {
    appData.currentClassIndex = index;
    
    // Update active state
    $('.class-card').removeClass('active');
    $('.class-card').eq(index).addClass('active');
    
    renderSections();
}

function renderSections() {
    const classData = appData.classes[appData.currentClassIndex];
    if (!classData) return;
    
    const classInfo = classData.class;
    $('#selectedClassInfo').html(`
        <h4>${escapeHtml(classInfo.class_short_name || classInfo.class_name)}</h4>
    `);
    
    let sectionsHtml = '';
    
    classData.sections.forEach(section => {
        const clsSecId = section.cls_sec_id;
        const assignments = appData.assignments[clsSecId] || {};
        const assignedCount = Object.keys(assignments).length;
        const totalSubjects = appData.subjects.length;
        const progressPercent = totalSubjects > 0 ? (assignedCount / totalSubjects * 100) : 0;
        
        sectionsHtml += `
            <div class="section-card">
                <h5>
                    <i class="fas fa-users mr-2"></i>
                    Section ${escapeHtml(section.section_short_name || section.section_name)}
                    <span class="badge badge-primary float-right">${assignedCount}/${totalSubjects} assigned</span>
                </h5>
                
                <div class="progress-bar-custom">
                    <div class="progress-fill" style="width: ${progressPercent}%"></div>
                </div>
                
                <div class="row mt-3" id="subjects-${clsSecId}">
        `;
        
        // Filter subjects based on search and filter
        let filteredSubjects = appData.subjects;
        
        // Apply search filter
        if (appData.searchTerm) {
            filteredSubjects = filteredSubjects.filter(s => 
                (s.subject_name || '').toLowerCase().includes(appData.searchTerm.toLowerCase()) ||
                (s.subject_short_name || '').toLowerCase().includes(appData.searchTerm.toLowerCase())
            );
        }
        
        filteredSubjects.forEach(subject => {
            const isAssigned = assignments[subject.sid];
            
            // Apply status filter
            if (appData.filterStatus === 'assigned' && !isAssigned) return;
            if (appData.filterStatus === 'unassigned' && isAssigned) return;
            
            sectionsHtml += `
                <div class="col-md-4">
                    <div class="subject-item ${isAssigned ? 'assigned' : ''}">
                        <div class="form-check">
                            <input type="checkbox" 
                                   class="form-check-input subject-checkbox" 
                                   id="sub_${clsSecId}_${subject.sid}"
                                   data-clssec="${clsSecId}"
                                   data-subject="${subject.sid}"
                                   data-record-id="${isAssigned || ''}"
                                   ${isAssigned ? 'checked' : ''}
                                   onchange="toggleSubject(this)">
                            <label class="form-check-label" for="sub_${clsSecId}_${subject.sid}">
                                <strong>${escapeHtml(subject.subject_short_name || subject.subject_name)}</strong>
                                <br>
                                <small class="text-muted">${escapeHtml(subject.subject_name || '')}</small>
                            </label>
                        </div>
                    </div>
                </div>
            `;
        });
        
        if (filteredSubjects.length === 0) {
            sectionsHtml += `
                <div class="col-12">
                    <div class="alert alert-info">No subjects match your filters</div>
                </div>
            `;
        }
        
        sectionsHtml += `</div></div>`;
    });
    
    $('#sectionsContainer').html(sectionsHtml);
}

function toggleSubject(checkbox) {
    const $cb = $(checkbox);
    const clsSecId = $cb.data('clssec');
    const subjectId = $cb.data('subject');
    const recordId = $cb.data('record-id');
    const status = $cb.prop('checked') ? 1 : 0;
    
    $cb.prop('disabled', true);
    
    $.ajax({
        url: "<?= base_url('admin/section_subjects/update') ?>",
        type: "POST",
        data: {
            cls_sec_id: clsSecId,
            subject_id: subjectId,
            record_id: recordId,
            status: status
        },
        dataType: "json",
        success: function(res) {
            if (res.success) {
                $cb.data('record-id', res.record_id);
                
                // Update local data
                if (!appData.assignments[clsSecId]) {
                    appData.assignments[clsSecId] = {};
                }
                
                if (status === 1) {
                    appData.assignments[clsSecId][subjectId] = res.record_id;
                    toastr.success('Subject assigned');
                } else {
                    delete appData.assignments[clsSecId][subjectId];
                    toastr.success('Subject unassigned');
                }
                
                // Update UI
                $cb.closest('.subject-item').toggleClass('assigned', status === 1);
                updateSectionProgress(clsSecId);
            } else {
                $cb.prop('checked', !status);
                toastr.error(res.msg || 'Update failed');
            }
        },
        error: function() {
            $cb.prop('checked', !status);
            toastr.error('Network error');
        },
        complete: function() {
            $cb.prop('disabled', false);
        }
    });
}

function updateSectionProgress(clsSecId) {
    const assignments = appData.assignments[clsSecId] || {};
    const assignedCount = Object.keys(assignments).length;
    const totalSubjects = appData.subjects.length;
    const progressPercent = totalSubjects > 0 ? (assignedCount / totalSubjects * 100) : 0;
    
    $(`#section-${clsSecId} .badge`).text(`${assignedCount}/${totalSubjects} assigned`);
    $(`#section-${clsSecId} .progress-fill`).css('width', progressPercent + '%');
}

// Search and filter handlers
$('#searchSubjects').on('keyup', function() {
    appData.searchTerm = $(this).val();
    if (appData.classes.length > 0) {
        renderSections();
    }
});

$('#filterStatus').on('change', function() {
    appData.filterStatus = $(this).val();
    if (appData.classes.length > 0) {
        renderSections();
    }
});

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>

<?= $this->endSection() ?>