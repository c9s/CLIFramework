<?php
namespace CLIFramework\Command;
use CLIFramework\Command;
use ClassTemplate\ClassTemplate;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;


class BuildGitHubWikiTopicsCommand extends Command
{

    public function brief() { return 'Build topic classes from the wiki of a GitHub Project'; }

    public function options($opts) {
        $opts->add('ns:', 'Class namespace');
        $opts->add('dir:', 'Output directory');
    }

    public function arguments($args) {
        $args->add('user')->desc('GitHub Account');
        $args->add('repo')->desc('GitHub Repository');
    }

    public function execute($user, $repo) {
        $ns = $this->options->ns;

        // Use git to clone the wiki
        $wikiURI = "https://github.com/$user/$repo.wiki.git";
        $localRepoName = "$repo.wiki";

        $currentDir = getcwd();

        chdir('/tmp');

        if (is_dir($localRepoName)) {
            chdir($localRepoName);
            system("git pull origin", $retval);
            if ($retval != 0) {
                return $this->logger->error("Can't clone wiki repository");
            }
        } else {
            system("git clone $wikiURI", $retval);
            if ($retval != 0) {
                return $this->logger->error("Can't clone wiki repository");
            }
            chdir($localRepoName);
        }

        // Build classes
        $this->logger->info("Building...");

        $directory = new RecursiveDirectoryIterator('.');
        $iterator = new RecursiveIteratorIterator($directory);
        $regex = new RegexIterator($Iterator, '/^.+\.php$/i', RecursiveRegexIterator::GET_MATCH);

        chdir($currentDir);
        $this->logger->info("Done");


        /*
        $cTemplate = new ClassTemplate( $schema->getBaseModelClass() , array(
            'template' => 'Class.php.twig',
        ));
        $cTemplate->addConsts(array(
            'schema_proxy_class' => $schema->getSchemaProxyClass(),
            'collection_class' => $schema->getCollectionClass(),
            'model_class' => $schema->getModelClass(),
            'table' => $schema->getTable(),
        ));
        $cTemplate->addStaticVar( 'column_names',  $schema->getColumnNames() );
        $cTemplate->addStaticVar( 'column_hash',  array_fill_keys($schema->getColumnNames(), 1 ) );
        $cTemplate->addStaticVar( 'mixin_classes', array_reverse($schema->getMixinSchemaClasses()) );
        $cTemplate->extendClass( '\\' . $baseClass );
        return $cTemplate;
        */
    }
}




