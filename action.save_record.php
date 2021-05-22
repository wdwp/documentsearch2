зваff<?php
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

include(dirname(__FILE__).'/lib/autoload.php');
include(dirname(__FILE__).'/lib/rtf2text.php');

// get our records from the database
$db = $gCms->GetDb();

if (isset($params['cancel'])) {
	$this->Redirect($id, 'defaultadmin', $returnid);
}

if (isset($params['documentsearch_id']) && $params['documentsearch_id'] != -1) {

	// we received a documentsearch_id that was not -1, which means we're updating an
	// existing record. So we issue a SQL Update

	if ( !isset($_FILES[$id .'locationfile']['name']) || $_FILES[$id .'locationfile']['name'] == '' ) {

		$postdate = time();
		if (isset($params['postdate_Month'])) {
			$postdate = mktime($params['postdate_Hour'], $params['postdate_Minute'], $params['postdate_Second'], $params['postdate_Month'], $params['postdate_Day'], $params['postdate_Year']);
		}

		//If no name is give make file name the default name
		if ($params['name'] == '') {
			$tmpname = $params['location'];
    			$ext = substr(strrchr($tmpname, '.'), 1);
			$tmpext = '.' . $ext;
			$cleanname = str_replace('_', " ", $tmpname);
			$cleanname = str_replace($tmpext, "", $cleanname);
			$params['name'] = $cleanname;
		}

    		$documentid = $params['documentsearch_id'];
		$query = 'UPDATE '.cms_db_prefix().
    		'module_documentsearch set name=?, documentsearch_category_id=?, author=?, document_date=? where documentsearch_id = ?';
		$result = $db->Execute($query,array($params['name'],$params['category'],$params['author'],trim($db->DBTimeStamp($postdate), "'"),$params['documentsearch_id']));

	} else {

		$postdate = time();
		if (isset($params['postdate_Month'])) {
			$postdate = mktime($params['postdate_Hour'], $params['postdate_Minute'], $params['postdate_Second'], $params['postdate_Month'], $params['postdate_Day'], $params['postdate_Year']);
		}

    	$documentid = $params['documentsearch_id'];

    	// check to see if a file has been selected
    	$config = cmsms()->GetConfig();
    	$fieldname = $id .'locationfile';
    	$mod = cms_utils::get_module('DocumentSearch2');
    	$p = cms_join_path($config['uploads_path'],'documentsearch');
    	if (!is_dir($p)) {
      		$res = @mkdir($p);
      		if( $res === FALSE ) {
				$this->DisplayErrorPage($id, $params, $returnid,
					$this->Lang('error_mkdir'));
	    		return FALSE;
      		}
    	}

    	$p = cms_join_path($config['uploads_path'],'documentsearch','id'.$documentid);
    	if (!is_dir($p)) {
      		if( @mkdir($p) === FALSE ) {
				$this->DisplayErrorPage($id, $params, $returnid,
					$this->Lang('error_mkdir'));
	    		return FALSE;
      		}
    	}

    	$filename = basename($_FILES[$fieldname]['name']);
    	$dest = cms_join_path($config['uploads_path'],'documentsearch','id'.$documentid,$filename);
    	$params['location'] = $filename;

    	// Get the files extension
    	$ext = substr(strrchr($filename, '.'), 1);

    	// compare it against the 'allowed extentions'
    	$exts = explode(',',$mod->GetPreference('allowed_upload_types',''));
    	if( !in_array( $ext, $exts ) ) {
			$this->DisplayErrorPage($id, $params, $returnid,
				$this->Lang('error_invalidfiletype'));
			return FALSE;
      	}

    	if( @cms_move_uploaded_file($_FILES[$fieldname]['tmp_name'], $dest) === FALSE ) {
			$this->DisplayErrorPage($id, $params, $returnid,
				$this->Lang('error_movefile'));
			return FALSE;
      	}
        if ($ext == 'pdf') {

        $parser = new \Smalot\PdfParser\Parser();
        $pdf = $parser->parseFile($dest);
        $params['content'] = $pdf->getText();

		} elseif ($ext == 'docx') {
			function docx2text($filenamedocx) {
    			return readZippedXML($filenamedocx, "word/document.xml");
			}
			function readZippedXML($archiveFile, $dataFile) {
    			// Create new ZIP archive
    			$zip = new ZipArchive;

    			// Open received archive file
    			if (true === $zip->open($archiveFile)) {
        			// If done, search for the data file in the archive
        			if (($index = $zip->locateName($dataFile)) !== false) {
            			// If found, read it to the string
            			$data = $zip->getFromIndex($index);
            			// Close archive file
            			$zip->close();
            			// Load XML from a string
            			// Skip errors and warnings
            			$xml = DOMDocument::loadXML($data, LIBXML_NOENT | LIBXML_XINCLUDE | LIBXML_NOERROR | LIBXML_NOWARNING);
            			// Return data without XML formatting tags
            			return strip_tags($xml->saveXML());
        			}
        			$zip->close();
    			}
    			// In case of failure return empty string
    			return "";
			}
			$params['content'] = docx2text($dest);

		} elseif ($ext == 'doc') {
			function parseWord($userDoc) {
				$config = cmsms()->GetConfig();
				setlocale(LC_ALL,$config['locale']);
				putenv('LC_ALL='.$config['locale']);
				$outtext = shell_exec('antiword -w 0 '.$userDoc);
				return $outtext;
			}
			$params['content'] = parseWord($dest);

		} elseif ($ext == 'rtf') {
			function parseWord($userDoc) {

				$outtext = rtf2text($userDoc);
				$outtext = trim(preg_replace('/\s\s+/', ' ', $outtext));
				return str_replace('?', '', $outtext);
			}
			$params['content'] = parseWord($dest);
		} elseif ($ext == 'txt') {
			$params['content'] = file_get_contents($dest);
		}        

		$query = 'UPDATE '.cms_db_prefix().
    		'module_documentsearch set name=?, content=?, documentsearch_category_id=?, location=?, author=?, document_date=? where documentsearch_id = ?';
		$result = $db->Execute($query,array($params['name'],$params['content'],$params['category'],$params['location'],$params['author'],'9999',$params['documentsearch_id']));

		// Make content accessible to the search module
		$search = $this->GetModuleInstance('Search');
		$searchName = $params['name'] . $params['content'];
		$searchName = trim(preg_replace('/\s\s+/', ' ', $searchName));
		if( $search ) {
			$search->AddWords( $this->Getname(), $params['documentsearch_id'], 'documentsearch', $searchName, NULL);
		}
	}
	$params = array('tab_message'=> 'updated_record', 'active_tab' => 'document');
	$this->Redirect($id, 'defaultadmin', $returnid, $params);

} else {

	// we received no documentsearch_id or one that was -1, which means we're creating
	// a new record. So we issue a SQL Insert. But first, we use the sequence to get a fresh ID

	if ( !isset($_FILES[$id .'locationfile']['name']) || $_FILES[$id .'locationfile']['name'] == '' ) {

		echo $this->DisplayErrorPage($id, $params, $return_id, $this->Lang('uploaderror'));

	} else {

		$postdate = time();
		if (isset($params['postdate_Month'])) {
			$postdate = mktime($params['postdate_Hour'], $params['postdate_Minute'], $params['postdate_Second'], $params['postdate_Month'], $params['postdate_Day'], $params['postdate_Year']);
		}

    	$sid = $db->GenID(cms_db_prefix().'module_documentsearch_seq');
    	$documentid = $params['documentsearch_id'];

    	$config = cmsms()->GetConfig();
		$fieldname = $id .'locationfile';
    	$mod = cms_utils::get_module('DocumentSearch2');
    	$p = cms_join_path($config['uploads_path'],'documentsearch');
    	if (!is_dir($p)) {
      		$res = @mkdir($p);
      			if( $res === FALSE ) {
					$this->DisplayErrorPage($id, $params, $returnid,
				   		$this->Lang('error_mkdir'));
	    			return FALSE;
      			}
    	}

    	$p = cms_join_path($config['uploads_path'],'documentsearch','id'.$sid);
    	if (!is_dir($p)) {
      		if( @mkdir($p) === FALSE ) {
				$this->DisplayErrorPage($id, $params, $returnid,
				   	$this->Lang('error_mkdir'));
	    		return FALSE;
      		}
    	}

    	$filename = basename($_FILES[$fieldname]['name']);
    	$dest = cms_join_path($config['uploads_path'],'documentsearch','id'.$sid,$filename);
    	$params['location'] = $filename;

    	// Get the files extension
    	$ext = substr(strrchr($filename, '.'), 1);

    	// compare it against the 'allowed extentions'
    	$exts = explode(',',$mod->GetPreference('allowed_upload_types',''));
    	if( !in_array( $ext, $exts ) ) {
			$this->DisplayErrorPage($id, $params, $returnid,
				$this->Lang('error_invalidfiletype'));
			return FALSE;
      	}

    	if( @cms_move_uploaded_file($_FILES[$fieldname]['tmp_name'], $dest) === FALSE ) {
			$this->DisplayErrorPage($id, $params, $returnid,
				$this->Lang('error_movefile'));
			return FALSE;
      	}
        if ($ext == 'pdf') {

        	$parser = new \Smalot\PdfParser\Parser();
        	$pdf = $parser->parseFile($dest);
        	$params['content'] = $pdf->getText();

		} elseif ($ext == 'docx') {
			function docx2text($filenamedocx) {
    			return readZippedXML($filenamedocx, "word/document.xml");
			}
			function readZippedXML($archiveFile, $dataFile) {
    			// Create new ZIP archive
    			$zip = new ZipArchive;

    			// Open received archive file
    			if (true === $zip->open($archiveFile)) {
        			// If done, search for the data file in the archive
        			if (($index = $zip->locateName($dataFile)) !== false) {
            			// If found, read it to the string
            			$data = $zip->getFromIndex($index);
            			// Close archive file
            			$zip->close();
            			// Load XML from a string
            			// Skip errors and warnings
            			$xml = DOMDocument::loadXML($data, LIBXML_NOENT | LIBXML_XINCLUDE | LIBXML_NOERROR | LIBXML_NOWARNING);
            			// Return data without XML formatting tags
            			return strip_tags($xml->saveXML());
        			}
        			$zip->close();
    			}
    			// In case of failure return empty string
    			return "";
			}
			$params['content'] = docx2text($dest);
		} elseif ($ext == 'doc') {
			function parseWord($userDoc) {
				$config = cmsms()->GetConfig();
				setlocale(LC_ALL,$config['locale']);
				putenv('LC_ALL='.$config['locale']);
				$outtext = @shell_exec('antiword -w 0 '.$userDoc);
				return $outtext;
			}
			$params['content'] = parseWord($dest);
		} elseif ($ext == 'rtf') {
			function parseWord($userDoc) {

				$outtext = rtf2text($userDoc);
				$outtext = trim(preg_replace('/\s\s+/', ' ', $outtext));
				return str_replace('?', '', $outtext);
			}
			$params['content'] = parseWord($dest);
		} elseif ($ext == 'txt') {
			$params['content'] = file_get_contents($dest);
		}

		if ($params['name'] == '') {
			$tmpname = $params['location'];
			$tmpext = '.' . $ext;
			$cleanname = str_replace('_', " ", $tmpname);
			$cleanname = str_replace($tmpext, "", $cleanname);
			$params['name'] = $cleanname;
		}

		$query = 'INSERT INTO '.cms_db_prefix().
    		'module_documentsearch (documentsearch_id, name, content, documentsearch_category_id, location, author, document_date) VALUES (?,?,?,?,?,?,?)';
		$result = $db->Execute($query,array($sid,$params['name'],$params['content'],$params['category'],$params['location'],$params['author'],trim($db->DBTimeStamp($postdate), "'")));

		// Make content accessible to the search module
		$search = $this->GetModuleInstance('Search');
		$searchName = $params['name'] . $params['content'];
		$searchName = trim(preg_replace('/\s\s+/', ' ', $searchName));
		if( $search ) {
			$search->AddWords( $this->Getname(), $sid, 'documentsearch', $searchName, NULL);
		}
		$params = array('tab_message'=> 'added_record', 'active_tab' => 'document');
		$this->Redirect($id, 'defaultadmin', $returnid, $params);
	}
}

if ($result === false) {
	// yeah, that's graceful :(
	echo "Database error!";
	exit;
}

unset($params['documentsearch_id']);
?>