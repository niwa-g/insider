<?php

namespace App\Domains\Assets\Policies;

use App\Domains\Assets\Models\SslCertificate;
use App\Models\User;

class SslCertificatePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('domains.view');
    }

    public function view(User $user, SslCertificate $model): bool
    {
        return $user->can('domains.view')
            && $user->organization_id === $model->organization_id;
    }

    public function create(User $user): bool
    {
        return $user->can('domains.create');
    }

    public function update(User $user, SslCertificate $model): bool
    {
        return $user->can('domains.update')
            && $user->organization_id === $model->organization_id;
    }

    public function delete(User $user, SslCertificate $model): bool
    {
        return $user->can('domains.delete')
            && $user->organization_id === $model->organization_id;
    }
}
