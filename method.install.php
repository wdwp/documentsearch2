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

$db = $this->GetDb();
$dict = NewDataDictionary( $db );
$flds = "
     documentsearch_id I KEY,
	 documentsearch_category_id I,
	 documentsearch_category_name C(255),
	 name C(255),
	 content LONGTEXT,
	 location C(255),
	 author C(255),
	 document_date " . CMS_ADODB_DT . "
";

$taboptarray = array( 'mysql' => 'TYPE=MyISAM' );
$sqlarray = $dict->CreateTableSQL( cms_db_prefix()."module_documentsearch",
				   $flds, $taboptarray);
$dict->ExecuteSQLArray($sqlarray);
$db->CreateSequence(cms_db_prefix()."module_documentsearch_seq");

$flds = "
	documentsearch_category_id I KEY,
	documentsearch_category_name C(255),
	parent_id I,
	hierarchy C(255),
	long_name X,
	create_date T,
	modified_date T
";

$taboptarray = array('mysql' => 'TYPE=MyISAM');
$sqlarray = $dict->CreateTableSQL(cms_db_prefix()."module_documentsearch_categories", 
		$flds, $taboptarray);
$dict->ExecuteSQLArray($sqlarray);
$db->CreateSequence(cms_db_prefix()."module_documentsearch_categories_seq");

// create a permission
$this->CreatePermission('Use Document Search', 'Use Document Search');

// create a preference
$this->SetPreference("allow_add", true);

# Other preferences
$this->SetPreference('allowed_upload_types','pdf,doc,docx,rtf,txt');

# Setup General category
$catid = $db->GenID(cms_db_prefix()."module_documentsearch_categories_seq");
$query = 'INSERT INTO '.cms_db_prefix().'module_documentsearch_categories (documentsearch_category_id, documentsearch_category_name, parent_id, create_date, modified_date) VALUES (?,?,?,'.$db->DBTimeStamp(time()).','.$db->DBTimeStamp(time()).')';
$db->Execute($query, array($catid, 'General', -1));

# Setup detail template
$fn = dirname(__FILE__).DIRECTORY_SEPARATOR.
  'templates'.DIRECTORY_SEPARATOR.'documentsearch_list.tpl';
if( file_exists( $fn ) ) {
    $template = @file_get_contents($fn);
    $this->SetPreference('default_detail_template_contents',$template);
    $this->SetTemplate('detailSample',$template);
    $this->SetPreference('current_detail_template','Default');
}

# Indexes
$sqlarray = $dict->CreateIndexSQL(cms_db_prefix().'document_postdate', cms_db_prefix().'module_documentsearch', 'document_date');

// register an event that the Document Search will issue. Other modules
// or user tags will be able to subscribe to this event, and trigger
// other actions when it gets called.
$this->CreateEvent( 'OnDocumentSearchPreferenceChange' );

// put mention into the admin log
$this->Audit( 0, 
	      $this->Lang('friendlyname'), 
	      $this->Lang('installed', $this->GetVersion()) );

	      
?>