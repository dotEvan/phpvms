<?php

namespace App\Services;

use App\Contracts\Service;
use App\Repositories\KvpRepository;
use DateTime;
use DateTimeZone;
use Exception;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\PhpExecutableFinder;

class CronService extends Service
{
    private $kvpRepo;

    public function __construct(
        KvpRepository $kvpRepo
    ) {
        $this->kvpRepo = $kvpRepo;
    }

    /**
     * Get the path for running a cron job
     *
     * @return string
     */
    public function getCronPath(): string
    {
        $finder = new PhpExecutableFinder();
        $php_path = $finder->find(false);

        $path = [
            'cd '.base_path(),
            '&&',
            str_replace('-fpm', '', $php_path),
            'artisan schedule:run',
        ];

        return implode(' ', $path);
    }

    /**
     * Show an example cron command that runs every minute
     *
     * @return string
     */
    public function getCronExecString(): string
    {
        return implode(' ', [
            '* * * * *',
            $this->getCronPath(),
            '>> /dev/null 2>&1',
        ]);
    }

    /**
     * Update the last time the cron was run in the kvp repo
     */
    public function updateLastRunTime()
    {
        $dt = new DateTime('now', new DateTimeZone('UTC'));
        $this->kvpRepo->save('cron_last_run', $dt->format(DateTime::ISO8601));
    }

    /**
     * True/false if there's a problem with the cron. Now this is mainly
     * if the cron hasn't run in the last 5 minutes at least
     *
     * @return bool
     */
    public function cronProblemExists(): bool
    {
        $last_run = $this->kvpRepo->get('cron_last_run');
        if (empty($last_run)) {
            return true;
        }

        try {
            $dt = DateTime::createFromFormat(DateTime::ISO8601, $last_run);
            $dt_now = new DateTime('now', new DateTimeZone('UTC'));
        } catch (Exception $e) {
            Log::error('Error checking for cron problem: '.$e->getMessage());
            return true;
        }

        // More than 5 minutes... there's a problem
        $diff = $dt_now->diff($dt);
        return $diff->i > 5;
    }
}
