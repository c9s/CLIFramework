<?php
namespace CLIFramework\Topic;

abstract class BaseTopic
{

    public $title = 'Untitled Topic';

    public $url;

    public $id;

    public function getUrl() { return $this->url; }
    public function getId() { return $this->id; }

    public function getTitle() { return $this->title; }
    public function getContent() { return ''; }
    public function getFooter() { 
        if ($url = $this->getUrl() ) {
            return "\tYou may view this topic at " . $url . "\n";
        }
        return '';
    }
}



