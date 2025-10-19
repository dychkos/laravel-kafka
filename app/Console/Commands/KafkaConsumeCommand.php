<?php

namespace App\Console\Commands;

use App\Components\Kafka\Enums\KafkaTopicEnum;
use App\Components\Kafka\Interfaces\KafkaServiceInterface;
use Illuminate\Console\Command;
use Illuminate\Validation\Rule;
use Symfony\Component\Console\Command\Command as CommandAlias;

class KafkaConsumeCommand extends Command
{
    protected $signature = 'kafka:consume {topic} {--timeout=120000} {--max-messages=}';
    protected $description = 'Consume messages from Kafka topic';

    public function handle(KafkaServiceInterface $kafkaService)
    {
        $topic = $this->argument('topic');
        $error = $this->validatePrompt($topic, ['required', 'string', Rule::enum(KafkaTopicEnum::class)]);

        if ($error) {
            $this->error("Invalid topic: {$topic}");
            return CommandAlias::FAILURE;
        }

        $timeout = $this->option('timeout');
        $maxMessages = $this->option('max-messages');

        $this->info("Consuming from topic: {$topic}");

        $kafkaService->consume(KafkaTopicEnum::tryFrom($topic), function ($message) {
            $this->line(json_encode($message, JSON_PRETTY_PRINT));
        }, timeout: (int) $timeout, maxMessages: $maxMessages ? (int) $maxMessages : null);
    }
}
