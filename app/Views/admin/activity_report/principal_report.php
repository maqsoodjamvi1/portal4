<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<style>
.review-card {
    margin-bottom: 20px;
    border: 1px solid #e0e0e0;
    border-radius: 10px;
    overflow: hidden;
    transition: all 0.3s;
}
.review-card:hover {
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}
.review-card.pending {
    border-left: 4px solid #ffc107;
}
.review-card.reviewed {
    border-left: 4px solid #28a745;
}
.review-header {
    background: #f8f9fa;
    padding: 15px 20px;
    border-bottom: 1px solid #e0e0e0;
}
.review-body {
    padding: 20px;
}
.rating-input {
    display: flex;
    flex-direction: row-reverse;
    justify-content: flex-end;
    gap: 8px;
}
.rating-input input {
    display: none;
}
.rating-input label {
    font-size: 30px;
    color: #ddd;
    cursor: pointer;
    transition: color 0.2s;
}
.rating-input input:checked ~ label,
.rating-input label:hover,
.rating-input label:hover ~ label {
    color: #ffc107;
}
.filter-section {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 10px;
    margin-bottom: 20px;
}
.status-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
}
.status-pending {
    background: #fff3cd;
    color: #856404;
}
.status-reviewed {
    background: #d4edda;
    color: #155724;
}
.session-info {
    background: #e3f2fd;
    padding: 10px 15px;
    border-radius: 8px;
    margin-bottom: 20px;
    display: inline-block;
}
</style>

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Activity Review Dashboard</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item active">Activity Reviews</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<section class="content">
    <!-- Session Info Banner -->
    <div class="session-info">
        <i class="fa fa-calendar"></i> <strong>Current Session:</strong> <?= esc($session_name) ?>
    </div>
    
    <!-- Filter Section -->
    <div class="filter-section">
        <form method="get" action="<?= base_url('admin/activity-report/principal-report') ?>" class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label>Select Teacher</label>
                    <select name="teacher_id" class="form-control" onchange="this.form.submit()">
                        <option value="0">All Teachers</option>
                        <?php foreach ($teachers as $teacher): ?>
                            <option value="<?= $teacher->id ?>" <?= ($selected_teacher == $teacher->id) ? 'selected' : '' ?>>
                                <?= esc($teacher->first_name) ?> <?= esc($teacher->last_name) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>&nbsp;</label>
                    <div class="form-check">
                        <input type="checkbox" name="unrated_only" class="form-check-input" id="unrated_only" 
                               value="1" <?= $show_unrated_only ? 'checked' : '' ?> onchange="this.form.submit()">
                        <label class="form-check-label" for="unrated_only">
                            Show unrated activities only
                        </label>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>&nbsp;</label>
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fa fa-filter"></i> Apply Filters
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3><?= count($activities) ?></h3>
                    <p>Total Activities</p>
                </div>
                <div class="icon"><i class="fa fa-tasks"></i></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3><?= count(array_filter($activities, function($a) { return is_null($a->rating); })) ?></h3>
                    <p>Pending Review</p>
                </div>
                <div class="icon"><i class="fa fa-clock-o"></i></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3><?= count(array_filter($activities, function($a) { return !is_null($a->rating); })) ?></h3>
                    <p>Reviewed</p>
                </div>
                <div class="icon"><i class="fa fa-check-circle"></i></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-secondary">
                <div class="inner">
                    <?php 
                    $rated = array_filter($activities, function($a) { return !is_null($a->rating); });
                    $avgRating = count($rated) > 0 ? array_sum(array_column($rated, 'rating')) / count($rated) : 0;
                    ?>
                    <h3><?= number_format($avgRating, 1) ?> ★</h3>
                    <p>Average Rating</p>
                </div>
                <div class="icon"><i class="fa fa-star"></i></div>
            </div>
        </div>
    </div>

    <!-- Activities List for Review -->
    <?php if (empty($activities)): ?>
        <div class="alert alert-info text-center">
            <i class="fa fa-info-circle fa-2x"></i>
            <p>No activities found for the selected filters.</p>
        </div>
    <?php else: ?>
        <?php foreach ($activities as $activity): ?>
            <?php 
            $isReviewed = !is_null($activity->rating);
            $cardClass = $isReviewed ? 'reviewed' : 'pending';
            ?>
            <div class="review-card <?= $cardClass ?>" id="activity-<?= $activity->did ?>">
                <div class="review-header">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <strong>
                                <i class="fa fa-calendar"></i> <?= date('l, d M Y', strtotime($activity->date)) ?>
                            </strong>
                            <div class="text-muted small mt-1">
                                <i class="fa fa-user"></i> Teacher: <?= esc($activity->first_name) ?> <?= esc($activity->last_name) ?><br>
                                <i class="fa fa-graduation-cap"></i> Class: <?= esc($activity->class_name) ?> - <?= esc($activity->section_name) ?>
                                &nbsp;|&nbsp;
                                <i class="fa fa-book"></i> Subject: <?= esc($activity->subject_name) ?>
                            </div>
                        </div>
                        <div>
                            <?php if ($isReviewed): ?>
                                <span class="status-badge status-reviewed">
                                    <i class="fa fa-check-circle"></i> Reviewed (<?= $activity->rating ?>★)
                                </span>
                            <?php else: ?>
                                <span class="status-badge status-pending">
                                    <i class="fa fa-clock-o"></i> Pending Review
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="review-body">
                    <?php foreach ($activity->activities_list as $act): ?>
                        <div class="mb-3">
                            <h6>
                                <?= esc($act['name']) ?>
                                <small class="text-muted">
                                    (<?= ucfirst(str_replace('-', ' ', $act['type'])) ?>)
                                </small>
                            </h6>
                            <?php if (!empty($act['description'])): ?>
                                <p class="small text-muted"><?= nl2br(esc($act['description'])) ?></p>
                            <?php endif; ?>
                            <?php if (!empty($act['learning_objective'])): ?>
                                <div class="small"><strong>Objective:</strong> <?= esc($act['learning_objective']) ?></div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                    
                    <!-- Review Form (only show if not reviewed) -->
                    <?php if (!$isReviewed): ?>
                        <div class="review-form mt-3" id="review-form-<?= $activity->did ?>">
                            <h6><i class="fa fa-star text-warning"></i> Submit Review</h6>
                            <input type="hidden" name="did" value="<?= $activity->did ?>">
                            <input type="hidden" name="activity_id" value="<?= $activity->activities_list[0]['activity_id'] ?? '' ?>">
                            
                            <div class="form-group">
                                <label>Rating <span class="text-danger">*</span></label>
                                <div class="rating-input">
                                    <input type="radio" name="rating_<?= $activity->did ?>" value="5" id="star5_<?= $activity->did ?>">
                                    <label for="star5_<?= $activity->did ?>"><i class="fa fa-star"></i></label>
                                    <input type="radio" name="rating_<?= $activity->did ?>" value="4" id="star4_<?= $activity->did ?>">
                                    <label for="star4_<?= $activity->did ?>"><i class="fa fa-star"></i></label>
                                    <input type="radio" name="rating_<?= $activity->did ?>" value="3" id="star3_<?= $activity->did ?>">
                                    <label for="star3_<?= $activity->did ?>"><i class="fa fa-star"></i></label>
                                    <input type="radio" name="rating_<?= $activity->did ?>" value="2" id="star2_<?= $activity->did ?>">
                                    <label for="star2_<?= $activity->did ?>"><i class="fa fa-star"></i></label>
                                    <input type="radio" name="rating_<?= $activity->did ?>" value="1" id="star1_<?= $activity->did ?>">
                                    <label for="star1_<?= $activity->did ?>"><i class="fa fa-star"></i></label>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label>Feedback / Comments <span class="text-danger">*</span></label>
                                <textarea class="form-control feedback-text" rows="3" 
                                          placeholder="Provide your feedback on this activity..."></textarea>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Strengths Observed</label>
                                        <textarea class="form-control strengths-text" rows="2" 
                                                  placeholder="What went well?"></textarea>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Areas for Improvement</label>
                                        <textarea class="form-control areas-text" rows="2" 
                                                  placeholder="What could be improved?"></textarea>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Recommendations</label>
                                        <textarea class="form-control recommendations-text" rows="2" 
                                                  placeholder="Suggestions for future"></textarea>
                                    </div>
                                </div>
                            </div>
                            
                            <button type="button" class="btn btn-success btn-submit-review" 
                                    data-did="<?= $activity->did ?>"
                                    data-activity-id="<?= $activity->activities_list[0]['activity_id'] ?? '' ?>">
                                <i class="fa fa-save"></i> Submit Review
                            </button>
                        </div>
                    <?php else: ?>
                        <!-- Display existing review -->
                        <div class="review-box mt-3">
                            <div class="d-flex justify-content-between">
                                <strong><i class="fa fa-star text-warning"></i> Review Details</strong>
                                <div>
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fa fa-star <?= ($i <= $activity->rating) ? 'text-warning' : 'text-muted' ?>"></i>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <p class="mt-2"><?= nl2br(esc($activity->feedback)) ?></p>
                            <?php if ($activity->strengths): ?>
                                <div class="small text-success"><strong>Strengths:</strong> <?= esc($activity->strengths) ?></div>
                            <?php endif; ?>
                            <?php if ($activity->areas_for_improvement): ?>
                                <div class="small text-warning"><strong>Areas to Improve:</strong> <?= esc($activity->areas_for_improvement) ?></div>
                            <?php endif; ?>
                            <?php if ($activity->recommendations): ?>
                                <div class="small text-info"><strong>Recommendations:</strong> <?= esc($activity->recommendations) ?></div>
                            <?php endif; ?>
                            <div class="small text-muted mt-2">
                                Reviewed on: <?= date('d M Y', strtotime($activity->review_date)) ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</section>

