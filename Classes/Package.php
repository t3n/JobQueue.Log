<?php

declare(strict_types=1);

namespace t3n\JobQueue\Log;

use Flowpack\JobQueue\Common\Job\JobManager;
use Neos\Flow\Configuration\ConfigurationManager;
use Neos\Flow\Core\Bootstrap;
use Neos\Flow\Package\Package as BasePackage;
use t3n\JobQueue\Log\Service\JobLogService;

class Package extends BasePackage
{
    public function boot(Bootstrap $bootstrap): void
    {
        $dispatcher = $bootstrap->getSignalSlotDispatcher();

        $dispatcher->connect(ConfigurationManager::class, 'configurationManagerReady', static function (ConfigurationManager $manager) use ($dispatcher): void {
            $configuration = $manager->getConfiguration(ConfigurationManager::CONFIGURATION_TYPE_SETTINGS, 't3n.JobQueue.Log');

            if ((bool) $configuration['logFailedJobs']) {
                $dispatcher->connect(JobManager::class, 'messageFailed', JobLogService::class, 'logFailedJob');
            }
            if ((bool) $configuration['logSubmittedJobs']) {
                $dispatcher->connect(JobManager::class, 'messageSubmitted', JobLogService::class, 'logSubmittedJob');
            }
            if ((bool) $configuration['logFinishedJobs']) {
                $dispatcher->connect(JobManager::class, 'messageFinished', JobLogService::class, 'logFinishedJob');
            }
        });
    }
}
