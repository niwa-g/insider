<?php

namespace App\Domains\Crm\Models;

use App\Domains\Crm\Enums\ContactStatus;
use App\Support\Concerns\BelongsToClient;
use App\Support\Concerns\BelongsToOrganization;
use Database\Factories\Crm\ContactFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contact extends Model
{
    use BelongsToClient;
    use BelongsToOrganization;

    /** @use HasFactory<ContactFactory> */
    use HasFactory;

    use HasUlids;
    use SoftDeletes;

    protected $fillable = [
        'organization_id',
        'client_id',
        'name',
        'position',
        'email',
        'phone',
        'department',
        'is_primary',
        'is_billing',
        'is_technical',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'is_billing' => 'boolean',
            'is_technical' => 'boolean',
            'status' => ContactStatus::class,
        ];
    }

    protected static function newFactory(): ContactFactory
    {
        return ContactFactory::new();
    }

    protected static function booted(): void
    {
        static::creating(function (Contact $contact): void {
            if (! $contact->organization_id && $contact->client_id) {
                $contact->organization_id = Client::query()->find($contact->client_id)?->organization_id;
            }
        });
    }
}
