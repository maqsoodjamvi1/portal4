<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?= view('components/page_header', [
    'title' => 'AI Quiz Builder',
    'icon' => 'fas fa-robot',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => base_url('admin/dashboard')],
        ['label' => 'AI Quiz Builder', 'active' => true],
    ],
]) ?>

<section class="content">
    <div class="row">
        <div class="col-md-6">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Prompt to AI</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Topic / Prompt</label>
                        <textarea id="prompt" class="form-control" rows="4" placeholder="Create 5 MCQs for Grade 6 on photosynthesis"></textarea>
                    </div>
                    <div class="form-group">
                        <label>No. of MCQs</label>
                        <input type="number" id="mcq_count" class="form-control" value="5" min="1" max="50">
                    </div>
                    <button id="btnGenerate" class="btn btn-primary">
                        Generate from AI
                    </button>
                    <span id="aiStatus" class="ms-2 text-muted"></span>
                </div>
            </div>
        </div>
    </div>

    <form action="<?= site_url('admin/quiz/save') ?>" method="post" id="quizForm">
        <?= csrf_field() ?>

        <div class="card mt-3">
            <div class="card-header">
                <h3 class="card-title">MCQs</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-bordered mb-0" id="mcqTable">
                    <thead>
                        <tr>
                            <th style="width: 30%">Question</th>
                            <th>Option A</th>
                            <th>Option B</th>
                            <th>Option C</th>
                            <th>Option D</th>
                            <th>Correct</th>
                            <th style="width:50px;">#</th>
                        </tr>
                    </thead>
                    <tbody id="mcqBody">
                        <!-- rows will be appended here by JS -->
                    </tbody>
                </table>
            </div>
            <div class="card-footer text-end">
                <button type="submit" class="btn btn-success">Save Quiz</button>
            </div>
        </div>
    </form>
</section>

<script>
function addMcqRow(item = {}) {
    const tbody = document.getElementById('mcqBody');
    const tr = document.createElement('tr');

    tr.innerHTML = `
        <td>
            <textarea name="question[]" class="form-control" rows="2">${item.question ?? ''}</textarea>
        </td>
        <td><input type="text" name="option_a[]" class="form-control" value="${item.option_a ?? ''}"></td>
        <td><input type="text" name="option_b[]" class="form-control" value="${item.option_b ?? ''}"></td>
        <td><input type="text" name="option_c[]" class="form-control" value="${item.option_c ?? ''}"></td>
        <td><input type="text" name="option_d[]" class="form-control" value="${item.option_d ?? ''}"></td>
        <td>
            <select name="correct_option[]" class="form-control">
                <option value="A" ${(item.correct_option === 'A') ? 'selected' : ''}>A</option>
                <option value="B" ${(item.correct_option === 'B') ? 'selected' : ''}>B</option>
                <option value="C" ${(item.correct_option === 'C') ? 'selected' : ''}>C</option>
                <option value="D" ${(item.correct_option === 'D') ? 'selected' : ''}>D</option>
            </select>
        </td>
        <td>
            <button type="button" class="btn btn-danger btn-sm" onclick="this.closest('tr').remove()">X</button>
        </td>
    `;
    tbody.appendChild(tr);
}

document.getElementById('btnGenerate').addEventListener('click', function () {
    const prompt = document.getElementById('prompt').value.trim();
    const mcqCount = document.getElementById('mcq_count').value;

    if (!prompt) {
        alert('Please enter a prompt');
        return;
    }

    document.getElementById('aiStatus').textContent = 'Generating...';

    fetch('<?= site_url('admin/quiz/generate') ?>', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/x-www-form-urlencoded',
            '<?= csrf_header() ?>': '<?= csrf_hash() ?>'
        },
        body: 'prompt=' + encodeURIComponent(prompt) + '&mcq_count=' + encodeURIComponent(mcqCount)
    })
    .then(r => r.json())
    .then(data => {
        document.getElementById('aiStatus').textContent = '';
        if (data.error) {
            alert(data.error);
            return;
        }

        // clear old rows
        document.getElementById('mcqBody').innerHTML = '';

        (data.mcqs || []).forEach(item => {
            addMcqRow(item);
        });
    })
    .catch(err => {
        document.getElementById('aiStatus').textContent = '';
        alert('Error contacting AI');
    });
});
</script>

<?= $this->endSection() ?>
