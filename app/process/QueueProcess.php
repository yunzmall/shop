<?php


namespace app\process;


class QueueProcess
{
    use Fork;
    public $queueName;
    public $queueTotal;
    public $index;
    private $option;
    private $isSerial;

    public function __construct($queueName, $queueTotal, $index, $option, $isSerial = false)
    {
        $this->queueName = $queueName;
        $this->queueTotal = $queueTotal;
        $this->index = $index;
        $this->option = $option;
        $this->isSerial = $isSerial;
    }


    public function getOption()
    {
        $option = $this->option;

        if ($this->isSerial) {
            if ($this->queueTotal > 1 && $this->index > 0) {
                $option['--queue'] = $this->queueName . ':' . $this->index;

            } else {
                $option['--queue'] = $this->queueName . ',' . $this->queueName . ':' . $this->index;

            }

        } else {
            $option['--queue'] = $this->queueName;
        }
        return $option;
    }
}