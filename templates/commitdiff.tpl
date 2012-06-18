{*
 *  commitdiff.tpl
 *  gitphp: A PHP git repository browser
 *  Component: Commitdiff view template
 *
 *  Copyright (C) 2009 Christopher Han <xiphux@gmail.com>
 *}
{extends file='projectbase.tpl'}

{block name=javascriptpaths}
{if file_exists('js/commitdiff.min.js')}
GitPHPJSPaths.commitdiff = "commitdiff.min";
{/if}
{/block}
{block name=javascriptmodules}
	GitPHPJSModules = ['commitdiff'];
{/block}

{block name=main}

{assign var="baseurl"
      value="{$SCRIPT_NAME}?p={$project->GetProject('f')}"
}

 {* Nav *}
 <div class="page_nav">
   {if $commit}
   {assign var=tree value=$commit->GetTree()}
   {/if}
   {include file='nav.tpl' current='commitdiff' logcommit=$commit treecommit=$commit}
   <br />
   {if $sidebyside}
   <a href="{$baseurl}&amp;a=commitdiff&amp;h={$commit->GetHash()}{if $hashparent}&amp;hp={$hashparent}{/if}&amp;o=unified">{t}unified{/t}</a>
   {else}
   <a href="{$baseurl}&amp;a=commitdiff&amp;h={$commit->GetHash()}{if $hashparent}&amp;hp={$hashparent}{/if}&amp;o=sidebyside">{t}side by side{/t}</a>
   {/if}
   | <a href="{$baseurl}&amp;a=commitdiff_plain&amp;h={$commit->GetHash()}{if $hashparent}&amp;hp={$hashparent}{/if}">{t}plain{/t}</a>
 </div>

 {include file='title.tpl' titlecommit=$commit}

 <div class="page_body">
   {assign var=bugpattern value=$project->GetBugPattern()}
   {assign var=bugurl value=$project->GetBugUrl()}
   {assign var=comment value=$commit->GetComment()}
   {if end($comment) != $commit->GetTitle()}
   {foreach from=$comment item=line}
     {if strstr(trim($line),'-by: ') || strstr(trim($line),'Cc: ')}
     <span class="signedOffBy">{$line|htmlspecialchars|buglink:$bugpattern:$bugurl}</span>
     {elseif strstr(trim($line),'Link: ')}
     <span class="signedOffBy commentLink">{$line|buglink:'/(http(s)?:\/\/)(.)*[\.](.)*$/':"\$0"}</span>
     {elseif strncasecmp(trim($line),'Change-Id:',10) == 0}
     <span class="changeId">{$line|buglink:$bugpattern:$bugurl}</span>
     {else}
     {$line|htmlspecialchars|commithash|buglink:$bugpattern:$bugurl}
     {/if}
     <br />
   {/foreach}
   <br />
   {/if}

   {if $sidebyside && ($treediff->Count() > 1)}
    <div class="commitDiffSBS">

     <div class="SBSTOC">
       <ul>
       <li class="listcount">
       {t count=$treediff->Count() 1=$treediff->Count() plural="%1 files changed:"}%1 file changed:{/t} [+/- {$treediff->StatCount()}] <a href="#" class="showAll">{t}(show all){/t}</a></li>
       {foreach from=$treediff item=filediff}
       <li class="SBSTOCFile">
       <a href="#{$filediff->GetFromHash()}_{$filediff->GetToHash()}" class="SBSTOCItem">
       {if $filediff->GetStatus() == 'A'}
         {if $filediff->GetToFile()}{$filediff->GetToFile()}{else}{$filediff->GetToHash()}{/if}</a> <span class="add">{t}(new){/t}</span>
       {elseif $filediff->GetStatus() == 'D'}
         {if $filediff->GetFromFile()}{$filediff->GetFromFile()}{else}{$filediff->GetToFile()}{/if}</a> <span class="del">{t}(deleted){/t}</span>
       {elseif $filediff->GetStatus() == 'M'}
	 {if $filediff->GetFromFile()}
	   {assign var=fromfilename value=$filediff->GetFromFile()}
	 {else}
	   {assign var=fromfilename value=$filediff->GetFromHash()}
	 {/if}
	 {if $filediff->GetToFile()}
	   {assign var=tofilename value=$filediff->GetToFile()}
	 {else}
	   {assign var=tofilename value=$filediff->GetToHash()}
	 {/if}
	 {$fromfilename}</a>{if $fromfilename != $tofilename} -&gt; {$tofilename}{/if}
	 <span class="add">{if $filediff->totAdd}+{$filediff->totAdd}{/if}</span>
	 <span class="del">{if $filediff->totDel}-{$filediff->totDel}{/if}</span>
       {/if}
       </li>
       {/foreach}
       </ul>
     </div>

     <div class="SBSContent">
   {/if}

   {* Diff each file changed *}
   {foreach from=$treediff item=filediff}
     <div class="diffBlob diff-file" id="{$filediff->GetFromHash()}_{$filediff->GetToHash()}">
     <div class="diff_info">
     {if ($filediff->GetStatus() == 'D') || ($filediff->GetStatus() == 'M')}
       {assign var=localfromtype value=$filediff->GetFromFileType(1)}
       {$localfromtype}:<a href="{$baseurl}&amp;a=blob&amp;h={$filediff->GetFromHash()}&amp;hb={$commit->GetHash()}{if $filediff->GetFromFile()}&amp;f={$filediff->GetFromFile('f')}{/if}">{if $filediff->GetFromFile()}a/{$filediff->GetFromFile()}{else}{$filediff->GetFromHash()}{/if}</a>
       {if $filediff->GetStatus() == 'D'}
         {t}(deleted){/t}
       {/if}
     {/if}

     {if $filediff->GetStatus() == 'M'}
       -&gt;
     {/if}

     {if ($filediff->GetStatus() == 'A') || ($filediff->GetStatus() == 'M')}
       {assign var=localtotype value=$filediff->GetToFileType(1)}
       {$localtotype}:<a href="{$baseurl}&amp;a=blob&amp;h={$filediff->GetToHash()}&amp;hb={$commit->GetHash()}{if $filediff->GetToFile()}&amp;f={$filediff->GetToFile('f')}{/if}">{if $filediff->GetToFile()}b/{$filediff->GetToFile()}{else}{$filediff->GetToHash()}{/if}</a>
       {if $filediff->GetStatus() == 'A'}
         {t}(new){/t}
       {/if}
     {/if}

     {if $sidebyside and $filediff->GetStatus() == 'M'}
         <div class="diff-head-links">
         <a onclick="toggleTabs(this);" href="javascript:void(0)">{t}toggle tabs{/t}</a>, 
         <a onclick="toggleNumbers(this);" href="javascript:void(0)">{t}numbers{/t}</a> | 
         <a onclick="toggleLeft(this);" href="javascript:void(0)">{t}left only{/t}</a>
         <a onclick="toggleRight(this);" href="javascript:void(0)">{t}right only{/t}</a>
          | <a onclick="scrollToDiff(this,'tr.diff-focus:first');" href="javascript:void(0)">{t}first diff{/t}</a>
         <a onclick="scrollToDiff(this,'tr.diff-focus:last');" href="javascript:void(0)">{t}last diff{/t}</a>
         <!--<a href="#D{$filediff->diffCount}">{t}last diff{/t}</a>-->
         (<span class="diff-count">{$filediff->diffCount}</span>)
         </div>
     {/if}

     </div>

     {if $filediff->isPicture}
     <div class="diff_pict">
      {if $filediff->GetStatus() == 'A'}
       {t}(new){/t}
      {else}
       <img class="old" valign="middle" src="{$baseurl}&amp;a=blob_plain&amp;h={$filediff->GetFromHash()}&amp;f={$filediff->GetFromFile('f')}">
      {/if}
       <img class="new" valign="middle" src="{$baseurl}&amp;a=blob_plain&amp;h={$filediff->GetToHash()}&amp;f={$filediff->GetToFile('f')}">
     </div>
     {elseif $sidebyside}
        {include file='filediffsidebyside.tpl' diffsplit=$filediff->GetDiffSplit()}
     {else}
        {include file='filediff.tpl' diff=$filediff->GetDiff('', true, true)}
     {/if}
     </div>
   {/foreach}

   {if $sidebyside && ($treediff->Count() > 1)}
     </div>
     <div class="SBSFooter"></div>

    </div>
   {/if}


 </div>

{/block}
