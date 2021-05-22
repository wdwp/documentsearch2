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
if( !isset($gCms) ) exit;
if( !$this->CheckPermission('Use Document Search') ) return;

    $themeObject = cms_utils::get_theme_object();

	#Put together a list of current categories...
	$entryarray = array();

	$query = "SELECT * FROM ".cms_db_prefix()."module_documentsearch_categories ORDER BY hierarchy";
	$dbresult = $db->Execute($query);

	$rowclass = 'row1';

	while ($dbresult && $row = $dbresult->FetchRow())
	{
		$onerow = new stdClass();

		$depth = count(preg_split('/\./', $row['hierarchy']));

		$onerow->id = $row['documentsearch_category_id'];
		$onerow->name = str_repeat('&nbsp;&gt;&nbsp;', $depth-1).$this->CreateLink($id, 'editcategory', $returnid, $row['documentsearch_category_name'], array('catid'=>$row['documentsearch_category_id']));

		$onerow->editlink = $this->CreateLink($id, 'editcategory', $returnid, $themeObject->DisplayImage('icons/system/edit.gif', $this->Lang('edit'),'','','systemicon'), array('catid'=>$row['documentsearch_category_id']));
		$onerow->deletelink = $this->CreateLink($id, 'deletecategory', $returnid, $themeObject->DisplayImage('icons/system/delete.gif', $this->Lang('delete'),'','','systemicon'), array('catid'=>$row['documentsearch_category_id']), $this->Lang('areyousure'));

		$onerow->rowclass = $rowclass;

		$entryarray[] = $onerow;

		($rowclass=="row1"?$rowclass="row2":$rowclass="row1");
	}

	$smarty->assign_by_ref('items', $entryarray);
	$smarty->assign('itemcount', count($entryarray));

	#Setup links
	$smarty->assign('addlink', $this->CreateLink($id, 'addcategory', $returnid, $this->Lang('addcategory'), array(), '', false, false, 'class="pageoptions"'));
	$smarty->assign('addlink', $this->CreateLink($id, 'addcategory', $returnid, $themeObject->DisplayImage('icons/system/newfolder.gif', $this->Lang('addcategory'),'','','systemicon'), array(), '', false, false, '') .' '. $this->CreateLink($id, 'addcategory', $returnid, $this->Lang('addcategory'), array(), '', false, false, 'class="pageoptions"'));

	$smarty->assign('categorytext', $this->Lang('category'));

	#Display template
	echo $this->ProcessTemplate('categorylist.tpl');

// EOF
?>