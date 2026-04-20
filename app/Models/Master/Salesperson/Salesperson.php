<?php

namespace App\Models\Master\Salesperson;

use App\Models\User;
use App\Traits\Log\LogHistoryTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class Salesperson extends Model
{
    use SoftDeletes, LogHistoryTrait;

    protected $fillable = [
        'row_no', 'name', 'email', 'phone', 'designation',
        'department', 'region', 'commission_rate', 'status'
    ];

    protected static string $cache = 'activeSalesmen:';
    protected static array $cacheColumns = [
        'id', 'row_no', 'name', 'commission_rate','status'
    ];

    protected $table = 'sales_persons';

    public static function activeSalesperson($id = null)
    {
        Cache::forget(self::$cache . cacheName());
        $salesPersons = Cache::rememberForever(static::$cache . cacheName(), function () {
            return DB::table('sales_persons')->where(['status' => 1, 'company_id' => companyId()])
                ->whereNull('deleted_at')->latest('id')
                ->select(static::$cacheColumns)
                ->get();
        });
        return ($id) ? $salesPersons->where('id', $id) : $salesPersons;
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
