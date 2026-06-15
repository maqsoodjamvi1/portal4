<div class="card shadow-sm mb-4">
    <div class="card-header bg-white">
        <div>
            <h5 class="mb-1"><i class="fa fa-heartbeat me-2 text-danger"></i>
                <?= $isUrdu ? 'صحت اور بی ایم آئی مانیٹر' : 'Health & BMI Monitor' ?>
            </h5>
            <?php if ($bmiData && $bmiData->bmi_updated_date): ?>
                <small class="text-muted">
                    <?= $isUrdu ? 'آخری اپ ڈیٹ:' : 'Last updated:' ?>
                    <?= date('M d, Y', strtotime($bmiData->bmi_updated_date)) ?>
                </small>
            <?php endif; ?>
        </div>
    </div>
    <div class="card-body">
        <?php if ($bmiData && $bmiData->height && $bmiData->weight): ?>
            <!-- 4 Cards in a Row - Height, Weight, BMI, Age -->
            <div class="row g-2 mb-4">
                <!-- Height Card -->
                <div class="col-3">
                    <div class="bmi-card text-center p-2 bg-light rounded">
                        <div class="bmi-card-label"><?= $isUrdu ? 'قد' : 'Height' ?></div>
                        <div class="bmi-card-value"><?= round($bmiData->height) ?></div>
                        <div class="bmi-card-unit"><?= $isUrdu ? 'سینٹی میٹر' : 'cm' ?></div>
                    </div>
                </div>
                <!-- Weight Card -->
                <div class="col-3">
                    <div class="bmi-card text-center p-2 bg-light rounded">
                        <div class="bmi-card-label"><?= $isUrdu ? 'وزن' : 'Weight' ?></div>
                        <div class="bmi-card-value"><?= round($bmiData->weight) ?></div>
                        <div class="bmi-card-unit"><?= $isUrdu ? 'کلوگرام' : 'kg' ?></div>
                    </div>
                </div>

                <div class="col-3">
    <div class="bmi-card text-center p-2 bg-primary text-white rounded">
        <div class="bmi-card-label"><?= $isUrdu ? 'عمر' : 'Age' ?></div>
        <div class="bmi-card-value">
            <?php
            // Calculate age with years and months
            $ageYears = 0;
            $ageMonths = 0;
            $ageDisplay = '0 years';
            
            if (!empty($studentInfo)) {
                $dob = null;
                if (isset($studentInfo->db_status) && $studentInfo->db_status == 1 && !empty($studentInfo->date_of_birth_age)) {
                    $dob = new DateTime($studentInfo->date_of_birth_age);
                } elseif (!empty($studentInfo->date_of_birth)) {
                    $dob = new DateTime($studentInfo->date_of_birth);
                }
                
                if ($dob) {
                    $today = new DateTime();
                    $diff = $dob->diff($today);
                    $ageYears = $diff->y;
                    $ageMonths = $diff->m;
                    
                    if ($ageYears > 0) {
                        $ageDisplay = $ageYears . 'y';
                        if ($ageMonths > 0) {
                            $ageDisplay .= ' ' . $ageMonths . 'm';
                        }
                    } elseif ($ageMonths > 0) {
                        $ageDisplay = $ageMonths . 'm';
                    } else {
                        $ageDisplay = '0y';
                    }
                }
            }
            ?>
            <?= $ageDisplay ?>
        </div>
        <div class="bmi-card-unit"><?= $isUrdu ? 'سال' : 'years' ?></div>
    </div>
