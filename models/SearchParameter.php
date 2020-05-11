<?php


namespace app\models;


class SearchParameter
{
    public string $header_name;
    /**
     * @var string|array $to_search
     */
    public $to_search;
    public int $mode;
    public string $header_in_array_name;
    public array $array_for_search;

    /**
     * SearchParameter constructor.
     * @param string $header
     * @param int $mode
     * @param string|array $to_search
     * @param string $header_in_array_name
     * @param array $array_for_search
     */
    public function __construct($header, $mode, $to_search, $header_in_array_name = '', $array_for_search = [])
    {
        $this->header_name = $header;
        $this->to_search = $to_search;
        $this->mode = $mode;
        $this->header_in_array_name = $header_in_array_name;
        $this->array_for_search = $array_for_search;
    }
}