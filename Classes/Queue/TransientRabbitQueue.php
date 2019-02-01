<?php

declare(strict_types=1);

namespace t3n\JobQueue\Log\Queue;

use Neos\Flow\Utility\Algorithms;
use t3n\JobQueue\RabbitMQ\Queue\RabbitQueue;

class TransientRabbitQueue extends RabbitQueue
{
    /**
     * @var string
     */
    protected $queueName;

    /**
     * @param mixed[] $options
     */
    public function __construct(string $name, array $options = [])
    {
        $this->queueName = $name;

        // Override the default queue options and names so every listener will
        // create it's very own message queue to get all logs
        $name = 't3n-log-' . Algorithms::generateRandomString(8);
        $queueOptions = $options['queueOptions'];
        $queueOptions['declare'] = true;
        $queueOptions['durable'] = false;
        $queueOptions['exclusive'] = true;
        $queueOptions['autoDelete'] = false;

        $options['queueOptions'] = $queueOptions;
        parent::__construct($name, $options);
    }

    public function getTransientName(): string
    {
        return $this->name;
    }

    public function getName(): string
    {
        return $this->queueName;
    }
}
