<?php

namespace Rosalana\Tracker\Enums;

enum TrackerReportType: string
{
    case ROUTE = 'route';
    case EXCEPTION = 'exception';
    
    case CUSTOM = 'custom';
}
