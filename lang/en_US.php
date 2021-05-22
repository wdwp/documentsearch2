<?php
$lang['friendlyname'] = 'Document Search';
$lang['postinstall'] = 'Be sure to set "Use Document Search" permissions to use this module!';
$lang['postuninstall'] = 'Sorry, it\'s not you, it\'s me.';
$lang['really_uninstall'] = 'Really? You\'re sure you want to uninstall this super fine module?';
$lang['uninstalled'] = 'Module Uninstalled.';
$lang['installed'] = 'Module version %s installed.';
$lang['prefsupdated'] = 'Module preferences updated.';
$lang['submit'] = 'Save';
$lang['cancel'] = 'Cancel';
$lang['default'] = 'Restore default template';
$lang['restoredefaultsconfirm'] = 'Do you really want to restore the default template?';
$lang['accessdenied'] = 'Access Denied. Please check your permissions.';
$lang['error'] = 'Error!';
$lang['error_duplicatename'] = 'This category name already exists!';
$lang['error_invalidfiletype'] = 'This file type is not allowed! Only PDF, DOC and DOCX files can be uploaded.';
$lang['error_mkdir'] = 'Error making the directory, check your permissions and try again.!';
$lang['error_movefile'] = 'Error moving the file, check your permissions and try again.!';
$lang['link_view'] = 'View';
$lang['edit'] = 'Edit';
$lang['idtext'] = 'ID';
$lang['nametext'] = 'Name';
$lang['templatetext'] = 'Detail template';
$lang['filetext'] = 'File';
$lang['title_author'] = 'Author';
$lang['postdate'] = 'Document date';
$lang['categorytext'] = 'Category';
$lang['category'] = 'Category';
$lang['name'] = 'Category name';
$lang['selecttext'] = 'Select';
$lang['title_num_records'] = '%s documents found.';
$lang['add_record'] = 'Add a document';
$lang['added_record'] = 'Document added';
$lang['addcategory'] = 'Add a category';
$lang['uploaderror'] = 'A document must be selected!';
$lang['categoryupdated'] = 'Category updated';
$lang['categoryadded'] = 'Category added';
$lang['categorydeleted'] = 'Category deleted';
$lang['delete_selected'] = 'Delete selected documents';
$lang['areyousure_deletemultiple'] = 'Are you sure you want to delete all of these documents?\nThis action cannot be undone!';
$lang['delete_multiple'] = 'Documents deleted';
$lang['updated_record'] = 'Document updated';
$lang['filtertext'] = 'Filter by:';
$lang['filteralltext'] = 'Show all';
$lang['templateupdated'] = 'Template updated';
$lang['upgraded'] = 'Module upgraded to version %s.';
$lang['title_general'] = 'Document list';
$lang['title_allow_add'] = 'Users may add records?';
$lang['title_allow_add_help'] = 'Click here to allow users to add records.';
$lang['title_mod_prefs'] = 'Preferences';
$lang['title_mod_admin'] = 'Admin Panel';
$lang['title_name'] = 'File name';
$lang['title_location'] = 'Select document';
$lang['title_locationedit'] = 'Replace current document';
$lang['tab_document'] = 'Documents';
$lang['tab_categories'] = 'Categories';
$lang['tab_templates'] = 'Templates';
$lang['areyousure'] = 'Are you sure you want to delete?';
$lang['dash_record_count'] = 'This module handles %s documents';
$lang['alert_no_records'] = 'There have not been any documents added in the Document Search module!';
$lang['help_documentsearch_id'] = 'Internally identifier for selecting documents';
$lang['help_description'] = 'Internal parameter used to pass description info when creating or editing a document';
$lang['help_explanation'] = 'Internal parameter used to pass explanation info when creating or editing a document';
$lang['help_module_message'] = 'Internally used parameter for passing messages to user';
$lang['help_category'] = 'Which category to display documents from.';
$lang['help_sortby'] = 'Field to sort by. Options are: "date".';
$lang['help_sortdesc'] = 'Sort news items in descending date order rather than ascending.';

$lang['moddescription'] = 'This module extracts the contents of PDF, DOC, DOCX, RTF and TXT files and adds the content to the database making the documents content searchable, with a link reference to the original document for downloading/viewing.';
$lang['changelog'] = '<ul>
<li>Version 1.0 30 January 2013. Initial Release.</li>
<li>Version 1.1 30 January 2013. Fix for bug #8875.</li>
<li>Version 1.2 30 January 2013. Fix to search results URL, missing documentsearch directory.</li>
<li>Version 1.3 31 January 2013. Added sortby and sortdesc parameters plus tweaked template.</li>
<li>Version 1.3.1 7 February 2013. Bug fix #8911 and added file icons to front end template.</li>
<li>Version 1.4 24 February 2013. Added template tab to enable editing of output and if no name is specified the documents file name will be used by default, minus extension and any _ replaced by spaces and per feature requests #8941 and #8942. Made category name appear in admin table rather than ID.</li>
<li>Version 1.4.1 25 February 2013. Using what appears to be a more reliable pdf2text class.</li>
<li>Version 1.4.2 26 February 2013. Bug fixes and a handful of new features.</li>
<li>Version 1.4.3 28 February 2013. Better conversion of non standard characters.</li>
<li>Version 1.4.4 28 June 2013. Bug fixes and removed Status dropdown and added support for RTF and TXT docs.</li>
<li>Version 1.4.5 17 February 2014. Bug fix #9751 compatability with PHP 5.4</li>
<li>Version 2.0 22 May 2021. Modified for compatibility with CMSMS 2.0. The content search was fixed for pdf, RTF, and doc files. For doc files, you have to install antiword on your Linux server.</li>
</ul>';
$lang['help'] = '<h3>What Does This Do?</h3>
<p>This module allows for the uploading of PDF, DOCX, DOC and RTF files with the intention of having the documents content extracted and added to the database making the content searchable via CMSMS search module. The uploaded files can also be listed on the front end.</p>
<h3>How Do I Use It</h3>
<p>Simply place the module in a page or template using the smarty tag <code>{DocumentSearch}</code></p>
<h3>Support</h3>
<p>This module does not include commercial support. However, there are a number of resources available to help you with it:</p>
<ul>
<li>For the latest version of this module, FAQs, or to file a Bug Report, please visit the Module Forge
<a href="http://dev.cmsmadesimple.org/projects/documentsearch/">Document Search Page</a>.</li>
<li>Additional discussion of this module may also be found in the <a href="http://forum.cmsmadesimple.org">CMS Made Simple Forums</a>.</li>
<li>The author, oliverseddon, can often be found in the <a href="irc://irc.freenode.net/#cms">CMS IRC Channel</a>.</li>
<li>Lastly, you may have some success emailing the author directly.</li>
</ul>
<p>As per the GPL, this software is provided as-is. Please read the text of the license for the full disclaimer.</p>

<h3>Copyright and License</h3>
<p>Copyright &copy; 2013, Oliver Seddon <a href="mailto:oliver@threefold.co.uk">&lt;oliver@threefold.co.uk&gt;</a>. All Rights Are Reserved.</p>
<p>This module has been released under the <a href="http://www.gnu.org/licenses/licenses.html#GPL">GNU Public License</a>. You must agree to this license before using the module.</p>
';
?>
