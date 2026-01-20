<?php

namespace Rosalana\Tracker\Enums;

enum TrackerReportType: string
{
    case ROUTE = 'route';
    case EXCEPTION = 'exception';

    case OUTPOST_SEND = 'outpost_send';
    case OUTPOST_RECEIVE = 'outpost_receive';

    case BASECAMP = 'basecamp';

    case CUSTOM = 'custom';
}
