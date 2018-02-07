<!-- Background Image with Text : BEGIN -->
<tr>
	<!-- Bulletproof Background Images c/o https://backgrounds.cm -->
	<td background="<?php echo $section->images ? $section->images[0] : 'http://placehold.it/680x180/222222/666666' ?>" bgcolor="#222222" valign="middle" style="text-align: center; background-position: center center !important; background-size: cover !important;">
		<!--[if gte mso 9]>
		<v:image xmlns:v="urn:schemas-microsoft-com:vml" fill="true" stroke="false" style="border: 0; display: inline-block; width: 680px; height: 180px;" src="<?php echo $section->images ? $section->images[0] : 'http://placehold.it/680x180/222222/666666' ?>" />
		<v:rect xmlns:v="urn:schemas-microsoft-com:vml" fill="true" stroke="false" style="border: 0; display: inline-block; position: absolute; width: 680px; height: 180px;">
			<v:fill opacity="0%" color="#222222" />
		<![endif]-->
		<div>
			<!--[if mso]>
			<table role="presentation" border="0" cellspacing="0" cellpadding="0" align="center" width="500">
				<tr>
					<td align="center" valign="top" width="500">
			<![endif]-->
			<table role="presentation" border="0" cellpadding="0" cellspacing="0" align="center" width="100%" style="max-width:500px; margin: auto;">
				<tr>
					<td valign="middle" style="text-align: center; padding: 40px 20px; font-family: sans-serif; font-size: 15px; line-height: 140%; color: #ffffff;">
						<?php
						/** @var \Core\Controller\EmailSection $paragraph */
						foreach($section->paragraphs as $paragraph) {
							echo '<p style="margin: 0 0 10px 0;">'. nl2br($paragraph) .'</p>';
						}
						?>
					</td>
				</tr>
			</table>
			<!--[if mso]>
			</td>
			</tr>
			</table>
			<![endif]-->
		</div>
		<!--[if gte mso 9]>
		</v:fill>
		</v:rect>
		</v:image>
		<![endif]-->
	</td>
</tr>
<!-- Background Image with Text : END -->