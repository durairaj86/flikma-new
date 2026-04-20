<?php

namespace App\Livewire\Finance\Accounts;

use App\Models\Finance\Account\Account;
use Livewire\Component;

class AccountsTable extends Component
{
    public $activeTab = 'asset';
    public $accounts;

    protected $types = [
        'asset' => 'Asset',
        'liability' => 'Liability',
        'equity' => 'Equity',
        'income' => 'Income',
        'expense' => 'Expense',
    ];

    public function mount()
    {
        $this->loadAccounts();
    }

    public function switchTab($tab)
    {
        $this->activeTab = $tab;
        $this->loadAccounts();
    }

    private function loadAccounts()
    {
        /*$this->accounts = Account::where('type', $this->types[$this->activeTab])
            ->orderBy('parent_id')
            ->orderBy('name')
            ->get();*/
        $this->accounts = Account::with('parent')
            ->orderBy('parent_id')
            ->orderBy('name')->get();
    }

    public function render()
    {
        return view('livewire.finance.accounts.accounts-table', [
            'types' => $this->types,
        ]);
    }

    public function renderAccounts($parentId = null, $level = 0)
    {
        $this->accounts = Account::with('parent')
            ->orderBy('parent_id')
            ->orderBy('name')->get();
        $items = $this->accounts->where('parent_id', $parentId);
        $html = '';

        foreach ($items as $account) {
            $indent = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $level);
            $badge = $account->is_active ? 'bg-success' : 'bg-secondary';

            $html .= '<tr>
                <td>' . $indent . ($account->is_grouped ? '<strong>' . e($account->name) . '</strong>' : e($account->name)) . '</td>
                <td>' . e($account->code ?? '-') . '</td>
                <td>' . e($account->account_number ?? '-') . '</td>
                <td><span class="badge ' . $badge . '">' . ($account->is_active ? 'Active' : 'Inactive') . '</span></td>
                <td class="text-center">
                    <button class="btn btn-sm btn-light border" wire:click="edit(' . $account->id . ')">
                        <i class="bi bi-pencil"></i>
                    </button>
                </td>
            </tr>';

            $html .= $this->renderAccounts($account->id, $level + 1);
        }

        return $html;
    }
}
