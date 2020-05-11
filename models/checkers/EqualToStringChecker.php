<?php


namespace app\models\checkers;


class EqualToStringChecker extends AbstractSimpleChecker
{
    protected function getCheckingFunction(): string
    {
        return 'strings_are_equal';
    }
}