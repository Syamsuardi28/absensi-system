<?php

return [
    'entry_cutoff' => env('ATTENDANCE_ENTRY_CUTOFF', '07:30'),
    'exit_time' => env('ATTENDANCE_EXIT_TIME', '15:00'),
    'grace_period' => env('ATTENDANCE_GRACE_PERIOD', 15),
];
