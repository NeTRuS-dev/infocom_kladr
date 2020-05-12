<?php


namespace app\models\checkers;


class CompositeChecker extends AbstractChecker
{
    public array $checkers;

    /**
     * CompositeChecker constructor.
     * @param AbstractChecker[] $checkers
     */
    public function __construct($checkers)
    {
        $this->checkers = $checkers;
    }

    /**
     * @inheritDoc
     */
    public function check($row_to_check)
    {
        foreach ($this->checkers as $checker) {
            if (!($checker->check($row_to_check))) {
                $this->pointlessChecker($checker->searching_is_pointless);
                return false;
            }
        }
        return true;
    }

    protected function getCheckingFunction(): string
    {
        return '';
    }

}