<?php


namespace app\models\checkers;


class ContainsStringChecker extends AbstractSimpleChecker
{
    protected function getCheckingFunction(): string
    {
        return 'in_string';
    }
}