<script>
const URL_SUBMIT_REVIEW = "<?= base_url('admin/activity-report/submit-review') ?>";
const CSRF_NAME = "<?= csrf_token() ?>";
let CSRF_HASH = "<?= csrf_hash() ?>";

function addCsrf(payload) {
    if (CSRF_NAME && CSRF_HASH) payload[CSRF_NAME] = CSRF_HASH;
    return payload;
}

function refreshCsrfFromXHR(xhr) {
    const t = xhr && (xhr.getResponseHeader('X-CSRF-TOKEN') || xhr.getResponseHeader('X-CSRF-Token'));
    if (t) { CSRF_HASH = t; }
}

$(document).ready(function() {
    $('.btn-submit-review').click(function() {
        const did = $(this).data('did');
        const activityId = $(this).data('activity-id');
        const $card = $('#review-form-' + did);
        
        const rating = $card.find('input[name="rating_' + did + '"]:checked').val();
        const feedback = $card.find('.feedback-text').val();
        const strengths = $card.find('.strengths-text').val();
        const areas = $card.find('.areas-text').val();
        const recommendations = $card.find('.recommendations-text').val();
        
        if (!rating) {
            toastr.error('Please select a rating');
            return;
        }
        if (!feedback.trim()) {
            toastr.error('Please provide feedback comments');
            return;
        }
        
        const payload = addCsrf({
            did: did,
            activity_id: activityId,
            rating: rating,
            feedback: feedback,
            strengths: strengths,
            areas_for_improvement: areas,
            recommendations: recommendations
        });
        
        $.ajax({
            url: URL_SUBMIT_REVIEW,
            type: 'POST',
            data: payload,
            dataType: 'json',
            success: function(res, status, xhr) {
                refreshCsrfFromXHR(xhr);
                if (res.success) {
                    toastr.success('Review submitted successfully');
                    location.reload();
                } else {
                    toastr.error(res.msg || 'Failed to submit review');
                }
            },
            error: function() {
                toastr.error('Error submitting review');
            }
        });
    });
});
</script>

<?= $this->endSection() ?>