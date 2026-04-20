/**
 * Opening Balance Module
 */
const MODULE = 'opening-balance';

const OPENING_BALANCE = {
    init() {
        OPENING_BALANCE.form.load();
        OPENING_BALANCE.list.load();
    },
    form: {
        load() {
            // Initialize any form-specific functionality here
            OPENING_BALANCE.form.initTomSelect();
            OPENING_BALANCE.form.initTabHandling();
        },
        initTomSelect() {
            // Initialize TomSelect for all dropdowns
            document.addEventListener('livewire:initialized', () => {
                this.initializeSelects();
            });

            // Re-initialize TomSelect when new rows are added or entry type changes
            document.addEventListener('livewire:update', () => {
                setTimeout(() => {
                    this.initializeSelects();
                }, 100);
            });
        },

        initTabHandling() {
            // Handle tab changes to ensure TomSelect is initialized in newly visible tabs
            const tabs = document.querySelectorAll('button[data-bs-toggle="tab"]');
            tabs.forEach(tab => {
                tab.addEventListener('shown.bs.tab', (event) => {
                    // Re-initialize selects when a tab is shown
                    setTimeout(() => {
                        this.initializeSelects();
                    }, 100);
                });
            });
        },

        initializeSelects() {
            // Initialize account selects
            const accountSelects = document.querySelectorAll('select[wire\\:model*="entries"][wire\\:model*="account_id"]:not(.tomselected)');
            if (accountSelects.length > 0) {
                accountSelects.forEach(select => {
                    new TomSelect(select, {
                        plugins: ['dropdown_input'],
                        placeholder: 'Select Account',
                        allowEmptyOption: true,
                        create: false
                    });
                });
            }

            // Initialize customer selects
            const customerSelects = document.querySelectorAll('select[wire\\:model*="entries"][wire\\:model*="customer_id"]:not(.tomselected)');
            if (customerSelects.length > 0) {
                customerSelects.forEach(select => {
                    new TomSelect(select, {
                        plugins: ['dropdown_input'],
                        placeholder: 'Select Customer',
                        allowEmptyOption: true,
                        create: false
                    });
                });
            }

            // Initialize supplier selects
            const supplierSelects = document.querySelectorAll('select[wire\\:model*="entries"][wire\\:model*="supplier_id"]:not(.tomselected)');
            if (supplierSelects.length > 0) {
                supplierSelects.forEach(select => {
                    new TomSelect(select, {
                        plugins: ['dropdown_input'],
                        placeholder: 'Select Supplier',
                        allowEmptyOption: true,
                        create: false
                    });
                });
            }

            // Initialize entry type selects
            const entryTypeSelects = document.querySelectorAll('select[wire\\:model*="entries"][wire\\:model*="entry_type"]:not(.tomselected)');
            if (entryTypeSelects.length > 0) {
                entryTypeSelects.forEach(select => {
                    new TomSelect(select, {
                        plugins: ['dropdown_input'],
                        placeholder: 'Select Type',
                        allowEmptyOption: false,
                        create: false
                    });
                });
            }
        }
    },

    list: {
        load() {
            // Initialize DataTable for existing entries if available
            const entriesTable = document.querySelector('#list-tab-pane table');
            if (entriesTable && typeof $.fn.DataTable !== 'undefined') {
                $(entriesTable).DataTable({
                    responsive: true,
                    ordering: true,
                    searching: true,
                    paging: true,
                    lengthChange: false,
                    pageLength: 10,
                    language: {
                        search: "Search:",
                        zeroRecords: "No matching records found",
                        info: "Showing _START_ to _END_ of _TOTAL_ entries",
                        infoEmpty: "Showing 0 to 0 of 0 entries",
                        infoFiltered: "(filtered from _MAX_ total entries)"
                    }
                });
            }

            // Handle delete button clicks
            document.addEventListener('click', function(e) {
                if (e.target && e.target.matches('.delete-entry-btn')) {
                    const id = e.target.getAttribute('data-id');
                    OPENING_BALANCE.list.deleteEntry(id);
                }
            });
        },

        deleteEntry(id) {
            if (confirm('Are you sure you want to delete this opening balance entry? This action cannot be undone.')) {
                fetch(`/finance/opening-balance/${id}/delete`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        alert(data.message);
                        window.location.reload();
                    } else {
                        alert(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while deleting the opening balance entry.');
                });
            }
        }
    }
};

// Initialize the module when the document is ready
document.addEventListener('DOMContentLoaded', function() {
    OPENING_BALANCE.init();
});
