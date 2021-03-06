<?php
#BEGIN_LICENSE
#-------------------------------------------------------------------------
# Module: DocumentSearch (c) 2013 by Oliver Seddon 
#         (oliver@threefold.co.uk)
#  An addon module for CMS Made Simple to provide PDF and Word document
#  content searchability.
# 
#-------------------------------------------------------------------------
# CMS - CMS Made Simple is (c) 2005 by Ted Kulp (wishy@cmsmadesimple.org)
# This project's homepage is: http://www.cmsmadesimple.org
#
#-------------------------------------------------------------------------
#
# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
#
# However, as a special exception to the GPL, this software is distributed
# as an addon module to CMS Made Simple.  You may not use this software
# in any Non GPL version of CMS Made simple, or in any version of CMS
# Made simple that does not indicate clearly and obviously in its admin 
# section that the site was built with CMS Made simple.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
# Or read it online: http://www.gnu.org/licenses/licenses.html#GPL
#
#-------------------------------------------------------------------------
#END_LICENSE

final class documentsearch_ops {

	protected function __construct() {}

	private static $_categories_loaded;
	private static $_cached_categories;

	public static function get_categories($id,$params,$returnid=-1) {
    	$tmp = self::get_all_categories();
    	if( !count($tmp) ) return;

    	$catinfo = array();
    	if( !isset($params['category']) || $params['category'] == '' ) {
	  		$catinfo = $tmp;
    	} else {
	  		$categories = explode(',', $params['category']);
	  		for( $i = 0; $i < count($tmp); $i++ ) {
	    	if( !count($categories) || in_array($tmp[$i]['documentsearch_category_name'],$categories) ) {
		  		$catinfo[] = $tmp[$i];
	    	}
	  	}
    }

    unset($tmp);
    if( !count($catinfo) ) return;

    $cat_ids = array();
    for( $i = 0; $i < count($catinfo); $i++ ) {
	  $cat_ids[] = $catinfo[$i]['documentsearch_category_id'];
    }
    sort($cat_ids);
    $cat_ids = array_unique($cat_ids);

    // get counts.
    $depth = 1;
    $db = cmsms()->GetDb();
    $counts = array();  {
      $q2 = 'SELECT documentsearch_category_id,COUNT(documentsearch_id) AS cnt FROM '.cms_db_prefix().'module_documentsearch WHERE documentsearch_category_id IN (';
      $q2 .= implode(',',$cat_ids).')';
      $q2 .= ' GROUP BY documentsearch_category_id';
      $tmp = $db->GetArray($q2);
      if( count($tmp) )	{
	    for( $i = 0; $i < count($tmp); $i++ ) {
	      $counts[$tmp[$i]['documentsearch_category_id']] = $tmp[$i]['cnt'];
	    }
	  }
    }

    $rowcounter=0;
    $items = array();
    $depth = 1;
    for( $i = 0; $i < count($catinfo); $i++ ) {
	  $row =& $catinfo[$i];
	  $row['index'] = $rowcounter++;
	  $row['count'] = (isset($counts[$row['documentsearch_category_id']]))?$counts[$row['documentsearch_category_id']]:0;
	  $row['prevdepth'] = $depth;
	  $depth = count(explode('.', $row['hierarchy']));
	  $row['depth']=$depth;

	  // changes so that parameters supplied to the tag
	  // gets carried down through the links
	  // screw pretty urls
	  $parms = $params;
	  unset($parms['browsecat']);
	  unset($parms['category']);
	  $parms['category_id'] = $row['documentsearch_category_id'];

	  $pageid = (isset($params['detailpage']) && $params['detailpage']!='')?$params['detailpage']:$returnid;
	  $mod = cms_utils::get_module('DocumentSearch2');
	  $row['url'] = $mod->CreateLink($id,'summary',$pageid,$row['documentsearch_category_name'],$parms,'',true);
	  $items[] = $row;
    }
    return $items;
  }


