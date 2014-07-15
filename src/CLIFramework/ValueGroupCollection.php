<?php
namespace CLIFramework;
use IteratorAggregate;
use ArrayIterator;

/**
 * 
 */
class ValueGroupCollection implements IteratorAggregate
{
    public $groups = array();

    public $labels = array();

    public function add($groupId, $value) {
        // for indexed array
        if (is_array($value)) {
            if ( !isset($this->groups[ $groupId ])) {
                $this->groups[$groupId] = $value;
            } else {
                $this->groups[ $groupId ] = array_merge(
                    $this->groups[ $groupId ], $value );
            }
        } else {
            $this->groups[ $groupId ][] = $value;
        }
    }


    public function set($groupId, $values) {
        $this->groups[ $groupId ] = $values;
    }

    public function get($groupId) {
        return $this->groups[ $groupId ];
    }

    public function setLabel($groupId, $label) {
        $this->labels[ $groupId ] = $label;
    }

    public function getLabel($groupId) {
        if ( isset($this->labels[ $groupId ]) ) {
            return $this->labels[ $groupId ];
        }
    }

    public function getLabels() {
        return $this->labels;
    }

    public function toJson() {
        return json_encode($this->groups);
    }

    public function getIterator() {
        return new ArrayIterator( $this->groups );
    }
}


