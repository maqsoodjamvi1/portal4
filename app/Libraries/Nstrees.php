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

    /* ******************************************************************* */
    /* Tree Constructors */
    /* ******************************************************************* */

    function nstNewRoot($othercols)
    {
        $newnode['l'] = 1;
        $newnode['r'] = 2;
        $newnode['root_id'] = 0;
        $this->db->query('LOCK TABLE ' . $this->thandle['table'] . ' WRITE');
        $this->_insertNew($newnode, $othercols);
        $new_root_id = $this->db->insertID();
        $this->db->table($this->thandle['table'])
                 ->where('id', $new_root_id)
                 ->update(['root_id' => $new_root_id]);
        $this->db->query('UNLOCK TABLES');
        $newnode['root_id'] = $new_root_id;
        return $newnode;
    }

    function nstNewFirstChild($node, $othercols)
    {
        $newnode['l'] = $node['l'] + 1;
        $newnode['r'] = $node['l'] + 2;
        $newnode['root_id'] = $node['root_id'];
        $this->db->query('LOCK TABLE ' . $this->thandle['table'] . ' WRITE');
        $this->_shiftRLValues($newnode['l'], 2, $newnode['root_id']);
        $this->_insertNew($newnode, $othercols);
        $this->db->query('UNLOCK TABLES');
        return $newnode;
    }

    function nstNewLastChild($node, $othercols)
    {
        $newnode['l'] = $node['r'];
        $newnode['r'] = $node['r'] + 1;
        $newnode['root_id'] = $node['root_id'];
        $this->db->query('LOCK TABLE ' . $this->thandle['table'] . ' WRITE');
        $this->_shiftRLValues($newnode['l'], 2, $newnode['root_id']);
        $this->_insertNew($newnode, $othercols);
        $this->db->query('UNLOCK TABLES');
        return $newnode;
    }

    function nstNewPrevSibling($node, $othercols)
    {
        $newnode['l'] = $node['l'];
        $newnode['r'] = $node['l'] + 1;
        $newnode['root_id'] = $node['root_id'];
        $this->db->query('LOCK TABLE ' . $this->thandle['table'] . ' WRITE');
        $this->_shiftRLValues($newnode['l'], 2, $newnode['root_id']);
        $this->_insertNew($newnode, $othercols);
        $this->db->query('UNLOCK TABLES');
        return $newnode;
    }

    function nstNewNextSibling($node, $othercols)
    {
        $newnode['l'] = $node['r'] + 1;
        $newnode['r'] = $node['r'] + 2;
        $newnode['root_id'] = $node['root_id'];
        $this->db->query('LOCK TABLE ' . $this->thandle['table'] . ' WRITE');
        $this->_shiftRLValues($newnode['l'], 2, $newnode['root_id']);
        $this->_insertNew($newnode, $othercols);
        $this->db->query('UNLOCK TABLES');
        return $newnode;
    }

    /* *** internal routines *** */

    private function _shiftRLValues($first, $delta, $root_id)
    {
        $this->db->query("UPDATE " . $this->thandle['table'] . " SET " . 
            $this->thandle['lvalname'] . " = " . $this->thandle['lvalname'] . " + $delta 
            WHERE " . $this->thandle['lvalname'] . " >= $first 
            AND root_id = " . $root_id);
            
        $this->db->query("UPDATE " . $this->thandle['table'] . " SET " . 
            $this->thandle['rvalname'] . " = " . $this->thandle['rvalname'] . " + $delta 
            WHERE " . $this->thandle['rvalname'] . " >= $first 
            AND root_id = " . $root_id);
    }

    private function _shiftRLRange($first, $last, $delta, $root_id)
    {
        $this->db->query("UPDATE " . $this->thandle['table'] . " SET " . 
            $this->thandle['lvalname'] . " = " . $this->thandle['lvalname'] . " + $delta 
            WHERE " . $this->thandle['lvalname'] . " BETWEEN $first AND $last 
            AND root_id = " . $root_id);
            
        $this->db->query("UPDATE " . $this->thandle['table'] . " SET " . 
            $this->thandle['rvalname'] . " = " . $this->thandle['rvalname'] . " + $delta 
            WHERE " . $this->thandle['rvalname'] . " BETWEEN $first AND $last 
            AND root_id = " . $root_id);
            
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
            $this->db->query("INSERT INTO " . $this->thandle['table'] . " SET 
                " . $othercols . "
                " . $this->thandle['lvalname'] . " = " . $node['l'] . ",
                " . $this->thandle['rvalname'] . " = " . $node['r'] . ",
                root_id = " . $node['root_id']);
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
        $this->db->query('LOCK TABLE ' . $this->thandle['table'] . ' WRITE');
        
        if ($parent != 0) {
            $this->db->query('UPDATE ' . $this->thandle['table'] . ' 
                SET parent_id = ' . $parent . ' 
                WHERE id = ' . $src['id']);
        }
        
        $treesize = $src['r'] - $src['l'] + 1;
        $this->_shiftRLValues($to, $treesize, $src['root_id']);
        
        if ($src['l'] >= $to) {
            $src['l'] += $treesize;
            $src['r'] += $treesize;
        }
        
        $newpos = $this->_shiftRLRange($src['l'], $src['r'], $to - $src['l'], $src['root_id']);
        $this->_shiftRLValues($src['r'] + 1, -$treesize, $src['root_id']);
        
        $this->db->query('UNLOCK TABLES');
        
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
        $this->db->query('LOCK TABLE ' . $this->thandle['table'] . ' WRITE');
        
        $this->db->table($this->thandle['table'])
                 ->where($this->thandle['lvalname'] . ' >=', $node['l'])
                 ->where($this->thandle['rvalname'] . ' <=', $node['r'])
                 ->where('root_id', $node['root_id'])
                 ->delete();
                 
        $this->_shiftRLValues($node['r'] + 1, $node['l'] - $node['r'] - 1, $node['root_id']);
        $this->db->query('UNLOCK TABLES');
        
        return $this->nstGetNodeWhere(
            $this->thandle['lvalname'] . " < $leftanchor
            AND root_id = " . $node['root_id'] . "
            ORDER BY " . $this->thandle['lvalname'] . " DESC"
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
        $query = $this->db->query("SELECT COUNT(*) AS level 
            FROM " . $this->thandle['table'] . " 
            WHERE " . $this->thandle['lvalname'] . " < " . $node['l'] . " 
            AND " . $this->thandle['rvalname'] . " > " . $node['r'] . " 
            AND root_id = " . $node['root_id']);
            
        $row = $query->getRowArray();
        return $row ? $row['level'] : 0;
    }

    /* ******************************************************************* */
    /* Tree Walks */
    /* ******************************************************************* */

    function nstWalkPreorder($node, $root = false)
    {
        if ($root) {
            $sql = "SELECT (COUNT(parent.id) - 1 AS depth, node.* 
                FROM " . $this->thandle['table'] . " AS node,
                " . $this->thandle['table'] . " AS parent 
                WHERE node." . $this->thandle['lvalname'] . " 
                BETWEEN parent." . $this->thandle['lvalname'] . " 
                AND parent." . $this->thandle['rvalname'] . " 
                AND parent.root_id = " . $node['root_id'] . " 
                AND node.root_id = " . $node['root_id'] . " 
                GROUP BY node.id 
                ORDER BY node." . $this->thandle['lvalname'];
        } else {
            $sql = "SELECT (COUNT(parent.id) - 1 AS depth, node.* 
                FROM " . $this->thandle['table'] . " AS node,
                " . $this->thandle['table'] . " AS parent 
                WHERE node." . $this->thandle['lvalname'] . " 
                BETWEEN parent." . $this->thandle['lvalname'] . " 
                AND parent." . $this->thandle['rvalname'] . " 
                AND parent.root_id = " . $node['root_id'] . " 
                AND node.root_id = " . $node['root_id'] . " 
                GROUP BY node.id 
                HAVING node.parent_id <> 0 
                ORDER BY node." . $this->thandle['lvalname'];
        }
        
        $query = $this->db->query($sql);
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