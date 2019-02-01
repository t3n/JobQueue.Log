[![Build Status](https://travis-ci.com/t3n/JobQueue.Log.svg?branch=master)](https://travis-ci.com/t3n/JobQueue.Log)

# t3n.JobQueue.Log
A sidecar package for [t3n.JobQueue.RabbitMQ](https://github.com/t3n/JobQueue.RabbitMQ). This package will log infos
about submitted, failed and finished jobs to a RabbitMQ backend. This package could also be used to simply log some
custom messages.

## Setup
To use RabbitMQ as a backend for a JobQueue you need a running RabbitMQ Service. You can use our docker image which also
includes the management console [t3n/rabbitmq](https://quay.io/repository/t3n/rabbitmq)

Install the package using composer:

```
composer require t3n/jobqueue-log
```

## Configuration
For a basic setup of RabbitMQ Queues check [t3n.JobQueue.RabbitMQ](https://github.com/t3n/JobQueue.RabbitMQ). This
section will only cover the additional configuration.

There are some preconfigured queues:

```yaml
Flowpack:
  JobQueue:
    Common:
      queues:
        # All queues should be adjusted to your needs / configurations to fit your exchange config
        # We just provide different queues to send messages with different routing keys to an exchange
        # these queues only acts as a producer and dont't need to be declared
        log-info:
          className: 't3n\JobQueue\RabbitMQ\Queue\RabbitQueue'
          options:
            routingKey: 'log.info'  # Adjust to your needs
            queueOptions:
              declare: false
        log-success:
          className: 't3n\JobQueue\RabbitMQ\Queue\RabbitQueue'
          options:
            routingKey: 'log.success'  # Adjust to your needs
            queueOptions:
              declare: false
        log-error:
          className: 't3n\JobQueue\RabbitMQ\Queue\RabbitQueue'
          options:
            routingKey: 'log.error'  # Adjust to your needs
            queueOptions:
              declare: false
        log-message:
          className: 't3n\JobQueue\RabbitMQ\Queue\RabbitQueue'
          options:
            routingKey: 'log.message'  # Adjust to your needs
            queueOptions:
              declare: false
```
Adjust those queues to fit your needs. You probably want to adjust the routing key as well
as the preset / exchange configuration. Those queue names are used internally, so make sure
you got those covered! We won't declare them as they only act as a producer. There is no need
to declare or persist them for now.

Now add a consumer queue like this:
```yaml
Flowpack:
  JobQueue:
    Common:
      queues:
        log-listener:
          className: 't3n\JobQueue\Log\Queue\TransientRabbitQueue'
          options:
            routingKey: 'log.*'
            queueOptions:
              declare: true
              exchangeName: 'your-exchange'
```

This queue uses a TransientRabbitQueue. This is important as we override the default queue options
to make sure it has a unique name so you several consumer can connect and all messages
are forwarded to each consumer. The queue will also only exist as long as a consumer is 
connected (realised with the `exclusive` flag).
This queue now would receive all logs with all severities. If you only need warnings adjust
the routing key to your needs or configure another consumer queue.

We wont log anything by default but is able to log logs for submitted, failed and finished jobs.
You can enable logging in your `Settings.yaml`:

```yaml
t3n:
  JobQueue:
    Log:
      logFailedJobs: false
      logSubmittedJobs: false
      logFinishedJobs: false

```

## Usage

After you configured your producer and consumer queues there is a flow command to output
all logs:
```bash
./flow job:work log:live --severity <string> --verbose <bool> 
```

The severity flag controls whether to log only failed Jobs (`warning`), submitted jobs (`info`)
or finished jobs (`success`). By default all logs (`*`) are outputted.
Setting verbose to true will also display some more meta information.


### Send generic messages
This package is made to have a live overview of your jobqueues. But you can also use it
to send some generic logs. Therefore we added the severity `message`. 

Sending a message to all consumers:
```php

/**
 * @Flow\Inject
 *
 * @var JobManager
 */
protected $jobManager;
    
[...]

$message = new LogJob(string $subject, string $label, string $messageBody, array $additionalData, string $severity);
$this->jobManager->queue('log-message', $message);
```
The queue name, routing keys etc. can be customized to fit your needs!



