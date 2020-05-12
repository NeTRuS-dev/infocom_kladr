<?php


namespace app\models;


use app\models\checkers\AbstractChecker;

class SearchParameter
{
    public AbstractChecker $checker;
    public int $start_index;
    public array $array_for_search;

    /**
     * SearchParameter constructor.
     * @param AbstractChecker $checker
     * @param int $start_index
     * @param array $array_for_search
     */
    public function __construct($checker, $start_index = 1, $array_for_search = [])
    {
        $this->checker = $checker;
        $this->array_for_search = $array_for_search;
        $this->start_index = $start_index;
    }
}