<?php

namespace App\Libraries;

use CodeIgniter\Database\BaseConnection;
use Config\Database;

/**
 * Nstrees - Nested Set Tree Library (CI4 Version)
 * 
 * Author: Chaegumi (ported from CI3)
 * Based on original by Rolf Brugger, edutech
 */
class Nstrees
{
    private $db;
    private $thandle;

    function __construct($options = array())
    {
        $this->db = Database::connect();
        $this->thandle = $options;
    }

    private function tableName(): string
    {
        $table = (string) ($this->thandle['table'] ?? '');
        if (! preg_match('/^[a-z][a-z0-9_]*$/', $table)) {
            throw new \InvalidArgumentException('Invalid nested-set table name');
        }

        return $table;
    }

    private function columnName(string $key): string
    {
        $column = (string) ($this->thandle[$key] ?? '');
        if (! preg_match('/^[a-z][a-z0-9_]*$/', $column)) {
            throw new \InvalidArgumentException('Invalid nested-set column name');
        }

        return $column;
    }

    private function lockTableWrite(): void
    {
        $this->db->query('LOCK TABLE ' . $this->tableName() . ' WRITE');
    }

    private function unlockTables(): void
    {
        $this->unlockTables();
    }

    /* ******************************************************************* */
    /* Tree Constructors */
    /* ******************************************************************* */

    function nstNewRoot($othercols)
    {
        $newnode['l'] = 1;
        $newnode['r'] = 2;
        $newnode['root_id'] = 0;
        $this->lockTableWrite();
        $this->_insertNew($newnode, $othercols);
        $new_root_id = $this->db->insertID();
        $this->db->table($this->thandle['table'])
                 ->where('id', $new_root_id)
                 ->update(['root_id' => $new_root_id]);
        $this->unlockTables();
        $newnode['root_id'] = $new_root_id;
        return $newnode;
    }

    function nstNewFirstChild($node, $othercols)
    {
        $newnode['l'] = $node['l'] + 1;
        $newnode['r'] = $node['l'] + 2;
        $newnode['root_id'] = $node['root_id'];
        $this->lockTableWrite();
        $this->_shiftRLValues($newnode['l'], 2, $newnode['root_id']);
        $this->_insertNew($newnode, $othercols);
        $this->unlockTables();
        return $newnode;
    }

    function nstNewLastChild($node, $othercols)
    {
        $newnode['l'] = $node['r'];
        $newnode['r'] = $node['r'] + 1;
        $newnode['root_id'] = $node['root_id'];
        $this->lockTableWrite();
        $this->_shiftRLValues($newnode['l'], 2, $newnode['root_id']);
        $this->_insertNew($newnode, $othercols);
        $this->unlockTables();
        return $newnode;
    }

    function nstNewPrevSibling($node, $othercols)
    {
        $newnode['l'] = $node['l'];
        $newnode['r'] = $node['l'] + 1;
        $newnode['root_id'] = $node['root_id'];
        $this->lockTableWrite();
        $this->_shiftRLValues($newnode['l'], 2, $newnode['root_id']);
        $this->_insertNew($newnode, $othercols);
        $this->unlockTables();
        return $newnode;
    }

    function nstNewNextSibling($node, $othercols)
    {
        $newnode['l'] = $node['r'] + 1;
        $newnode['r'] = $node['r'] + 2;
        $newnode['root_id'] = $node['root_id'];
        $this->lockTableWrite();
        $this->_shiftRLValues($newnode['l'], 2, $newnode['root_id']);
        $this->_insertNew($newnode, $othercols);
        $this->unlockTables();
        return $newnode;
    }

    /* *** internal routines *** */

    private function _shiftRLValues($first, $delta, $root_id)
    {
        $table = $this->tableName();
        $lcol  = $this->columnName('lvalname');
        $rcol  = $this->columnName('rvalname');
        $first = (int) $first;
        $delta = (int) $delta;
        $root_id = (int) $root_id;

        $this->db->query(
            "UPDATE {$table} SET {$lcol} = {$lcol} + ? WHERE {$lcol} >= ? AND root_id = ?",
            [$delta, $first, $root_id]
        );

        $this->db->query(
            "UPDATE {$table} SET {$rcol} = {$rcol} + ? WHERE {$rcol} >= ? AND root_id = ?",
            [$delta, $first, $root_id]
        );
    }

