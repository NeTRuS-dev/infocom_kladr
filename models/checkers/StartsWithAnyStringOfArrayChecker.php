<?php


namespace app\models\checkers;


class StartsWithAnyStringOfArrayChecker extends AbstractArrayChecker
{


    protected function getCheckingFunction(): string
    {
        return 'starts_with';
    }

    protected function pointlessChecker(bool $cur_value): void
    {
        if ($this->previous_check_result == true && $cur_value == false) {
            $this->searching_is_pointless = true;
        }
        parent::pointlessChecker($cur_value);
    }
}