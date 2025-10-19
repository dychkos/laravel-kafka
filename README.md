
# Laravel Kafka Integration with php-rdkafka

  

Complete guide for integrating Apache Kafka with Laravel using the `arnaud-lb/php-rdkafka` library.

  

## Table of Contents

  

- [Installation](#installation)

- [Configuration](#configuration)

- [Architecture](#architecture)

- [Quick Start](#quick-start)

- [Publishing Messages](#publishing-messages)

- [Consuming Messages](#consuming-messages)

- [Docker Setup](#docker-setup)

  

## Installation

  

### Prerequisites

  

- PHP 8.0+

- Laravel 9+

- Apache Kafka 2.0+

- librdkafka C library

  

### Install Dependencies

### Using Docker

All required dependencies are already included in the provided Dockerfile. If you are running the project inside the Docker container, you don’t need to install anything manually.

### Local Environment Setup

If you need to run the project outside Docker, follow these steps:

### Install librdkafka

#### macOS:

`brew install librdkafka`


#### Ubuntu/Debian:

`sudo apt-get install librdkafka-dev pkg-config`

Enable the PHP Extension

`pecl install rdkafka
echo "extension=rdkafka.so" >> /etc/php.ini`

  

Verify installation:

```bash

php -m | grep rdkafka

```

  

## Configuration

  

### 1. Create Config File

  

Create `config/kafka.php`:

  

```php

return [

'brokers' => env('KAFKA_BROKERS', 'localhost:9092'),

'group_id' => env('KAFKA_GROUP_ID', 'laravel-consumer-group'),

'client_id' => env('KAFKA_CLIENT_ID', 'laravel-client'),

'auto_offset_reset' => env('KAFKA_AUTO_OFFSET_RESET', 'earliest'),

'enable_auto_commit' => env('KAFKA_AUTO_COMMIT', true),

'session_timeout_ms' => env('KAFKA_SESSION_TIMEOUT', 6000),

'socket_timeout_ms' => env('KAFKA_SOCKET_TIMEOUT', 60000),

];

```

  

### 2. Set Environment Variables

  

In your `.env` file:

  

```bash  

# For Docker container networking

KAFKA_BROKERS=kafka:29092
KAFKA_GROUP_ID=laravel-consumer-group
KAFKA_CLIENT_ID=laravel-client
KAFKA_AUTO_OFFSET_RESET=earliest
KAFKA_AUTO_COMMIT=true
KAFKA_SESSION_TIMEOUT=6000
KAFKA_SOCKET_TIMEOUT=60000

```

  

### 3. Register Provider

  

Add to `config/app.php` providers array:

  

```php

'providers' => [
	// ...
	App\Providers\KafkaProvider::class,
],

```

  

## Architecture

  

### Core Components

  

#### 1. ****KafkaProvider**** (`app/Providers/KafkaProvider.php`)

Service provider that registers Producer and Consumer in the service container.

  

Methods:

- `createProducer()` - Returns configured Producer instance

- `createConsumer()` - Returns configured Consumer instance

  

#### 2. ****KafkaService**** (`app/Services/KafkaService.php`)

High-level service for Kafka operations.

  

Methods:

- `publishMessage(KafkaTopicEnum $topic, array $payload, ?string $key)` - Publish single message

- `publishBatch(KafkaTopicEnum $topic, array $messages)` - Publish multiple messages

- `consume(KafkaTopicEnum|array $topics, callable $callback, int $timeout, ?int $maxMessages)` - Consume messages

- `getProducer()` - Get raw Producer instance

- `getConsumer()` - Get raw Consumer instance
  

## Quick Start

  

### Publishing a Message

  

```php

use App\Components\Kafka\Enums\KafkaTopicEnum;
use App\Components\Kafka\Interfaces\KafkaServiceInterface;
 
class UserController extends Controller
{

	public function store(KafkaServiceInterface $kafka)
	{
		$kafka->publishMessage(
			topic: KafkaTopicEnum::USER_REGISTRATION,
			payload: [
			'event' => 'user.created',
			'user_id' => 123,
			'email' => 'user@example.com',
			'timestamp' => now()->toIso8601String(),
			],
			key: 'user_123'
		);

		return response()->json(['status' => 'published']);
		}
	}
}

```

  

### Consuming Messages

  

```bash

sail artisan kafka:consume user-events

```

  



## Publishing Messages

  

### Single Message

  

```php

$kafka->publishMessage(
	topic: KafkaTopicEnum::DEFAULT->value,
	payload: [
		'order_id' => 456,
		'amount' => 99.99,
		'status' => 'pending',
	],
	key: 'order_456'
);

```

  

### Batch Messages

  

```php

$kafka->publishBatch(KafkaTopicEnum::DEFAULT->value, [
	[
		'payload' => ['order_id' => 1, 'status' => 'shipped'],
		'key' => 'order_1'
	],

	[
		'payload' => ['order_id' => 2, 'status' => 'delivered'],
		'key' => 'order_2'
	],
]);

```
  

## Docker Setup

  

### Using Docker Compose

  

```bash

docker-compose up -d

```

  

Kafka will be available at `kafka:29092` (internal) and `localhost:9092` (external).

 

### Creating Topics

  

Inside Kafka container:

  

```bash

docker compose exec kafka kafka-topics --create \
--topic user-events \
--bootstrap-server localhost:9092 \
--partitions 1 \
--replication-factor 1
```

  

### Listing Topics

  

```bash

docker compose exec kafka kafka-topics --list --bootstrap-server localhost:9092

```

  

### Monitoring Messages

  

```bash

docker compose exec kafka kafka-console-consumer \
--topic user-events \
--bootstrap-server localhost:9092 \
--from-beginning

```

  

## Resources

  

- [Apache Kafka Documentation](https://kafka.apache.org/documentation/)

- [php-rdkafka Documentation](https://github.com/arnaud-lb/php-rdkafka)

- [Confluent Kafka Documentation](https://docs.confluent.io/)

- [Laravel Service Container](https://laravel.com/docs/container)

