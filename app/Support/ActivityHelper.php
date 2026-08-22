<?php

namespace App\Support;

use RuntimeException;
use Spatie\Activitylog\Contracts\Activity as ActivityContract;

class ActivityHelper
{
    /**
     * @param  array<string, mixed>  $properties
     */
    public static function log(string $description, ?object $subject = null, array $properties = []): ActivityContract
    {
        $logger = activity()->withProperties($properties);

        if ($subject !== null) {
            $logger->performedOn($subject);
        }

        $activity = $logger->log($description);

        if ($activity === null) {
            throw new RuntimeException('Failed to write activity log.');
        }

        return $activity;
    }
}
