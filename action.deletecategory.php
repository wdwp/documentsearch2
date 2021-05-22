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
if (!isset($gCms)) exit;
if (!$this->CheckPermission('Use Document Search')) return;

	$catid = '';
	if (isset($params['catid']))
	{
		$catid = $params['catid'];
	}

// Get the category details
$query = 'SELECT * FROM '.cms_db_prefix().'module_documentsearch_categories
           WHERE documentsearch_category_id = ?';
$row = $db->GetRow( $query, array( $catid ) );

	//Now remove the category
	$query = "DELETE FROM ".cms_db_prefix()."module_documentsearch_categories WHERE documentsearch_category_id = ?";
	$db->Execute($query, array($catid));

//And remove it from any articles
$query = "UPDATE ".cms_db_prefix()."module_documentsearch SET documentsearch_category_id = -1 WHERE documentsearch_category_id = ?";
	$db->Execute($query, array($catid));
	
@$this->SendEvent('CategoryDeleted', array('category_id' => $catid, 'name' => $row['documentsearch_category_name']));
	  // put mention into the admin log
	  audit($catid, 'Category: '.$catid, ' Category deleted');

documentsearch_admin_ops::UpdateHierarchyPositions();

	$params = array('tab_message'=> 'categorydeleted', 'active_tab' => 'categories');
	$this->Redirect($id, 'defaultadmin', $returnid, $params);
?>
