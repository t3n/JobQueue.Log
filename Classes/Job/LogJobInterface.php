<?php

declare(strict_types=1);

namespace t3n\JobQueue\Log\Job;

interface LogJobInterface
{
    public const LOG_SEVERITY_ALL = '*';
    public const LOG_SEVERITY_MESSAGE = 'message';
    public const LOG_SEVERITY_INFO = 'info';
    public const LOG_SEVERITY_WARNING = 'warning';
    public const LOG_SEVERITY_SUCCESS = 'success';

    public function getSubject(): string;

    public function getLabel(): string;

    public function getMessageBody(): string;

    /**
     * @return mixed[]
     */
    public function getAdditionalData(): array;

    public function getSeverity(): string;
}
