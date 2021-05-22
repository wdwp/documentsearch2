<?php
// BEGIN_LICENSE
// -------------------------------------------------------------------------
// Module: DocumentSearch (c) 2013 by Oliver Seddon
// (oliver@threefold.co.uk)
// An addon module for CMS Made Simple to provide PDF and Word document
// content searchability.

// -------------------------------------------------------------------------
// CMS - CMS Made Simple is (c) 2005 by Ted Kulp (wishy@cmsmadesimple.org)
// This project's homepage is: http://www.cmsmadesimple.org

// -------------------------------------------------------------------------

// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or
// (at your option) any later version.

// However, as a special exception to the GPL, this software is distributed
// as an addon module to CMS Made Simple.  You may not use this software
// in any Non GPL version of CMS Made simple, or in any version of CMS
// Made simple that does not indicate clearly and obviously in its admin
// section that the site was built with CMS Made simple.

// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
// Or read it online: http://www.gnu.org/licenses/licenses.html#GPL

// -------------------------------------------------------------------------
// END_LICENSE
error_log("Document search default");

if (!isset($gCms)) exit;

$in = '';
foreach ($params as $ffield => $fvalue) {
    $in .= " [$ffield]=$fvalue, ";
}
error_log($in);
// get our records from the database
$db = $gCms->GetDb();
$db->debug=true;
$query = 'SELECT documentsearch_id, name, content, location, author, document_date, ' .
	'(select documentsearch_category_name from '. cms_db_prefix().'module_documentsearch_categories b where a.documentsearch_category_id =b.documentsearch_category_id) as documentsearch_category_name '.
	 'from '.cms_db_prefix() .'module_documentsearch a ';

$order="";

if (isset($params['sortby'])) {

	switch ($params['sortby']) {
		case 'date':
			$order = " ORDER BY document_date ";
			break;
		case 'random':
			$order = " ORDER BY RAND() ";
			break;
	    case 'name':
	    	$order = " ORDER BY name ";
	    	break;
	} // switch
// descending order only when "sortby" is set
	if (isset($params['sortdesc']) &&
	(strtolower($params['sortdesc']) == 'true')) {
		$order .=' desc ';
}
}
if (isset($params['documentsearch_id'])) {
    // *ALWAYS* use parameterized queries with user-provided input
    // to prevent SQL-Injection attacks!
    $query .= ' where documentsearch_id = ?';
    $dbresult = $db->GetRow($query, array($params['documentsearch_id']));
    $mode = 'detail'; // we're viewing a single record

}elseif (isset($params['category'])) {
    $cat_id = $db->GetOne('SELECT documentsearch_category_id from ' . cms_db_prefix() . 'module_documentsearch_categories where lower(documentsearch_category_name) = lower(?) ', array($params['category']));
    error_log("category_id =$cat_id");
    if ($cat_id) {
        $query .= " where documentsearch_category_id  = ? $order";
        error_log("query=$query");
        $dbresult = $db->Execute($query, array($cat_id));
    }
    $mode = 'summary';
}else {
    // we're not getting a specific record, so show 'em all. Probably should paginate.
	$query .=$order;
    $dbresult = $db->Execute($query);
    $mode = 'summary'; // we're viewing a list of records
}
// if detail - show the document
if ($mode == 'detail') {
    $dest = cms_join_path($config['uploads_path'], 'documentsearch', 'id' . $dbresult['documentsearch_id'], $dbresult['location']);
    $file_parts = pathinfo($dest);
    $content = file_get_contents($dest);

    $mime = "text/plain";
    switch ($file_parts['extension']) {
        case 'pdf' : $mime = "application/pdf";
            break;
        case 'gif' : $mime = "image/gif";
            break;
        case 'png' : $mime = "image/png";
            break;
        case 'jpg' : $mime = "image/jpeg";
            break;
        case 'jpeg' : $mime = "image/jpeg";
            break;
        case 'rtf' : $mime = "text/rtf";
            break;
        case 'xls' :
        case 'xlsx' :$mime = "application/msexcel";
            break;
        case 'doc' :
        case 'docx' :$mime = "application/msword";
            break;
    }
    // kill any output that may have happened already.
    $handlers = ob_list_handlers();
    for ($cnt = 0; $cnt < sizeof($handlers); $cnt++) {
       ob_end_clean();
    }
    header('Content-type: ' . $mime);
    header('Content-disposition: inline; filename="' . $dbresult['location'] . '"');
    echo $content;

    exit;
}

