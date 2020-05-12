<?php


namespace app\models\checkers;


abstract class AbstractSimpleChecker extends AbstractChecker
{
    public string $sample_string;
    public string $checking_header;


    /**
     * @param string $checking_header
     * @param string $sample_string
     */
    public function __construct($checking_header, $sample_string)
    {
        $this->checking_header = $checking_header;
        $this->sample_string = $sample_string;
    }

    /**
     * @inheritDoc
     */
    public function check($row_to_check)
    {
        $result = call_user_func([$this, $this->getCheckingFunction()], $row_to_check[$this->checking_header], $this->sample_string);
        $this->pointlessChecker($result);
        return $result;

    }
}