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

// get our records from the database
$db = $gCms->GetDb();

if (isset($params['documentsearch_id'])) {
	$query = 'SELECT documentsearch_id, name, content, documentsearch_category_id, location, author, document_date from '.cms_db_prefix().
    	'module_documentsearch where documentsearch_id = ?';
	$result = $db->Execute($query,array($params['documentsearch_id']));

	if ($result !== false) {
		// load in the record if there was no error
    	$row=$result->FetchRow();
    	$sid = $row['documentsearch_id']; // stupid -- we're passing the param, and then using the database version.
    	$name = $row['name'];
    	$category = $row['documentsearch_category_id'];

		// we decode this next one, because it gets stored encoded, and the CreateTextArea API encodes as well, so
		// if we didn't decode it, we'd get double encoding.
    	$content = html_entity_decode($row['content']); 
    	$location = $row['location'];
    	$author = $row['author'];
    	$postdate = $row['document_date'];

		$usedcategory = $this->GetPreference('default_category', '');
		if (isset($category))	{
			$usedcategory = $category;
		}

    } else {
    	// yeah, that's graceful :(
    	echo "Database error!";
    	exit;
    }
} else {
	// if we didn't retrieve a record, set some default values
	$sid = -1;
	$name = '';
	$content = '';
	$location = '';
	$author = '';
	$usedcategory = '';
	$postdate = time();
}

$categorylist = array();
$query = "SELECT * FROM ".cms_db_prefix()."module_documentsearch_categories ORDER BY hierarchy";
$dbresult = $db->Execute($query);
    
while ($dbresult && $row = $dbresult->FetchRow()) {
	$categorylist[$row['documentsearch_category_name']] = $row['documentsearch_category_id'];
}


$onchangetext='onchange="document'.$id.'moduleform_1.submit()"';

// set up form for Smarty
$smarty->assign('start_form',$this->CreateFormStart($id,'save_record',$returnid,'post','multipart/form-data',false,'',$params));

// give Smarty translated field titles 
$smarty->assign('title_name',$this->Lang('title_name'));
$smarty->assign('title_author',$this->Lang('title_author'));
$smarty->assign('title_content',$this->Lang('title_content'));
$smarty->assign('title_locationfile',$this->Lang('title_location'));
$smarty->assign('title_locationfileedit',$this->Lang('title_locationedit'));
$smarty->assign('categorytext', $this->Lang('category'));
$smarty->assign('postdatetext', $this->Lang('postdate'));
$smarty->assign('postdateprefix', $id.'postdate_');
$smarty->assign('input_name',$this->CreateInputText($id,'name',$name, 30));
$smarty->assign('input_author',$this->CreateInputText($id,'author',$author, 30));
$smarty->assign('input_content',$this->CreateTextArea(true, $id, $content, 'content', '', '', '', '', 40, 5));
$smarty->assign('input_locationfile',$this->CreateFileUploadInput($id,'locationfile'));
$smarty->assign('postdate', $postdate);
$smarty->assign('location',$location);
$smarty->assign('inputcategory', $this->CreateInputDropdown($id, 'category', $categorylist, -1, $usedcategory, $onchangetext));
$smarty->assign('input_location',$this->CreateInputHidden($id,'location',$location));
$smarty->assign('submit', $this->CreateInputHidden($id,'documentsearch_id',$sid).$this->CreateInputSubmit($id, 'submit', $this->Lang('submit')));
$smarty->assign('cancel', $this->CreateInputSubmit($id, 'cancel', $this->Lang('cancel')));
$smarty->assign('end_form', $this->CreateFormEnd());

// Display the populated template
echo $this->ProcessTemplate('add_edit.tpl');

?>