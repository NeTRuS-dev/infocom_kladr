<?php


namespace app\models\checkers;


class StartsWithStringChecker extends AbstractSimpleChecker
{

    protected function getCheckingFunction(): string
    {
        return 'starts_with';
    }
}