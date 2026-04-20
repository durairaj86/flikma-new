<?php

namespace App\Livewire\Finance\OpeningBalance;

use App\Models\Finance\Account\Account;
use App\Models\Customer\Customer;
use App\Models\Supplier\Supplier;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class OpeningBalanceForm extends Component
{
    public $accounts = [];
    public $customers = [];
    public $suppliers = [];
    public $date;
    public $description;
    public $entries = [];
    public $totalDebit = 0;
    public $totalCredit = 0;
    public $entryType = 'all'; // Default to all entry types

    // Account IDs for AR and AP
    protected $arAccountId = 5; // Accounts Receivable
    protected $apAccountId = 18; // Accounts Payable

    protected $rules = [
        'date' => 'required|date',
        'description' => 'required|string|max:255',
        'entries' => 'required|array|min:1',
        'entries.*.account_id' => 'nullable|exists:accounts,id',
        'entries.*.customer_id' => 'nullable|exists:customers,id',
        'entries.*.supplier_id' => 'nullable|exists:suppliers,id',
        'entries.*.debit' => 'nullable|numeric|min:0',
        'entries.*.credit' => 'nullable|numeric|min:0',
        'entries.*.entry_type' => 'required|in:account,customer,supplier',
    ];

    protected $messages = [
        'entries.required' => 'At least one entry is required.',
        'entries.*.account_id.exists' => 'Selected account does not exist.',
        'entries.*.customer_id.exists' => 'Selected customer does not exist.',
        'entries.*.supplier_id.exists' => 'Selected supplier does not exist.',
        'entries.*.debit.numeric' => 'Debit amount must be a number.',
        'entries.*.credit.numeric' => 'Credit amount must be a number.',
        'entries.*.entry_type.required' => 'Entry type is required.',
        'entries.*.entry_type.in' => 'Entry type must be account, customer, or supplier.',
    ];

    public function mount($entryType = 'all')
    {
        $this->entryType = $entryType;
        $this->date = date('Y-m-d');
        $this->description = 'Opening Balance Entry';
        $this->addEntry();
    }

    public function addEntry()
    {
        // Set default entry type based on the tab
        $defaultEntryType = 'account';
        if ($this->entryType === 'customer') {
            $defaultEntryType = 'customer';
        } elseif ($this->entryType === 'supplier') {
            $defaultEntryType = 'supplier';
        }

        $this->entries[] = [
            'entry_type' => $defaultEntryType,
            'account_id' => '',
            'customer_id' => '',
            'supplier_id' => '',
            'debit' => 0,
            'credit' => 0,
        ];
    }

    public function changeEntryType($index, $type)
    {
        $this->entries[$index]['entry_type'] = $type;
        $this->entries[$index]['account_id'] = '';
        $this->entries[$index]['customer_id'] = '';
        $this->entries[$index]['supplier_id'] = '';
    }

    public function removeEntry($index)
    {
        if (count($this->entries) > 1) {
            unset($this->entries[$index]);
            $this->entries = array_values($this->entries);
        }
        $this->calculateTotals();
    }

    public function updated($name)
    {
        // Custom validation for entry fields based on entry type
        if (preg_match('/^entries\.(\d+)\.entry_type$/', $name, $matches)) {
            $index = $matches[1];
            $this->validateEntryFields($index);
        } elseif (preg_match('/^entries\.(\d+)\.(account_id|customer_id|supplier_id)$/', $name, $matches)) {
            $index = $matches[1];
            $this->validateEntryFields($index);
        } else {
            $this->validateOnly($name);
        }

        $this->calculateTotals();
    }

    /**
     * Validate entry fields based on entry type
     *
     * @param int $index
     * @return void
     */
    protected function validateEntryFields($index)
    {
        $entryType = $this->entries[$index]['entry_type'] ?? 'account';

        $rules = [
            "entries.{$index}.entry_type" => 'required|in:account,customer,supplier',
            "entries.{$index}.debit" => 'nullable|numeric|min:0',
            "entries.{$index}.credit" => 'nullable|numeric|min:0',
        ];

        // Add validation rule based on entry type
        switch ($entryType) {
            case 'account':
                $rules["entries.{$index}.account_id"] = 'required|exists:accounts,id';
                break;
            case 'customer':
                $rules["entries.{$index}.customer_id"] = 'required|exists:customers,id';
                break;
            case 'supplier':
                $rules["entries.{$index}.supplier_id"] = 'required|exists:suppliers,id';
                break;
        }

        $this->validate($rules);
    }

    public function calculateTotals()
    {
        $this->totalDebit = 0;
        $this->totalCredit = 0;

        foreach ($this->entries as $entry) {
            $this->totalDebit += floatval($entry['debit'] ?? 0);
            $this->totalCredit += floatval($entry['credit'] ?? 0);
        }
    }

    public function save()
    {
        // Validate all entries
        foreach (array_keys($this->entries) as $index) {
            $this->validateEntryFields($index);
        }

        // Validate other fields
        $this->validate([
            'date' => 'required|date',
            'description' => 'required|string|max:255',
            'entries' => 'required|array|min:1',
        ]);

        $this->calculateTotals();

        // Check if debits and credits are balanced
        if (abs($this->totalDebit - $this->totalCredit) > 0.001) {
            $this->addError('balance', 'Debits and credits must be equal.');
            return;
        }

        try {
            DB::beginTransaction();

            // Create journal voucher entry for opening balance
            $voucherNo = 'OB-' . date('YmdHis');
            $journalVoucher = new \App\Models\Finance\JournalVoucher\JournalVoucher();
            $journalVoucher->voucher_no = $voucherNo;
            $journalVoucher->voucher_type = 'OB'; // Opening Balance
            $journalVoucher->voucher_date = $this->date;
            $journalVoucher->description = $this->description;
            $journalVoucher->debit_total = $this->totalDebit;
            $journalVoucher->credit_total = $this->totalCredit;
            $journalVoucher->base_debit_total = $this->totalDebit;
            $journalVoucher->base_credit_total = $this->totalCredit;
            $journalVoucher->status = 1;//1 Approved
            $journalVoucher->currency = 'SAR';//1 Approved
            $journalVoucher->created_by = auth()->id();
            $journalVoucher->company_id = companyId();
            $journalVoucher->save();

            // Create journal voucher items
            foreach ($this->entries as $entry) {
                if (($entry['debit'] ?? 0) > 0 || ($entry['credit'] ?? 0) > 0) {
                    $accountId = $this->getAccountIdForEntry($entry);

                    $journalVoucherItem = new \App\Models\Finance\JournalVoucher\JournalVoucherItem();
                    $journalVoucherItem->journal_voucher_id = $journalVoucher->id;
                    $journalVoucherItem->account_id = $accountId;
                    $journalVoucherItem->debit_amount = $entry['debit'] ?? 0;
                    $journalVoucherItem->credit_amount = $entry['credit'] ?? 0;
                    $journalVoucherItem->base_debit_amount = $entry['debit'] ?? 0;
                    $journalVoucherItem->base_credit_amount = $entry['credit'] ?? 0;
                    $journalVoucherItem->description = $this->description;
                    $journalVoucherItem->company_id = companyId();

                    // Set entity type and entity id if applicable
                    if ($entry['entry_type'] === 'customer' && !empty($entry['customer_id'])) {
                        $journalVoucherItem->entity_type = 'customer';
                        $journalVoucherItem->entity_id = $entry['customer_id'];
                    } elseif ($entry['entry_type'] === 'supplier' && !empty($entry['supplier_id'])) {
                        $journalVoucherItem->entity_type = 'supplier';
                        $journalVoucherItem->entity_id = $entry['supplier_id'];
                    }

                    $journalVoucherItem->save();
                }
            }

            // Create finance entry
            $finance = new \App\Models\Finance\Finance();
            $finance->voucher_type = 'OB'; // Opening Balance
            $finance->voucher_no = $voucherNo;
            $finance->posted_at = $this->date;
            $finance->reference_date = $this->date; // Set reference_date to match posted_at
            $finance->description = $this->description;
            $finance->reference_no = $voucherNo;
            $finance->total_debit = $this->totalDebit;
            $finance->total_credit = $this->totalCredit;
            $finance->base_total_debit = $this->totalDebit;
            $finance->base_total_credit = $this->totalCredit;
            $finance->linked_id = $journalVoucher->id;
            $finance->linked_type = 'journal_voucher';
            $finance->is_approved = 1; // Mark as approved
            $finance->company_id = companyId();
            $finance->user_id = auth()->id();
            $finance->save();

            // Create finance sub entries
            foreach ($this->entries as $entry) {
                if (($entry['debit'] ?? 0) > 0 || ($entry['credit'] ?? 0) > 0) {
                    $accountId = $this->getAccountIdForEntry($entry);

                    $financeSub = new \App\Models\Finance\FinanceSub();
                    $financeSub->finance_id = $finance->id;
                    $financeSub->voucher_type = 'OB'; // Opening Balance
                    $financeSub->voucher_no = $voucherNo;
                    $financeSub->account_id = $accountId;
                    $financeSub->debit = $entry['debit'] ?? 0;
                    $financeSub->credit = $entry['credit'] ?? 0;
                    $financeSub->base_debit = $entry['debit'] ?? 0;
                    $financeSub->base_credit = $entry['credit'] ?? 0;
                    $financeSub->description = $this->description;
                    $financeSub->company_id = companyId();
                    $financeSub->user_id = auth()->id();

                    // Add customer_id or supplier_id if applicable
                    if ($entry['entry_type'] === 'customer' && !empty($entry['customer_id'])) {
                        $financeSub->customer_id = $entry['customer_id'];
                    } elseif ($entry['entry_type'] === 'supplier' && !empty($entry['supplier_id'])) {
                        $financeSub->supplier_id = $entry['supplier_id'];
                    }

                    $financeSub->save();
                }
            }

            DB::commit();

            session()->flash('success', 'Opening balance has been saved successfully.');
            $this->reset(['entries', 'description']);
            $this->date = date('Y-m-d');
            $this->addEntry();
            $this->calculateTotals();

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error saving opening balance: ' . $e->getMessage());
        }
    }

    /**
     * Get the account ID for an entry based on its type
     *
     * @param array $entry
     * @return int
     */
    private function getAccountIdForEntry($entry)
    {
        switch ($entry['entry_type']) {
            case 'customer':
                return $this->arAccountId; // Accounts Receivable
            case 'supplier':
                return $this->apAccountId; // Accounts Payable
            case 'account':
            default:
                return $entry['account_id'];
        }
    }

    public function render()
    {
        $this->accounts = Account::orderBy('name')->get();
        $this->customers = Customer::orderBy('name_en')->get();
        $this->suppliers = Supplier::orderBy('name_en')->get();

        // Filter entries based on the tab if not on 'all' tab
        if ($this->entryType !== 'all') {
            foreach ($this->entries as $key => $entry) {
                if ($entry['entry_type'] !== $this->entryType) {
                    $this->changeEntryType($key, $this->entryType);
                }
            }
        }

        return view('livewire.finance.opening-balance.opening-balance-form');
    }
}
