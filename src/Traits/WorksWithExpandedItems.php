<?php

namespace DigitSoft\LaravelRbac\Traits;
/**
 * Trait WorksWithExpandedItems
 * @package DigitSoft\LaravelRbac\Traits
 */
trait WorksWithExpandedItems
{
    protected $parentsExpanded;

    protected $childrenExpanded;

    /**
     * Get expanded children list (recursively)
     * @param string|null $parent_name
     * @return array
     */
    protected function getChildrenExpanded($parent_name = null)
    {
        if ($this->childrenExpanded === null) {
            $this->childrenExpanded = $this->expandChildren();
        }
        if ($parent_name === null) {
            return $this->childrenExpanded;
        }
        return $this->childrenExpanded[$parent_name] ?? [];
    }

    /**
     * Get expanded parents list (recursively)
     * @param string|null $child_name
     * @return array
     */
    protected function getParentsExpanded($child_name = null)
    {
        if ($this->parentsExpanded === null) {
            $this->parentsExpanded = $this->expandParents();
        }
        if ($child_name === null) {
            return $this->parentsExpanded;
        }
        return $this->parentsExpanded[$child_name] ?? [];
    }

    /**
     * @return array
     */
    private function expandChildren()
    {
        $children = $this->getChildren();
        foreach ($children as $name => $childrenArray) {
            $this->buildRecursiveChildren($name, $children);
        }
        return $children;
    }

    /**
     * @return array
     */
    private function expandParents()
    {
        $children = $this->getChildren();
        $parents = [];
        foreach ($children as $parentName => $childrenList) {
            foreach ($childrenList as $childName) {
                if ($childName === '*') {
                    continue;
                }
                $parents[$childName] = $parents[$childName] ?? [];
                if (!in_array($parentName, $parents[$childName])) {
                    $parents[$childName][] = $parentName;
                }
            }
        }
        foreach ($parents as $name => $childrenArray) {
            $this->buildRecursiveParents($name, $parents);
        }
        return $parents;
    }


    /**
     * Build parents recursive
     * @param string $name
     * @param array  $parents
     * @return array
     */
    private function buildRecursiveParents($name, &$parents)
    {
        if ($name === '*') {
            return [];
        }

        foreach ($parents[$name] as $parentName) {
            $parentsInt = $this->buildRecursiveParents($parentName, $parents);
            if (!empty($parentsInt)) {
                $parents[$name] = array_merge($parents[$name], $parentsInt);
            }
        }
        sort($parents[$name]);
        return $parents[$name];
    }

    /**
     * Build children recursive
     * @param string $name
     * @param array  $children
     * @return array
     */
    private function buildRecursiveChildren($name, &$children)
    {
        if ($name === '*') {
            return [];
        }

        foreach ($children[$name] as $childName) {
            $childrenInt = $this->buildRecursiveChildren($childName, $children);
            if (!empty($childrenInt)) {
                $children[$name] = array_merge($children[$name], $childrenInt);
            }
        }
        sort($children[$name]);

        return $children[$name];
    }

    /**
     * @return string[]
     */
    abstract protected function getChildren();
}