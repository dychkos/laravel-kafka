<?php

namespace App\Http\Controllers;

use App\Components\Kafka\Enums\KafkaTopicEnum;
use App\Components\Kafka\Interfaces\KafkaServiceInterface;
use Illuminate\Http\JsonResponse;

class KafkaTestController extends Controller
{
    public function publishMessage(KafkaServiceInterface $kafkaService): JsonResponse
    {
        $kafkaService->publishMessage(topic: KafkaTopicEnum::USER_REGISTRATION, payload: [
            'user_id' => 123, 'action' => 'user.created', 'timestamp' => now()->toIso8601String(),
            'data' => ['name' => 'John Doe', 'email' => 'john@example.com']
        ], key: 'user_123');

        return response()->json(['status' => 'published']);
    }

    public function publishBatch(KafkaServiceInterface $kafkaService): JsonResponse
    {
        $kafkaService->publishBatch(KafkaTopicEnum::DEFAULT, [
            [
                'payload' => ['event' => 'event1', 'data' => 'value1'], 'key' => 'event_1'
            ], [
                'payload' => ['event' => 'event2', 'data' => 'value2'], 'key' => 'event_2'
            ], [
                'payload' => ['event' => 'event3', 'data' => 'value3'], 'key' => 'event_3'
            ],
        ]);

        return response()->json(['status' => 'batch published']);
    }
}
