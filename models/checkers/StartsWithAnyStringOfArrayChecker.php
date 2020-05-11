<?php


namespace app\models\checkers;


class StartsWithAnyStringOfArrayChecker extends AbstractArrayChecker
{

    protected function getCheckingFunction(): string
    {
        return 'starts_with';
    }
}