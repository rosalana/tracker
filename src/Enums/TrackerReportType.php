<?php

namespace Rosalana\Tracker\Enums;

enum TrackerReportType: string
{
    case ROUTE = 'route';
    case EXCEPTION = 'exception';

    case OUTPOST = 'outpost';
    case BASECAMP = 'basecamp';
    
    case CUSTOM = 'custom';
}