	public static function get_all_categories() {
    	if( !self::$_categories_loaded ) {
			$db = cmsms()->GetDb();
			$query = "SELECT * FROM ".cms_db_prefix()."module_documentsearch_categories ORDER BY hierarchy";
			$dbresult = $db->GetArray($query);
			if( $dbresult ) {
	    		self::$_cached_categories = $dbresult;
	  		}
			self::$_categories_loaded = TRUE;
      	}
    	return self::$_cached_categories;
	}


	public static function get_category_list() {
    	self::get_all_categories();
    	$categorylist = array();
    	for( $i = 0; $i < count(self::$_cached_categories); $i++ ) {
			$row = self::$_cached_categories[$i];
			$categorylist[$row['long_name']] = $row['documentsearch_category_id'];
      	}

    	return $categorylist;
  	}


  	public static function get_category_names_by_id() {
    	self::get_all_categories();
    	$list = array();
    	for( $i = 0; $i < count(self::$_cached_categories); $i++ ) {
			$list[self::$_cached_categories[$i]['documentsearch_category_id']] = self::$_cached_categories[$i]['documentsearch_category_name'];
      	}
    	return $list;
  	}

  
  	public static function get_category_name_from_id($id) {
    	self::get_all_categories();
    	for( $i = 0; $i < count(self::$_cached_categories); $i++ ) {
			if( $id == self::$_cached_categories[$i]['documentsearch_category_id'] ) {
	    		return self::$_cached_categories[$i]['documentsearch_category_name'];
	  		}
      	}
  	}


	static private function &get_article_from_row($row,$get_fields = 'PUBLIC') {
    	if( !is_array($row) ) return;
    	$article = new documentsearch_article;
    	foreach( $row as $key => $value ) {
			switch( $key ) {
	  		case 'documentsearch_id':
	    		$article->id = $value;
	    		break;

	  		case 'documentsearch_category_id':
	    		$article->category_id = $value;
	    		break;

	  		case 'documentsearch_title':
	    		$article->title = $value;
	    		break;

	  		case 'documentsearch_data':
	    		$article->content = $value;
	    		break;

	  		case 'documentsearch_date':
	    		$article->postdate = $value;
	    		break;

	  		case 'summary':
	    		$article->summary = $value;

	  		case 'start_time':
	    		$article->startdate = $value;
	    		break;

	  		case 'end_time':
	    		$article->enddate = $value;
	    		break;

	  		case 'create_date':
	    		$article->create_date = $value;
	    		break;

	  		case 'modified_date':
	    		$article->modified_date = $value;
	    		break;

	  		case 'author_id':
	    		$article->author_id = $value;
	    		break;

	  		case 'documentsearch_extra':
	    		$article->extra = $value;
	    		break;

	  		case 'documentsearch_url':
	    		$article->documentsearch_url = $value;
	    		break;

	  		case 'postdate_Month':
	    		$news->postdate = mktime($params['postdate_Hour'], $params['postdate_Minute'], $params['postdate_Second'], $params['postdate_Month'], $params['postdate_Day'], $params['postdate_Year']);
	    		break;
	  		}
		}

    	if( $get_fields && $get_fields != 'NONE' && $article->id ) {
			self::preloadFieldData($article->id);
			$fields = self::get_fields($article->id);
			if( count($fields) ) {
	    		foreach( $fields as $field ) {
					$article->set_field($field);
	      		}
	  		}
		}

    	return $article;
	}

	static public function &get_latest_article($for_display = TRUE) {
    	$db = cmsms()->GetDb();
    	$now = $db->DbTimeStamp(time());
    	$query = "SELECT mn.*, mdc.documentsearch_category_name FROM ".cms_db_prefix()."module_documentsearch md LEFT OUTER JOIN ".cms_db_prefix()."module_documentsearch_categories mdc ON mdc.documentsearch_category_id = md.documentsearch_category_id WHERE ";
    	$query .= "(".$db->IfNull('start_time',$db->DBTimeStamp(1))." < $now) AND ";
    	$query .= "((".$db->IfNull('end_time',$db->DBTimeStamp(1))." = ".$db->DBTimeStamp(1).") OR (end_time > $now)) ";
    	$query .= 'ORDER BY documentsearch_date DESC LIMIT 1';
    	$row = $db->GetRow($query);

    	return self::get_article_from_row($row,($for_display)?'PUBLIC':'ALL');    
	}


