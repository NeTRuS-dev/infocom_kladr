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
        $length = mb_strlen($searching_string);
        return (mb_strtolower(mb_substr($target, 0, $length)) === mb_strtolower($searching_string));
    }

    /**
     * @param string $target
     * @param string $searching_string
     * @return bool
     */
    protected function ends_with($target, $searching_string)
    {
        $len = mb_strlen($searching_string);
        if ($len === 0) {
            return true;
        }
        return (mb_strtolower(mb_substr($target, -$len)) === mb_strtolower($searching_string));
    }

    /**
     * @param string $target
     * @param string $searching_string
     * @return bool
     */
    protected function in_string($target, $searching_string)
    {
        return (mb_stripos($target, $searching_string) !== false);
    }

    /**
     * @param string $target
     * @param string $searching_string
     * @return bool
     */
    protected function strings_are_equal($target, $searching_string)
    {
        return (mb_strtolower($target) === mb_strtolower($searching_string));
    }

}