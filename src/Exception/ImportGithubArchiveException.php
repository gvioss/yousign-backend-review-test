<?php

namespace App\Exception;

class ImportGithubArchiveException extends \Exception
{
    public static function missingArchive(string $filename): self
    {
        return new self(sprintf('The requested archive (%s) is missing.', $filename));
    }
}
