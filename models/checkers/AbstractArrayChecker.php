<?php


namespace app\models\checkers;


abstract class AbstractArrayChecker extends AbstractChecker
{
    public array $array_with_samples;
    public string $sample_header;
    public string $checking_header;
    protected int $last_checking_array_index;

    /**
     * @param string $checking_header
     * @param array $array_with_samples
     * @param string $sample_header
     */
    public function __construct($checking_header, $array_with_samples, $sample_header)
    {
        $this->array_with_samples = $array_with_samples;
        $this->checking_header = $checking_header;
        $this->sample_header = $sample_header;
        $this->last_checking_array_index = count($array_with_samples) - 1;
    }

    /**
     * @inheritDoc
     */
    public function check($row_to_check)
    {
        $checking_value = $row_to_check[$this->checking_header];
        foreach ($this->array_with_samples as $index => $sample) {
            if (call_user_func([$this, $this->getCheckingFunction()], $checking_value, $sample[$this->sample_header])) {
                $this->pointlessChecker($this->last_checking_array_index == $index);
                return true;
            }
        }
        $this->pointlessChecker(false);
        return false;
    }
}