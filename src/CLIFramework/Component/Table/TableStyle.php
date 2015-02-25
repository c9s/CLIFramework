<?php
namespace CLIFramework\Component\Table;

class TableStyle
{
    public $cellPadding = 1;

    public $cellPaddingChar = ' ';

    public $verticalBorderChar = '|';


    public $rowSeparatorBorderChar = '-';

    public $rowSeparatorCrossChar = '+';

    public $rowSeparatorLeftmostCrossChar = '+';

    public $rowSeparatorRightmostCrossChar = '+';

    public $drawTableBorder = true;

    public $drawRowSeparator = false;

    public function setCellPadding($padding) 
    {
        $this->cellPadding = $padding;
    }

    public function setCellPaddingChar($c) 
    {
        $this->cellPaddingChar = $c;
    }

    public function setVerticalBorderChar($c) 
    {
        $this->verticalBorderChar = $c;
    }

    public function setRowSeparatorCrossChar($c)
    {
        $this->rowSeparatorCrossChar = $c;
    }

    public function setRowSeparatorRightmostCrossChar($c)
    {
        $this->rowSeparatorRightmostCrossChar = $c;
    }

    public function setRowSeparatorLeftmostCrossChar($c)
    {
        $this->rowSeparatorLeftmostCrossChar = $c;
    }

    public function setRowSeparatorBorderChar($c) {
        $this->rowSeparatorBorderChar = $c;
    }

}
