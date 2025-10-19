<?php

namespace App\Components\Kafka\Interfaces;

use App\Components\Kafka\Enums\KafkaTopicEnum;
use RdKafka\Exception;
use RdKafka\KafkaConsumer;
use RdKafka\Producer;

interface KafkaServiceInterface
{
    public function publishMessage(KafkaTopicEnum $topic, array $payload, ?string $key = null): void;

    public function publishBatch(KafkaTopicEnum $topic, array $messages): void;

    /**
     * @throws Exception
     */
    public function consume(
        KafkaTopicEnum|array $topics,
        callable $callback,
        int $timeout = 120000,
        ?int $maxMessages = null,
    ): void;

    public function getProducer(): Producer;

    public function getConsumer(): KafkaConsumer;
}
