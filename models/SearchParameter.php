<?php


namespace app\models;


class SearchParameter
{
    public string $header_name;
    public string $string_to_search;
    public string $mode;

    public function __construct(string $header, string $string_to_search, string $mode)
    {
        $this->header_name = $header;
        $this->string_to_search = $string_to_search;
        $this->mode = $mode;
    }
}