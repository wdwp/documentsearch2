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

$current_version = $oldversion;
$db =& $this->GetDb();

switch($current_version) {
  // we are now 1.0 and want to upgrade to latest
 case "1.0":
 case "1.1":
 case "1.2":
	$dict = NewDataDictionary($db);
	$sqlarray = $dict->AddColumnSQL(cms_db_prefix()."module_documentsearch", "document_date " . CMS_ADODB_DT ."");
	$dict->ExecuteSQLArray($sqlarray);
	$current_version = "1.3";
 case "1.3":
 case "1.3.1":
	$dict = NewDataDictionary($db);
	$sqlarray = $dict->AddColumnSQL(cms_db_prefix()."module_documentsearch", "documentsearch_category_name C(255)");
	$dict->ExecuteSQLArray($sqlarray);
	$fn = dirname(__FILE__).DIRECTORY_SEPARATOR.
  	'templates'.DIRECTORY_SEPARATOR.'documentsearch_list.tpl';
	if( file_exists( $fn ) ) {
    	$template = @file_get_contents($fn);
    	$this->SetPreference('default_detail_template_contents',$template);
    	$this->SetTemplate('detailSample',$template);
    	$this->SetPreference('current_detail_template','Default');
	}
	$current_version = "1.4";
 case "1.4":
 case "1.4.1":
 case "1.4.2":
 case "1.4.3":
	$dict = NewDataDictionary($db);
	$sqlarray = $dict->DropTableSQL( cms_db_prefix()."module_documentsearch_status" );
	$dict->ExecuteSQLArray($sqlarray);
	$db->DropSequence( cms_db_prefix()."module_documentsearch_status_seq" );
	$current_version = "1.4.4";
}

// put mention into the admin log
$this->Audit( 0, 
	      $this->Lang('friendlyname'), 
	      $this->Lang('upgraded', $this->GetVersion()));

//note: module api handles sending generic event of module upgraded here
?>