<?php if (empty($subjectGroups)) : ?>

  <div class="alert alert-info mb-0">No quizzes are published yet for your class and board. Check back soon.</div>

<?php else : ?>

  <div class="bp-subject-accordion" id="bpSubjectAccordion">

    <?php foreach ($subjectGroups as $i => $group) : ?>

      <?php

        $collapseId = 'bpSubject' . esc($group['subject_key'], 'attr');

        $expanded   = $i === 0;

      ?>

      <div class="bp-subject-card card border-0 shadow-sm mb-2">

        <button

          class="bp-subject-card__toggle btn btn-link w-100 text-start d-flex align-items-center justify-content-between <?= $expanded ? '' : 'collapsed' ?>"

          type="button"

          data-bs-toggle="collapse"

          data-bs-target="#<?= $collapseId ?>"

          aria-expanded="<?= $expanded ? 'true' : 'false' ?>"

          aria-controls="<?= $collapseId ?>"

        >

          <span>

            <i class="fas fa-book-open text-muted me-2"></i>

            <strong><?= esc($group['subject_name']) ?></strong>

            <span class="badge text-bg-light border ms-2"><?= (int) $group['quiz_count'] ?> quiz<?= (int) $group['quiz_count'] === 1 ? '' : 'zes' ?></span>

          </span>

          <i class="fas fa-chevron-down bp-subject-card__chevron"></i>

        </button>

        <div id="<?= $collapseId ?>" class="collapse <?= $expanded ? 'show' : '' ?>" data-bs-parent="#bpSubjectAccordion">

          <div class="card-body pt-0">

            <?php foreach ($group['quizzes'] as $quiz) : ?>

              <div class="quiz-list-item d-flex flex-wrap justify-content-between align-items-center">

                <div class="mb-2 mb-md-0 pe-2">

                  <strong><?= esc($quiz->title) ?></strong>

                  <?php if (! empty($quiz->board_name)) : ?>

                    <span class="badge text-bg-light border ms-1"><?= esc($quiz->board_name) ?></span>

                  <?php endif; ?>

                  <div class="small text-muted mt-1">

                    <?= (int) ($quiz->questions_count ?? 0) ?> questions

                    <?php if ((int) ($quiz->time_limit_sec ?? 0) > 0) : ?>

                      · <?= (int) ceil($quiz->time_limit_sec / 60) ?> min limit

                    <?php endif; ?>

                    <?php if (isset($quiz->attempts_used) && (int) $quiz->attempts_used > 0) : ?>

                      · <?= (int) $quiz->attempts_used ?> attempt<?= (int) $quiz->attempts_used === 1 ? '' : 's' ?>

                    <?php endif; ?>

                    <?php if (isset($quiz->best_percent) && $quiz->best_percent !== null) : ?>

                      · Best: <?= round((float) $quiz->best_percent, 1) ?>%

                    <?php endif; ?>

                  </div>

                </div>

                <div>

                  <a href="<?= board_prep_url('quizzes/start/' . (int) $quiz->quiz_id) ?>" class="btn btn-bp-primary btn-sm">

                    <?= (int) ($quiz->attempts_used ?? 0) > 0 ? 'Attempt again' : 'Start quiz' ?>

                  </a>

                </div>

              </div>

            <?php endforeach; ?>

          </div>

        </div>

      </div>

    <?php endforeach; ?>

  </div>

<?php endif; ?>
