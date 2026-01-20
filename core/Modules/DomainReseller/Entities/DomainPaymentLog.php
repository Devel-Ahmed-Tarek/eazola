<?php

namespace Modules\DomainReseller\Entities;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Modules\DomainReseller\Http\Enums\StatusEnum;
use Stancl\Tenancy\Contracts\SyncMaster;
use Stancl\Tenancy\Database\Concerns\CentralConnection;
use Stancl\Tenancy\Database\Concerns\ResourceSyncing;

class DomainPaymentLog extends Model implements SyncMaster
{
    use ResourceSyncing, CentralConnection;

    protected $table = "domain_payment_logs";
    protected $fillable = [
        'user_id', 'tenant_id', 'first_name', 'last_name', 'email', 'phone', 'user_details', 'domain', 'ip_address',
        'contact_billing', 'contact_registrant', 'contact_tech','domain_price', 'extra_fee', 'payment_gateway', 'payment_status',
        'status', 'custom_field', 'track', 'period', 'privacy', 'unique_key', 'expire_at', 'purchase_count'
    ];

    protected $dates = ['expire_at'];
    protected $hidden = ['track'];

    public function scopeValid()
    {
        return $this->where(['payment_status' => StatusEnum::ACTIVE, 'status' => StatusEnum::ACTIVE]);
    }

    public function scopeInValid()
    {
        return $this->where(['payment_status' => StatusEnum::ACTIVE, 'status' => StatusEnum::INACTIVE]);
    }

    public function scopeExclude($query, $value = [])
    {
        $columns = array_merge($this->fillable, ['id', 'created_at', 'updated_at']);
        return $query->select(array_diff($columns, (array) $value));
    }

    public function scopeCurrentUser($query)
    {
        $current_tenant = \tenant();
        $current_user_id = $current_tenant->user_id;

        return $query->where(['user_id' => $current_user_id, 'tenant_id' => $current_tenant->id]);
    }

    public function paymentable_tenant()
    {
        return $this->hasOne(Tenant::class, 'id', 'tenant_id');
    }

    public function tenants(): BelongsToMany
    {
        return $this->belongsToMany(Tenant::class);
    }

    public function getTenantModelName(): string
    {
        return 'DomainPaymentLog';
    }

    public function getGlobalIdentifierKeyName(): string
    {
        return 'unique_key';
    }

    public function getGlobalIdentifierKey()
    {
        return $this->getAttribute($this->getGlobalIdentifierKeyName());
    }

    public function getCentralModelName(): string
    {
        return static::class;
    }

    public function getSyncedAttributeNames(): array
    {
        return ['unique_key'];
    }

    public function triggerSyncEvent()
    {
        return null;
    }
}
