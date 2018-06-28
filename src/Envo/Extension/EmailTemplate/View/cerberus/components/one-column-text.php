<!-- 1 Column Text : BEGIN -->
<tr>
	<td bgcolor="#ffffff">
		<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
			<tr>
				<td style="padding: 40px; font-family: sans-serif; font-size: 15px; line-height: 140%; color: <?php $this->getStyle('color', '#222222') ?>; text-align: <?php echo $section->align ?: 'justify' ?>">
					<?php if($section->title): ?>
					<h2 style="margin: 0 0 10px 0; font-family: sans-serif; font-size: 18px; line-height: 125%; color: <?php echo $section->getStyle('color', '#333333') ?>; font-weight: bold;"><?php echo $section->title ?: '' ?></h2>
                    <?php
                	endif;
                    /** @var \Core\Controller\EmailSection $paragraph */
					foreach($section->paragraphs as $paragraph) {
                        echo '<p style="margin: 0 0 10px 0;">'. nl2br($paragraph) .'</p>';
                    }
                    ?>
				</td>
			</tr>
		</table>
	</td>
</tr>
<!-- 1 Column Text : END -->