    private function _shiftRLRange($first, $last, $delta, $root_id)
    {
        $table = $this->tableName();
        $lcol  = $this->columnName('lvalname');
        $rcol  = $this->columnName('rvalname');
        $first = (int) $first;
        $last  = (int) $last;
        $delta = (int) $delta;
        $root_id = (int) $root_id;

        $this->db->query(
            "UPDATE {$table} SET {$lcol} = {$lcol} + ? WHERE {$lcol} BETWEEN ? AND ? AND root_id = ?",
            [$delta, $first, $last, $root_id]
        );

        $this->db->query(
            "UPDATE {$table} SET {$rcol} = {$rcol} + ? WHERE {$rcol} BETWEEN ? AND ? AND root_id = ?",
            [$delta, $first, $last, $root_id]
        );

        return ['l' => $first + $delta, 'r' => $last + $delta];
    }

    private function _insertNew($node, $othercols)
    {
        if (is_array($othercols)) {
            $newdata = [
                $this->thandle['lvalname'] => $node['l'],
                $this->thandle['rvalname'] => $node['r'],
                'root_id' => $node['root_id']
            ];
            $newdata = array_merge($newdata, $othercols);
            $this->db->table($this->thandle['table'])->insert($newdata);
        } else {
            $table = $this->tableName();
            $lcol  = $this->columnName('lvalname');
            $rcol  = $this->columnName('rvalname');
            $this->db->query(
                "INSERT INTO {$table} SET {$othercols} {$lcol} = ?, {$rcol} = ?, root_id = ?",
                [(int) $node['l'], (int) $node['r'], (int) $node['root_id']]
            );
        }
    }

    /* ******************************************************************* */
    /* Tree Reorganization */
    /* ******************************************************************* */

    function nstMoveToNextSibling($src, $dst)
    {
        return $this->_moveSubtree($src, $dst['r'] + 1, $dst['parent_id']);
    }

    function nstMoveToPrevSibling($src, $dst)
    {
        return $this->_moveSubtree($src, $dst['l']);
    }

    function nstMoveToFirstChild($src, $dst)
    {
        return $this->_moveSubtree($src, $dst['l'] + 1);
    }

    function nstMoveToLastChild($src, $dst)
    {
        return $this->_moveSubtree($src, $dst['r'], $dst['parent_id']);
    }

    private function _moveSubtree($src, $to, $parent = 0)
    {
        $this->lockTableWrite();
        
        if ($parent != 0) {
            $this->db->table($this->tableName())
                ->where('id', (int) $src['id'])
                ->update(['parent_id' => (int) $parent]);
        }
        
        $treesize = $src['r'] - $src['l'] + 1;
        $this->_shiftRLValues($to, $treesize, $src['root_id']);
        
        if ($src['l'] >= $to) {
            $src['l'] += $treesize;
            $src['r'] += $treesize;
        }
        
        $newpos = $this->_shiftRLRange($src['l'], $src['r'], $to - $src['l'], $src['root_id']);
        $this->_shiftRLValues($src['r'] + 1, -$treesize, $src['root_id']);
        
        $this->unlockTables();
        
        if ($src['l'] <= $to) {
            $newpos['l'] -= $treesize;
            $newpos['r'] -= $treesize;
        }
        
        return $newpos;
    }

    /* ******************************************************************* */
    /* Tree Destructors */
    /* ******************************************************************* */

    function nstDeleteTree($node)
    {
        $this->db->table($this->thandle['table'])
                 ->where('root_id', $node['root_id'])
                 ->delete();
    }

    function nstDelete($node)
    {
        $leftanchor = $node['l'];
        $this->lockTableWrite();
        
        $this->db->table($this->thandle['table'])
                 ->where($this->thandle['lvalname'] . ' >=', $node['l'])
                 ->where($this->thandle['rvalname'] . ' <=', $node['r'])
                 ->where('root_id', $node['root_id'])
                 ->delete();
                 
        $this->_shiftRLValues($node['r'] + 1, $node['l'] - $node['r'] - 1, $node['root_id']);
        $this->unlockTables();
        
        return $this->nstGetNodeWhere(
            $this->columnName('lvalname') . ' < ' . (int) $leftanchor
            . ' AND root_id = ' . (int) $node['root_id']
            . ' ORDER BY ' . $this->columnName('lvalname') . ' DESC'
        );
    }

    /* ******************************************************************* */
    /* Tree Queries */
    /* ******************************************************************* */

