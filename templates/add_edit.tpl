{if isset($error)}
  <h3><font color="red">{$error}</font></h3>
{else}
  {if isset($message)}
    <h3>{$message}</h3>
  {/if}
{/if}
{$start_form}
<div class="pageoverflow">
	<p class="pagetext">*{$categorytext}:</p>
	<p class="pageinput">{$inputcategory}</p>
</div>
<div class="pageoverflow">
   <p class="pagetext">{$title_name}</p>
   <p class="pageinput">{$input_name}</p>
</div>
<div class="pageoverflow">
   <p class="pagetext">{$title_author}</p>
   <p class="pageinput">{$input_author}</p>
</div>
<div class="pageoverflow">
   {if $location != ''}
      <p>&nbsp;</p>
      <p><span class="pagetext">Current file:</span> {$location}</p>
   {/if}
</div>
<div class="pageoverflow">
   {if $location != ''}<p class="pagetext">{$title_locationfileedit}</p>{else}<p class="pagetext">{$title_locationfile}</p>{/if}
   <p class="pageinput">{$input_locationfile}</p>
   <p class="pageinput">{$input_location}</p>
</div>
<div class="pageoverflow">
	<p class="pagetext">{$postdatetext}:</p>
	<p class="pageinput">{html_select_date prefix=$postdateprefix time=$postdate start_year="-10" end_year="+15"} {html_select_time prefix=$postdateprefix time=$postdate}</p>
</div>
<div class="pageoverflow">
   <p class="pagetext"></span>
   <p class="pageinput">{$cancel}{$submit}</p>
</div>
{$end_form}