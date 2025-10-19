<?php

namespace App\Components\Kafka\Enums;

enum KafkaTopicEnum: string
{
    case DEFAULT = 'default';
    case USER_REGISTRATION = 'user_registration';
}
