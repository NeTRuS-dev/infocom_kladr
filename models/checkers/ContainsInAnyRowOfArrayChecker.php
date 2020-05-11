<?php


namespace app\models\checkers;


class ContainsInAnyRowOfArrayChecker extends AbstractArrayChecker
{

    protected function getCheckingFunction(): string
    {
        return 'in_string';
    }
}