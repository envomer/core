<!-- Thumbnail Left, Text Right : BEGIN -->
<tr>
	<!-- dir=ltr is where the magic happens. This can be changed to dir=rtl to swap the alignment on wide while maintaining stack order on narrow. -->
	<td dir="ltr" bgcolor="<?php echo $section->style ? $section->style->containerColor : '#ffffff' ?>" align="center" height="100%" valign="top" width="100%" style="padding: 10px 0;">
		<!--[if mso]>
		<table role="presentation" border="0" cellspacing="0" cellpadding="0" align="center" width="660" style="width: 660px;">
			<tr>
				<td align="center" valign="top" width="660" style="width: 660px;">
		<![endif]-->
		<table role="presentation" border="0" cellpadding="0" cellspacing="0" align="center" width="100%" style="max-width:660px;">
			<tr>
				<td align="center" valign="top" style="font-size:0; padding: 10px 0;">
					<!--[if mso]>
					<table role="presentation" border="0" cellspacing="0" cellpadding="0" align="center" width="660" style="width: 660px;">
						<tr>
							<td align="left" valign="top" width="220" style="width: 220px;">
					<![endif]-->
					<div style="display:inline-block; margin: 0 -2px; max-width: 200px; min-width:160px; vertical-align:top; width:100%;" class="stack-column">
						<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
							<tr>
								<td dir="ltr" style="padding: 0 10px 10px 10px;">
									<?php
									/** @var \Core\Controller\EmailSection $paragraph */
									foreach($section->images as $image) {
									    $src = $image ?: 'http://placehold.it/200';
										echo '<img src="'.$src.'" width="200" height="" border="0" alt="alt_text" class="center-on-narrow" style="width: 100%; max-width: 200px; height: auto; background: #dddddd; font-family: sans-serif; font-size: 15px; line-height: 140%; color: #555555; margin-bottom: 15px">';
									}
									?>
								</td>
							</tr>
						</table>
					</div>
					<!--[if mso]>
					</td>
					<td align="left" valign="top" width="440" style="width: 440px;">
					<![endif]-->
					<div style="display:inline-block; margin: 0 -2px; max-width:66.66%; min-width:320px; vertical-align:top;" class="stack-column">
						<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
							<tr>
								<td dir="ltr" style="font-family: sans-serif; font-size: 15px; line-height: 140%; color: <?php echo $section->getStyle('color', '#555555') ?>; padding: 10px 10px 0; text-align: left;" class="center-on-narrow">
									<h2 style="margin: 0 0 10px 0; font-family: sans-serif; font-size: 18px; line-height: 125%; color: <?php echo $section->getStyle('color', '#333333') ?>; font-weight: bold;"><?php echo $section->title ?: '' ?></h2>
									<?php
									/** @var \Core\Controller\EmailSection $paragraph */
									foreach($section->paragraphs as $paragraph) {
										echo '<p style="margin: 0 0 10px 0;">'. nl2br($paragraph) .'</p>';
									}
                                    
                                    if($section->link):
									?>
									<!-- Button : BEGIN -->
									<table role="presentation" cellspacing="0" cellpadding="0" border="0" class="center-on-narrow" style="float:left;">
										<tr>
											<td style="border-radius: 3px; background: <?php echo $section->getStyle('btnBackgroundColor', '#333333') ?>; text-align: center;" class="button-td">
												<a href="http://www.google.com" style="background: <?php echo $section->getStyle('btnBackgroundColor', '#333333') ?>; border: 15px solid <?php echo $section->getStyle('btnBackgroundColor', '#333333') ?>; font-family: sans-serif; font-size: 13px; line-height: 110%; text-align: center; text-decoration: none; display: block; border-radius: 3px; font-weight: bold;" class="button-a">
													<span style="color:<?php echo $section->getStyle('btnColor', '#ffffff') ?>;" class="button-link"><?php echo $section->linkTitle ?: 'Link' ?></span>
												</a>
											</td>
										</tr>
									</table>
									<!-- Button : END -->
                                    <?php endif ?>
								</td>
							</tr>
						</table>
					</div>
					<!--[if mso]>
					</td>
					</tr>
					</table>
					<![endif]-->
				</td>
			</tr>
		</table>
		<!--[if mso]>
		</td>
		</tr>
		</table>
		<![endif]-->
	</td>
</tr>
<!-- Thumbnail Left, Text Right : END -->