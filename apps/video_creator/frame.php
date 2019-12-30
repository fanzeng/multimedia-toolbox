<?php
    class Frame {
        public function __construct($order, $source_file_name, $number_of_frames, $selected=false) {
            $this->order = $order;
            $this->source_file_name = $source_file_name;
            $this->number_of_frames = $number_of_frames;
            	        echo $this->order . ","  . $this->source_file_name . "," . $this->number_of_frames . ".\n";
            $this->selected = $selected;
        }

        public function to_string() {
            echo $this->order . ","  . $this->source_file_name . "," . $this->number_of_frames . "," . $this->selected . ".\n";
        }
    }
?>