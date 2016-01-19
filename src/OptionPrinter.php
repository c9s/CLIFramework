<?php
/**
 * This file is part of the GetOptionKit package.
 *
 * (c) Yo-An Lin <cornelius.howl@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */
namespace CLIFramework;
use GetOptionKit\OptionCollection;
use GetOptionKit\Option;
use GetOptionKit\OptionPrinter\OptionPrinterInterface;
use CLIFramework\Formatter;

class OptionPrinter implements OptionPrinterInterface
{
    public $screenWidth = 78;

    public $formatter;

    public function __construct() {
        $this->formatter = new Formatter;
    }

    /**
     * Render readable spec
     */
    public function renderOption(Option $opt)
    {
        $columns = array();
        if ($opt->short) {
            $columns[] = $this->formatter->format(sprintf('-%s',$opt->short), 'strong_white') 
                . $this->renderOptionValueHint($opt, false);
        }
        if ($opt->long) {
            $columns[] = $this->formatter->format(sprintf('--%s',$opt->long ), 'strong_white')
                . $this->renderOptionValueHint($opt, true);
        }
        return join(', ', $columns);
    }

    public function renderOptionValueHint(Option $opt, $assign = true)
    {
        $n = 'value';
        if ($opt->valueName) {
            $n = $opt->valueName;
        } elseif ($opt->isa) {
            $n = $opt->isa;
        }

        if ( $opt->isRequired() ) {
            return sprintf('%s<%s>',   $assign ? '=' : ' ', $this->formatter->format($n, 'underline') );
        } elseif ( $opt->isOptional() ) {
            return sprintf('%s[<%s>]', $assign ? '=' : ' ', $this->formatter->format($n, 'underline'));
        }

        return '';
    }

    /**
     * render option descriptions
     *
     * @return string output
     */
    public function render(OptionCollection $options)
    {
        # echo "* Available options:\n";
        $lines = array();
        foreach( $options as $option ) {
            $c1 = $this->renderOption($option);
            $lines[] = "\t" . $c1;
            $lines[] = wordwrap("\t\t" . $option->desc, $this->screenWidth, "\n\t\t");  # wrap text
            $lines[] = "";
        }
        return join("\n",$lines);
    }
}


