<?php
namespace CLIFramework;
use IteratorAggregate;
use ArrayIterator;

/**
 * 
 */
class ValueCollection implements IteratorAggregate
{

    public $groups = array();

    /**
     * @var label[group id] group labels
     */
    public $labels = array();



    /**
     * Add Grouped Values:
     *
     *     ->group('id', 'ID', [ 'a', 'b', 'c' ]);
     *     ->group('id', 'ID', [ 'label' => 'desc' ]);
     *
     */
    public function group($groupId, $label, $value) {
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
        $this->setGroupLabel($groupId, $label);
    }


    public function setGroup($groupId, $values) {
        $this->groups[ $groupId ] = $values;
    }

    public function getGroup($groupId) {
        return $this->groups[ $groupId ];
    }

    public function setGroupLabel($groupId, $label) {
        $this->labels[ $groupId ] = $label;
    }

    public function getGroupLabel($groupId) {
        if ( isset($this->labels[ $groupId ]) ) {
            return $this->labels[ $groupId ];
        }
    }

    public function getGroupLabels() {
        return $this->labels;
    }

    public function toJson() {
        return json_encode($this->groups);
    }

    public function getIterator() {
        return new ArrayIterator( $this->groups );
    }
}


