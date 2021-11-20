<?php
declare(strict_types=1);

namespace App\Model;

abstract class AbstractAppModel
{
    public function convertBoolean(mixed $value): bool
    {
        if ('false' === $value) {
            return false;
        }

        if ('true' === $value) {
            return true;
        }

        return $value;
    }
}
