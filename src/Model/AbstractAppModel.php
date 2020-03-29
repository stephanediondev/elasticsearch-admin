<?php

namespace App\Model;

abstract class AbstractAppModel
{
    public function convertBoolean($value): bool
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
