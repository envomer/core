<!-- 1 Column Text + Button : BEGIN -->
<tr>
	<td bgcolor="#ffffff">
		<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
            <?php if($section->title): ?>
			<tr>
				<td style="padding: 40px 40px 20px; ">
					<h1 style="margin: 0; font-family: sans-serif; font-size: 20px; line-height: 125%; color: #444444; font-weight: bold;"><?php echo strtoupper($section->title) ?></h1>
				</td>
			</tr>
            <?php
            endif;
            if($section->paragraphs):
            ?>
			<tr>
				<td style="padding: 0 40px 40px; font-family: sans-serif; font-size: 15px; line-height: 140%; color: <?php $this->getStyle('color', '#222222') ?>; text-align: <?php echo $section->align ?: 'left' ?>;">
					<?php
					/** @var \Core\Controller\EmailSection $paragraph */
					foreach($section->paragraphs as $paragraph) {
						echo '<p style="margin: 0 0 10px 0;">'. nl2br($paragraph) .'</p>';
					}
					?>
				</td>
			</tr>
            <?php
            endif;
            if($section->link):
            ?>
			<tr>
				<td style="padding: 0 40px 40px; font-family: sans-serif; font-size: 15px; line-height: 140%; color: <?php $this->getStyle('color', '#222222') ?>;">
					<!-- Button : BEGIN -->
					<table role="presentation" cellspacing="0" cellpadding="0" border="0" align="center" style="margin: auto;">
						<tr>
							<td style="border-radius: 3px; background: #222222; text-align: center;" class="button-td">
								<a href="<?php echo $section->link ?>" style="background: #222222; border: 15px solid #222222; font-family: sans-serif; font-size: 13px; line-height: 110%; text-align: center; text-decoration: none; display: block; border-radius: 3px; font-weight: bold;" class="button-a">
									<span style="color:#ffffff;" class="button-link"><?php echo $section->linkTitle ?: 'Link' ?></span>
								</a>
							</td>
						</tr>
					</table>
					<!-- Button : END -->
				</td>
			</tr>
		    <?php endif; ?>
		</table>
	</td>
</tr>
<!-- 1 Column Text + Button : END -->