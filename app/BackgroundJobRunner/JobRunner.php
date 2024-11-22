namespace App\BackgroundJobRunner;

use Exception;
use Throwable;
use Illuminate\Support\Facades\Log;
use App\BackgroundJobRunner\JobValidator;
use App\BackgroundJobRunner\Exceptions\BackgroundJobException;

class JobRunner
{
    const MAX_RETRIES = 3;
    const RETRY_DELAY_SECONDS = 5;

    /**
     * Execute a background job with error handling and retry mechanism
     *
     * @param string $className
     * @param string $methodName
     * @param array $params
     * @param int $retryAttempts
     * @return bool
     */
    public static function run(
        string $className,
        string $methodName,
        array $params = [],
        int $retryAttempts = self::MAX_RETRIES
    ): bool {
        try {
            // Validate class and method
            JobValidator::validate($className);
            JobValidator::validateMethod($className, $methodName);

            // Log job start
            self::logJobStart($className, $methodName, $params);

            // Create instance and invoke method
            $instance = new $className();
            $result = $instance->$methodName(...$params);

            // Log successful completion
            self::logJobCompletion($className, $methodName);

            return true;
        } catch (BackgroundJobException $e) {
            // Handle validation errors
            self::logJobError($className, $methodName, $e);
            return false;
        } catch (Throwable $e) {
            // Retry mechanism for unexpected errors
            return self::handleJobFailure($className, $methodName, $params, $e, $retryAttempts);
        }
    }

    // ```php
    /**
     * Handle job failure with retry mechanism
     *
     * @param string $className
     * @param string $methodName
     * @param array $params
     * @param Throwable $exception
     * @param int $retriesLeft
     * @return bool
     */
    protected static function handleJobFailure(
        string $className,
        string $methodName,
        array $params,
        Throwable $exception,
        int $retriesLeft
    ): bool {
        if ($retriesLeft > 0) {
            // Log retry attempt
            self::logJobRetry($className, $methodName, $exception, $retriesLeft);

            // Wait before retry
            sleep(self::RETRY_DELAY_SECONDS);

            // Recursive retry
            return self::run($className, $methodName, $params, $retriesLeft - 1);
        }

        // Final failure logging
        self::logJobError($className, $methodName, $exception);
        return false;
    }

    /**
     * Log job start
     *
     * @param string $className
     * @param string $methodName
     * @param array $params
     */
    protected static function logJobStart(string $className, string $methodName, array $params): void
    {
        Log::channel('background_jobs')->info("Job Started: $className::$methodName", [
            'params' => $params,
            'timestamp' => now()
        ]);
    }

    /**
     * Log job completion
     *
     * @param string $className
     * @param string $methodName
     */
    protected static function logJobCompletion(string $className, string $methodName): void
    {
        Log::channel('background_jobs')->info("Job Completed: $className::$methodName", [
            'timestamp' => now()
        ]);
    }

    /**
     * Log job retry
     *
     * @param string $className
     * @param string $methodName
     * @param Throwable $exception
     * @param int $retriesLeft
     */
    protected static function logJobRetry(
        string $className,
        string $methodName,
        Throwable $exception,
        int $retriesLeft
    ): void {
        Log::channel('background_jobs_errors')->warning("Job Retry: $className::$methodName", [
            'error' => $exception->getMessage(),
            'retries_left' => $retriesLeft,
            'timestamp' => now()
        ]);
    }

    /**
     * Log job error
     *
     * @param string $className
     * @param string $methodName
     * @param Throwable $exception
     */
    protected static function logJobError(string $className, string $methodName, Throwable $exception): void
    {
        Log::channel('background_jobs_errors')->error("Job Failed: $className::$methodName", [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
            'timestamp' => now()
        ]);
    }
}
