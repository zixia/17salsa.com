<?exit?>
<!--{template header}-->
</div><!--header end-->

<div id="nav">
	<div class="main_nav">
		<ul>
			<!--{if empty($_SCONFIG['defaultchannel'])}-->
			<li><a href="{S_URL}/index.php">首页</a></li>
			<!--{/if}-->
			<!--{loop $channels['menus'] $key $value}-->
			<li <!--{if $key == 'news' }--> class="current"<!--{/if}-->><a href="$value[url]">$value[name]</a></li>
			<!--{/loop}-->
		</ul>
	</div>

	<!--{block name="category" parameter="type/news/isroot/1/order/c.displayorder/limit/0,100/cachetime/80800/cachename/category"}-->
	<ul class="ext_nav clearfix">
		<!--{eval $dot = '|'}-->
		<!--{eval $total = count($_SBLOCK['category'])}-->
		<!--{eval $i = 1;}-->
		<!--{loop $_SBLOCK['category'] $value}-->
		<li><a href="$value[url]">$value[name]</a><!--{if $total != $i}-->&nbsp;$dot<!--{/if}--></li>
		<!--{eval $i++;}-->
		<!--{/loop}-->
	</ul>
</div><!--nav end-->

<div class="column">
	<div class="col1">

		<div class="global_module margin_bot10 bg_fff">
			<div class="global_module3_caption"><h3>你的位置：<a href="{S_URL}/">$_SCONFIG[sitename]</a>
			&gt;&gt; $title</h3></div>
			<!--{if $tag[spacenewsnum]}-->
			<!--{block name="spacetag" parameter="tagid/$tag[tagid]/type/news/order/st.dateline DESC/perpage/20/cachetime/86400/cachename/news/tpl/data"}-->
			<ul class="global_tx_list4">
				<!--{loop $_SBLOCK['news'] $value}-->
				<li><span class="box_r"><a href="#uid/$value[uid]#">$value[username]</a></span><a href="$value[url]" title="$value[subjectall]">$value[subject]</a></li>
				<!--{/loop}-->	
			</ul>
			<!--{/if}-->
			<!--{if $_SBLOCK['news_multipage']}-->
				 $_SBLOCK['news_multipage']
			<!--{/if}-->
		</div>

	</div><!--col1 end-->
	<div class="col2">
		<div id="user_login">
			<script src="{S_URL}/batch.panel.php?rand={eval echo rand(1, 999999)}" type="text/javascript" language="javascript"></script>
		</div><!--user_login end-->
		
		<div class="global_module margin_bot10 bg_fff">
			<div class="global_module2_caption"><h3>相关TAG</h3></div>
			<ul class="tag_list clearfix">
				<!--{if $tag[relativetags]}-->
				<!--{loop $tag[relativetags] $key $value}-->
				<a href="#action/tag/tagname/$key#">$value</a>
				<!--{/loop}-->
				<!--{else}-->
				暂无相关TAG
				<!--{/if}-->
			</ul>
		</div>
	</div><!--col2 end-->
</div><!--column end-->

<!--{template footer}-->