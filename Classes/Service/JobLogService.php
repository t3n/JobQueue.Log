<?php

declare(strict_types=1);

namespace t3n\JobQueue\Log\Service;

use Flowpack\JobQueue\Common\Job\JobInterface;
use Flowpack\JobQueue\Common\Job\JobManager;
use Flowpack\JobQueue\Common\Queue\Message;
use Flowpack\JobQueue\Common\Queue\QueueInterface;
use Neos\Flow\Annotations as Flow;
use ReflectionClass;
use t3n\JobQueue\Log\Job\LogJob;
use t3n\JobQueue\Log\Job\LogJobInterface;

class JobLogService
{
    /**
     * @Flow\Inject
     *
     * @var JobManager
     */
    protected $jobManager;

    public function logFailedJob(QueueInterface $queue, Message $message, ?\Throwable $jobExecutionException = null): void
    {
        /** @var JobInterface $job */
        $job = unserialize($message->getPayload());

        if ($job instanceof LogJobInterface) {
            return;
        }

        $additionalData = [
            'message id' => $message->getIdentifier(),
            'number of releases' => $message->getNumberOfReleases(),
            'exception' => $jobExecutionException->getMessage(),
            'queue' => $queue->getName(),
            'datetime' => (new \DateTime('now'))->format('d.m. H:i')
        ];

        $reflect = new ReflectionClass($job);
        $logJob = new LogJob($reflect->getShortName(), $job->getLabel(), 'Job execution failed (sent via signal)', $additionalData, LogJobInterface::LOG_SEVERITY_WARNING);
        $this->jobManager->queue('log-error', $logJob);
    }

    /**
     * @param mixed[] $options
     */
    public function logSubmittedJob(QueueInterface $queue, string $messageId, string $payload, array $options = []): void
    {
        $job = unserialize($payload);

        if ($job instanceof LogJobInterface) {
            return;
        }

        $additionalData = [
            'message id' => $messageId,
            'queue' => $queue->getName(),
            'datetime' => (new \DateTime('now'))->format('d.m. H:i:s')
        ];

        $reflect = new ReflectionClass($job);
        $logJob = new LogJob($reflect->getShortName(), $job->getLabel(), 'New Job submitted (sent via signal)', $additionalData, LogJobInterface::LOG_SEVERITY_INFO);
        $this->jobManager->queue('log-info', $logJob);
    }

    public function logFinishedJob(QueueInterface $queue, Message $message): void
    {
        /** @var JobInterface $job */
        $job = unserialize($message->getPayload());
        if ($job instanceof LogJobInterface) {
            return;
        }

        $additionalData = [
            'message id' => $message->getIdentifier(),
            'number of releases' => $message->getNumberOfReleases(),
            'queue' => $queue->getName(),
            'datetime' => (new \DateTime('now'))->format('d.m. H:i:s')
        ];

        $reflect = new ReflectionClass($job);
        $logJob = new LogJob($reflect->getShortName(), $job->getLabel(), 'Job execution finished (sent via signal)', $additionalData, LogJobInterface::LOG_SEVERITY_SUCCESS);
        $this->jobManager->queue('log-success', $logJob);
    }
}
