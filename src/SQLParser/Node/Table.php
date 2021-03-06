<?php

/**
 * expression-types.php.
 *
 *
 * Copyright (c) 2010-2013, Justin Swanhart
 * with contributions by André Rothe <arothe@phosco.info, phosco@gmx.de>
 * and David Négrier <d.negrier@thecodingmachine.com>
 *
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without modification,
 * are permitted provided that the following conditions are met:
 *
 *   * Redistributions of source code must retain the above copyright notice,
 *     this list of conditions and the following disclaimer.
 *   * Redistributions in binary form must reproduce the above copyright notice,
 *     this list of conditions and the following disclaimer in the documentation
 *     and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY
 * EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES
 * OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT
 * SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED
 * TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR
 * BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH
 * DAMAGE.
 */

namespace SQLParser\Node;

use Doctrine\DBAL\Connection;
use Mouf\MoufInstanceDescriptor;
use Mouf\MoufManager;

/**
 * This class represents a table (and optionnally a JOIN .. ON expression in an SQL expression.
 *
 * @author David Négrier <d.negrier@thecodingmachine.com>
 */
class Table implements NodeInterface
{
    private $table;

    /**
     * Returns the table name.
     *
     * @return string
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * Sets the table name.
     *
     * @Important
     *
     * @param string $table
     */
    public function setTable($table)
    {
        $this->table = $table;
    }

    private $alias;

    /**
     * Returns the alias.
     *
     * @return string
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * Sets the alias.
     *
     * @Important
     *
     * @param string $alias
     */
    public function setAlias($alias)
    {
        $this->alias = $alias;
    }

    private $joinType;

    /**
     * Returns the join type.
     *
     * @return string
     */
    public function getJoinType()
    {
        return $this->joinType;
    }

    /**
     * Sets the join type (JOIN, LEFT JOIN, RIGHT JOIN, etc...).
     *
     * @Important
     *
     * @param string $joinType
     */
    public function setJoinType($joinType)
    {
        $this->joinType = $joinType;
    }

    private $refClause;

    /**
     * Returns the list of refClause statements.
     *
     * @return NodeInterface[]|NodeInterface
     */
    public function getRefClause()
    {
        return $this->refClause;
    }

    /**
     * Sets the list of refClause statements.
     *
     * @Important
     *
     * @param NodeInterface[]|NodeInterface $refClause
     */
    public function setRefClause($refClause)
    {
        $this->refClause = $refClause;
    }

    /**
     * Returns a Mouf instance descriptor describing this object.
     *
     * @param MoufManager $moufManager
     *
     * @return MoufInstanceDescriptor
     */
    public function toInstanceDescriptor(MoufManager $moufManager)
    {
        $instanceDescriptor = $moufManager->createInstance(get_called_class());
        $instanceDescriptor->getProperty('table')->setValue($this->table);
        $instanceDescriptor->getProperty('alias')->setValue($this->alias);
        $instanceDescriptor->getProperty('joinType')->setValue($this->joinType);
        $instanceDescriptor->getProperty('refClause')->setValue(NodeFactory::nodeToInstanceDescriptor($this->refClause, $moufManager));

        return $instanceDescriptor;
    }

    /**
     * Renders the object as a SQL string.
     *
     * @param Connection $dbConnection
     * @param array      $parameters
     * @param number     $indent
     * @param int        $conditionsMode
     *
     * @return string
     */
    public function toSql(array $parameters = array(), Connection $dbConnection = null, $indent = 0, $conditionsMode = self::CONDITION_APPLY)
    {
        $sql = '';
        if ($this->refClause) {
            $sql .= "\n  ".$this->joinType.' ';
        }
        $sql .= NodeFactory::escapeDBItem($this->table, $dbConnection);
        if ($this->alias) {
            $sql .= ' AS '.NodeFactory::escapeDBItem($this->alias, $dbConnection);
        }
        if ($this->refClause) {
            $sql .= ' ON ';
            $sql .= NodeFactory::toSql($this->refClause, $dbConnection, $parameters, ' ', true, $indent, $conditionsMode);
        }

        return $sql;
    }
}
