<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Academic Setup Wizard</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= base_url('/admin') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item active">Academic Setup</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<section class="content">
    <div class="container-fluid">
        <!-- Wizard Steps -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 text-center">
                                <div class="step active" id="step1Indicator">
                                    <i class="fa fa-book fa-2x"></i>
                                    <h5>Step 1: Classes</h5>
                                    <small class="text-muted">Add classes</small>
                                </div>
                            </div>
                            <div class="col-md-3 text-center">
                                <div class="step" id="step2Indicator">
                                    <i class="fa fa-users fa-2x"></i>
                                    <h5>Step 2: Sections</h5>
                                    <small class="text-muted">Add sections</small>
                                </div>
                            </div>
                            <div class="col-md-3 text-center">
                                <div class="step" id="step3Indicator">
                                    <i class="fa fa-tasks fa-2x"></i>
                                    <h5>Step 3: Subjects</h5>
                                    <small class="text-muted">Add subjects</small>
                                </div>
                            </div>
                            <div class="col-md-3 text-center">
                                <div class="step" id="step4Indicator">
                                    <i class="fa fa-link fa-2x"></i>
                                    <h5>Step 4: Assignments</h5>
                                    <small class="text-muted">Link classes & sections</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 1: Classes -->
        <div id="step1" class="step-content">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Add Classes</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-sm btn-primary" id="addClassBtn">
                            <i class="fa fa-plus"></i> Add Class
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="classesTable">
                            <thead>
                                <tr>
                                    <th width="5%">#</th>
                                    <th width="35%">Class Name</th>
                                    <th width="25%">Short Name</th>
                                    <th width="30%">Description</th>
                                    <th width="5%">Action</th>
                                </tr>
                            </thead>
                            <tbody id="classesBody">
                                <tr class="class-row">
                                    <td>1</td>
                                    <td><input type="text" name="class_name[]" class="form-control" placeholder="e.g., Pre-Nursery" required></td>
                                    <td><input type="text" name="class_short[]" class="form-control" placeholder="e.g., PN"></td>
                                    <td><input type="text" name="class_detail[]" class="form-control" placeholder="Optional description"></td>
                                    <td><button type="button" class="btn btn-sm btn-danger remove-row"><i class="fa fa-trash"></i></button></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        <button type="button" class="btn btn-success" id="saveClassesBtn">Save Classes</button>
                        <button type="button" class="btn btn-primary next-step float-right">Next: Sections <i class="fa fa-arrow-right"></i></button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 2: Sections -->
        <div id="step2" class="step-content" style="display:none;">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Add Sections</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-sm btn-primary" id="addSectionBtn">
                            <i class="fa fa-plus"></i> Add Section
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="sectionsTable">
                            <thead>
                                <tr>
                                    <th width="5%">#</th>
                                    <th width="45%">Section Name</th>
                                    <th width="45%">Short Name</th>
                                    <th width="5%">Action</th>
                                </tr>
                            </thead>
                            <tbody id="sectionsBody">
                                <tr class="section-row">
                                    <td>1</td>
                                    <td><input type="text" name="section_name[]" class="form-control" placeholder="e.g., Section A" required></td>
                                    <td><input type="text" name="section_short[]" class="form-control" placeholder="e.g., A"></td>
                                    <td><button type="button" class="btn btn-sm btn-danger remove-row"><i class="fa fa-trash"></i></button></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        <button type="button" class="btn btn-success" id="saveSectionsBtn">Save Sections</button>
                        <button type="button" class="btn btn-primary prev-step float-left"><i class="fa fa-arrow-left"></i> Previous: Classes</button>
                        <button type="button" class="btn btn-primary next-step float-right">Next: Subjects <i class="fa fa-arrow-right"></i></button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 3: Subjects -->
        <div id="step3" class="step-content" style="display:none;">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Add Subjects</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-sm btn-primary" id="addSubjectBtn">
                            <i class="fa fa-plus"></i> Add Subject
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="subjectsTable">
                            <thead>
                                <tr>
                                    <th width="5%">#</th>
                                    <th width="45%">Subject Name</th>
                                    <th width="45%">Short Name</th>
                                    <th width="5%">Action</th>
                                </tr>
                            </thead>
                            <tbody id="subjectsBody">
                                <tr class="subject-row">
                                    <td>1</td>
                                    <td><input type="text" name="subject_name[]" class="form-control" placeholder="e.g., Mathematics" required></td>
                                    <td><input type="text" name="subject_short[]" class="form-control" placeholder="e.g., Math"></td>
                                    <td><button type="button" class="btn btn-sm btn-danger remove-row"><i class="fa fa-trash"></i></button></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        <button type="button" class="btn btn-success" id="saveSubjectsBtn">Save Subjects</button>
                        <button type="button" class="btn btn-primary prev-step float-left"><i class="fa fa-arrow-left"></i> Previous: Sections</button>
                        <button type="button" class="btn btn-primary next-step float-right">Next: Assignments <i class="fa fa-arrow-right"></i></button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 4: Class-Section & Subject Assignments -->
        <div id="step4" class="step-content" style="display:none;">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Assign Classes to Sections</h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fa fa-info-circle"></i> Select which sections belong to each class. A class can have multiple sections.
                    </div>
                    <div id="classSectionsAssignment">
                        <!-- Dynamic content will be loaded here -->
                        <div class="text-center text-muted py-5">
                            <i class="fa fa-spinner fa-spin fa-2x"></i>
                            <p>Loading classes and sections...</p>
                        </div>
                    </div>
                    <div class="mt-3">
                        <button type="button" class="btn btn-success" id="saveClassSectionsBtn">Save Class-Section Assignments</button>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title">Assign Subjects to Sections</h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fa fa-info-circle"></i> Select which subjects are taught in each section.
                    </div>
                    <div id="sectionSubjectsAssignment">
                        <!-- Dynamic content will be loaded here -->
                        <div class="text-center text-muted py-5">
                            <i class="fa fa-spinner fa-spin fa-2x"></i>
                            <p>Loading sections and subjects...</p>
                        </div>
                    </div>
                    <div class="mt-3">
                        <button type="button" class="btn btn-success" id="saveSectionSubjectsBtn">Save Subject Assignments</button>
                        <button type="button" class="btn btn-primary prev-step float-left"><i class="fa fa-arrow-left"></i> Previous: Subjects</button>
                        <button type="button" class="btn btn-success float-right" id="finishSetupBtn">Finish Setup <i class="fa fa-check"></i></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.step {
    cursor: pointer;
    padding: 15px;
    border-radius: 5px;
    transition: all 0.3s;
}
.step.active {
    background: #007bff;
    color: white;
}
.step.active .text-muted {
    color: rgba(255,255,255,0.8) !important;
}
.step:hover:not(.active) {
    background: #f4f4f4;
}
.step-content {
    animation: fadeIn 0.5s;
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>

<script>
$(document).ready(function() {
    let currentStep = 1;
    let classesData = [];
    let sectionsData = [];
    let subjectsData = [];
    
    // Load existing data
    loadExistingData();
    
    function loadExistingData() {
        $.ajax({
            url: '<?= base_url("admin/academic-setup/fetch-classes") ?>',
            method: 'GET',
            dataType: 'json',
            success: function(res) {
                if (res.success && res.data.length > 0) {
                    classesData = res.data;
                    populateClassesTable();
                }
            }
        });
        
        $.ajax({
            url: '<?= base_url("admin/academic-setup/fetch-sections") ?>',
            method: 'GET',
            dataType: 'json',
            success: function(res) {
                if (res.success && res.data.length > 0) {
                    sectionsData = res.data;
                    populateSectionsTable();
                }
            }
        });
        
        $.ajax({
            url: '<?= base_url("admin/academic-setup/fetch-subjects") ?>',
            method: 'GET',
            dataType: 'json',
            success: function(res) {
                if (res.success && res.data.length > 0) {
                    subjectsData = res.data;
                    populateSubjectsTable();
                }
            }
        });
    }
    
    function populateClassesTable() {
        if (classesData.length > 0) {
            $('#classesBody').empty();
            classesData.forEach((cls, index) => {
                addClassRow(cls.class_name, cls.class_short_name, cls.detail, index + 1);
            });
        }
    }
    
    function populateSectionsTable() {
        if (sectionsData.length > 0) {
            $('#sectionsBody').empty();
            sectionsData.forEach((section, index) => {
                addSectionRow(section.section_name, section.short_name, index + 1);
            });
        }
    }
    
    function populateSubjectsTable() {
        if (subjectsData.length > 0) {
            $('#subjectsBody').empty();
            subjectsData.forEach((subject, index) => {
                addSubjectRow(subject.subject_name, subject.subject_short_name, index + 1);
            });
        }
    }
    
    // Add class row
    $('#addClassBtn').click(function() {
        addClassRow('', '', '', $('#classesBody tr').length + 1);
    });
    
    function addClassRow(name, short, detail, count) {
        let row = `
            <tr class="class-row">
                <td>${count}</td>
                <td><input type="text" name="class_name[]" class="form-control" value="${escapeHtml(name)}" placeholder="e.g., Pre-Nursery" required></td>
                <td><input type="text" name="class_short[]" class="form-control" value="${escapeHtml(short)}" placeholder="e.g., PN"></td>
                <td><input type="text" name="class_detail[]" class="form-control" value="${escapeHtml(detail)}" placeholder="Optional description"></td>
                <td><button type="button" class="btn btn-sm btn-danger remove-row"><i class="fa fa-trash"></i></button></td>
            </tr>
        `;
        $('#classesBody').append(row);
        renumberRows('#classesBody');
    }
    
    // Add section row
    $('#addSectionBtn').click(function() {
        addSectionRow('', '', $('#sectionsBody tr').length + 1);
    });
    
    function addSectionRow(name, short, count) {
        let row = `
            <tr class="section-row">
                <td>${count}</td>
                <td><input type="text" name="section_name[]" class="form-control" value="${escapeHtml(name)}" placeholder="e.g., Section A" required></td>
                <td><input type="text" name="section_short[]" class="form-control" value="${escapeHtml(short)}" placeholder="e.g., A"></td>
                <td><button type="button" class="btn btn-sm btn-danger remove-row"><i class="fa fa-trash"></i></button></td>
            </tr>
        `;
        $('#sectionsBody').append(row);
        renumberRows('#sectionsBody');
    }
    
    // Add subject row
    $('#addSubjectBtn').click(function() {
        addSubjectRow('', '', $('#subjectsBody tr').length + 1);
    });
    
    function addSubjectRow(name, short, count) {
        let row = `
            <tr class="subject-row">
                <td>${count}</td>
                <td><input type="text" name="subject_name[]" class="form-control" value="${escapeHtml(name)}" placeholder="e.g., Mathematics" required></td>
                <td><input type="text" name="subject_short[]" class="form-control" value="${escapeHtml(short)}" placeholder="e.g., Math"></td>
                <td><button type="button" class="btn btn-sm btn-danger remove-row"><i class="fa fa-trash"></i></button></td>
            </tr>
        `;
        $('#subjectsBody').append(row);
        renumberRows('#subjectsBody');
    }
    
    // Remove row
    $(document).on('click', '.remove-row', function() {
        $(this).closest('tr').remove();
        renumberRows($(this).closest('tbody'));
    });
    
    function renumberRows(tbody) {
        $(tbody).find('tr').each(function(index) {
            $(this).find('td:first').text(index + 1);
        });
    }
    
    function escapeHtml(str) {
        if (!str) return '';
        return str.replace(/[&<>]/g, function(m) {
            if (m === '&') return '&amp;';
            if (m === '<') return '&lt;';
            if (m === '>') return '&gt;';
            return m;
        });
    }
    
    // Save Classes
    $('#saveClassesBtn').click(function() {
        let classes = [];
        $('#classesBody tr').each(function() {
            let name = $(this).find('input[name="class_name[]"]').val();
            let short = $(this).find('input[name="class_short[]"]').val();
            let detail = $(this).find('input[name="class_detail[]"]').val();
            if (name) {
                classes.push({name: name, short_name: short, detail: detail});
            }
        });
        
        if (classes.length === 0) {
            toastr.error('Please add at least one class');
            return;
        }
        
        $.ajax({
            url: '<?= base_url("admin/academic-setup/save-classes") ?>',
            method: 'POST',
            data: {classes: classes},
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    toastr.success(res.msg);
                    // Reload classes data
                    setTimeout(() => location.reload(), 1500);
                } else {
                    toastr.error(res.msg);
                }
            },
            error: function() {
                toastr.error('Network error');
            }
        });
    });
    
    // Save Sections
    $('#saveSectionsBtn').click(function() {
        let sections = [];
        $('#sectionsBody tr').each(function() {
            let name = $(this).find('input[name="section_name[]"]').val();
            let short = $(this).find('input[name="section_short[]"]').val();
            if (name) {
                sections.push({name: name, short_name: short});
            }
        });
        
        if (sections.length === 0) {
            toastr.error('Please add at least one section');
            return;
        }
        
        $.ajax({
            url: '<?= base_url("admin/academic-setup/save-sections") ?>',
            method: 'POST',
            data: {sections: sections},
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    toastr.success(res.msg);
                    setTimeout(() => location.reload(), 1500);
                } else {
                    toastr.error(res.msg);
                }
            }
        });
    });
    
    // Save Subjects
    $('#saveSubjectsBtn').click(function() {
        let subjects = [];
        $('#subjectsBody tr').each(function() {
            let name = $(this).find('input[name="subject_name[]"]').val();
            let short = $(this).find('input[name="subject_short[]"]').val();
            if (name) {
                subjects.push({name: name, short_name: short});
            }
        });
        
        if (subjects.length === 0) {
            toastr.error('Please add at least one subject');
            return;
        }
        
        $.ajax({
            url: '<?= base_url("admin/academic-setup/save-subjects") ?>',
            method: 'POST',
            data: {subjects: subjects},
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    toastr.success(res.msg);
                    setTimeout(() => location.reload(), 1500);
                } else {
                    toastr.error(res.msg);
                }
            }
        });
    });
    
 // Function to make AJAX calls with better error handling
