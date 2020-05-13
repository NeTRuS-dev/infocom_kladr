<?php


namespace app\models\checkers;


class EndsWithStringChecker extends AbstractSimpleChecker
{

    protected function getCheckingFunction(): string
    {
        return 'ends_with';
    }
}