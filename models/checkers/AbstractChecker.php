<?php


namespace app\models\checkers;


abstract class AbstractChecker
{
    public bool $searching_is_pointless = false;
    protected bool $previous_check_result = false;

    protected abstract function getCheckingFunction(): string;

    protected function pointlessChecker(bool $cur_value): void
    {
        if (!$this->searching_is_pointless) {
            $this->previous_check_result = $cur_value;
        }
    }

    /**
     * @param array $row_to_check
     * @return bool
     */
    public abstract function check($row_to_check);

    /**
     * @param string $target
     * @param string $searching_string
     * @return bool
     */
    protected function starts_with($target, $searching_string)
    {
        $length = strlen($searching_string);
        return (substr($target, 0, $length) === $searching_string);
    }

    /**
     * @param string $target
     * @param string $searching_string
     * @return bool
     */
    protected function in_string($target, $searching_string)
    {
        return (strpos($target, $searching_string) !== false);
    }

    /**
     * @param string $target
     * @param string $searching_string
     * @return bool
     */
    protected function strings_are_equal($target, $searching_string)
    {
        return ($target === $searching_string);
    }

}