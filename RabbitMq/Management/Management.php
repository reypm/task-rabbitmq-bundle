<?php

/*
 * This file is part of the TaskRabbitMqBundle.
 *
 * (c) Yonel Ceruto <yonelceruto@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yceruto\TaskRabbitMqBundle\RabbitMq\Management;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class Management
{
    private $clientHttp;
    private $vhost;

    /**
     * Management constructor.
     *
     * @param string $url      http://127.0.0.1:15672
     * @param string $user
     * @param string $password
     * @param string $vhost
     */
    public function __construct($url, $user, $password, $vhost)
    {
        $this->clientHttp = new Client(array(
            'base_uri' => $url.'/api/',
            'auth' => array($user, $password),
        ));

        $this->vhost = $vhost;
    }

    /**
     * Get queues stats.
     *
     * @return ManagementQueueResponse[]|bool
     */
    public function getQueues()
    {
        try {
            $response = $this->clientHttp->get(sprintf('queues/%s/', urlencode($this->vhost)));

            $queues = json_decode((string) $response->getBody(), true);
        } catch (RequestException $e) {
            return false;
        }

        $result = false;
        foreach ($queues as $queue) {
            $response = new ManagementQueueResponse();
            $response->name = $queue['name'];
            $response->messages = $queue['messages'];
            $response->consumers = $queue['consumers'];

            $result[] = $response;
        }

        return $result;
    }

    /**
     * Get queue stats.
     *
     * @param string $name the queue name
     *
     * @return ManagementQueueResponse|bool
     */
    public function getQueue($name)
    {
        try {
            $raw = $this->clientHttp->get(sprintf('queues/%s/%s', urlencode($this->vhost), urlencode($name)));
            $queue = json_decode((string) $raw->getBody(), true);

            $response = new ManagementQueueResponse();
            $response->name = $name;
            $response->messages = $queue['messages'];
            $response->consumers = $queue['consumers'];
        } catch (RequestException $e) {
            return false;
        }

        return $response;
    }

    /**
     * Gets the best queue.
     *
     * @return string|bool the shortest queue name if available, false otherwise
     */
    public function getShortestQueueName()
    {
        $queues = $this->getQueues();

        if (empty($queues)) {
            return false;
        }

        if (count($queues) > 1) {
            usort($queues, function (ManagementQueueResponse $q1, ManagementQueueResponse $q2) {
                return $q1->messages === $q2->messages ? 0 : ($q1->messages < $q2->messages ? -1 : 1);
            });
        }

        return $queues[0]->name;
    }

    public function setClientHttp(Client $clientHttp)
    {
        $this->clientHttp = $clientHttp;
    }
}
