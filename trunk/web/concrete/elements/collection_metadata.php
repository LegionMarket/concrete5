<?
defined('C5_EXECUTE') or die(_("Access Denied."));
global $c;
Loader::model('collection_types');
Loader::model('collection_attributes');
$dt = Loader::helper('form/date_time');
$uh = Loader::helper('form/user_selector');

if ($cp->canAdminPage()) {
	$ctArray = CollectionType::getList();
}
?>
<div class="ccm-pane-controls">
<form method="post" name="permissionForm" id="ccmMetadataForm" action="<?=$c->getCollectionAction()?>">
<input type="hidden" name="rel" value="<?=$_REQUEST['rel']?>" />

	<script type="text/javascript"> 
		
		function ccm_triggerSelectUser(uID, uName) {
			$('#ccm-uID').val(uID);
			$('#ccm-uName').html(uName);
		}
		
		
		var ccm_activePropertiesTab = "ccm-properties-standard";
		
		$("#ccm-properties-tabs a").click(function() {
			$("li.ccm-nav-active").removeClass('ccm-nav-active');
			$("#" + ccm_activePropertiesTab + "-tab").hide();
			ccm_activePropertiesTab = $(this).attr('id');
			$(this).parent().addClass("ccm-nav-active");
			$("#" + ccm_activePropertiesTab + "-tab").show();
		});
		
		$(function() {
			$("#ccmMetadataForm").ajaxForm({
				type: 'POST',
				iframe: true,
				beforeSubmit: function() {
					jQuery.fn.dialog.showLoader();
				},
				success: function(r) {
					try {
						var r = eval('(' + r + ')');
						if (r != null && r.rel == 'SITEMAP') {
							jQuery.fn.dialog.hideLoader();
							jQuery.fn.dialog.closeTop();
							ccmSitemapHighlightPageLabel(r.cID, r.name);
						} else {
							ccm_mainNavDisableDirectExit();
							ccm_hidePane(function() {
								jQuery.fn.dialog.hideLoader();						
							});
						}
						ccmAlert.hud(ccmi18n.savePropertiesMsg, 2000, 'success', ccmi18n.properties);
					} catch(e) {
						alert(r);
					}
				}
			});
		});
	</script>
	
	<style type="text/css">
	.ccm-field-meta #newAttrValueRows{ margin-top:4px; }
	.ccm-field-meta .newAttrValueRow{margin-top:4px}	
	.ccm-field-meta input.faint{ color:#999 }
	
	#ccm-properties-custom-tab, #ccm-properties-standard-tab, #ccm-page-paths-tab {
		margin-top: 12px;
	}
	</style>
	
	<h1><?=t('Page Properties')?></h1>

	
	<div id="ccm-required-meta">
	
		
	<ul class="ccm-dialog-tabs" id="ccm-properties-tabs">
		<li class="ccm-nav-active"><a href="javascript:void(0)" id="ccm-properties-standard"><?=t('Standard Properties')?></a></li>
		<li><a href="javascript:void(0)" id="ccm-page-paths"><?=t('Page Paths and Location')?></a></li>
		<li><a href="javascript:void(0)" id="ccm-properties-custom"><?=t('Custom Fields')?></a></li>
	</ul>

	<div id="ccm-properties-standard-tab">
	
	<div class="ccm-field-one">
	<label><?=t('Name')?></label> <input type="text" name="cName" value="<?=htmlentities( $c->getCollectionName(), ENT_QUOTES, APP_CHARSET) ?>" class="ccm-input-text">
	</div>
	
	<div class="ccm-field-one">
	
	<label><?=t('Public Date/Time')?></label> 
	<? 
	print $dt->datetime('cDatePublic', $c->getCollectionDatePublic(null, 'user')); ?>
	</div>
	
	<div class="ccm-field-two">
	<label><?=t('Owner')?></label>
	
		<? 
		print $uh->selectUser('uID', $c->getCollectionUserID());
		?>
		
	</div>
		
	
	<div class="ccm-field">
	<label><?=t('Description')?></label> <textarea name="cDescription" class="ccm-input-text" style="width: 570px; height: 50px"><?=$c->getCollectionDescription()?></textarea>
	</div>
	
	</div>
	
	<div id="ccm-page-paths-tab" style="display: none">
		
		<div class="ccm-field">
		<label><?= t('Canonical URL')?></label>
		<?php if (!$c->isGeneratedCollection()) { ?>
			<?=BASE_URL . DIR_REL . DISPATCHER_FILENAME .
			substr($c->getCollectionPath(), 0, -1 * strlen($c->getCollectionHandle()))?><input type="text" name="cHandle" class="ccm-input-text" value="<?php echo $c->getCollectionHandle()?>" id="cHandle"><input type="hidden" name="oldCHandle" value="<?php echo $c->getCollectionHandle()?>"><br /><br />
		<?php  } else { ?>
			<?php echo $c->getCollectionHandle()?><br /><br />
		<?php  } ?>
		<div class="ccm-note"><?=t('This page must always be available from at least one URL. That URL is listed above.')?></div>
		</div>

		<?php if (!$c->isGeneratedCollection()) { ?>
			<label><?= t('Additional Page URL(s)') ?></label>
	
			<div class="ccm-field">
			<?php
				$paths = $c->getPagePaths();
				foreach ($paths as $path) {
					if (!$path['ppIsCanonical']) {
						$ppID = $path['ppID'];
						$cPath = $path['cPath'];
						echo '<span class="ccm-meta_path">' .
			     			'<input type="text" name="ppURL-' . $ppID . '" class="ccm-input-text" value="' . $cPath . '" id="ppID-'. $ppID . '"> ' .
			     			'<a href="javascript:void(0)" class="ccm-meta-path-del">' . t('Remove Path') . '</a></span>'."\n";
					}
				}
			?>
		    <span class="ccm-meta-path">
	     		<input type="text" name="ppURL-add-0" class="ccm-input-text" value="" id="ppID-add-0">
		 		<a href="javascript:void(0)" class="ccm-meta-path-add"><?=t('Add Path')?></a>
			</span>
			</div>
		<?php } ?>
	
	</div>
	
	<div id="ccm-properties-custom-tab" style="display: none">
		<? Loader::element('collection_metadata_fields', array('c'=>$c ) ); ?>
	</div>
	
	
	<input type="hidden" name="update_metadata" value="1" />
	<input type="hidden" name="processCollection" value="1">
	<div class="ccm-spacer">&nbsp;</div>
</form>
</div>
	<div class="ccm-buttons">
<!--	<a href="javascript:void(0)" onclick="ccm_hidePane()" class="ccm-button-left cancel"><span><em class="ccm-button-close">Cancel</em></span></a>//-->
	<a href="javascript:void(0)" onclick="$('#ccmMetadataForm').submit()" class="ccm-button-right accept"><span><?=t('Save')?></span></a>
	</div>
