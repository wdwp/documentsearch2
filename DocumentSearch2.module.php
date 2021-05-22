<?php
#BEGIN_LICENSE
#-------------------------------------------------------------------------
# Fork of Module: DocumentSearch (c) 2013 by Oliver Seddon
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

class DocumentSearch2 extends CMSModule
{

  function GetName()
  {
    return 'DocumentSearch2';
  }

  function GetFriendlyName()
  {
    return $this->Lang('friendlyname');
  }

  function GetVersion()
  {
    return '2.0';
  }

  function GetHelp()
  {
    return $this->Lang('help');
  }

  function GetAuthor()
  {
    return 'oliverseddon';
  }

  function GetAuthorEmail()
  {
    return 'oliver@threefold.co.uk';
  }

  function GetChangeLog()
  {
    return $this->Lang('changelog');
  }

  function IsPluginModule()
  {
    return true;
  }

  function HasAdmin()
  {
    return true;
  }

  function GetAdminSection()
  {
    return 'content';
  }

  function GetAdminDescription()
  {
    return $this->Lang('moddescription');
  }

  function VisibleToAdminUser()
  {
    return $this->CheckPermission('Use Document Search');
  }

  function GetDashboardOutput()
  {
	$db = cmsms()->GetDb();

	$rcount = $db->GetOne('select count(*) from '.cms_db_prefix().'module_documentsearch');

    return $this->Lang('dash_record_count',$rcount);
  }

  function DisplayErrorPage($id, $params, $return_id, $message='')
    {
		$this->smarty->assign('title_error', $this->Lang('error'));
		$this->smarty->assign_by_ref('message', $message);

        // Display the populated template
        echo $this->ProcessTemplate('error.tpl');
    }

  function GetDependencies()
  {
    return array();
  }

  function MinimumCMSVersion()
  {
    return "2.0";
  }

  function InitializeAdmin()
	{
	  $this->CreateParameter('category', 'General', $this->Lang('help_category'));
	  $this->CreateParameter('sortby', 'date', $this->Lang('help_sortby'));
	  $this->CreateParameter('sortdesc', 'true', $this->Lang('help_sortdesc'));
	}

  function InitializeFrontend()
  {
  $this->RegisterModulePlugin();

	$this->RegisterRoute('/documentsearch\/view\/(?P<documentsearch_id>[0-9]+)\/(?P<returnid>[0-9]+)$/',array('action'=>'default'));

	$gCms = cmsms();
	$contentops = $gCms->GetContentOperations();
	$returnid = $contentops->GetDefaultContent();
	// The previous three lines are to get a returnid; many modules, like News, have a default
	// page in which to display detail views. In that case, the page_id would be used for returnid.

	// The next three lines are where we map the URL to our detail page.
	$parms = array('action'=>'default','documentsearch_id'=>1,'returnid'=>$returnid);
	$route = new CmsRoute('this/is/insanely/great/stuff',$this->GetName(),$parms,TRUE);
	cms_route_manager::register($route);


   // Don't allow parameters other than the ones you've explicitly defined
   $this->RestrictUnknownParams();

   // syntax for creating a parameter is parameter name, default value, description
   $this->CreateParameter('documentsearch_id', -1, $this->Lang('help_documentsearch_id'));

   $this->SetParameterType('documentsearch_id',CLEAN_INT);

   $this->CreateParameter('module_message','',$this->Lang('help_module_message'));
   $this->SetParameterType('module_message',CLEAN_STRING);

   $this->CreateParameter('name','',$this->Lang('help_description'));
   $this->SetParameterType('name',CLEAN_STRING);

   $this->CreateParameter('content','',$this->Lang('help_explanation'));
   $this->SetParameterType('content',CLEAN_STRING);

   $this->CreateParameter('location','',$this->Lang('help_explanation'));
   $this->SetParameterType('location',CLEAN_STRING);

   $this->CreateParameter('author','',$this->Lang('help_author'));
   $this->SetParameterType('author',CLEAN_STRING);

   $this->SetParameterType('cancel',CLEAN_STRING);
   $this->SetParameterType('extension',CLEAN_STRING);
   $this->SetParameterType('iconpath',CLEAN_STRING);

   $this->CreateParameter('category', 'General', $this->lang('helpcategorytext'));
   $this->SetParameterType('category',CLEAN_STRING);

   $this->CreateParameter('sortby', 'document_date', $this->lang('helpsortby'));
   $this->SetParameterType('sortby',CLEAN_STRING);

   $this->CreateParameter('sortdesc', 'true', $this->lang('helpsortdesc'));
   $this->SetParameterType('sortdesc',CLEAN_STRING);

   $this->SetParameterType('postdate',CLEAN_STRING);
   $this->SetParameterType('postdate_Hour',CLEAN_STRING);
   $this->SetParameterType('postdate_Minute',CLEAN_STRING);
   $this->SetParameterType('postdate_Second',CLEAN_STRING);
   $this->SetParameterType('postdate_Month',CLEAN_STRING);
   $this->SetParameterType('postdate_Day',CLEAN_STRING);
   $this->SetParameterType('postdate_Year',CLEAN_STRING);


  }

