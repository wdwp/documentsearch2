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

$themeObject = cms_utils::get_theme_object();

$smarty->assign('formstart',$this->CreateFormStart($id,'defaultadmin',$returnid));

if (isset($params['submit_massdelete']) ) {
	if( isset($params['sel']) && is_array($params['sel']) && count($params['sel']) > 0 ) {
		foreach( $params['sel'] as $documentsearch_document_id ) {
			documentsearch_admin_ops::delete_document( $documentsearch_document_id );
			$params = array('tab_message'=> 'delete_multiple', 'active_tab' => 'document');
			$this->Redirect($id, 'defaultadmin', $returnid, $params);
		}
	}
}

$db = $gCms->GetDb();

$query ='SELECT a.documentsearch_id, a.name, content, b.documentsearch_category_name as documentsearch_category_id, location from '.cms_db_prefix().'module_documentsearch a, '.cms_db_prefix().'module_documentsearch_categories b';
$query .=  ' where a.documentsearch_category_id=b.documentsearch_category_id';

if (isset($params['documentsearch_id'])) {
   // *ALWAYS* use parameterized queries with user-provided input
   // to prevent SQL-Injection attacks!
   $query .= ' and a.documentsearch_id = ?';
   $result = $db->Execute($query,array($params['documentsearch_id']));
   $mode = 'detail'; // we're viewing a single record
} else {
   // we're not getting a specific record, so show 'em all. Probably should paginate.
  if (isset($params['category']) && ($params['category'] > 0)) {
	$query .= ' and a.documentsearch_category_id =?';
	$result = $db->Execute($query,array($params['category']));
  } else {
	$params['category']='';
	$result = $db->Execute($query);
  }
  $mode = 'summary'; // we're viewing a list of records
}

$categorylist = array();
$query = "SELECT * FROM ".cms_db_prefix()."module_documentsearch_categories ORDER BY hierarchy";
$dbresult = $db->Execute($query);

while ($dbresult && $row = $dbresult->FetchRow()) {
	$categorylist[$row['documentsearch_category_name']] = $row['documentsearch_category_id'];
}

$usedcategory = $this->GetPreference('default_category', '');
if (isset($category))	{
	$usedcategory = $category;
}

$onchangetext='id="category"';

$rowclass = 'row1';

$records = array();
while ($result !== false && $row=$result->FetchRow()) {
	// create a new object for every record that we retrieve
	$rec = new stdClass();
    $rec->rowclass = $rowclass;
	$rec->id = $row['documentsearch_id'];
	$rec->name = $row['name'];
	$rec->content = $row['content'];
	$rec->location = $row['location'];
	$rec->category = $row['documentsearch_category_id'];
	$rec->select = $this->CreateInputCheckbox($id,'sel[]',$row['documentsearch_id']);
	$rec->editlink = $this->CreateLink($id, 'add_edit', $returnid, $themeObject->DisplayImage('icons/system/edit.gif', $this->Lang('edit'),'','','systemicon'), array('documentsearch_id'=>$rec->id));
	$rec->deletelink = $this->CreateLink($id, 'deletedocument', $returnid, $themeObject->DisplayImage('icons/system/delete.gif', $this->Lang('delete'),'','','systemicon'), array('documentsearch_id'=>$rec->id), $this->Lang('areyousure'));
	array_push($records,$rec);

    ($rowclass=="row1"?$rowclass="row2":$rowclass="row1");
}

// Expose the list to smarty.
$this->smarty->assign('records',$records);
$smarty->assign('recordcount', count($records));

// Tell Smarty which mode we're in
$this->smarty->assign('mode',$mode);

// and a count of records
$this->smarty->assign('title_num_records',$this->Lang('title_num_records',array(count($records))));

$this->smarty->assign('add', $this->CreateFrontendLink($id, $returnid, 'add_edit', $this->Lang('add_record'), array(), '', false,true, '', false));

$this->smarty->assign('category',$this->CreateInputDropDown($id, 'category', $categorylist, -1, $usedcategory, $onchangetext));

// Content defines and Form stuff for the admin
$smarty->assign('formend',$this->CreateFormEnd());
$smarty->assign('submit_massdelete', $this->CreateInputSubmit($id,'submit_massdelete',$this->Lang('delete_selected'), '','',$this->Lang('areyousure_deletemultiple')));

// translated strings
$smarty->assign('title_allow_add',$this->Lang('title_allow_add'));
$smarty->assign('welcometext',$this->Lang('welcometext'));
$smarty->assign('idtext',$this->Lang('idtext'));
$smarty->assign('nametext',$this->Lang('nametext'));
$smarty->assign('filetext',$this->Lang('filetext'));
$smarty->assign('categorytext',$this->Lang('categorytext'));
$smarty->assign('selecttext',$this->Lang('selecttext'));
$smarty->assign('filtertext', $this->Lang('filtertext'));
$smarty->assign('filteralltext', $this->Lang('filteralltext'));

if (isset($params['module_message'])) {
	$this->smarty->assign('module_message',$params['module_message']);
} else {
	$this->smarty->assign('module_message','');
}
//echo $submit_massdelete;

echo $this->ProcessTemplate('adminpanel.tpl');

?>