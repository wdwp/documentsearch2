{if isset($module_message)}<h2>{$module_message|escape}</h2>{/if}
{if $category_name != ''}
	<p class="title">{$category_name}</p>
{/if}
{foreach from=$items item=entry}
	{if $category_name != ''}
		{if $entry->category == $category_name}
			<div class="documentList">
				{if $entry->documenticon ne ''}<span class="documentIcon">{$entry->documenticon}</span>{/if}
				{if $entry->title ne ''}<span class="documentTitle">{$entry->title}</span>{/if}
				{if $entry->author ne ''}<span class="documentAuthor">{$entry->author}</span>{/if}
				{if $entry->documentdate ne ''}<span class="documentDate">{$entry->documentdate|date_format}</span>{/if}
				{if $entry->location ne ''}<span class="documentLink"><a href="uploads/documentsearch/id{$entry->id}/{$entry->location}" title="Link to the {$entry->name} document">Download &raquo;</a></span>{/if}
			</div>
		{/if}
	{else}
		<div class="documentList">
			{if $entry->documenticon ne ''}<span class="documentIcon">{$entry->documenticon}</span>{/if}
			{if $entry->title ne ''}<span class="documentTitle">{$entry->title}</span>{/if}
			{if $entry->author ne ''}<span class="documentAuthor">{$entry->author}</span>{/if}
			{if $entry->documentdate ne ''}<span class="documentDate">{$entry->documentdate|date_format}</span>{/if}
			{if $entry->location ne ''}<span class="documentLink"><a href="uploads/documentsearch/id{$entry->id}/{$entry->location}" title="Link to the {$entry->name} document">Download &raquo;</a></span>{/if}
		</div>
	{/if}
{/foreach}