<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Activitylog\Models\Activity;

class TimelineController extends Controller
{
    public function index(Request $request): Response
    {
        abort_unless($request->user()?->can('timeline.view'), 403);

        $orgId = $request->user()->organization_id;
        $orgUserIds = User::query()->where('organization_id', $orgId)->pluck('id');

        $activities = Activity::query()
            ->with(['causer:id,name', 'subject'])
            ->where(function ($q) use ($orgUserIds) {
                $q->whereIn('causer_id', $orgUserIds);
            })
            ->latest()
            ->paginate(30)
            ->withQueryString();

        return Inertia::render('Operations/Timeline/Index', [
            'activities' => $activities->through(fn (Activity $a) => [
                'id' => $a->id,
                'description' => $a->description,
                'created_at' => $a->created_at?->toIso8601String(),
                'causer' => $a->causer ? ['id' => $a->causer->id, 'name' => $a->causer->name] : null,
                'subject_type' => $a->subject_type ? class_basename($a->subject_type) : null,
                'subject_id' => $a->subject_id,
            ]),
        ]);
    }
}
