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

$sqlarray = $dict->DropTableSQL( cms_db_prefix()."module_documentsearch" );
$dict->ExecuteSQLArray($sqlarray);

$sqlarray = $dict->DropTableSQL( cms_db_prefix()."module_documentsearch_categories" );
$dict->ExecuteSQLArray($sqlarray);

$mydirectory = '../uploads/documentsearch';

recursive_remove_directory($mydirectory);


function recursive_remove_directory($directory, $empty=FALSE)
{
    // if the path has a slash at the end we remove it here
    if(substr($directory,-1) == '/')
    {
        $directory = substr($directory,0,-1);
    }

    // if the path is not valid or is not a directory ...
    if(!file_exists($directory) || !is_dir($directory))
    {
        // ... we return false and exit the function
        return FALSE;

    // ... if the path is not readable
    }elseif(!is_readable($directory))
    {
        // ... we return false and exit the function
        return FALSE;

    // ... else if the path is readable
    }else{

        // we open the directory
        $handle = opendir($directory);

        // and scan through the items inside
        while (FALSE !== ($item = readdir($handle)))
        {
            // if the filepointer is not the current directory
            // or the parent directory
            if($item != '.' && $item != '..')
            {
                // we build the new path to delete
                $path = $directory.'/'.$item;

                // if the new path is a directory
                if(is_dir($path)) 
                {
                    // we call this function with the new path
                    recursive_remove_directory($path);

                // if the new path is a file
                }else{
                    // we remove the file
                    unlink($path);
                }
            }
        }
        // close the directory
        closedir($handle);

        // if the option to empty is not set to true
        if($empty == FALSE)
        {
            // try to delete the now empty directory
            if(!rmdir($directory))
            {
                // return false if not possible
                return FALSE;
            }
        }
        // return success
        return TRUE;
    }
}

// remove the sequences
$db->DropSequence( cms_db_prefix()."module_documentsearch_seq" );
$db->DropSequence( cms_db_prefix()."module_documentsearch_categories_seq" );

// remove the permissions
$this->RemovePermission('Use Document Search');
$this->RemovePermission('Set Document Search Prefs');

// remove the preference
$this->RemovePreference("allow_add");

// remove the event
$this->RemoveEvent( 'OnDocumentSearchPreferenceChange' );

// put mention into the admin log
$this->Audit( 0, $this->Lang('friendlyname'), $this->Lang('uninstalled'));

?>