## Configuration

```yaml
task_rabbit_mq:
    task_class: AppBundle\Entity\Task
    rabbit_mq:
        url: 'amqp://symfony:rabbit@127.0.0.1:5672/%2fdemo?lazy=1'
```

```php
<?php
// src/AppBundle/Entity/Task.php

namespace AppBundle\Entity;

use Yceruto\TaskRabbitMqBundle\Model\Task as BaseTask;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="task_rabbit_mq")
 */
class Task extends BaseTask
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    public function __construct()
    {
        parent::__construct();
        // your own logic
    }
}
```

## Defines the Worker and register it as service 

```php
<?php

class SendEmailWorker implements WorkerInterface
{
    private $mailer;

    public function __construct(\Swift_Mailer $mailer) 
    {
        $this->mailer = $mailer;
    }

    /**
     * @param array $job
     *
     * @return mixed|void
     */
    public function execute($job)
    {
        $message = (new \Swift_Message('Hello Email'))
            ->setFrom('me@example.com')
            ->setTo($job['email'])
            ->setBody('...', 'text/html')
            ->attach(Swift_Attachment::fromPath($job['attachment']))
        ;
        
        $this->mailer->send($message);
    }
}
```

```yaml
services:
    AppBundle\Worker\SendEmailWorker:
        class: AppBundle\Worker\SendEmailWorker
        arguments: ['@mailer']
        tags:
            - { name: task_rabbitmq.worker }
```

## Creates the Task/Jobs and assign it to the Worker

```php
<?php

$taskManager = $this->get('task_rabbit_mq.manager');
$taskAssigner = $this->get('task_rabbit_mq.assigner');

$task = $taskManager->createTask()
    ->setName('Delivering Monthly Reports')
    ->addJobData(array('email' => 'john@gmail.com', 'attachment' => 'path/to/report.pdf'))
    ->addJobData(array('email' => 'jane@gmail.com', 'attachment' => 'path/to/statements.docx'))
    //...
;

$taskManager->updateTask($task);

// this will send all jobs to RabbitMq server and the worker starts to execute each job.
$taskAssigner->assign($task, SendEmailWorker::class);
```


