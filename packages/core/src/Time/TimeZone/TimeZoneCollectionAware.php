<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Time\TimeZone;

interface TimeZoneCollectionAware
{
    public function getTimeZones(): TimeZoneCollection;
}