    function nstGetNodeWhere($whereclause, $root_id = 0)
    {
        $noderes = ['l' => 0, 'r' => 0, 'root_id' => $root_id];
        $builder = $this->db->table($this->thandle['table']);
        
        if ($root_id != 0) {
            $builder->where('root_id', $root_id);
        }
        
        $query = $builder->where($whereclause, null, false)
                         ->get();
        
        if ($row = $query->getRowArray()) {
            $noderes = $row;
            $noderes['l'] = $row[$this->thandle['lvalname']];
            $noderes['r'] = $row[$this->thandle['rvalname']];
            $noderes['root_id'] = $row['root_id'];
        }
        
        return $noderes;
    }

    function nstGetNodeWhereLeft($node, $leftval)
    {
        return $this->nstGetNodeWhere(
            $this->thandle['lvalname'] . " = $leftval 
            AND root_id = " . $node['root_id']
        );
    }

    function nstGetNodeWhereRight($node, $rightval)
    {
        return $this->nstGetNodeWhere(
            $this->thandle['rvalname'] . " = $rightval 
            AND root_id = " . $node['root_id']
        );
    }

    function nstRoot($root_id)
    {
        return $this->nstGetNodeWhere(
            $this->thandle['lvalname'] . " = 1 
            AND root_id = " . $root_id
        );
    }

    function nstFirstChild($node)
    {
        return $this->nstGetNodeWhere(
            $this->thandle['lvalname'] . " = " . ($node['l'] + 1) . " 
            AND root_id = " . $node['root_id']
        );
    }

    function nstLastChild($node)
    {
        return $this->nstGetNodeWhere(
            $this->thandle['rvalname'] . " = " . ($node['r'] - 1) . " 
            AND root_id = " . $node['root_id']
        );
    }

    function nstPrevSibling($node)
    {
        return $this->nstGetNodeWhere(
            $this->thandle['rvalname'] . " = " . ($node['l'] - 1) . " 
            AND root_id = " . $node['root_id']
        );
    }

    function nstNextSibling($node)
    {
        return $this->nstGetNodeWhere(
            $this->thandle['lvalname'] . " = " . ($node['r'] + 1) . " 
            AND root_id = " . $node['root_id']
        );
    }

    function nstAncestor($node)
    {
        return $this->nstGetNodeWhere(
            $this->thandle['lvalname'] . " < " . $node['l'] . " 
            AND " . $this->thandle['rvalname'] . " > " . $node['r'] . " 
            AND root_id = " . $node['root_id'] . " 
            ORDER BY " . $this->thandle['rvalname'] . " ASC"
        );
    }

    /* ******************************************************************* */
    /* Tree Functions */
    /* ******************************************************************* */

    function nstValidNode($node)
    {
        return ($node['l'] < $node['r']);
    }

    function nstHasAncestor($node)
    {
        return $this->nstValidNode($this->nstAncestor($node));
    }

    function nstHasPrevSibling($node)
    {
        return $this->nstValidNode($this->nstPrevSibling($node));
    }

    function nstHasNextSibling($node)
    {
        return $this->nstValidNode($this->nstNextSibling($node));
    }

    function nstHasChildren($node)
    {
        return (($node['r'] - $node['l']) > 1);
    }

    function nstIsRoot($node)
    {
        return ($node['l'] == 1);
    }

    function nstIsLeaf($node)
    {
        return (($node['r'] - $node['l']) == 1);
    }

    function nstIsChild($node1, $node2)
    {
        return (($node1['l'] > $node2['l']) && ($node1['r'] < $node2['r']));
    }

    function nstIsChildOrEqual($node1, $node2)
    {
        return (($node1['l'] >= $node2['l']) && ($node1['r'] <= $node2['r']));
    }

    function nstEqual($node1, $node2)
    {
        return (($node1['l'] == $node2['l']) && ($node1['r'] == $node2['r']));
    }

    /* ******************************************************************* */
    /* Tree Functions */
    /* ******************************************************************* */

    function nstNbChildren($node)
    {
        return (($node['r'] - $node['l'] - 1) / 2);
    }

    function nstLevel($node)
    {
        $table = $this->tableName();
        $lcol  = $this->columnName('lvalname');
        $rcol  = $this->columnName('rvalname');
        $query = $this->db->query(
            "SELECT COUNT(*) AS level FROM {$table}
            WHERE {$lcol} < ? AND {$rcol} > ? AND root_id = ?",
            [(int) $node['l'], (int) $node['r'], (int) $node['root_id']]
        );
            
        $row = $query->getRowArray();
        return $row ? $row['level'] : 0;
    }

    /* ******************************************************************* */
    /* Tree Walks */
    /* ******************************************************************* */

