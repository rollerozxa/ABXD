<!doctype html>
<html>
<head>
	<title><?=$layout_title?></title>
	<?php include("header.php"); ?>
</head>
<body style="width:100%; font-size: <?=$loguser['fontsize']; ?>%;">
<div id="body">
<div id="body-wrapper">
	<div id="main" style="padding:8px;">
		<div class="outline margin" id="header">
			<table class="outline margin">
				<tr>
					<td colspan="3" class="cell0">
						<!-- Board header goes here -->
						<table>
							<tr>
								<td style="border: 0px none; text-align: left;">
									<a href="<?=$boardroot;?>">
										<img id="theme_banner" src="<?=htmlspecialchars($layout_logopic); ?>" alt="" title="<?=htmlspecialchars($layout_logotitle); ?>" style="padding: 8px;">
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
				<tr class="cell1 mainMenuContainer">
					<td>
						<div class="userDropdownContainer">
						<?=userLink($loguser, true);
							if ($loguserid) {
								$layout_userpanel->shift();
								$layout_userpanel->setClass("userMenu");
							}
							print $layout_userpanel->build();
							$layout_navigation->setClass("mainMenu");
							?>
						</div>
						<?=$layout_navigation->build(); ?>
					</td>
				</tr>
			</table>
		</div>
	<div style="text-align: right;" class="nOnlineUsers">
		<?=$layout_onlineusers; ?>
	</div>
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
