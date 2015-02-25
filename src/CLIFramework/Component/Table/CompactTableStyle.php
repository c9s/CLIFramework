<?php
namespace CLIFramework\Component\Table;

class CompactTableStyle extends TableStyle
{
    public $cellPadding = 1;

    public $cellPaddingChar = ' ';

    public $verticalBorderChar = ' ';

    public $drawTableBorder = false;

    public $drawRowSeparator = false;

    public $rowSeparatorCrossChar = '-';

    public $rowSeparatorBorderChar = '-';

    public $rowSeparatorLeftmostCrossChar = '-';

    public $rowSeparatorRightmostCrossChar = '-';
}

