<?php if(!defined('IN_SUPESITE')) exit('Access Denied'); ?></div>
</div>

<div class="side">
<?php include template('admin/tpl/side.htm',1); ?>
</div>
</div>
<div id="footer">
<p>
Powered by <a  href="http://x.discuz.net" target="_blank" title="<?php echo S_RELEASE;; ?>">SupeSite</a> <?php echo S_VER;; ?> 
<?php if(!empty($_SCONFIG['licensed'])) { ?>
<a  href="http://license.comsenz.com/?pid=7&host=<?=$_SERVER['HTTP_HOST']?>" target="_blank">Licensed</a>
<?php } ?>
, 
Copyright 2001-2009 <a  href="http://www.comsenz.com/" target="_blank">Comsenz Inc.</a>
</p>
<p><?php echo debuginfo();; ?></p>
</div>
</div>
<iframe id="phpframe" name="phpframe" width="0" height="0" marginwidth="0" frameborder="0" src="about:blank"></iframe>
</body>
</html>