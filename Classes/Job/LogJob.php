<?php

declare(strict_types=1);

namespace t3n\JobQueue\Log\Job;

use Flowpack\JobQueue\Common\Job\JobInterface;
use Flowpack\JobQueue\Common\Queue\Message;
use Flowpack\JobQueue\Common\Queue\QueueInterface;

class LogJob implements LogJobInterface, JobInterface
{
    /**
     * @var string
     */
    protected $subject;

    /**
     * @var string
     */
    protected $label;

    /**
     * @var string;
     */
    protected $messageBody;

    /**
     * @var string[]
     */
    protected $additionalData;

    /**
     * @var string
     */
    protected $severity;

    /**
     * @param string[] $additionalData
     */
    public function __construct(string $subject, string $label, string $messageBody, array $additionalData, string $severity)
    {
        $this->subject = $subject;
        $this->label = $label;
        $this->messageBody = $messageBody;
        $this->additionalData = $additionalData;
        $this->severity = $severity;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getMessageBody(): string
    {
        return $this->messageBody;
    }

    /**
     * @return string[]
     */
    public function getAdditionalData(): array
    {
        return $this->additionalData;
    }

    public function getSeverity(): string
    {
        return $this->severity;
    }

    public function execute(QueueInterface $queue, Message $message): bool
    {
        return true;
    }
}