    function nstWalkPreorder($node, $root = false)
    {
        $table = $this->tableName();
        $lcol  = $this->columnName('lvalname');
        $rcol  = $this->columnName('rvalname');
        $rootId = (int) $node['root_id'];
        $binds = [$rootId, $rootId];

        if ($root) {
            $sql = "SELECT (COUNT(parent.id) - 1) AS depth, node.*
                FROM {$table} AS node, {$table} AS parent
                WHERE node.{$lcol} BETWEEN parent.{$lcol} AND parent.{$rcol}
                AND parent.root_id = ? AND node.root_id = ?
                GROUP BY node.id
                ORDER BY node.{$lcol}";
        } else {
            $sql = "SELECT (COUNT(parent.id) - 1) AS depth, node.*
                FROM {$table} AS node, {$table} AS parent
                WHERE node.{$lcol} BETWEEN parent.{$lcol} AND parent.{$rcol}
                AND parent.root_id = ? AND node.root_id = ?
                GROUP BY node.id
                HAVING node.parent_id <> 0
                ORDER BY node.{$lcol}";
        }

        $query = $this->db->query($sql, $binds);
        $resultArray = $query->getResultArray();
        
        return [
            'recset' => $resultArray,
            'currentIndex' => 0,
            'prevl' => $node['l'],
            'prevr' => $node['r'],
            'level' => -2
        ];
    }

    function nstWalkNext(&$walkhand)
    {
        if (!isset($walkhand['recset'][$walkhand['currentIndex']])) {
            return false;
        }
        
        $row = $walkhand['recset'][$walkhand['currentIndex']];
        $walkhand['level'] += $walkhand['prevl'] - $row[$this->thandle['lvalname']] + 2;
        $walkhand['prevl'] = $row[$this->thandle['lvalname']];
        $walkhand['prevr'] = $row[$this->thandle['rvalname']];
        $walkhand['row'] = $row;
        $walkhand['currentIndex']++;
        
        return [
            'l' => $row[$this->thandle['lvalname']],
            'r' => $row[$this->thandle['rvalname']]
        ];
    }

    function nstWalkAttribute($walkhand, $attribute)
    {
        return $walkhand['row'][$attribute] ?? null;
    }

    function nstWalkCurrent($walkhand)
    {
        return [
            'l' => $walkhand['prevl'],
            'r' => $walkhand['prevr']
        ];
    }

    function nstWalkLevel($walkhand)
    {
        return $walkhand['level'];
    }

    /* ******************************************************************* */
    /* Printing Tools */
    /* ******************************************************************* */

    function nstNodeAttribute($node, $attribute)
    {
        $query = $this->db->table($this->thandle['table'])
                          ->where($this->thandle['lvalname'], $node['l'])
                          ->where('root_id', $node['root_id'])
                          ->get();
        
        $row = $query->getRowArray();
        return $row[$attribute] ?? '';
    }

    function nstPrintSubtree($node, $attributes)
    {
        $wlk = $this->nstWalkPreorder($node, true);
        $depth = 0;
        $html = '';
        
        foreach ($wlk['recset'] as $row) {
            $newDepth = (int)$row['depth'];
            
            // Handle depth changes
            if ($newDepth > $depth) {
                $html .= str_repeat('<ul><li>', $newDepth - $depth);
            } elseif ($newDepth < $depth) {
                $html .= str_repeat('</li></ul>', $depth - $newDepth);
                $html .= '</li><li>';
            } else {
                $html .= $depth > 0 ? '</li><li>' : '<ul><li>';
            }
            
            $depth = $newDepth;
            
            // Print attributes
            $html .= '<a href="#">';
            foreach ($attributes as $att) {
                $html .= $row[$att] . ' ';
            }
            $html .= '</a>';
        }
        
        // Close remaining tags
        if ($depth > 0) {
            $html .= str_repeat('</li></ul>', $depth);
        }
        
        return $html;
    }

    function nstPrintTree($node, $attributes)
    {
        $root = $this->nstRoot($node['root_id']);
        return $this->nstPrintSubtree($root, $attributes);
    }

    function nstBreadcrumbsString($node)
    {
        $ret = $this->nstNodeAttribute($node, "name");
        $current = $node;
        
        while ($this->nstHasAncestor($current)) {
            $ancestor = $this->nstAncestor($current);
            $ret = $this->nstNodeAttribute($ancestor, "name") . ' &gt; ' . $ret;
            $current = $ancestor;
        }
        
        return $ret;
    }

    /* ******************************************************************* */
    /* Error Handling */
    /* ******************************************************************* */

    private function _prtError()
    {
        $error = $this->db->error();
        log_message('error', "Database error: {$error['code']} - {$error['message']}");
    }
}