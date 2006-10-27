<?php
/**
* copyright            : (C) 2001-2004 Advanced Internet Designs Inc.
* email                : forum@prohost.org
* $Id: cat.inc,v 1.32 2005/04/09 17:33:55 hackie Exp $
*
* This program is free software; you can redistribute it and/or modify it
* under the terms of the GNU General Public License as published by the
* Free Software Foundation; either version 2 of the License, or
* (at your option) any later version.
**/

class fud_cat
{
	var $id, $name, $description, $view_order, $cat_opt, $parent;

	function fud_cat()
	{
		foreach ($this as $k => $v) {
			if (isset($_POST['cat_'.$k])) {
				$this->{$k} = $_POST['cat_'.$k];
			}
		}
	}

	function add($pos)
	{
		if (!db_locked()) {
			$ll = 1;
			db_lock($GLOBALS['DBHOST_TBL_PREFIX'].'cat WRITE');
		}

		if ($pos == 'LAST') {
			$this->view_order = (int) q_singleval('SELECT MAX(view_order) FROM '.$GLOBALS['DBHOST_TBL_PREFIX'].'cat WHERE parent='.(int)$this->parent) + 1;
		} else {
			$this->view_order = 1;
			q('UPDATE '.$GLOBALS['DBHOST_TBL_PREFIX'].'cat SET view_order=view_order+1 WHERE parent='.(int)$this->parent);
		}

		$this->id = db_qid("INSERT INTO ".$GLOBALS['DBHOST_TBL_PREFIX']."cat (name, description, view_order, cat_opt, parent) VALUES ('".addslashes($this->name)."', '".addslashes($this->description)."', ".$this->view_order.", ".$this->cat_opt.", ".(int)$this->parent.")");

		if (isset($ll)) {
			db_unlock();
		}

		return $this->id;
	}

	function sync($id)
	{
		q("UPDATE ".$GLOBALS['DBHOST_TBL_PREFIX']."cat SET parent=".(int)$this->parent.", name='".addslashes($this->name)."',description='".addslashes($this->description)."', cat_opt=".$this->cat_opt." WHERE id=".$id);
		cat_rebuild_order();
	}
}

function cat_rebuild_order()
{
	$old_p = -1;
	$c = q('SELECT id, view_order, parent FROM '.$GLOBALS['DBHOST_TBL_PREFIX'].'cat ORDER BY parent, view_order');
	while ($r = db_rowarr($c)) {
		if ($r[2] != $old_p) {
			$i = 1;
			$old_p = $r[2];
		}
		if ($i != $r[1]) {
			q('UPDATE '.$GLOBALS['DBHOST_TBL_PREFIX'].'cat SET view_order='.$i.' WHERE id='.$r[0]);
		}
		++$i;
	}
	unset($c);
}

function cat_change_pos($oldp, $newp, $parent)
{
	if ($oldp == $newp || !$newp) {
		return;
	}

	db_lock($GLOBALS['DBHOST_TBL_PREFIX'].'cat WRITE');
	q('UPDATE '.$GLOBALS['DBHOST_TBL_PREFIX'].'cat SET view_order=2147483647 WHERE view_order='.$oldp.' AND parent='.(int)$parent);
	if ($oldp < $newp) {
		q('UPDATE '.$GLOBALS['DBHOST_TBL_PREFIX'].'cat SET view_order=view_order-1 WHERE view_order<='.$newp.' AND view_order>'.$oldp.' AND parent='.(int)$parent);
		$maxp = q_singleval('SELECT MAX(view_order) FROM '.$GLOBALS['DBHOST_TBL_PREFIX'].'cat WHERE view_order!=2147483647 AND parent='.(int)$parent);
		if ($newp > $maxp) {
			$newp = $maxp + 1;
		}
	} else {
		q('UPDATE '.$GLOBALS['DBHOST_TBL_PREFIX'].'cat SET view_order=view_order+1 WHERE view_order<'.$oldp.' AND view_order>='.$newp.' AND parent='.(int)$parent);
	}
	q('UPDATE '.$GLOBALS['DBHOST_TBL_PREFIX'].'cat SET view_order='.$newp.' WHERE view_order=2147483647 AND parent='.(int)$parent);
	db_unlock();
}

