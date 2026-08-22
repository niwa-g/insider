<?php

namespace App\Domains\Crm\Models;

use App\Domains\Crm\Enums\ClientStatus;
use App\Models\User;
use App\Support\Concerns\BelongsToOrganization;
use Database\Factories\Crm\ClientFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use BelongsToOrganization;

    /** @use HasFactory<ClientFactory> */
    use HasFactory;

    use HasUlids;
    use SoftDeletes;

    protected $fillable = [
        'organization_id',
        'company_name',
        'trading_name',
        'client_code',
        'client_type',
        'industry',
        'website',
        'email',
        'phone',
        'address',
        'country',
        'billing_information',
        'tax_information',
        'status',
        'account_manager_id',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => ClientStatus::class,
        ];
    }

    protected static function newFactory(): ClientFactory
    {
        return ClientFactory::new();
    }

    public function accountManager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'account_manager_id');
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class);
    }

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(ClientGroup::class, 'client_group_client')->withTimestamps();
    }

    public function services(): HasMany
    {
        return $this->hasMany(\App\Domains\Catalog\Models\ClientService::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(\App\Domains\Delivery\Models\Project::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(\App\Domains\Assets\Models\Application::class);
    }

    public function domains(): HasMany
    {
        return $this->hasMany(\App\Domains\Assets\Models\Domain::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(\App\Domains\Operations\Models\Ticket::class);
    }

    public function hostingAccounts(): HasMany
    {
        return $this->hasMany(\App\Domains\Assets\Models\HostingAccount::class);
    }

    public function servers(): HasMany
    {
        return $this->hasMany(\App\Domains\Assets\Models\Server::class);
    }

    public function cloudResources(): HasMany
    {
        return $this->hasMany(\App\Domains\Assets\Models\CloudResource::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(\App\Domains\Operations\Models\Document::class);
    }
}
