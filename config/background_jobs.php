return [
    'max_retries' => 3,
    'retry_delay' => 5, // seconds
    'log_path' => storage_path('logs/background_jobs.log'),
    'error_log_path' => storage_path('logs/background_jobs_errors.log'),
    'approved_namespaces' => [
        'App\\Services\\',
        'App\\Jobs\\ 'App\\BackgroundProcesses\\'
    ]
];
