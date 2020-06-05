<?php


namespace app\models;


interface ISearcher
{
    public function toDoSearch();
    /**
     * @param array $row
     * @param int $type
     * @return string
     */
    public function getCodeSlice($row, $type);
}