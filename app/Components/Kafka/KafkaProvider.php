<?php

namespace App\Components\Kafka;

use App\Components\Kafka\Interfaces\KafkaServiceInterface;
use Illuminate\Support\ServiceProvider;
use RdKafka\Conf;
use RdKafka\KafkaConsumer;
use RdKafka\Producer;

class KafkaProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton('kafka.producer', function () {
            return $this->createProducer();
        });

        $this->app->singleton('kafka.consumer', function () {
            return $this->createConsumer();
        });

        $this->app->singleton(KafkaServiceInterface::class, function ($app) {
            return new KafkaService(
                $app->make('kafka.producer'),
                $app->make('kafka.consumer')
            );
        });
    }

    protected function createProducer(): Producer
    {
        $config = config('kafka');
        $conf = new Conf();

        // List of Kafka brokers (servers) that the producer connects to
        $conf->set('metadata.broker.list', $config['brokers']);

        // Optional client identifier, useful for debugging and metrics
        $conf->set('client.id', $config['client_id']);

        // Timeout for network socket operations (in milliseconds)
        $conf->set('socket.timeout.ms', $config['socket_timeout_ms']);

        return new Producer($conf);
    }

    protected function createConsumer(): KafkaConsumer
    {
        $config = config('kafka');
        $conf = new Conf();

        // List of Kafka brokers (servers)
        $conf->set('metadata.broker.list', $config['brokers']);

        // Consumer group ID — defines which group this consumer belongs to
        $conf->set('group.id', $config['group_id'] ?? 'laravel-group');

        // Determines what to do when there is no stored offset:
        // "earliest" = read from beginning, "latest" = only new messages
        $conf->set('auto.offset.reset', $config['auto_offset_reset']);

        // Automatically commit offsets after message processing
        // Disable if you want to manually manage offsets
        $conf->set('enable.auto.commit', $config['enable_auto_commit']);

        // Session timeout — the maximum time before Kafka considers the consumer dead
        $conf->set('session.timeout.ms', $config['session_timeout_ms']);

        // Timeout for socket operations (in milliseconds)
        $conf->set('socket.timeout.ms', $config['socket_timeout_ms']);

        return new KafkaConsumer($conf);
    }
}

