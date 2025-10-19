<?php

namespace App\Components\Kafka;

use App\Components\Kafka\Enums\KafkaTopicEnum;
use App\Components\Kafka\Interfaces\KafkaServiceInterface;
use Illuminate\Support\Facades\Log;
use RdKafka\Exception;
use RdKafka\KafkaConsumer;
use RdKafka\Message;
use RdKafka\Producer;

class KafkaService implements KafkaServiceInterface
{
    private Producer $producer;
    private KafkaConsumer $consumer;

    public function __construct(Producer $producer, KafkaConsumer $consumer)
    {
        $this->producer = $producer;
        $this->consumer = $consumer;
    }

    public function publishMessage(KafkaTopicEnum $topic, array $payload, ?string $key = null): void
    {
        $partition = RD_KAFKA_PARTITION_UA;
        $message = json_encode($payload);

        $topicProducer = $this->producer->newTopic($topic->value);
        $topicProducer->produce(partition: $partition, msgflags: 0, payload: $message, key: $key);

        for ($i = 0; $i < 10; $i++) {
            if ($this->producer->flush(1000) == 0) {
                Log::info("Published message to {$topic->value}", ['key' => $key]);
                break;
            }
        }
    }

    public function publishBatch(KafkaTopicEnum $topic, array $messages): void
    {
        $topicProducer = $this->producer->newTopic($topic->value);

        foreach ($messages as $message) {
            $payload = is_array($message['payload']) ? json_encode($message['payload']) : $message['payload'];

            $key = $message['key'] ?? null;

            $topicProducer->produce(partition: RD_KAFKA_PARTITION_UA, msgflags: 0, payload: $payload, key: $key);
        }

        $remaining = $this->producer->flush(30000);
        if ($remaining > 0) {
            Log::warning("Failed to flush {$remaining} messages");
        }
    }

    /**
     * @throws Exception
     */
    public function consume(
        KafkaTopicEnum|array $topics,
        callable $callback,
        int $timeout = 120000,
        ?int $maxMessages = null,
    ): void {
        $topics = is_array($topics) ? $topics : [$topics->value];
        $this->consumer->subscribe($topics);

        $messageCount = 0;

        try {
            while (true) {
                $message = $this->consumer->consume($timeout);

                match ($message->err) {
                    RD_KAFKA_RESP_ERR_NO_ERROR => $this->handleMessage($message, $callback),
                    RD_KAFKA_RESP_ERR__TIMED_OUT => null,
                    RD_KAFKA_RESP_ERR__PARTITION_EOF => null,
                    default => Log::error('Kafka consumer error: '.$message->errstr()),
                };

                if ($maxMessages && ++$messageCount >= $maxMessages) {
                    break;
                }
            }
        } catch (\Exception $e) {
            Log::error('Kafka consumer exception: '.$e->getMessage());
            throw $e;
        } finally {
            $this->consumer->unsubscribe();
        }
    }

    private function handleMessage(Message $message, callable $callback): void
    {
        try {
            $payload = json_decode($message->payload, true) ?? $message->payload;

            $callback([
                'topic' => $message->topic_name, 'partition' => $message->partition, 'offset' => $message->offset,
                'key' => $message->key, 'payload' => $payload, 'timestamp' => $message->timestamp,
            ]);

            Log::debug('Processed message', [
                'topic' => $message->topic_name, 'offset' => $message->offset
            ]);
        } catch (\Exception $e) {
            Log::error('Error processing message: '.$e->getMessage(), [
                'topic' => $message->topic_name, 'offset' => $message->offset
            ]);
            throw $e;
        }
    }

    public function getProducer(): Producer
    {
        return $this->producer;
    }

    public function getConsumer(): KafkaConsumer
    {
        return $this->consumer;
    }
}
