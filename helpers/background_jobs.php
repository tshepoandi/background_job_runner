use App\BackgroundJobRunner\JobRunner;

if (!function_exists('runBackgroundJob')) {
    /**
     * Run a background job across different operating systems
     *
     * @param string $className
     * @param string $methodName
     * @param array $params
     * @param int $retryAttempts
     * @return bool
     */
    function runBackgroundJob(
        string $className,
        string $methodName,
        array $params = [],
        int $retryAttempts = 3
    ): bool {
        // Determine OS and execute accordingly
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            return runWindowsBackgroundJob($className, $methodName, $params, $retryAttempts);
        } else {
            return runUnixBackgroundJob($className, $methodName, $params, $retryAttempts);
        }
    }

    // Windows and Unix job execution functions remain unchanged
}
