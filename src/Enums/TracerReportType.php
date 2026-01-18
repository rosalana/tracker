<?php

namespace Rosalana\Tracer\Enums;

enum TracerReportType: string
{
    case ROUTE = 'route';
    case EXCEPTION = 'exception';

    case OUTPOST = 'outpost';
    case BASECAMP = 'basecamp';
    
    case CUSTOM = 'custom';
}
