<div class="row">
    <div class="col-md-6">
        <strong><i class="fas fa-id-card mr-1"></i> CNIC</strong>
        <p class="text-muted"><?= esc($user->cnic) ?></p>
        
        <strong><i class="fas fa-user mr-1"></i> Father's Name</strong>
        <p class="text-muted"><?= esc($user->f_name) ?></p>
        
        <strong><i class="fas fa-calendar mr-1"></i> Date of Birth</strong>
        <p class="text-muted"><?= esc($user->dob) ?></p>
        
        <strong><i class="fas fa-venus-mars mr-1"></i> Gender</strong>
        <p class="text-muted"><?= ucfirst(esc($user->gender)) ?></p>
    </div>
    
    <div class="col-md-6">
        <strong><i class="fas fa-calendar-check mr-1"></i> Joining Date</strong>
        <p class="text-muted"><?= esc($user->joining_date) ?></p>
        
        <strong><i class="fas fa-graduation-cap mr-1"></i> Qualification</strong>
        <p class="text-muted"><?= esc($user->qualification) ?></p>
        
        <strong><i class="fas fa-briefcase mr-1"></i> Experience</strong>
        <p class="text-muted"><?= esc($user->experience) ?></p>
        
        <strong><i class="fas fa-map-marker mr-1"></i> Address</strong>
        <p class="text-muted"><?= esc($user->address) ?></p>
    </div>
</div>

<div class="row mt-3">
    <div class="col-12">
        <h5 class="text-primary">Bank Details</h5>
        <hr>
        <div class="row">
            <div class="col-md-3">
                <strong>Bank Name</strong>
                <p><?= esc($user->bank_name) ?></p>
            </div>
            <div class="col-md-3">
                <strong>Account Title</strong>
                <p><?= esc($user->account_title) ?></p>
            </div>
            <div class="col-md-3">
                <strong>Account Number</strong>
                <p><?= esc($user->account_number) ?></p>
            </div>
            <div class="col-md-3">
                <strong>Branch Code</strong>
                <p><?= esc($user->branch_code) ?></p>
            </div>
        </div>
    </div>
</div>