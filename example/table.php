<?php
require 'vendor/autoload.php';

use CLIFramework\Component\Table\Table;
use CLIFramework\Component\Table\TableStyle;
use CLIFramework\Component\Table\CellAttribute;
use CLIFramework\Component\Table\CurrencyFormatCell;
use CLIFramework\Component\Table\MarkdownTableStyle;

$bluehighlight = new CellAttribute;
$bluehighlight->setBackgroundColor('blue');

$redhighlight = new CellAttribute;
$redhighlight->setBackgroundColor('red');

$priceCell = new CurrencyFormatCell('fr', 'EUR');

$table = new Table;
$table->setColumnCellAttribute(0, $bluehighlight);
$table->setColumnCellAttribute(3, $priceCell);
$table->setHeaders([ 'Published Date', 'Title', 'Description' ]);
// $table->setStyle(new MarkdownTableStyle);
$table->addRow(array( 
    "September 16, 2014",
    [$redhighlight, "Zero to One: Notes on Startups, or How to Build the Future"],
    "If you want to build a better future, you must believe in secrets.
    The great secret of our time is that there are still uncharted frontiers to explore and new inventions to create. In Zero to One, legendary entrepreneur and investor Peter Thiel shows how we can find singular ways to create those new things. ",
    29.5
));
$table->addRow(array( 
    "November 4, 2014",
    "Hooked: How to Build Habit-Forming Products",

    "Why do some products capture widespread attention while others flop? What makes us engage with certain products out of sheer habit? Is there a pattern underlying how technologies hook us? "
    . "Nir Eyal answers these questions (and many more) by explaining the Hook Model—a four-step process embedded into the products of many successful companies to subtly encourage customer behavior. Through consecutive “hook cycles,” these products reach their ultimate goal of bringing users back again and again without depending on costly advertising or aggressive messaging.\n",
    99,
));
echo $table->render();
