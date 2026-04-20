<div>
    <!-- Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <table class="table table-sm align-middle mb-0">
                <thead class="table-light">
                <tr>
                    <th>Name</th>
                    <th>Code</th>
                    <th>Account Number</th>
                    <th>Status</th>
                    <th class="text-center" style="width: 60px;">Action</th>
                </tr>
                </thead>
                <tbody>
                {!! app('App\\Livewire\\Finance\\Accounts\\AccountsTable')->renderAccounts() !!}
                </tbody>
            </table>
        </div>
    </div>
</div>
