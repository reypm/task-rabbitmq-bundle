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
    public $recipe;
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
            ->setTo($job->getRecipe())
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

$task = $taskManager->createTask()
    ->setName('Delivering Monthly Reports')
    ->addJobData(new SendEmailJob('john@gmail.com', 'path/to/build/report.pdf'))
    ->addJobData(new SendEmailJob('jane@gmail.com', 'path/to/build/statements.docx'))
    //...
;

$taskManager->updateTask($task);

// this will send all jobs to RabbitMq server and the worker starts to execute each job.
$assigner->assign($task, SendEmailWorker::class);
```

## License ##

This software is published under the [MIT License](LICENSE)