function rebuild_forum_cat_order()
{
	$tbl =& $GLOBALS['DBHOST_TBL_PREFIX'];

	if (!db_locked()) {
		$ll = 1;
		db_lock($tbl.'cat c WRITE,'.$tbl.'fc_view WRITE,'.$tbl.'forum f WRITE');
	}

	q("DELETE FROM ".$tbl."fc_view");

	$max_depth = q_singleval("SELECT max(c.parent) FROM ".$tbl."cat c");

	/* we use left join to catch categories without any forums, but with sub-categories */
	$c = q("SELECT c.id, f.id, c.parent, c.name, (c.view_order * POW(10, ".$max_depth." - c.parent)) 
			FROM ".$tbl."cat c 
			LEFT JOIN ".$tbl."forum f ON f.cat_id=c.id
			ORDER BY c.parent, c.view_order, f.view_order");
	
	$lvl = $cat_cache = $cat_par = $p = $data = array();
	while ($r = db_rowarr($c)) {
		/* this is slow, but we have no other way of avoiding int overflows in PHP on 32bit machines */
		$val = $p[$r[0]] = q_singleval("SELECT ".$r[4]." + ".($r[2] ? $p[$r[2]] : 0));

		$cat_par[$r[0]] = (int) $r[2];

		if (isset($data[$val])) {
			$data[$val][] = $r;
		} else {
			$data[$val] = array($r);
		}

		if (!$r[2]) {
			$lvl[$r[0]] = 0;
		} else {
			$lvl[$r[0]] = $lvl[$r[2]] + 1;
		}
	}
	ksort($data);

	foreach ($data as $r) {
		foreach ($r as $v) {
			if ($v[1]) {		
				q("INSERT INTO ".$tbl."fc_view (c, f, lvl) VALUES(".$v[0].",".$v[1].",".$lvl[$v[0]].")");
			}
			if (isset($cat_cache[$v[0]])) {
				continue;
			}
			$cat_cache[$v[0]] = array($lvl[$v[0]], htmlspecialchars($v[3]));
			if ($v[2]) {
				if (!isset($cat_cache[$v[2]][2])) {
					$cat_cache[$v[2]][2] = array();
				}
				$cat_cache[$v[2]][2][] = $v[0];
			}
		}
	}
	unset($data, $lvl, $p);

	/* create full child/parent associations */
	end($cat_cache);
	while (($v = prev($cat_cache))) {
		if (isset($v[2])) {
			$children = array();
			foreach ($v[2] as $c) {
				$children[] = $c;
				if (!empty($cat_cache[$c][2])) {
					$children = array_merge($children, $cat_cache[$c][2]);
				}
			}
			$cat_cache[key($cat_cache)][2] = $children;
		}
	}

	$cache_file = $GLOBALS['FORUM_SETTINGS_PATH'] . 'cat_cache.inc';
	$fp = fopen($cache_file.'.tmp', "w");
	fwrite($fp, '<?php $cat_cache = '.var_export($cat_cache, 1).'; $cat_par = '.var_export($cat_par, 1).'; ?>');
	fclose($fp);
	
	if (!strncasecmp('win', PHP_OS, 3)) {
		@unlink($cache_file);
	}
	rename($cache_file.'.tmp', $cache_file);

	/* now we build a special cache specifically for the front page */
	$c = uq("SELECT 0, name, description, cat_opt, parent, id FROM ".$tbl."cat c");
	while ($r = db_rowarr($c)) {
		$cid = (int) array_pop($r);

		$ch = array($cid => $cid);
		if (isset($cat_cache[$cid][2])) {
			foreach ($cat_cache[$cid][2] as $v) {
				$ch[$v] = $v;
			}	
		}
		$lvl = $cat_cache[$cid][0];

		$cat_cache[$cid] = $r;
		$cat_cache[$cid][0] = $lvl;
		$cat_cache[$cid][] = $ch;
	}

	$cache_file = $GLOBALS['FORUM_SETTINGS_PATH'] . 'idx.inc';
	$fp = fopen($cache_file.'.tmp', "w");
	fwrite($fp, '<?php $cidxc = '.var_export($cat_cache, 1).'; ?>');
	fclose($fp);
	if (!strncasecmp('win', PHP_OS, 3)) {
		@unlink($cache_file);
	}
	rename($cache_file.'.tmp', $cache_file);

	if (isset($ll)) {
		db_unlock();
	}
}
?>