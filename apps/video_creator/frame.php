<?php
class Frame
{
    public function __construct($order, $srcFilename, $numRepetition, $selected = false)
    {
        $this->order = $order;
        $this->srcFilename = $srcFilename;
        $this->numRepetition = $numRepetition;
        echo $this->order . ',' . $this->srcFilename . ',' . $this->numRepetition . ".\n";
        $this->selected = $selected;
    }

    public function toString()
    {
        echo $this->order . ',' . $this->srcFilename . ',' . $this->numRepetition . ',' . $this->selected . ".\n";
    }
}