</div>
                <!-- BMI Card -->
                <div class="col-3">
                    <div class="bmi-card text-center p-2 rounded text-white <?= 
                        $bmiData->bmi_category == 'normal' ? 'bg-success' : 
                        ($bmiData->bmi_category == 'underweight' ? 'bg-warning text-dark' : 
                        ($bmiData->bmi_category == 'overweight' ? 'bg-info' : 'bg-danger')) 
                    ?>">
                        <div class="bmi-card-label"><?= $isUrdu ? 'بی ایم آئی' : 'BMI' ?></div>
                        <div class="bmi-card-value"><?= round($bmiData->bmi) ?></div>
                        <div class="bmi-card-unit">
                            <?php
                            if ($isUrdu) {
                                $categoryUrdu = [
                                    'underweight' => 'کم وزن',
                                    'normal' => 'معمول',
                                    'overweight' => 'زیادہ وزن',
                                    'obese' => 'موٹاپا'
                                ];
                                echo $categoryUrdu[$bmiData->bmi_category] ?? $bmiData->bmi_category;
                            } else {
                                echo ucfirst($bmiData->bmi_category ?? 'Normal');
                            }
                            ?>
                        </div>
                    </div>
                </div>
                <!-- Age Card -->
              <!-- Age Card -->

            </div>

            <div id="bmiHealthDetails" class="mt-3" role="region"
                 aria-label="<?= $isUrdu ? 'صحت اور بی ایم آئی تفصیلات' : 'Health and BMI details' ?>">

            <!-- BMI Scale Indicator -->
            <div class="mt-2 mb-3">
                <div class="progress" style="height: 6px;">
                    <div class="progress-bar bg-warning" style="width: 18.5%;"></div>
                    <div class="progress-bar bg-success" style="width: 6%;"></div>
                    <div class="progress-bar bg-info" style="width: 10%;"></div>
                    <div class="progress-bar bg-danger" style="width: 10%;"></div>
                </div>
                <div class="d-flex justify-content-between small mt-1 px-1">
                    <span class="text-warning" style="font-size: 9px;">&lt;18.5</span>
                    <span class="text-success" style="font-size: 9px;">18.5-24.9</span>
                    <span class="text-info" style="font-size: 9px;">25-29.9</span>
                    <span class="text-danger" style="font-size: 9px;">≥30</span>
                </div>
                <div class="mt-1 text-center">
                    <i class="fa fa-arrow-down text-primary"></i>
                    <small class="text-primary"><?= round($bmiData->bmi) ?></small>
                </div>
            </div>
            
            <!-- Health Recommendations -->
            <div class="mt-3">
                <?php if ($bmiData->bmi_category == 'underweight'): ?>
                    <div class="alert alert-warning py-2 mb-2">
                        <i class="fa fa-exclamation-triangle"></i>
                        <strong><?= $isUrdu ? 'کم وزن' : 'Underweight' ?></strong>
                        <p class="mb-0 small"><?= $isUrdu ? 'وزن کم ہے۔ صحت مند غذا کا استعمال بڑھائیں۔' : 'Weight is low. Increase healthy food intake.' ?></p>
                    </div>
                    <div class="border rounded p-2 bg-light mb-2">
                        <div class="text-success fw-bold small mb-1"><i class="fa fa-apple"></i> <?= $isUrdu ? 'غذائی تجاویز' : 'Diet Tips' ?></div>
                        <p class="mb-0 small"><?= $isUrdu ? 'پروٹین اور صحت مند چکنائی والی غذائیں شامل کریں۔ دودھ، انڈے، دالیں، گری دار میوے کھائیں۔' : 'Add protein and healthy fats. Eat milk, eggs, lentils, nuts.' ?></p>
                    </div>
                    <div class="border rounded p-2 bg-light">
                        <div class="text-primary fw-bold small mb-1"><i class="fa fa-futbol"></i> <?= $isUrdu ? 'ورزش کی تجاویز' : 'Exercise Tips' ?></div>
                        <p class="mb-0 small"><?= $isUrdu ? 'وزن بڑھانے کے لیے طاقت کی مشقیں کریں۔' : 'Do strength training to gain weight.' ?></p>
                    </div>
                <?php elseif ($bmiData->bmi_category == 'overweight'): ?>
                    <div class="alert alert-info py-2 mb-2">
                        <i class="fa fa-info-circle"></i>
                        <strong><?= $isUrdu ? 'وزن زیادہ ہے' : 'Overweight' ?></strong>
                        <p class="mb-0 small"><?= $isUrdu ? 'وزن زیادہ ہے۔ متوازن غذا اور باقاعدہ ورزش کریں۔' : 'Weight is high. Balanced diet and regular exercise.' ?></p>
                    </div>
                    <div class="border rounded p-2 bg-light mb-2">
                        <div class="text-success fw-bold small mb-1"><i class="fa fa-apple"></i> <?= $isUrdu ? 'غذائی تجاویز' : 'Diet Tips' ?></div>
                        <p class="mb-0 small"><?= $isUrdu ? 'چکنائی اور میٹھی چیزیں کم کریں۔ سبزیاں، پھل زیادہ کھائیں۔' : 'Reduce fats and sugars. Eat more vegetables and fruits.' ?></p>
                    </div>
                    <div class="border rounded p-2 bg-light">
                        <div class="text-primary fw-bold small mb-1"><i class="fa fa-futbol"></i> <?= $isUrdu ? 'ورزش کی تجاویز' : 'Exercise Tips' ?></div>
                        <p class="mb-0 small"><?= $isUrdu ? 'روزانہ 30-45 منٹ ورزش کریں۔' : 'Exercise 30-45 minutes daily.' ?></p>
                    </div>
                <?php elseif ($bmiData->bmi_category == 'obese'): ?>
                    <div class="alert alert-danger py-2 mb-2">
                        <i class="fa fa-exclamation-circle"></i>
                        <strong><?= $isUrdu ? 'موٹاپا' : 'Obese' ?></strong>
                        <p class="mb-0 small"><?= $isUrdu ? 'موٹاپا ہے۔ ڈاکٹر سے رجوع کریں۔' : 'Obese. Consult a doctor.' ?></p>
                    </div>
                    <div class="border rounded p-2 bg-light mb-2">
                        <div class="text-success fw-bold small mb-1"><i class="fa fa-apple"></i> <?= $isUrdu ? 'غذائی تجاویز' : 'Diet Tips' ?></div>
                        <p class="mb-0 small"><?= $isUrdu ? 'میٹھا، تلی ہوئی چیزیں کم کریں۔ سبزیاں، پھل زیادہ کھائیں۔' : 'Reduce sugar and fried foods. Eat more vegetables and fruits.' ?></p>
                    </div>
                    <div class="border rounded p-2 bg-light">
                        <div class="text-primary fw-bold small mb-1"><i class="fa fa-futbol"></i> <?= $isUrdu ? 'ورزش کی تجاویز' : 'Exercise Tips' ?></div>
                        <p class="mb-0 small"><?= $isUrdu ? 'روزانہ چہل قدمی سے شروع کریں۔' : 'Start with daily walking.' ?></p>
                    </div>
                <?php else: ?>
                    <div class="alert alert-success py-2 mb-2">
                        <i class="fa fa-check-circle"></i>
                        <strong><?= $isUrdu ? 'وزن معمول پر ہے' : 'Normal Weight' ?></strong>
                        <p class="mb-0 small"><?= $isUrdu ? 'وزن معمول پر ہے۔ صحت مند طرز زندگی جاری رکھیں۔' : 'Weight is normal. Maintain healthy lifestyle.' ?></p>
                    </div>
                    <div class="border rounded p-2 bg-light mb-2">
                        <div class="text-success fw-bold small mb-1"><i class="fa fa-apple"></i> <?= $isUrdu ? 'غذائی تجاویز' : 'Diet Tips' ?></div>
                        <p class="mb-0 small"><?= $isUrdu ? 'متوازن غذا کھائیں۔' : 'Eat balanced diet.' ?></p>
                    </div>
                    <div class="border rounded p-2 bg-light">
                        <div class="text-primary fw-bold small mb-1"><i class="fa fa-futbol"></i> <?= $isUrdu ? 'ورزش کی تجاویز' : 'Exercise Tips' ?></div>
                        <p class="mb-0 small"><?= $isUrdu ? 'روزانہ 30 منٹ ورزش کریں۔' : 'Exercise 30 minutes daily.' ?></p>
                    </div>
                <?php endif; ?>
            </div>
            
         <!-- BMI History -->
