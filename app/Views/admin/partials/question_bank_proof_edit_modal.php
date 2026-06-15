<div class="modal fade" id="qbProofEditModal" tabindex="-1" role="dialog" aria-labelledby="qbProofEditModalLabel" aria-hidden="true">

  <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">

    <form id="qbProofEditForm" enctype="multipart/form-data">

      <?= csrf_field() ?>

      <div class="modal-content">

        <div class="modal-header">

          <h5 class="modal-title" id="qbProofEditModalLabel">Edit question</h5>

          <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>

        </div>

        <div class="modal-body">

          <p class="small text-muted mb-3" id="qbProofEditContext"></p>

          <input type="hidden" name="questions[0][id]" id="qb_pe_id" value="">

          <input type="hidden" name="questions[0][class_id]" id="qb_pe_class_id" value="">

          <input type="hidden" name="questions[0][subject_id]" id="qb_pe_subject_id" value="">

          <input type="hidden" name="questions[0][topic_id]" id="qb_pe_topic_id" value="">

          <input type="hidden" name="questions[0][question_type]" id="qb_pe_question_type" value="mcq">

          <input type="hidden" name="questions[0][difficulty]" id="qb_pe_difficulty" value="normal">

          <input type="hidden" name="questions[0][question_media]" id="qb_pe_question_media" value="text">

          <input type="hidden" name="questions[0][is_drag]" id="qb_pe_is_drag" value="0">

          <input type="hidden" name="questions[0][correct_option]" id="qb_pe_correct_option" value="A">



          <div class="form-group qb-pe-text-wrap">

            <label for="qb_pe_question">Question</label>

            <textarea name="questions[0][question]" id="qb_pe_question" class="form-control" rows="4"></textarea>

          </div>

          <p class="small text-muted qb-pe-image-note d-none mb-3">

            <i class="fas fa-image me-1"></i> This question uses an image on the proof-read page. You can still edit options or answers below.

          </p>



          <div class="qb-pe-block qb-pe-mcq qb-pe-mcq_multi">

            <div class="row">

              <div class="form-group col-md-6"><label for="qb_pe_option_a">Option A</label><input type="text" name="questions[0][option_a]" id="qb_pe_option_a" class="form-control"></div>

              <div class="form-group col-md-6"><label for="qb_pe_option_b">Option B</label><input type="text" name="questions[0][option_b]" id="qb_pe_option_b" class="form-control"></div>

              <div class="form-group col-md-6"><label for="qb_pe_option_c">Option C</label><input type="text" name="questions[0][option_c]" id="qb_pe_option_c" class="form-control"></div>

              <div class="form-group col-md-6"><label for="qb_pe_option_d">Option D</label><input type="text" name="questions[0][option_d]" id="qb_pe_option_d" class="form-control"></div>

            </div>

            <div class="qb-pe-mcq-multi d-none" aria-hidden="true">

              <label class="d-none">Correct options (multiple)</label>

              <input type="checkbox" name="questions[0][correct_multi][]" value="A" class="qb-pe-cm d-none">

              <input type="checkbox" name="questions[0][correct_multi][]" value="B" class="qb-pe-cm d-none">

              <input type="checkbox" name="questions[0][correct_multi][]" value="C" class="qb-pe-cm d-none">

              <input type="checkbox" name="questions[0][correct_multi][]" value="D" class="qb-pe-cm d-none">

            </div>

          </div>



          <div class="form-group qb-pe-block qb-pe-descriptive d-none">

            <label for="qb_pe_answer_descriptive">Answer (model answer)</label>

            <textarea name="questions[0][answer_text]" id="qb_pe_answer_descriptive" class="form-control" rows="4"></textarea>

          </div>



          <div class="form-group qb-pe-block qb-pe-tf d-none">

            <label for="qb_pe_answer_tf">Answer</label>

            <select id="qb_pe_answer_tf" class="form-control form-control-sm" style="max-width:140px;">

              <option value="True">True</option>

              <option value="False">False</option>

            </select>

          </div>



          <div class="form-group qb-pe-block qb-pe-fill qb-pe-short d-none">

            <label for="qb_pe_answer_text">Answer</label>

            <input type="text" id="qb_pe_answer_text" class="form-control">

          </div>



          <div class="qb-pe-block qb-pe-match d-none">

            <label>Match pairs</label>

            <div id="qb_pe_match_pairs"></div>

            <button type="button" class="btn btn-sm btn-outline-secondary mt-2" id="qb_pe_add_pair">+ Add pair</button>

          </div>

        </div>

        <div class="modal-footer justify-content-between">

          <button type="button" class="btn btn-danger" id="qbProofDeleteBtn"><i class="fas fa-trash"></i> Delete</button>

          <div>

            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>

            <button type="submit" class="btn btn-primary" id="qbProofSaveBtn"><i class="fas fa-save"></i> Save</button>

          </div>

        </div>

      </div>

    </form>

  </div>

</div>