$records = array();
/**
while ($result !== false && $row = $result->FetchRow()) {
    // create a new object for every record that we retrieve
    $rec = new stdClass();
    $rec->id = $row['documentsearch_id'];
    $rec->name = $row['name'];
    $rec->content = $row['content'];
    $rec->location = $row['location'];
    $rec->author = $row['author'];
    $rec->documentdate = $row['document_date'];
    // create attributes for rendering "view" links for the object.
    // $id and $returnid are predefined for us by the module API
    // that last parameter is the Pretty URL link
    $rec->view = $this->CreateFrontendLink($id, $returnid, 'default', $this->Lang('link_view'),
        array('documentsearch_id' => $rec->id), '', false, true, '', false, 'documentsearch/view/' . $rec->id . '/' . $returnid);
    $rec->edit = $this->CreateFrontendLink($id, $returnid, 'add_edit', $this->Lang('edit'),
        array('documentsearch_id' => $rec->id), '', false, true, '', false, 'documentsearch/edit/' . $rec->id . '/' . $returnid);
    array_push($records, $rec);

    $filename = $rec->location;
    $ext = substr(strrchr($filename, '.'), 1);
    $ext = strtoupper($ext);
    $modulePath = $this->GetModuleURLPath();
    $iconpath = $modulePath . '/images/appicons/' . $ext . 'icon.gif';
    $iconfile = '<img src="' . $iconpath . '" width="16" height="16" alt="' . $filename . '" />';
    // $smarty->assign('iconfile', $iconfile);
    $rec->documenticon = $iconfile;
}

$entryarray = array();
$query1 = "
            SELECT
                md.*,
                mdc.documentsearch_category_name,
                mdc.long_name
            FROM " .
cms_db_prefix() . "module_documentsearch md
            LEFT OUTER JOIN " . cms_db_prefix() . "module_documentsearch_categories mdc
            ON mdc.documentsearch_category_id = md.documentsearch_category_id
        ";

$query2 = "
            SELECT count(md.documentsearch_id) as count
            FROM " .
cms_db_prefix() . "module_documentsearch md
            LEFT OUTER JOIN " . cms_db_prefix() . "module_documentsearch_categories mdc
            ON mdc.documentsearch_category_id = md.documentsearch_category_id
        ";

if (isset($params['category_id'])) {
    $query1 .= "AND ( mdc.documentsearch_category_id = '" . (int)$params['category_id'] . "' ) AND ";
    $query2 .= "AND ( mdc.documentsearch_category_id = '" . (int)$params['category_id'] . "' ) AND ";
}else if (isset($params["category"]) && $params["category"] != '' && $params["category"] != 'none') {
    $category = cms_html_entity_decode(trim($params['category']));
    $categories = explode(',', $category);
    $query1 .= "AND (";
    $query2 .= "AND (";
    $count = 0;
    foreach ($categories as $onecat) {
        if ($count > 0) {
            $query1 .= ' OR ';
            $query2 .= ' OR ';
        }
        if (strpos($onecat, '|') !== false || strpos($onecat, '*') !== false) {
            $tmp = $db->qstr(trim(str_replace('*', '%', str_replace("'", '_', $onecat))));
            $query1 .= "upper(mdc.long_name) like upper({$tmp})";
            $query2 .= "upper(mdc.long_name) like upper({$tmp})";
        }else {
            $tmp = $db->qstr(trim(str_replace("'", '_', $onecat)));
            $query1 .= "mdc.documentsearch_category_name = {$tmp}";
            $query2 .= "mdc.documentsearch_category_name = {$tmp}";
        }
        $count++;
    }
    $query1 .= ") ";
    $query2 .= ") ";
}

$sortrandom = false;
if (isset($params['sortby'])) {
    if ($params['sortby'] == 'date') {
        $query1 .= "ORDER BY document_date ";
    }else if ($params['sortby'] == 'random') {
        $query1 .= "ORDER BY RAND() ";
        $sortrandom = true;
    }else {
        $query1 .= "ORDER BY md." . str_replace("'", '', str_replace(';', '', $db->qstr($params['sortby']))) . " ";
    }
}else {
    $query1 .= "ORDER BY documentsearch_category_id ";
}
if ($sortrandom == false) {
    if (isset($params['sortdesc']) &&
            (strtolower($params['sortdesc']) == 'true')) {
        $query1 .= "desc";
    }else {
        $query1 .= "asc";
    }
}
*/
//$dbresult = '';
//$dbresult = $db->Execute($query1);

