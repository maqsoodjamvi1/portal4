    <?php if ($isEligibleForPrayer ?? false): ?>
        <div class="namaz-tracker">
            <div class="card border-0 namaz-tracker-card shadow-lg mb-4">
                <div class="namaz-tracker-card__accent" aria-hidden="true"></div>
                <div class="card-body p-0">
                    <div class="namaz-tracker-inner">
                        <div class="namaz-tracker-toolbar">
                            <button type="button" class="btn namaz-tracker-nav" id="prayerPrevWeek" aria-label="<?= esc(lang('ParentPortal.prayer_prev')) ?>">
                                <i class="fa fa-chevron-left" aria-hidden="true"></i>
                                <span class="d-none d-sm-inline"><?= esc(lang('ParentPortal.prayer_prev')) ?></span>
                            </button>
                            <div class="namaz-tracker-week-pill text-center">
                                <span id="prayerWeekLabel" class="namaz-tracker-week-pill__label"><?= esc(lang('ParentPortal.prayer_week_loading')) ?></span>
                            </div>
                            <button type="button" class="btn namaz-tracker-nav" id="prayerNextWeek" aria-label="<?= esc(lang('ParentPortal.prayer_next')) ?>">
                                <span class="d-none d-sm-inline"><?= esc(lang('ParentPortal.prayer_next')) ?></span>
                                <i class="fa fa-chevron-right" aria-hidden="true"></i>
                            </button>
                        </div>

                        <?php if ($isMandatory ?? false): ?>
                            <div class="namaz-tracker-notice namaz-tracker-notice--mandatory" role="alert">
                                <div class="d-flex align-items-start">
                                    <i class="fa fa-exclamation-triangle namaz-tracker-notice__icon" aria-hidden="true"></i>
                                    <div class="namaz-tracker-notice__text">
                                        <div class="namaz-tracker-notice__title"><?= esc(lang('ParentPortal.prayer_notice_mandatory_title')) ?></div>
                                        <div class="namaz-tracker-notice__body"><?= esc(lang('ParentPortal.prayer_notice_mandatory_body')) ?></div>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="namaz-tracker-notice namaz-tracker-notice--encouraged" role="alert">
                                <div class="d-flex align-items-start">
                                    <i class="fa fa-info-circle namaz-tracker-notice__icon" aria-hidden="true"></i>
                                    <div class="namaz-tracker-notice__text">
                                        <div class="namaz-tracker-notice__title"><?= esc(lang('ParentPortal.prayer_notice_encouraged_title')) ?></div>
                                        <div class="namaz-tracker-notice__body"><?= esc(lang('ParentPortal.prayer_notice_encouraged_body')) ?></div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <p class="namaz-tracker-scroll-hint d-md-none small mb-1" role="note"><?= esc(lang('ParentPortal.prayer_table_swipe_hint')) ?></p>
                        <div
                            class="namaz-tracker-table-scroll"
                            role="region"
                            aria-label="<?= esc($isUrdu ? 'ہفتہ وار نماز گرڈ' : 'Weekly prayer grid') ?>"
                            tabindex="0"
                        >
                            <table class="table table-sm table-borderless align-middle mb-0 namaz-tracker-table" id="prayerWeekTable">
                                <thead>
                                    <tr>
                                        <th class="namaz-tracker-th-prayer"><?= $isUrdu ? 'نماز' : 'Prayer' ?></th>
                                        <th class="text-center"><?= esc(lang('ParentPortal.day_abbr_mon')) ?></th>
                                        <th class="text-center"><?= esc(lang('ParentPortal.day_abbr_tue')) ?></th>
                                        <th class="text-center"><?= esc(lang('ParentPortal.day_abbr_wed')) ?></th>
                                        <th class="text-center"><?= esc(lang('ParentPortal.day_abbr_thu')) ?></th>
                                        <th class="text-center"><?= esc(lang('ParentPortal.day_abbr_fri')) ?></th>
                                        <th class="text-center"><?= esc(lang('ParentPortal.day_abbr_sat')) ?></th>
                                        <th class="text-center"><?= esc(lang('ParentPortal.day_abbr_sun')) ?></th>
                                    </tr>
                                </thead>
                                <tbody id="prayerWeekBody">
                                    <tr>
                                        <td colspan="8" class="namaz-tracker-loading text-center py-4"><?= esc(lang('ParentPortal.prayer_grid_loading')) ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="namaz-tracker-actions d-flex flex-wrap justify-content-between align-items-center gap-2">
                            <div class="namaz-tracker-progress-meta small">
                                <span id="prayerCount" class="namaz-tracker-progress-meta__n">0</span>
                                <span class="namaz-tracker-meta-slash">/</span> 35
                            </div>
                            <button type="button" class="btn btn-sm namaz-tracker-btn-today" id="prayerThisWeek">
                                <?= esc(lang('ParentPortal.prayer_this_week')) ?>
                            </button>
                        </div>

                        <div class="namaz-tracker-progress">
                            <div class="namaz-tracker-progress-track">
                                <div class="namaz-tracker-progress-fill" id="prayerProgressBar" style="width: 0%"></div>
                            </div>
                        </div>

                        <div class="namaz-tracker-stats">
                            <div class="namaz-tracker-stat namaz-tracker-stat--week">
                                <div class="namaz-tracker-stat__n" id="weeklyStreak">0</div>
                                <div class="namaz-tracker-stat__l"><?= $isUrdu ? 'اس ہفتے' : 'This week' ?></div>
                            </div>
                            <div class="namaz-tracker-stat namaz-tracker-stat--month">
                                <div class="namaz-tracker-stat__n" id="monthlyStreak">0</div>
                                <div class="namaz-tracker-stat__l"><?= $isUrdu ? 'اس ماہ' : 'This month' ?></div>
                            </div>
                            <div class="namaz-tracker-stat namaz-tracker-stat--total">
                                <div class="namaz-tracker-stat__n" id="totalDays">0</div>
                                <div class="namaz-tracker-stat__l"><?= $isUrdu ? 'کل' : 'Total' ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="card shadow-sm border-0 bg-light mb-4">
            <div class="card-body text-center text-muted py-4 small">
                <?= $isUrdu ? 'نماز ٹریکنگ اس عمر کے لیے دستیاب نہیں۔' : 'Prayer tracking becomes available when the student reaches the school’s starting age.' ?>
            </div>
        </div>
    <?php endif; ?>
