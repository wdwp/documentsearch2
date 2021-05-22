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

if (! $this->CheckPermission('Use Document Search')) {
  return $this->DisplayErrorPage($id, $params, $returnid,$this->Lang('accessdenied'));
}

echo $this->StartTabHeaders();
if (FALSE == empty($params['active_tab']))
  {
    $tab = $params['active_tab'];
  } else {
  $tab = '';
 }

if ($this->CheckPermission('Use Document Search'))
   {
   echo $this->SetTabHeader('document',$this->Lang('tab_document'), ('document' == $tab)?true:false);
   echo $this->SetTabHeader('categories',$this->Lang('tab_categories'), ('categories' == $tab)?true:false);
   echo $this->SetTabHeader('templates',$this->Lang('tab_templates'), ('templates' == $tab)?true:false);
   }
echo $this->EndTabHeaders();


echo $this->StartTabContent();
if ($this->CheckPermission('Use Document Search') )
  {
    echo $this->StartTab('document', $params);
    include(dirname(__FILE__).'/function.admin_documenttab.php');
    echo $this->EndTab();
    echo $this->StartTab('categories', $params);
    include(dirname(__FILE__).'/function.admin_categoriestab.php');
    echo $this->EndTab();
    echo $this->StartTab('templates', $params);
    include(dirname(__FILE__).'/function.admin_templatetab.php');
    echo $this->EndTab();
  }

echo $this->EndTabContent();
?>