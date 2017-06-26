# TaskRabbitMqBundle #

## Installation ##

### For Symfony Framework >= 3.0 ###

Require the bundle and its dependencies with composer:

```bash
$ composer require yceruto/task-rabbitmq-bundle
```

Register the bundle:

```php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        new TaskRabbitMqBundle\TaskRabbitMqBundle(),
    );
}
```

## Usage ##

#### Defines the job data

You can create a class to define the job data, or simply use array.

```php
class SendEmailJob
{
    public $recipie;
    public $attachment;
}
```

#### Defines the Worker and register it as service 

```php
class SendEmailWorker implements WorkerInterface
{
    private $mailer;

    public function __construct(\Swift_Mailer $mailer) 
    {
        $this->mailer = $mailer;
    }

    /**
     * @param SendEmailJob $job
     *
     * @return mixed|void
     */
    public function execute($job)
    {
        $message = (new \Swift_Message('Hello Email'))
            ->setFrom('me@example.com')
            ->setTo($job->getRecipie())
            ->setBody('...', 'text/html')
            ->attach(Swift_Attachment::fromPath($job->getAttachment()))
        ;
        
        $this->mailer->send($message);
    }
}
```

```yaml
services:
    app.worker.send_email:
        class: AppBundle\Job\SendEmailWorker
        arguments: ['@mailer']
        tags:
            - { name: task_rabbitmq.worker }
```

#### Creates the Task/Jobs and assign it to the Worker

```php
$taskManager = $this->get('task_rabbit_mq.manager');
$assigner = $this->get('task_rabbit_mq.assigner');

$task = $taskManager->createTask();
$task->setName('Send Monthly Report');
$task->setDescription('Delivering monthly reports to customers.');

$data[] = new SendEmailJob('john@gmail.com', 'path/to/build/report.pdf');
$data[] = new SendEmailJob('jane@gmail.com', 'path/to/build/statements.docx');
//...
$task->setJobsData($data);

// save task in database
$taskManager->updateTask($task);

// assign the task to the worker
// the producer will publish all job messages to RabbitMQ Server.
$assigner->assign($task, SendEmailWorker::class);
```

## License ##

This software is published under the [MIT License](LICENSE)
