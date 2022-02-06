<!doctype html>
<html>
<head>
	<title><?=$layout_title?></title>
	<?php include("header.php"); ?>
</head>

<body style="width:100%; font-size: <?=$loguser['fontsize']; ?>%;">
<div id="body">
<div id="body-wrapper">
	<div class="outline margin" id="header" style="margin: 8px">
		<table class="outline">
			<tr>
				<td colspan="3" class="cell0">
					<!-- Board header goes here -->
					<table>
						<tr>
							<td style="border: 0px none; text-align: <?=$layout_pora ? 'left' : 'center' ?>;">
								<a href="<?=$boardroot;?>">
									<img id="theme_banner" src="<?=htmlspecialchars($layout_logopic); ?>" alt="" title="<?=htmlspecialchars($layout_logotitle); ?>">
								</a>
							</td>
							<?php if ($layout_pora) { ?>
							<td style="border: 0px none;">
								<?=$layout_pora; ?>
							</td>
							<?php } ?>
						</tr>
					</table>
				</td>
			</tr>
			<tr class="cell2">
				<td class="smallFonts" style="text-align: center; width: 10%;">
					<?=$layout_views; ?>
				</td>
				<td class="smallFonts" style="text-align: center">
					<ul class="pipemenu">
					<?=$layout_userpanel->build(); ?>
					</ul>
				</td>
				<td class="smallFonts" style="text-align: center; width: 10%;">
					<?=$layout_time; ?>
				</td>
			</tr>
			<tr class="cell1">
				<td colspan="3" class="smallFonts" style="text-align: center; width: 80%;">
					<ul class="pipemenu">
					<?=$layout_navigation->build();?>
					</ul>
				</td>
			</tr>
			<tr class="cell2">
				<td colspan="3" class="smallFonts" style="text-align: center">
					<?=$layout_onlineusers; ?>
				</td>
			</tr>
		</table>
	</div>

	<div id="main" style="padding-left:8px; padding-right:8px;">
<form action="<?=actionLink('login'); ?>" method="post" id="logout">
		<input type="hidden" name="action" value="logout">
	</form>

	<?=$layout_bars; ?>
	<div class="margin">
		<div style="float: right;">
			<?=$layout_links->build();?>
		</div>
		<?=$layout_crumbs->build();?>&nbsp;
	</div>
	<?=$layout_contents;?>
	<div class="margin">
		<div style="float: right;">
			<?=$layout_links->build();?>
		</div>
		<?=$layout_crumbs->build();?>&nbsp;
	</div>

	</div>
	<div class="footer" style='clear:both;'>
	<?=$layout_footer;?>
	</div>
</div>
</div>
</body>
</html>
