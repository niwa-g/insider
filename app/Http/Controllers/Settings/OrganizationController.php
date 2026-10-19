<?php

namespace App\Http\Controllers\Settings;

use App\Domains\Identity\Models\Organization;
use App\Domains\Platform\Services\AuditLogger;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\UpdateOrganizationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class OrganizationController extends Controller
{
    public function edit(Request $request): Response
    {
        abort_unless($request->user()?->can('settings.view'), 403);

        /** @var Organization $organization */
        $organization = Organization::query()->findOrFail($request->user()->organization_id);

        return Inertia::render('Settings/Organization/Edit', [
            'organization' => [
                'id' => $organization->id,
                'name' => $organization->name,
                'legal_name' => $organization->legal_name,
                'slug' => $organization->slug,
                'website' => $organization->website,
                'email' => $organization->email,
                'phone' => $organization->phone,
                'address_line1' => $organization->address_line1,
                'address_line2' => $organization->address_line2,
                'city' => $organization->city,
                'state' => $organization->state,
                'postal_code' => $organization->postal_code,
                'country' => $organization->country,
                'tax_id' => $organization->tax_id,
                'primary_color' => $organization->primary_color,
                'timezone' => $organization->timezone,
                'currency' => $organization->currency,
                'status' => $organization->status,
                'logo_url' => $organization->logo_url,
            ],
            'can' => [
                'update' => $request->user()?->can('settings.view') ?? false,
            ],
        ]);
    }

    public function update(UpdateOrganizationRequest $request, AuditLogger $auditLogger): RedirectResponse
    {
        /** @var Organization $organization */
        $organization = Organization::query()->findOrFail($request->user()->organization_id);
        $data = $request->safe()->except(['logo', 'remove_logo']);
        $old = $organization->only(array_keys($data));

        if ($request->boolean('remove_logo') && $organization->logo_path) {
            Storage::disk('public')->delete($organization->logo_path);
            $data['logo_path'] = null;
        }

        if ($request->hasFile('logo')) {
            if ($organization->logo_path) {
                Storage::disk('public')->delete($organization->logo_path);
            }
            $data['logo_path'] = $request->file('logo')->store(
                "organizations/{$organization->id}",
                'public',
            );
        }

        $organization->update($data);

        $auditLogger->log('organization.updated', $organization, $old, $organization->fresh()->only(array_keys($data)));

        return redirect()
            ->route('settings.organization.edit')
            ->with('success', 'Company profile updated.');
    }
}