  function GetEventDescription ( $eventname )
  {
    return $this->Lang('event_info_'.$eventname );
  }

  function GetEventHelp ( $eventname )
  {
    return $this->Lang('event_help_'.$eventname );
  }

  function InstallPostMessage()
  {
    return $this->Lang('postinstall');
  }

  function UninstallPostMessage()
  {
    return $this->Lang('postuninstall');
  }

  function UninstallPreMessage()
  {
    return $this->Lang('really_uninstall');
  }


	function SearchResult($returnid, $articleid, $attr = '') {
		$documentid = $params['documentsearch_id'];
		if (!empty($documentid)) $returnid = $documentid;
		$result = array();
		if ($attr == 'documentsearch') {
			$db  = cmsms()->GetDb();
			$q = 'SELECT * FROM ' . cms_db_prefix() . 'module_documentsearch WHERE documentsearch_id = ?';
			$row = $db->GetRow($q, array($articleid) );
			if ($row) {
				$result[0] = utf8_encode(html_entity_decode($this->GetFriendlyName()));
				$result[1] = $row['name'];
				$prettyurl = 'uploads/documentsearch/id'.$row['documentsearch_id'].'/'.$row['location'];
				$result[2] = $this->CreateLink('cntnt01', 'default', $returnid, $row['name'],
				       array('documentsearch_id' => $row['documentsearch_id']),
				       '', true, true, '', true, $prettyurl);
			}
		}
		return $result;
	}


	function SearchReindex($module) {
		$db = cmsms()->GetDb();

		$query = 'SELECT * FROM '.cms_db_prefix().'module_documentsearch ORDER BY documentsearch_id';
		$result = $db->Execute($query);

		while ($result && !$result->EOF) {
			$module->AddWords($this->Getname(), $result->fields['documentsearch_id'], 'documentsearch', $result->fields['content'], NULL);
			$result->MoveNext();
		}
	}

	public function DoAction ($action, $id, $params, $return_id = -1) {
		switch ($action) {
			case 'updatetemplate': {
				// check permissions again
				if ($this->CheckPermission ('Modify Templates')) {
					if (isset ($params['defaultbutton'])) {
						$fn = dirname(__FILE__).'/templates/documentsearch_list.tpl';
						if( file_exists($fn) ) {
							$template = @file_get_contents($fn);
							$this->SetTemplate( 'detailSample', $template);
						}
					} else {
						$this->SetTemplate ('detailSample', $params['templatecontent']);
					}
	  				$params = array('tab_message'=> 'templateupdated', 'active_tab' => 'templates');
	  				$this->Redirect($id, 'defaultadmin', $returnid, $params);
				}
			}

			default:
				parent::DoAction( $action, $id, $params, $return_id );
				break;
		}
	}




} //end class
?>