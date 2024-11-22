namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\BackgroundJobRunner\JobRunner;

class BackgroundJobCommand extends Command
{
    protected $signature = 'background:job {className} {methodName} {params}';
    protected $description = 'Run a background job';

    public function handle()
    {
        $className = $this->argument('className');
        $methodName = $this->argument('methodName');
        $params = json_decode($this->argument('params'), true) ?? [];

        JobRunner::run($className, $methodName, $params);
    }
}
