<?php
namespace DemoApp\Command;
use CLIFramework\ValueCollection;

class CommitCommand extends \CLIFramework\Command {

    public function brief() { return 'A simple demo command'; }

    public function aliases() { return array('c','ci'); }

    public function options($opts) {
        $opts->add('a|all','Tell the command to automatically stage files that have been modified and deleted, but new files you have not told Git about are not affected.');

        $opts->add('p|patch','Use the interactive patch selection interface to chose which changes to commit. See git-add(1) for details.');

        $opts->add('C|reuse-message:','Take an existing commit object, and reuse the log message and the authorship information (including the timestamp) when creating the commit.')
            ->isa('string')
            ->valueName('commit hash')
            // ->validValues([ 'static-50768ab', 'static-c2efdc2', 'static-ed5ba6a', 'static-cf0b1eb'])
            ->validValues(function() {
                $output = array();
                exec("git rev-list --abbrev-commit HEAD -n 20", $output);
                return $output;
            })
            ;

        $opts->add('c|reedit-message:','like -C, but with -c the editor is invoked, so that the user can further edit the commit message.')
            ->isa('string')
            ->valueName('commit hash')
            ->validValues(function() {
                // exec("git log -n 10 --pretty=format:%H:%s", $output);
                exec("git log -n 10 --pretty=format:%H:%s", $output);
                return array_map(function($line) {
                    list($key,$val) = explode(':',$line);
                    $val = preg_replace('/\W/',' ', $val);
                    return array($key, $val);
                }, $output);
            })
            ;

        $opts->add('author:', 'Override the commit author. Specify an explicit author using the standard A U Thor <author@example.com> format.')
            ->suggestions(array( 'c9s', 'foo' , 'bar' ))
            ->valueName('author name')
            ;

        $opts->add('output:', 'Output file')
            ->isa('file')
            ;
    }

    public function arguments($args) {
        $args->add('user')
            ->validValues(function() {
                $values = new ValueCollection;
                $values->group('authors', 'Authors', array(
                    'abba'    => 'ABBA',
                    'michael' => 'Michael Jackson',
                    'adele'   => 'Adele',
                    'air'     => 'Air',
                    'alicia'  => 'Alicia Keys',
                    'andras'  => 'Andras Schiff',
                ));
                $values->group('admins', 'Administrators', array( 'admin1', 'admin2' , 'admin3' ));
                $values->group('users', 'Users', array( 'userA', 'userB' , 'userC' ));
                $values->group('extension', 'PHP Extensions', get_loaded_extensions());

                $funcs = get_defined_functions();
                $values->group('internal-functions', 'PHP Internal Functions', $funcs['internal']);
                $values->group('user-functions', 'PHP User-defined Functions', $funcs['user']);
                return $values;
            })
            ;

        $args->add('repo')
            ->validValues(array('CLIFramework','GetOptionKit', 'PHPBrew', 'AssetKit', 'ActionKit'))
            ;

        $args->add('file')
            ->isa('file')
            ->glob('*.php')
            ->multiple()
            ;
    }

    public function execute($user,$repo) {
        $this->logger->notice('executing bar command.');
    }
}

