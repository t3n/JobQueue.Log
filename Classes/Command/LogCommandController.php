<?php

declare(strict_types=1);

namespace t3n\JobQueue\Log\Command;

use Flowpack\JobQueue\Common\Job\JobManager;
use Flowpack\JobQueue\Common\Queue\QueueManager;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Cli\CommandController;
use PhpAmqpLib\Exception\AMQPTimeoutException;
use t3n\JobQueue\Log\Job\LogJobInterface;
use t3n\JobQueue\Log\Queue\TransientRabbitQueue;

class LogCommandController extends CommandController
{
    /**
     * @Flow\Inject
     *
     * @var QueueManager
     */
    protected $queueManager;

    /**
     * @Flow\Inject
     *
     * @var JobManager
     */
    protected $jobManager;

    /**
     * Output live logs if job queue jobs
     *
     * @param string $queueName
     * @param string $severity
     * @param bool $verbose
     */
    public function liveCommand(string $queueName, string $severity = LogJobInterface::LOG_SEVERITY_ALL, bool $verbose = true): void
    {
        /** @var TransientRabbitQueue $queue */
        $queue = $this->queueManager->getQueue($queueName);
        $this->outputLine('Start listening for new logs as consumer ' . $queue->getTransientName());
        do {
            try {
                $message = $queue->waitAndTake();
                $job = unserialize($message->getPayload());

                if ($job instanceof LogJobInterface) {
                    if ($severity === LogJobInterface::LOG_SEVERITY_ALL || $job->getSeverity() === $severity) {
                        $this->outputLog($job, $verbose);
                    }
                }

                continue;
            } catch (AMQPTimeoutException $e) {
                continue;
            }
        } while (true);
    }

    protected function outputLog(LogJobInterface $logJob, bool $withAdditionalData): void
    {
        $severity = $logJob->getSeverity();
        $now = new \DateTime('now');

        $label = $now->format('d.m H:i') . ' :: ' . $logJob->getSubject() . ' :: ' . $logJob->getLabel();
        switch ($severity) {
            case LogJobInterface::LOG_SEVERITY_WARNING:
                $this->outputFormatted(sprintf('<bg=red>%s</>', $label));
                break;
            case LogJobInterface::LOG_SEVERITY_INFO:
                $this->outputFormatted(sprintf('<bg=yellow>%s</>', $label));
                break;
            case LogJobInterface::LOG_SEVERITY_MESSAGE:
                $this->outputFormatted(sprintf('<bg=blue>%s</>', $label));
                break;
            case LogJobInterface::LOG_SEVERITY_SUCCESS:
                $this->outputFormatted(sprintf('<bg=green>%s</>', $label));
                break;
            default:
                $this->outputFormatted(sprintf('%s', $label));
                break;
        }

        $this->outputLine($logJob->getMessageBody());

        if ($withAdditionalData) {
            $rows = [];

            foreach ($logJob->getAdditionalData() as $key => $value) {
                $rows[] = [$key, $value];
            }
            $this->output->outputTable($rows, ['Key', 'Value']);
        }
        $this->outputLine();
        $this->outputLine('-------------------------------------------------------------');
        $this->outputLine();
    }
}
