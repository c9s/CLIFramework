<?php
use CLIFramework\Component\Table\Table;
use CLIFramework\Component\Table\TableStyle;
use CLIFramework\Component\Table\CompactTableStyle;
use CLIFramework\Component\Table\MarkdownTableStyle;
use CLIFramework\Component\Table\CellAttribute;
use CLIFramework\Component\Table\NumberFormatCell;
use CLIFramework\Component\Table\CurrencyCellAttribute;
use CLIFramework\Component\Table\SpellOutNumberFormatCell;
use CLIFramework\Component\Table\RowSeparator;

class TableTest extends PHPUnit_Framework_TestCase
{

    public function testNumberFormatCell()
    {
        $numberFormatCell = new NumberFormatCell('en');
        $numberFormatCell->setBackgroundColor('blue');

        $table = new Table;
        $table->setColumnCellAttribute(2, $numberFormatCell);
        $table->addRow(array("AAA", "ASCII adjust AL after addition", 123));
        $table->addRow(array("AAD", "ASCII adjust AX before division", 222));
        $table->addRow(array("AAM", "ASCII adjust AX after multiplication", 12909));

        // echo "\n" . $table->render() . "\n";
        $this->assertStringEqualsFile('tests/data/default-table-number-column-cell-attribute.txt', $table);
    }

    public function testColumnCellAttribute()
    {
        $highlight = new CellAttribute;
        $highlight->setBackgroundColor('blue');

        $highlight2 = new CellAttribute;
        $highlight2->setForegroundColor('red');

        $table = new Table;
        $table->setColumnCellAttribute(0, $highlight);
        $table->setColumnCellAttribute(1, $highlight2);
        $table->addRow(array("AAA", "ASCII adjust AL after addition"));
        $table->addRow(array("AAD", "ASCII adjust AX before division"));
        $table->addRow(array("AAM", "ASCII adjust AX after multiplication"));

        // echo "\n" . $table->render() . "\n";
        $this->assertStringEqualsFile('tests/data/default-table-column-cell-attribute.txt', $table);
    }

    public function testRowSeparator()
    {
        $table = new Table;
        $table->setStyle(new CompactTableStyle);
        $table->addRow(array( 
            "September 16, 2014",
            "Zero to One: Notes on Startups, or How to Build the Future",
            "If you want to build a better future, you must believe in secrets.
            The great secret of our time is that there are still uncharted frontiers to explore and new inventions to create. In Zero to One, legendary entrepreneur and investor Peter Thiel shows how we can find singular ways to create those new things. ",
        ));
        $table->addRow(new RowSeparator);
        $table->addRow(array( 
            "September 16, 2014",
            "Zero to One: Notes on Startups, or How to Build the Future",
            "If you want to build a better future, you must believe in secrets.
            The great secret of our time is that there are still uncharted frontiers to explore and new inventions to create. In Zero to One, legendary entrepreneur and investor Peter Thiel shows how we can find singular ways to create those new things. ",
        ));
        // echo "\n" . $table->render() . "\n";
        $this->assertStringEqualsFile("tests/data/default-table-row-separator.txt", $table);
    }

    public function testCustomColumnCellAttribute()
    {
        $highlight = new CellAttribute;
        $highlight->setBackgroundColor('blue');

        $table = new Table;
        $table->addRow(array( 
            "September 16, 2014",
            array($highlight, "Zero to One: Notes on Startups, or How to Build the Future"),
            "If you want to build a better future, you must believe in secrets.
            The great secret of our time is that there are still uncharted frontiers to explore and new inventions to create. In Zero to One, legendary entrepreneur and investor Peter Thiel shows how we can find singular ways to create those new things. ",
        ));
        // echo "\n" . $table->render() . "\n";
        $this->assertStringEqualsFile('tests/data/default-table-cell-attribute.txt', $table);
    }

    public function testDefaultTableWithTextOverflowWithoutHeaderAndFooter()
    {
        $table = new Table;
        $table->setMaxColumnWidth(30);
        $table->getDefaultCellAttribute()->setTextOverflow(CellAttribute::ELLIPSIS);
        $table->addRow(array( 
            "September 16, 2014",
            "Zero to One: Notes on Startups, or How to Build the Future",
            "If you want to build a better future, you must believe in secrets.
            The great secret of our time is that there are still uncharted frontiers to explore and new inventions to create. In Zero to One, legendary entrepreneur and investor Peter Thiel shows how we can find singular ways to create those new things. ",
        ));

        $out = $table->render();
        $this->assertStringEqualsFile('tests/data/default-table-2.txt', $out);
    }



