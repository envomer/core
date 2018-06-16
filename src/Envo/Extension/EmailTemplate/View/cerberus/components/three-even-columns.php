<!-- 3 Even Columns : BEGIN -->
<?php /** @var \Core\Controller\EmailSection $section */ ?>
<tr>
	<td bgcolor="#ffffff" align="center" height="100%" valign="top" width="100%" style="padding: 10px 0;">
		<!--[if mso]>
		<table role="presentation" border="0" cellspacing="0" cellpadding="0" align="center" width="660">
			<tr>
				<td align="center" valign="top" width="660">
		<![endif]-->
		<table role="presentation" border="0" cellpadding="0" cellspacing="0" align="center" width="100%" style="max-width:660px;">
			<tr>
				<td align="center" valign="top" style="font-size:0;">
					<!--[if mso]>
					<table role="presentation" border="0" cellspacing="0" cellpadding="0" align="center" width="660">
						<tr>
							<td align="left" valign="top" width="220">
					<![endif]-->
					<div style="display:inline-block; margin: 0 -2px; max-width:33.33%; min-width:220px; vertical-align:top; width:100%;" class="stack-column">
						<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
							<tr>
								<td style="padding: 10px 10px;">
									<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="font-size: 14px;text-align: left;">
										<tr>
											<td>
												<img src="<?php echo isset($section->images[0]) ? $section->images[0] : 'http://placehold.it/200/222' ?>" width="200" height="" border="0" alt="alt_text" class="center-on-narrow" style="width: 100%; max-width: 200px; height: auto; background: #dddddd; font-family: sans-serif; font-size: 15px; line-height: 140%; color: <?php $this->getStyle('color', '#222222') ?>;">
											</td>
										</tr>
										<tr>
											<td style="font-family: sans-serif; font-size: 15px; line-height: 140%; color: <?php $this->getStyle('color', '#222222') ?>; padding-top: 10px;" class="stack-column-center">
												<?php echo isset($section->paragraphs[0]) ? '<p style="margin: 0;">'. nl2br($section->paragraphs[0]) .'</p>' : '' ?>
											</td>
										</tr>
									</table>
								</td>
							</tr>
						</table>
					</div>
					<!--[if mso]>
					</td>
					<td align="left" valign="top" width="220">
					<![endif]-->
					<div style="display:inline-block; margin: 0 -2px; max-width:33.33%; min-width:220px; vertical-align:top; width:100%;" class="stack-column">
						<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
							<tr>
								<td style="padding: 10px 10px;">
									<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="font-size: 14px;text-align: left;">
										<tr>
											<td>
												<img src="<?php echo isset($section->images[1]) ? $section->images[1] : 'http://placehold.it/200/222' ?>" width="200" height="" border="0" alt="alt_text" class="center-on-narrow" style="width: 100%; max-width: 200px; height: auto; background: #dddddd; font-family: sans-serif; font-size: 15px; line-height: 140%; color: <?php $this->getStyle('color', '#222222') ?>;">
											</td>
										</tr>
										<tr>
											<td style="font-family: sans-serif; font-size: 15px; line-height: 140%; color: <?php $this->getStyle('color', '#222222') ?>; padding-top: 10px;" class="stack-column-center">
												<?php echo isset($section->paragraphs[1]) ? '<p style="margin: 0;">'. nl2br($section->paragraphs[1]) .'</p>' : '' ?>
											</td>
										</tr>
									</table>
								</td>
							</tr>
						</table>
					</div>
					<!--[if mso]>
					</td>
					<td align="left" valign="top" width="220">
					<![endif]-->
					<div style="display:inline-block; margin: 0 -2px; max-width:33.33%; min-width:220px; vertical-align:top; width:100%;" class="stack-column">
						<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
							<tr>
								<td style="padding: 10px 10px;">
									<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="font-size: 14px;text-align: left;">
										<tr>
											<td>
                                                <img src="<?php echo isset($section->images[2]) ? $section->images[2] : 'http://placehold.it/200/333' ?>" width="200" height="" border="0" alt="alt_text" class="center-on-narrow" style="width: 100%; max-width: 200px; height: auto; background: #dddddd; font-family: sans-serif; font-size: 15px; line-height: 140%; color: <?php $this->getStyle('color', '#222222') ?>;">
											</td>
										</tr>
										<tr>
											<td style="font-family: sans-serif; font-size: 15px; line-height: 140%; color: <?php $this->getStyle('color', '#222222') ?>; padding-top: 10px;" class="stack-column-center">
												<?php echo isset($section->paragraphs[2]) ? '<p style="margin: 0;">'. nl2br($section->paragraphs[2]) .'</p>' : '' ?>
											</td>
										</tr>
									</table>
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
<!-- 3 Even Columns : END -->