function makeAjaxCall(url, method, data, successCallback) {
    $.ajax({
        url: url,
        method: method,
        data: data,
        dataType: 'json',
        timeout: 10000,
        success: function(response) {
            if (response && typeof response === 'object') {
                successCallback(response);
            } else {
                console.error('Invalid response format:', response);
                toastr.error('Invalid response from server');
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error - URL:', url);
            console.error('Status:', status);
            console.error('Error:', error);
            console.error('Response Text:', xhr.responseText);
            
            // Check if response is HTML (likely a 404 or PHP error)
            if (xhr.responseText && xhr.responseText.trim().startsWith('<')) {
                toastr.error('Server error: ' + url + ' returned HTML instead of JSON. Check if route exists.');
            } else {
                toastr.error('Network error: ' + error);
            }
        }
    });
}

// Load existing data with error handling
function loadExistingData() {
    makeAjaxCall('<?= base_url("admin/academic-setup/fetch-classes") ?>', 'GET', null, function(res) {
        if (res.success && res.data && res.data.length > 0) {
            classesData = res.data;
            populateClassesTable();
        }
    });
    
    makeAjaxCall('<?= base_url("admin/academic-setup/fetch-sections") ?>', 'GET', null, function(res) {
        if (res.success && res.data && res.data.length > 0) {
            sectionsData = res.data;
            populateSectionsTable();
        }
    });
    
    makeAjaxCall('<?= base_url("admin/academic-setup/fetch-subjects") ?>', 'GET', null, function(res) {
        if (res.success && res.data && res.data.length > 0) {
            subjectsData = res.data;
            populateSubjectsTable();
        }
    });
}

// Load class-sections assignment interface
function loadClassSectionsAssignment() {
    $('#classSectionsAssignment').html('<div class="text-center text-muted py-5"><i class="fa fa-spinner fa-spin fa-2x"></i><p>Loading classes and sections...</p></div>');
    
    let classes = null;
    let sections = null;
    
    // Load classes
    $.ajax({
        url: '<?= base_url("admin/academic-setup/fetch-classes") ?>',
        method: 'GET',
        dataType: 'json',
        timeout: 10000,
        success: function(classesRes) {
            if (classesRes.success && classesRes.data) {
                classes = classesRes.data;
                
                // Load sections
                $.ajax({
                    url: '<?= base_url("admin/academic-setup/fetch-sections") ?>',
                    method: 'GET',
                    dataType: 'json',
                    timeout: 10000,
                    success: function(sectionsRes) {
                        if (sectionsRes.success && sectionsRes.data) {
                            sections = sectionsRes.data;
                            
                            // Load existing assignments
                            $.ajax({
                                url: '<?= base_url("admin/academic-setup/get-class-sections-data") ?>',
                                method: 'GET',
                                dataType: 'json',
                                timeout: 10000,
                                success: function(existingRes) {
                                    renderClassSectionsAssignment(classes, sections, existingRes.data || []);
                                },
                                error: function(xhr, status, error) {
                                    console.error('Error loading existing assignments:', error);
                                    renderClassSectionsAssignment(classes, sections, []);
                                    $('#classSectionsAssignment').append('<div class="alert alert-warning mt-2">Could not load existing assignments, but you can still make new selections.</div>');
                                }
                            });
                        } else {
                            $('#classSectionsAssignment').html('<div class="alert alert-warning">No sections found. Please add sections in Step 2 first.</div>');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error loading sections:', error);
                        $('#classSectionsAssignment').html('<div class="alert alert-danger">Error loading sections. Please make sure sections are added in Step 2.</div>');
                    }
                });
            } else {
                $('#classSectionsAssignment').html('<div class="alert alert-warning">No classes found. Please add classes in Step 1 first.</div>');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading classes:', error);
            $('#classSectionsAssignment').html('<div class="alert alert-danger">Error loading classes. Please make sure classes are added in Step 1.</div>');
        }
    });
}

// Load section-subjects assignment interface
function loadSectionSubjectsAssignment() {
    $('#sectionSubjectsAssignment').html('<div class="text-center text-muted py-5"><i class="fa fa-spinner fa-spin fa-2x"></i><p>Loading sections and subjects...</p></div>');
    
    // Load class sections
    $.ajax({
        url: '<?= base_url("admin/academic-setup/get-class-sections-data") ?>',
        method: 'GET',
        dataType: 'json',
        timeout: 10000,
        success: function(classSectionsRes) {
            if (classSectionsRes.success && classSectionsRes.data && classSectionsRes.data.length > 0) {
                // Load subjects
                $.ajax({
                    url: '<?= base_url("admin/academic-setup/fetch-subjects") ?>',
                    method: 'GET',
                    dataType: 'json',
                    timeout: 10000,
                    success: function(subjectsRes) {
                        if (subjectsRes.success && subjectsRes.data) {
                            // Load existing assignments
                            $.ajax({
                                url: '<?= base_url("admin/academic-setup/get-section-subjects-data") ?>',
                                method: 'GET',
                                dataType: 'json',
                                timeout: 10000,
                                success: function(existingRes) {
                                    renderSectionSubjectsAssignment(classSectionsRes.data, subjectsRes.data, existingRes.data || []);
                                },
                                error: function(xhr, status, error) {
                                    console.error('Error loading existing subject assignments:', error);
                                    renderSectionSubjectsAssignment(classSectionsRes.data, subjectsRes.data, []);
                                    $('#sectionSubjectsAssignment').append('<div class="alert alert-warning mt-2">Could not load existing assignments, but you can still make new selections.</div>');
                                }
                            });
                        } else {
                            $('#sectionSubjectsAssignment').html('<div class="alert alert-warning">No subjects found. Please add subjects in Step 3 first.</div>');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error loading subjects:', error);
                        $('#sectionSubjectsAssignment').html('<div class="alert alert-danger">Error loading subjects. Please make sure subjects are added in Step 3.</div>');
                    }
                });
            } else {
                $('#sectionSubjectsAssignment').html('<div class="alert alert-warning">No class-sections found. Please assign classes to sections first (above).</div>');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading class sections:', error);
            $('#sectionSubjectsAssignment').html('<div class="alert alert-danger">Error loading class sections. Please make sure class-section assignments are saved first.</div>');
        }
    });
}

function renderClassSectionsAssignment(classes, sections, existing) {
    if (!classes || classes.length === 0) {
        $('#classSectionsAssignment').html('<div class="alert alert-warning">No classes available. Please add classes first.</div>');
        return;
    }
    
    if (!sections || sections.length === 0) {
        $('#classSectionsAssignment').html('<div class="alert alert-warning">No sections available. Please add sections first.</div>');
        return;
    }
    
    let html = '<div class="table-responsive"><table class="table table-bordered"><thead><tr><th>Class</th>';
    sections.forEach(section => {
        html += `<th>${escapeHtml(section.section_name)} (${escapeHtml(section.short_name)})</th>`;
    });
    html += '</tr></thead><tbody>';
    
    classes.forEach(cls => {
        html += `<tr><td><strong>${escapeHtml(cls.class_name)}</strong></td>`;
        sections.forEach(section => {
            let isChecked = existing && existing.some(e => e.class_id == cls.class_id && e.section_id == section.section_id);
            html += `<td class="text-center">
                        <input type="checkbox" class="class-section-checkbox" 
                               data-class-id="${cls.class_id}" 
                               data-section-id="${section.section_id}" 
                               ${isChecked ? 'checked' : ''}>
                      </td>`;
        });
        html += '</tr>';
    });
    
    html += '</tbody></table></div>';
    $('#classSectionsAssignment').html(html);
}

function renderSectionSubjectsAssignment(classSections, subjects, existing) {
    if (!classSections || classSections.length === 0) {
        $('#sectionSubjectsAssignment').html('<div class="alert alert-warning">No class-sections available. Please assign classes to sections first.</div>');
        return;
    }
    
    if (!subjects || subjects.length === 0) {
        $('#sectionSubjectsAssignment').html('<div class="alert alert-warning">No subjects available. Please add subjects first.</div>');
        return;
    }
    
    let html = '';
    classSections.forEach(cs => {
        html += `<div class="card mb-3">
                    <div class="card-header bg-light">
                        <strong>${escapeHtml(cs.class_name)} - ${escapeHtml(cs.section_name)}</strong>
                    </div>
                    <div class="card-body">
                        <div class="row">`;
        subjects.forEach(subject => {
            let isChecked = existing && existing.some(e => e.cls_sec_id == cs.cls_sec_id && e.subject_id == subject.sid);
            html += `<div class="col-md-3 mb-2">
                        <label class="checkbox-inline">
                            <input type="checkbox" class="subject-checkbox" 
                                   data-cls-sec-id="${cs.cls_sec_id}" 
                                   data-subject-id="${subject.sid}"
                                   ${isChecked ? 'checked' : ''}>
                            ${escapeHtml(subject.subject_name)} (${escapeHtml(subject.subject_short_name)})
                        </label>
                     </div>`;
        });
        html += `</div></div></div>`;
    });
    $('#sectionSubjectsAssignment').html(html);
}
  // Save class-sections assignments
$('#saveClassSectionsBtn').click(function() {
    let assignments = [];
    $('.class-section-checkbox:checked').each(function() {
        assignments.push({
            class_id: $(this).data('class-id'),
            section_id: $(this).data('section-id')
        });
    });
    
    if (assignments.length === 0) {
        toastr.warning('Please select at least one class-section assignment');
        return;
    }
    
    $('#saveClassSectionsBtn').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');
    
    $.ajax({
        url: '<?= base_url("admin/academic-setup/save-class-sections") ?>',
        method: 'POST',
        data: {assignments: assignments},
        dataType: 'json',
        timeout: 30000,
        success: function(res) {
            if (res.success) {
                toastr.success(res.msg);
                // Reload both assignments after save
                loadClassSectionsAssignment();
                loadSectionSubjectsAssignment();
            } else {
                toastr.error(res.msg || 'Save failed');
            }
            $('#saveClassSectionsBtn').prop('disabled', false).html('Save Class-Section Assignments');
        },
        error: function(xhr, status, error) {
            console.error('Save error:', error);
            console.error('Response:', xhr.responseText);
            toastr.error('Network error. Please check console for details.');
            $('#saveClassSectionsBtn').prop('disabled', false).html('Save Class-Section Assignments');
        }
    });
});

// Save section-subjects assignments
$('#saveSectionSubjectsBtn').click(function() {
    let assignments = [];
    $('.subject-checkbox:checked').each(function() {
        assignments.push({
            cls_sec_id: $(this).data('cls-sec-id'),
            subject_id: $(this).data('subject-id')
        });
    });
    
    $('#saveSectionSubjectsBtn').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');
    
    $.ajax({
        url: '<?= base_url("admin/academic-setup/save-section-subjects") ?>',
        method: 'POST',
        data: {assignments: assignments},
        dataType: 'json',
        timeout: 30000,
        success: function(res) {
            if (res.success) {
                toastr.success(res.msg);
            } else {
                toastr.error(res.msg || 'Save failed');
            }
            $('#saveSectionSubjectsBtn').prop('disabled', false).html('Save Subject Assignments');
        },
        error: function(xhr, status, error) {
            console.error('Save error:', error);
            console.error('Response:', xhr.responseText);
            toastr.error('Network error. Please check console for details.');
            $('#saveSectionSubjectsBtn').prop('disabled', false).html('Save Subject Assignments');
        }
    });
});
    
    // Step navigation
    $('.next-step').click(function() {
        if (currentStep < 4) {
            $(`#step${currentStep}`).hide();
            currentStep++;
            $(`#step${currentStep}`).show();
            updateStepIndicators();
            
            if (currentStep === 4) {
                loadClassSectionsAssignment();
                loadSectionSubjectsAssignment();
            }
        }
    });
    
    $('.prev-step').click(function() {
        if (currentStep > 1) {
            $(`#step${currentStep}`).hide();
            currentStep--;
            $(`#step${currentStep}`).show();
            updateStepIndicators();
        }
    });
    
    function updateStepIndicators() {
        for (let i = 1; i <= 4; i++) {
            if (i === currentStep) {
                $(`#step${i}Indicator`).addClass('active');
            } else {
                $(`#step${i}Indicator`).removeClass('active');
            }
        }
    }
    
    $('.step').click(function() {
        let step = parseInt($(this).attr('id').replace('step', '').replace('Indicator', ''));
        if (step !== currentStep) {
            $(`#step${currentStep}`).hide();
            currentStep = step;
            $(`#step${currentStep}`).show();
            updateStepIndicators();
            
            if (currentStep === 4) {
                loadClassSectionsAssignment();
                loadSectionSubjectsAssignment();
            }
        }
    });
    
   
$('#finishSetupBtn').click(function() {
    var $btn = $(this);
    var originalText = $btn.html();
    
    // Disable button and show loading state
    $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Checking...');
    
    // Get the system_id from PHP variable
    var systemId = <?= $system_id ?? 0 ?>;
    
    // First, check if fee types exist for this system
    $.ajax({
        url: '<?= base_url("admin/academic-setup/check-fee-types") ?>',
        method: 'GET',
        data: {system_id: systemId},
        dataType: 'json',
        timeout: 10000,
        success: function(res) {
            if (res.success) {
                if (res.has_fee_types) {
                    // Fee types exist - redirect to dashboard
                    toastr.success('Academic setup completed successfully!');
                    setTimeout(function() {
                        window.location.href = '<?= base_url("admin/dashboard") ?>';
                    }, 2000);
                } else {
                    // No fee types - redirect to fee type page
                    toastr.info('Please set up fee types before proceeding to dashboard');
                    setTimeout(function() {
                        window.location.href = '<?= base_url("admin/fee_type") ?>';
                    }, 2000);
                }
            } else {
                // Error checking fee types - default to dashboard
                toastr.warning('Unable to check fee types. Redirecting to dashboard.');
                setTimeout(function() {
                    window.location.href = '<?= base_url("admin/dashboard") ?>';
                }, 2000);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error checking fee types:', error);
            toastr.warning('Unable to check fee types. Redirecting to dashboard.');
            setTimeout(function() {
                window.location.href = '<?= base_url("admin/dashboard") ?>';
            }, 2000);
        },
        complete: function() {
            // Reset button (though redirect will happen, this is for safety)
            $btn.prop('disabled', false).html(originalText);
        }
    });
});
   
});
</script>

<?= $this->endSection() ?>