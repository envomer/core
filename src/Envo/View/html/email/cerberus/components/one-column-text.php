<!-- 1 Column Text : BEGIN -->
<tr>
	<td bgcolor="#ffffff">
		<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
			<tr>
				<td style="padding: 40px; font-family: sans-serif; font-size: 15px; line-height: 140%; color: #555555; text-align: <?php echo $section->align ?: 'justify' ?>">
                    <?php
                    /** @var \Core\Controller\EmailSection $paragraph */
					foreach($section->paragraphs as $paragraph) {
                        echo '<p style="margin: 0 0 10px 0;">'. $paragraph .'</p>';
                    }
                    ?>
				</td>
			</tr>
		</table>
	</td>
</tr>
<!-- 1 Column Text : END -->