<?php if (!empty($bmiHistory) && count($bmiHistory) > 0): ?>
<div class="mt-3">
  <h6 class="mb-2 fw-bold" style="font-size: 1.5rem;"><i class="fa fa-line-chart"></i> <?= $isUrdu ? 'بی ایم آئی ہسٹری' : 'BMI History' ?></h6>
    <div class="table-responsive">
        <table class="table table-sm table-bordered">
            <thead class="table-light">
                <tr>
                    <th class="text-center"><?= $isUrdu ? 'مہینہ' : 'Month' ?></th>
                    <th class="text-center"><?= $isUrdu ? 'قد (سینٹی میٹر)' : 'Height (cm)' ?></th>
                    <th class="text-center"><?= $isUrdu ? 'وزن (کلوگرام)' : 'Weight (kg)' ?></th>
                    <th class="text-center"><?= $isUrdu ? 'بی ایم آئی' : 'BMI' ?></th>
                    <th class="text-center"><?= $isUrdu ? 'کیٹیگری' : 'Category' ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($bmiHistory as $history): ?>
                <tr>
                    <td class="text-center small fw-bold"><?= esc($history['month_display']) ?></td>
                    <td class="text-center small"><?= round($history['height']) ?></td>
                    <td class="text-center small"><?= round($history['weight']) ?></td>
                    <td class="text-center small fw-bold"><?= round($history['bmi']) ?></td>
                    <td class="text-center">
                        <span class="badge <?= 
                            $history['bmi_category'] == 'normal' ? 'bg-success' : 
                            ($history['bmi_category'] == 'underweight' ? 'bg-warning' : 
                            ($history['bmi_category'] == 'overweight' ? 'bg-info' : 'bg-danger')) 
                        ?>">
                            <?= ucfirst($history['bmi_category']) ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

            </div><!-- #bmiHealthDetails -->

        <?php else: ?>
            <div class="text-center py-4 text-muted">
                <i class="fa fa-heartbeat fa-3x mb-3 opacity-50"></i>
                <p><?= $isUrdu ? 'اس طالب علم کے لیے کوئی ہیلتھ ریکارڈ موجود نہیں۔' : 'No health records available for this student.' ?></p>
                <p class="small"><?= $isUrdu ? 'براہ کرم قد اور وزن اپ ڈیٹ کرنے کے لیے اسکول سے رابطہ کریں۔' : 'Please contact the school to update height and weight measurements.' ?></p>
            </div>
        <?php endif; ?>
    </div>
</div>