//$dbresult = $db->Execute($query);
while ($dbresult && !$dbresult->EOF) {
    $row = $dbresult->fields;
    $onerow = new stdClass();

    $onerow->id = $row['documentsearch_id'];
    $onerow->title = $row['name'];
    $onerow->summary = (trim($row['content']) != '<br/>'?$row['content']:'');
    $onerow->location = $row['location'];
    $onerow->category = $row['documentsearch_category_name'];
    $onerow->author = $row['author'];
    $onerow->documentdate = $row['document_date'];

    $filename = $onerow->location;
    $ext = substr(strrchr($filename, '.'), 1);
    $ext = strtoupper($ext);
    $modulePath = $this->GetModuleURLPath();
    $iconpath = $modulePath . '/images/appicons/' . $ext . 'icon.gif';
    $iconfile = '<img src="' . $iconpath . '" width="16" height="16" alt="' . $filename . '" />';
    $smarty->assign('iconfile', $iconfile);
    $onerow->documenticon = $iconfile;

    $sendtodetail = array('documentsearchid' => $row['documentsearch_id']);
    if (isset($params['showall'])) {
        $sendtodetail['showall'] = $params['showall'];
    }
    if (isset($params['detailpage'])) {
        $sendtodetail['origid'] = $returnid;
    }
    if (isset($params['detailtemplate'])) {
        $sendtodetail['detailtemplate'] = $params['detailtemplate'];
    }

    $prettyurl = $row['location'];
    if ($prettyurl == '') {
        $aliased_title = munge_string_to_url($row['name']);
        $prettyurl = 'documentsearch/' . $row['documentsearch_id'] . '/' . ($detailpage != ''?$detailpage:$returnid) . "/$aliased_title";
        if (isset($sendtodetail['detailtemplate'])) {
            $prettyurl .= '/d,' . $sendtodetail['detailtemplate'];
        }
    }

    if (isset($params['lang'])) {
        $sendtodetail['lang'] = $params['lang'];
    }

    if (isset($params['category_id'])) {
        $sendtodetail['category_id'] = $params['category_id'];
    }

    if (isset($params['pagelimit'])) {
        $sendtodetail['pagelimit'] = $params['pagelimit'];
    }

    $onerow->link = $this->CreateLink($id, 'detail', $detailpage != ''?$detailpage:$returnid, '', $sendtodetail, '', true, false, '', true, $prettyurl);
    $onerow->titlelink = $this->CreateLink($id, 'detail', $detailpage != ''?$detailpage:$returnid, $row['name'], $sendtodetail, '', false, false, '', true, $prettyurl);
    $onerow->morelink = $this->CreateLink($id, 'detail', $detailpage != ''?$detailpage:$returnid, $moretext, $sendtodetail, '', false, false, '', true, $prettyurl);
    $onerow->moreurl = $this->CreateLink($id, 'detail', $detailpage != ''?$detailpage:$returnid, $moretext, $sendtodetail, '', true, false, '', true, $prettyurl);
    $onerow->postdate = $row['document_date'];

    $entryarray[] = $onerow;
    $dbresult->MoveNext();
}

$config = cmsms()->GetConfig();
$smarty->assign('root_path', $config['root_path']);
$smarty->assign('root_url', $config['root_url']);
$smarty->assign('uploads_path', $config['uploads_path']);
$smarty->assign('itemcount', count($entryarray));
$smarty->assign('items', $entryarray);
$smarty->assign('category_label', $this->Lang('category_label'));
$smarty->assign('author_label', $this->Lang('author_label'));

$catName = '';
if (isset($params['category'])) {
    $catName = $params['category'];
}else if (isset($params['category_id'])) {
    $catName = $db->GetOne('SELECT documentsearch_category_name FROM ' . cms_db_prefix() . 'module_documentsearch_categories where documentsearch_category_id=?', array($params['category_id']));
}
$smarty->assign('category_name', $catName);

unset($params['pagenumber']);
$items = documentsearch_ops::get_categories($id, $params, $returnid);
$smarty->assign('count', count($items));
$smarty->assign('cats', $items);
// Expose the list to smarty.
$this->smarty->assign('records', $records);
// Tell Smarty which mode we're in
$this->smarty->assign('mode', $mode);
// and a count of records
$this->smarty->assign('title_num_records', $this->Lang('title_num_records', array(count($records))));

$catName = '';
if (isset($params['category'])) {
    $catName = $params['category'];
} else if (isset($params['category_id'])) {
    $catName = $db->GetOne('SELECT documentsearch_category_name FROM ' . cms_db_prefix() . 'module_documentsearch_categories where documentsearch_category_id=?', array($params['category_id']));
}
$smarty->assign('category_name', $catName);

if (isset($params['module_message'])) {
    $this->smarty->assign('module_message', $params['module_message']);
}else {
    $this->smarty->assign('module_message', '');
}
// Display the populated template
// echo $this->ProcessTemplate($detailSample);
// echo $this->ProcessTemplateFromData($detailSample);
$cache_id = '|cgb' . md5(serialize($params) . $showdraft);
$compile_id = '';
$template = 'detailSample';
echo $smarty->fetch($this->GetDatabaseResource($template), $cache_id, $compile_id);

?>