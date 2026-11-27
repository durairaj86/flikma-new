<?php

namespace App\Traits;

/**
 * Updates and deletes of a single model (save(), delete(), soft delete) are written by Laravel as "... WHERE id = ?", outside the
 * global company scope. This adds the row's own company to that WHERE, so every write on a company table names its company:
 * the company_id the row was loaded with (NULL for a shared row), or, when the column was not loaded, the current company
 * (or a shared row).
 */
trait CompanyKeyedSaves
{
    protected function setKeysForSaveQuery($query)
    {
        parent::setKeysForSaveQuery($query);
        $column = $this->getTable() . '.company_id';

        if (array_key_exists('company_id', $this->original)) {
            $this->original['company_id'] === null
                ? $query->whereNull($column)
                : $query->where($column, $this->original['company_id']);
        } elseif ($companyId = companyId()) {
            $query->where(fn ($q) => $q->where($column, $companyId)->orWhereNull($column));
        }

        return $query;
    }
}
