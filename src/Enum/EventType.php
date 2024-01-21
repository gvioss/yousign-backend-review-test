<?php

namespace App\Enum;

enum EventType: string
{
    case COMMIT = 'COM';
    case COMMENT = 'MSG';
    case PULL_REQUEST = 'PR';

    public static function getEquivalentFromGHArchiveType(string $type): EventType|null
    {
        return match($type) {
            'PullRequestEvent' => self::PULL_REQUEST,
            'CommitCommentEvent', 'IssueCommentEvent' => self::COMMENT,
            'PushEvent' => self::COMMIT,
            default  => null
        };
    }
}
