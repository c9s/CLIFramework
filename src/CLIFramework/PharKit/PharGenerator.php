<?php
namespace CLIFramework\PharKit;
use Phar;
use CLIFramework\Logger;

use CodeGen\Block;
use CodeGen\Expr\NewObjectExpr;
use CodeGen\Statement\UseStatement;
use CodeGen\Statement\FunctionCallStatement;
use CodeGen\Statement\AssignStatement;
use CodeGen\Statement\MethodCallStatement;
use GetOptionKit\OptionResult;


class PharGenerator
{
    protected $pharFile;

    protected $alias;

    protected $map;

    protected $phar;

    protected $shellbang = '#!/usr/bin/env php';

    protected $logger;

    protected $options;

    public function __construct(Logger $logger, OptionResult $options, $pharFile, $alias = null)
    {
        $this->logger = $logger;
        $this->options = $options;
        $this->pharFile = $pharFile;

        if ($alias) {
            $this->alias = $alias;
        } else {
            $this->alias = basename($pharFile);
        }

        $this->phar = new Phar($this->pharFile, 0, $this->alias);
        $this->phar->setSignatureAlgorithm(Phar::SHA1);
    }

    public function shellbang($shellbang)
    {
        $this->shellbang = $shellbang;
    }

    public function getPhar()
    {
        return $this->phar;
    }

    public function generate()
    {
        // $this->phar->startBuffering();


        // Finish building...
        $this->phar->stopBuffering();

        $compressType = Phar::GZ;
        if ($this->options->{'no-compress'} ) {
            $compressType = null;
        } else if ($type = $this->options->compress) {
            switch ($type) {
            case 'gz':
                $compressType = Phar::GZ;
                break;
            case 'bz2':
                $compressType = Phar::BZ2;
                break;
            default:
                throw new Exception("Phar compression: $type is not supported, valid values are gz, bz2");
                break;
            }
        }
        if ($compressType) {
            $this->logger->info( "Compressing phar files...");
            $this->phar->compressFiles($compressType);
        }
    }





}