	static public function &get_article_by_id($article_id,$for_display = TRUE,$allow_expired = FALSE) {
    	$db = cmsms()->GetDb();
    	$query = 'SELECT mn.*, mdc.documentsearch_category_name FROM '.cms_db_prefix().'module_documentsearch md 
        	LEFT OUTER JOIN '.cms_db_prefix().'module_documentsearch_categories mdc ON mdc.documentsearch_category_id = md.documentsearch_category_id 
            WHERE documentsearch_id = ?
            AND ('.$db->ifNull('start_time',$db->DbTimeStamp(1)).' < NOW())';
    	if( !$allow_expired ) {
      		$query .= 'AND (('.$db->ifNull('end_time',$db->DbTimeStamp(1)).' = '.$db->DbTimeStamp(1).') OR (end_time > NOW()))';
    	}
    	$row = $db->GetRow($query, array($article_id));

    	$res = null;
    	if( !$row ) return $res;
    
    	return self::get_article_from_row($row,($for_display)?'PUBLIC':'ALL');    
  	}

	public static function preloadFieldData($ids) {
    	if( !is_array($ids) && is_numeric($ids) ) {
			$ids = array($ids);
      	}

    	$tmp = array();
    	for( $i = 0; $i < count($ids); $i++ ) {
			$n = (int)$ids[$i];
			if( $n < 0 ) continue;
			if( is_array(self::$_cached_fieldvals) && isset(self::$_cached_fieldvals[$n]) ) continue; 
			$tmp[] = $n;
      	}
    	if( !is_array($tmp) || !count($tmp) ) return;
    	sort($tmp);
    	$idlist = array_unique($tmp);

    	$fielddefs = self::get_fielddefs();
    	if( !count($fielddefs) ) return;

    	$db = cmsms()->GetDb();
    	$query = 'SELECT A.documentsearch_id,A.fielddef_id,A.value FROM '.cms_db_prefix().'module_documentsearch_fieldvals A
        	INNER JOIN '.cms_db_prefix().'module_documentsearch_fielddefs B
            ON A.fielddef_id = B.id
            WHERE documentsearch_id IN ('.implode(',',$idlist).') 
            ORDER BY A.documentsearch_id,B.item_order';
    	$dbr = $db->GetArray($query);
    	if( !$dbr ) return;

    	// initialization.
    	if( !is_array(self::$_cached_fieldvals) ) self::$_cached_fieldvals = array();
    	foreach( $idlist as $documentsearch_id ) {
			if( isset(self::$_cached_fieldvals[$documentsearch_id]) ) continue;

 			self::$_cached_fieldvals[$documentsearch_id] = array();
 			foreach( $fielddefs as $field ) {
 	    		$obj = new documentsearch_field;
 	    		foreach( $field as $k => $v ) {
					$obj->$k = $v;
	      		}
 	    		self::$_cached_fieldvals[$documentsearch_id][$field['id']] = $obj;
 	  		}
      	}

    	// fill with values.
    	foreach( $dbr as $row ) {
			$documentsearch_id = $row['documentsearch_id'];
			$flddef_id = $row['fielddef_id'];
			$value = $row['value'];

			if( !isset(self::$_cached_fieldvals[$documentsearch_id][$flddef_id]) ) continue;
			self::$_cached_fieldvals[$documentsearch_id][$flddef_id]->value = $value;
      	}
  	}

	public static function get_fields($documentsearch_id,$public_only = true,$filled_only = FALSE) {
    	if( $documentsearch_id <= 0 ) return;
    	if( !isset(self::$_cached_fieldvals[$documentsearch_id]) ) return;

    	$results = array();
    	foreach( self::$_cached_fieldvals[$documentsearch_id] as $fid => $data ) {
			if( !$public_only || $data->public ) {
	    		if( !$filled_only || (isset($data->value) && $data->value != '') ) {
	      			$results[$data->name] = $data;
	    		}
	  		}
      	}
    	return $results;
  	}
} // end of class

#
# EOF
#
?>