    public function testDefaultTableWithoutFooter()
    {
        $table = new Table;
        $table->setHeaders(array(
            'Published Date',
            'Title',
            'Description',
        ));
        $table->addRow(array( 
            "September 16, 2014",
            "Zero to One: Notes on Startups, or How to Build the Future",
            "If you want to build a better future, you must believe in secrets.
            The great secret of our time is that there are still uncharted frontiers to explore and new inventions to create. In Zero to One, legendary entrepreneur and investor Peter Thiel shows how we can find singular ways to create those new things. ",
        ));
        $table->addRow(array( 
            "November 4, 2014",
            "Hooked: How to Build Habit-Forming Products",

            "Why do some products capture widespread attention while others flop? What makes us engage with certain products out of sheer habit? Is there a pattern underlying how technologies hook us? "
            . "Nir Eyal answers these questions (and many more) by explaining the Hook Model—a four-step process embedded into the products of many successful companies to subtly encourage customer behavior. Through consecutive “hook cycles,” these products reach their ultimate goal of bringing users back again and again without depending on costly advertising or aggressive messaging.\n"
        ));
        $out = $table->render();
        $this->assertStringEqualsFile('tests/data/default-table.txt', $out);
    }


    public function testDefaultTableWithFooter()
    {
        $table = new Table;
        $table->setHeaders(array(
            'Published Date',
            'Title',
            'Description',
        ));
        $table->addRow(array( 
            "September 16, 2014",
            "Zero to One: Notes on Startups, or How to Build the Future",
            "If you want to build a better future, you must believe in secrets.
            The great secret of our time is that there are still uncharted frontiers to explore and new inventions to create. In Zero to One, legendary entrepreneur and investor Peter Thiel shows how we can find singular ways to create those new things. ",
        ));
        $table->addRow(array( 
            "November 4, 2014",
            "Hooked: How to Build Habit-Forming Products",

            "Why do some products capture widespread attention while others flop? What makes us engage with certain products out of sheer habit? Is there a pattern underlying how technologies hook us? "
            . "Nir Eyal answers these questions (and many more) by explaining the Hook Model—a four-step process embedded into the products of many successful companies to subtly encourage customer behavior. Through consecutive “hook cycles,” these products reach their ultimate goal of bringing users back again and again without depending on costly advertising or aggressive messaging.\n"
        ));
        $table->setFooter('Found 3 books...');
        $out = $table->render();
        $this->assertStringEqualsFile('tests/data/default-table-footer.txt', $out);
    }


    public function testMarkdownTable()
    {
        $table = new Table;
        $table->setStyle(new MarkdownTableStyle);
        $table->setHeaders(array(
            'Published Date',
            'Title',
            'Description',
        ));
        $table->addRow(array( 
            "September 16, 2014",
            "Zero to One: Notes on Startups, or How to Build the Future",
            "If you want to build a better future, you must believe in secrets.
            The great secret of our time is that there are still uncharted frontiers to explore and new inventions to create. In Zero to One, legendary entrepreneur and investor Peter Thiel shows how we can find singular ways to create those new things. ",
        ));
        $table->addRow(array( 
            "November 4, 2014",
            "Hooked: How to Build Habit-Forming Products",

            "Why do some products capture widespread attention while others flop? What makes us engage with certain products out of sheer habit? Is there a pattern underlying how technologies hook us? "
            . "Nir Eyal answers these questions (and many more) by explaining the Hook Model—a four-step process embedded into the products of many successful companies to subtly encourage customer behavior. Through consecutive “hook cycles,” these products reach their ultimate goal of bringing users back again and again without depending on costly advertising or aggressive messaging.\n"
        ));
        $table->setFooter('Found 3 books...');
        $out = $table->render();
        $this->assertStringEqualsFile('tests/data/markdown-table.txt', $out);
    }

    static public function assertStringEqualsFile($file, $str, $message = NULL, $canonicalize = false, $ignoreCase = false) {
        if ($str instanceof Table) {
            $str = $str->render();
        }
        if (!file_exists($file)) {
            file_put_contents($file, $str);
            echo "Actual:\n";
            echo $str , "\n";
        }
        parent::assertStringEqualsFile($file, $str, $message, $canonicalize, $ignoreCase);
    }

}

