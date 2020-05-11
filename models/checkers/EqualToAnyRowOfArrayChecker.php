<?php


namespace app\models\checkers;


class EqualToAnyRowOfArrayChecker extends AbstractArrayChecker
{

    protected function getCheckingFunction(): string
    {
        return 'strings_are_equal';
    }
}