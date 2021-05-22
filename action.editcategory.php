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

if (isset($params['cancel']))
  {
    $this->Redirect($id, 'defaultadmin', $returnid);
  }

$catid = '';
if (isset($params['catid']))
  {
    $catid = $params['catid'];
  }


$parentid = '-1';
if (isset($params['parent']))
  {
    $parentid = (int)$params['parent'];
  }

$origname = '';
if (isset($params['origname']))
  {
    $origname = $params['origname'];
  }

$name = '';
if (isset($params['name']))
  {
    $name = trim($params['name']);
    if ($name != '')
      {
	$query = 'SELECT documentsearch_category_id FROM '.cms_db_prefix().'module_documentsearch_categories WHERE documentsearch_category_name = ? AND documentsearch_category_id != ?';
	$tmp = $db->GetOne($query,array($parentid,$name,$catid));
	if( $tmp )
	  {
	    echo $this->ShowErrors($this->Lang('error_duplicatename'));
	  }
	else
	  {
	    $query = 'UPDATE '.cms_db_prefix().'module_documentsearch_categories SET documentsearch_category_name = ?, parent_id = ?, modified_date = '.$db->DBTimeStamp(time()).' WHERE documentsearch_category_id = ?';
	    $parms = array($name,$parentid);
	    $parms[] = $catid;
	    $db->Execute($query, $parms);
	    documentsearch_admin_ops::UpdateHierarchyPositions();
	    @$this->SendEvent('NewsCategoryEdited', array('category_id' => $catid, 'name' => $name, 'origname' => $origname));
			  // put mention into the admin log
	  audit($catid, 'News category: '.$catid, ' Category edited');
	    $params = array('tab_message'=> 'categoryupdated', 'active_tab' => 'categories');
	    $this->Redirect($id, 'defaultadmin', $returnid, $params);		
	  }
      }
    else
      {
		echo $this->ShowErrors($this->Lang('nonamegiven'));
      }
  }
 else
   {
     $query = 'SELECT * FROM '.cms_db_prefix().'module_documentsearch_categories WHERE documentsearch_category_id = ?';
     $row = $db->GetRow($query, array($catid));

     if ($row)
       {
	 $name = $row['documentsearch_category_name'];
	 $parentid = (int)$row['parent_id'];
       }
   }


#Display template
$smarty->assign('startform', $this->CreateFormStart($id, 'editcategory', $returnid));
$smarty->assign('endform', $this->CreateFormEnd());
$smarty->assign('nametext', $this->Lang('name'));
$smarty->assign('inputname', $this->CreateInputText($id, 'name', $name, 20, 255));
$smarty->assign('hidden', 
		      $this->CreateInputHidden($id, 'catid', $catid).
		      $this->CreateInputHidden($id,'origname',$name));
$smarty->assign('submit', $this->CreateInputSubmit($id, 'submit', lang('submit')));
$smarty->assign('cancel', $this->CreateInputSubmit($id, 'cancel', lang('cancel')));
echo $this->ProcessTemplate('editcategory.tpl');
?>
