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

class documentsearch_admin_ops {

	protected function __construct() {}

	public static function delete_document($documentid) {
    		$db = cmsms()->GetDb();

    		//Now remove the document
    		$query = "DELETE FROM ".cms_db_prefix()."module_documentsearch WHERE documentsearch_id = ?";
    		$db->Execute($query, array($documentid));
	     	$query = "DELETE FROM ".cms_db_prefix()."module_documentsearch_categories_link WHERE documentsearch_id = ?";
		    $db->Execute($query, array($documentid));

    		// delete any files...
    		//$config = cmsms()->GetConfig;
    		$mydirectory = '../uploads/documentsearch/id'.$documentid;

    		documentsearch_admin_ops::recursive_remove_directory($mydirectory);
    
    		documentsearch_admin_ops::delete_static_route($documentid);
    
    		//Update search index
    		$mod = cms_utils::get_module('DocumentSearch2');
    		$module = cms_utils::get_module('Search');
    		if ($module != FALSE) {
			$module->DeleteWords($mod->GetName(), $documentid, 'documentsearch');
      	}
    
    		@$mod->SendEvent('SearchDocumentDeleted', array('documentsearch_id' => $documentid));
		// put mention into the admin log
		audit($documentid, 'Document Search: '.$documentid, 'Document deleted');
	}

	public static function recursive_remove_directory($directory, $empty=FALSE) {
    	// if the path has a slash at the end we remove it here
    	if(substr($directory,-1) == '/') {
        	$directory = substr($directory,0,-1);
    	}

    	// if the path is not valid or is not a directory ...
    	if(!file_exists($directory) || !is_dir($directory)) {
        	// ... we return false and exit the function
        	return FALSE;

    	// ... if the path is not readable
    	} elseif(!is_readable($directory)) {
        	// ... we return false and exit the function
        	return FALSE;

    	// ... else if the path is readable
    	} else {

        	// we open the directory
        	$handle = opendir($directory);

        	// and scan through the items inside
        	while (FALSE !== ($item = readdir($handle))) {
            	// if the filepointer is not the current directory
            	// or the parent directory
            	if($item != '.' && $item != '..') {
                	// we build the new path to delete
                	$path = $directory.'/'.$item;

                	// if the new path is a directory
                	if(is_dir($path)) {
                    	// we call this function with the new path
                    	recursive_remove_directory($path);

                	// if the new path is a file
                	} else {
                    	// we remove the file
                    	unlink($path);
                	}
            	}
        	}
        	// close the directory
        	closedir($handle);

        	// if the option to empty is not set to true
        	if($empty == FALSE) {
            	// try to delete the now empty directory
            	if(!rmdir($directory)) {
                	// return false if not possible
                	return FALSE;
            	}
        	}
        	// return success
        	return TRUE;
    	}
	}

	static public function UpdateHierarchyPositions() {
    	$db = cmsms()->GetDb();

    	$query = "SELECT documentsearch_category_id, documentsearch_category_name FROM ".cms_db_prefix()."module_documentsearch_categories";
    	$dbresult = $db->Execute($query);
    	while ($dbresult && $row = $dbresult->FetchRow()) {
			$current_hierarchy_position = "";
			$current_long_name = $row['documentsearch_category_name'];
			$content_id = $row['documentsearch_category_id'];
			$current_parent_id = $row['documentsearch_category_id'];
			$count = 0;
	  
			while ($current_parent_id > -1) {
	    		$query = "SELECT documentsearch_category_id, documentsearch_category_name, parent_id FROM ".cms_db_prefix()."module_documentsearch_categories WHERE documentsearch_category_id = ?";
	    		$row2 = $db->GetRow($query, array($current_parent_id));
	    		if ($row2) {
					$current_hierarchy_position = str_pad($row2['documentsearch_category_id'], 5, '0', STR_PAD_LEFT) . "." . $current_hierarchy_position;
					$current_long_name = $row2['documentsearch_category_name'];
					$current_parent_id = $row2['parent_id'];
					$count++;
	      		} else {
					$current_parent_id = 0;
	      		}
	  		}
	  
			if (strlen($current_hierarchy_position) > 0) {
	    		$current_hierarchy_position = substr($current_hierarchy_position, 0, strlen($current_hierarchy_position) - 1);
	  		}
	  
			if (strlen($current_long_name) > 0) {
	    		$current_long_name = substr($current_long_name, 0, strlen($current_long_name) - 3);
	  		}
	  
			$query = "UPDATE ".cms_db_prefix()."module_documentsearch_categories SET hierarchy = ?, long_name = ? WHERE documentsearch_category_id = ?";
			$db->Execute($query, array($current_hierarchy_position, $current_long_name, $content_id));
      	}
	}

	static public function delete_static_route($documentsearch_document_id) {
    	return cms_route_manager::del_static('','DocumentSearch',$documentsearch_document_id);
  	}

  	static public function register_static_route($documentsearch_url,$documentsearch_document_id,$detailpage = '') {
    	if( $detailpage <= 0 ) {
			$gCms = cmsms();
			$module = cms_utils::get_module('DocumentSearch2');
			$detailpage = $module->GetPreference('detail_returnid',-1);
			if( $detailpage == -1 ) {
	    		$detailpage = $gCms->GetContentOperations()->GetDefaultContent();
	  		}
      	}
    	$parms = array('action'=>'detail','returnid'=>$detailpage,
			'documentid'=>$documentsearch_document_id);

    	$route = CmsRoute::new_builder($documentsearch_url,'DocumentSearch',$documentsearch_document_id,$parms,TRUE);
    	return cms_route_manager::add_static($route);
  	}

} // end of class

#
# EOF
#
?>