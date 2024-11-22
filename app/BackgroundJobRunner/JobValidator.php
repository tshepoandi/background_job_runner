namespace App\BackgroundJobRunner;

use ReflectionClass;
use ReflectionException;

class JobValidator
{
    // List of approved namespaces that can be executed
    protected static $approvedNamespaces = [
        'App\\Services\\',
        'App\\Jobs\\',
        'App\\BackgroundProcesses\\'
    ];

    /**
     * Validate if a class is allowed to be executed
     *
     * @param string $className
     * @return bool
     * @throws BackgroundJobException
     */
    public static function validate(string $className): bool
    {
        // Check if class exists
        if (!class_exists($className)) {
            throw new BackgroundJobException("Class $className does not exist");
        }

        // Validate against approved namespaces
        $isApproved = false;
        foreach (self::$approvedNamespaces as $namespace) {
            if (str_starts_with($className, $namespace)) {
                $isApproved = true;
                break;
            }
        }

        if (!$isApproved) {
            throw new BackgroundJobException("Execution of $className is not permitted");
        }

        return true;
    }

    /**
     * Validate method exists and is callable
     *
     * @param string $className
     * @param string $methodName
     * @return bool
     * @throws BackgroundJobException
     */
    public static function validateMethod(string $className, string $methodName): bool
    {
        try {
            $reflection = new ReflectionClass($className);

            if (!$reflection->hasMethod($methodName)) {
                throw new BackgroundJobException("Method $methodName does not exist in $className");
            }

            $method = $reflection->getMethod($methodName);

            if (!$method->isPublic()) {
                throw new BackgroundJobException("Method $methodName is not publicly accessible");
            }

            return true;
        } catch (ReflectionException $e) {
            throw new BackgroundJobException("Reflection error: " . $e->getMessage());
        }
    }
}
