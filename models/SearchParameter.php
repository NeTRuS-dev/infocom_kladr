<?php


namespace app\models;


class SearchParameter
{
    public string $header_name;
    /**
     * @var string|array $to_search
     */
    public $to_search;
    public string $mode;
    public ?int $level_type;

    /**
     * SearchParameter constructor.
     * @param string $header
     * @param string|array $to_search array if looking for types
     * @param string $mode
     * @param int|null $level_type
     */
    public function __construct($header, $to_search, $mode, $level_type = null)
    {
        $this->header_name = $header;
        $this->to_search = $to_search;
        $this->mode = $mode;
        $this->level_type = $level_type;
    }
}