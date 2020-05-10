<?php


namespace app\models;


class SearchParameter
{
    public string $header_name;
    public string $string_to_search;
    public string $mode;
    public int $level_type;

    public function __construct(string $header, string $string_to_search, string $mode, int $level_type)
    {
        $this->header_name = $header;
        $this->string_to_search = $string_to_search;
        $this->mode = $mode;
        $this->level_type = $level_type;
    }
}