{**
 * formErrors.tpl
 *
 * Copyright (c) 2005-2007 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * List errors that occurred during form processing.
 *
 * $Id$
 *}

{if $isError}
<p>
	<a name="formErrors"></a>
	<span class="formError">{translate key="form.errorsOccurred"}:</span>
	<ul class="formErrorList">
	{foreach key=field item=message from=$errors}
		<li>{translate key="$message"}</li>
	{/foreach}
	</ul>
</p>
<script type="text/javascript">
{literal}
<!--
// Jump to form errors.
window.location.hash="formErrors";
// -->
{/literal}
</script>
{/if}
