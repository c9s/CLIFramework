<?php
namespace CLIFramework\Component\Table;

#[\AllowDynamicProperties]
class MarkdownTableStyle extends TableStyle
{
    public $cellPadding = 1;

    public $cellPaddingChar = ' ';

    public $verticalBorderChar = '|';

    public $rowSeparatorCrossChar = '|';

    public $rowSeparatorBorderChar = '-';

    public $rowSeparatorLeftmostCrossChar = '|';

    public $rowSeparatorRightmostCrossChar = '|';

    public $drawTableBorder = false;
}

