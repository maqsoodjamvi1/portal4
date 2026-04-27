@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Edit Quiz: {{ $quiz->title }}</h3>
                    <a href="{{ route('quizzes.manage') }}" class="btn btn-secondary float-right">
                        <i class="fas fa-arrow-left"></i> Back to Manage
                    </a>
                </div>
                
                <form method="POST" action="{{ route('quizzes.update', $quiz->quiz_id) }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="card-body">
                        <div class="row">
                            <!-- Basic Information -->
                            <div class="col-md-6">
                                <h4>Basic Information</h4>
                                
                                <div class="form-group">
                                    <label for="title">Quiz Title *</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="title" 
                                           name="title" 
                                           value="{{ old('title', $quiz->title) }}"
                                           required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="instructions">Instructions</label>
                                    <textarea class="form-control" 
                                              id="instructions" 
                                              name="instructions" 
                                              rows="3">{{ old('instructions', $quiz->instructions) }}</textarea>
                                </div>
                                
                                <div class="form-group">
                                    <label for="time_limit_sec">Time Limit (seconds) *</label>
                                    <input type="number" 
                                           class="form-control" 
                                           id="time_limit_sec" 
                                           name="time_limit_sec" 
                                           value="{{ old('time_limit_sec', $quiz->time_limit_sec) }}"
                                           min="0"
                                           required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="max_attempts">Maximum Attempts *</label>
                                    <input type="number" 
                                           class="form-control" 
                                           id="max_attempts" 
                                           name="max_attempts" 
                                           value="{{ old('max_attempts', $quiz->max_attempts) }}"
                                           min="1"
                                           required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="per_question_marks">Marks per Question *</label>
                                    <input type="number" 
                                           class="form-control" 
                                           id="per_question_marks" 
                                           name="per_question_marks" 
                                           value="{{ old('per_question_marks', $quiz->per_question_marks) }}"
                                           min="1"
                                           required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="negative_mark_per_q">Negative Mark per Question</label>
                                    <input type="number" 
                                           class="form-control" 
                                           id="negative_mark_per_q" 
                                           name="negative_mark_per_q" 
                                           value="{{ old('negative_mark_per_q', $quiz->negative_mark_per_q) }}"
                                           step="0.01"
                                           min="0">
                                </div>
                            </div>
                            
                            <!-- Dates & Settings -->
                            <div class="col-md-6">
                                <h4>Timing & Settings</h4>
                                
                                <div class="form-group">
                                    <label for="start_at">Start Date & Time *</label>
                                    <input type="datetime-local" 
                                           class="form-control" 
                                           id="start_at" 
                                           name="start_at" 
                                           value="{{ old('start_at', $quiz->start_at ? \Carbon\Carbon::parse($quiz->start_at)->format('Y-m-d\TH:i') : '') }}"
                                           required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="end_at">End Date & Time *</label>
                                    <input type="datetime-local" 
                                           class="form-control" 
                                           id="end_at" 
                                           name="end_at" 
                                           value="{{ old('end_at', $quiz->end_at ? \Carbon\Carbon::parse($quiz->end_at)->format('Y-m-d\TH:i') : '') }}"
                                           required>
                                </div>
                                
                                <!-- Toggle Settings -->
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-check mb-3">
                                            <input type="checkbox" 
                                                   class="form-check-input" 
                                                   id="is_published" 
                                                   name="is_published"
                                                   {{ old('is_published', $quiz->is_published) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="is_published">Published</label>
                                        </div>
                                        
                                        <div class="form-check mb-3">
                                            <input type="checkbox" 
                                                   class="form-check-input" 
                                                   id="shuffle_questions" 
                                                   name="shuffle_questions"
                                                   {{ old('shuffle_questions', $quiz->shuffle_questions) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="shuffle_questions">Shuffle Questions</label>
                                        </div>
                                        
                                        <div class="form-check mb-3">
                                            <input type="checkbox" 
                                                   class="form-check-input" 
                                                   id="shuffle_options" 
                                                   name="shuffle_options"
                                                   {{ old('shuffle_options', $quiz->shuffle_options) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="shuffle_options">Shuffle Options</label>
                                        </div>
                                        
                                        <div class="form-check mb-3">
                                            <input type="checkbox" 
                                                   class="form-check-input" 
                                                   id="kids_mode" 
                                                   name="kids_mode"
                                                   {{ old('kids_mode', $quiz->kids_mode) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="kids_mode">Kids Mode</label>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="form-check mb-3">
                                            <input type="checkbox" 
                                                   class="form-check-input" 
                                                   id="show_solution" 
                                                   name="show_solution"
                                                   {{ old('show_solution', $quiz->show_solution) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="show_solution">Show Solution</label>
                                        </div>
                                        
                                        <div class="form-check mb-3">
                                            <input type="checkbox" 
                                                   class="form-check-input" 
                                                   id="wifi_only" 
                                                   name="wifi_only"
                                                   {{ old('wifi_only', $quiz->wifi_only) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="wifi_only">WiFi Only</label>
                                        </div>
                                        
                                        <div class="form-check mb-3">
                                            <input type="checkbox" 
                                                   class="form-check-input" 
                                                   id="is_urdu" 
                                                   name="is_urdu"
                                                   {{ old('is_urdu', $quiz->is_urdu) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="is_urdu">Is Urdu Quiz</label>
                                        </div>
                                        
                                        <div class="form-check mb-3">
                                            <input type="checkbox" 
                                                   class="form-check-input" 
                                                   id="is_public" 
                                                   name="is_public"
                                                   {{ old('is_public', $quiz->is_public) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="is_public">Public Quiz</label>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Adaptive Quiz Settings -->
                                <div class="form-group">
                                    <div class="form-check mb-2">
                                        <input type="checkbox" 
                                               class="form-check-input" 
                                               id="is_adaptive" 
                                               name="is_adaptive"
                                               {{ old('is_adaptive', $quiz->is_adaptive) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_adaptive">Adaptive Quiz</label>
                                    </div>
                                    
                                    <div id="adaptiveSettings" style="{{ !$quiz->is_adaptive ? 'display: none;' : '' }}">
                                        <label for="max_level">Maximum Level</label>
                                        <input type="number" 
                                               class="form-control" 
                                               id="max_level" 
                                               name="max_level" 
                                               value="{{ old('max_level', $quiz->max_level) }}"
                                               min="1">
                                    </div>
                                </div>
                                
                                <!-- Question Order -->
                                <div class="form-group">
                                    <div class="form-check">
                                        <input type="checkbox" 
                                               class="form-check-input" 
                                               id="is_order_by_qtype" 
                                               name="is_order_by_qtype"
                                               {{ old('is_order_by_qtype', $quiz->is_order_by_qtype) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_order_by_qtype">Order by Question Type</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Quiz Stats -->
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <h4>Quiz Statistics</h4>
                                <div class="row">
                                    <div class="col-md-2">
                                        <div class="info-box bg-light">
                                            <span class="info-box-icon"><i class="fas fa-question-circle"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Total Questions</span>
                                                <span class="info-box-number">{{ $quiz->questions_count }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="info-box bg-light">
                                            <span class="info-box-icon"><i class="fas fa-dot-circle"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Single MCQ</span>
                                                <span class="info-box-number">{{ $quiz->count_mcq_single }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="info-box bg-light">
                                            <span class="info-box-icon"><i class="fas fa-check-double"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Multi MCQ</span>
                                                <span class="info-box-number">{{ $quiz->count_mcq_multi }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="info-box bg-light">
                                            <span class="info-box-icon"><i class="fas fa-check"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">True/False</span>
                                                <span class="info-box-number">{{ $quiz->count_tf }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="info-box bg-light">
                                            <span class="info-box-icon"><i class="fas fa-edit"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Fill in Blank</span>
                                                <span class="info-box-number">{{ $quiz->count_fill }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Quiz
                        </button>
                        <a href="{{ route('quizzes.manage') }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Show/hide adaptive settings
    $('#is_adaptive').change(function() {
        if ($(this).is(':checked')) {
            $('#adaptiveSettings').show();
        } else {
            $('#adaptiveSettings').hide();
            $('#max_level').val('');
        }
    });
    
    // Validate end date is after start date
    $('form').submit(function(e) {
        const startAt = new Date($('#start_at').val());
        const endAt = new Date($('#end_at').val());
        
        if (endAt <= startAt) {
            e.preventDefault();
            alert('End date must be after start date.');
            return false;
        }
        
        return true;
    });
</script>
@endpush