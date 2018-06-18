<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
	<meta charset="utf-8"> <!-- utf-8 works for most cases -->
	<meta name="viewport" content="width=device-width"> <?php // Forcing initial-scale shouldn't be necessary ?>
	<meta http-equiv="X-UA-Compatible" content="IE=edge"> <?php // Use the latest (edge) version of IE rendering engine ?>
	<meta name="x-apple-disable-message-reformatting">  <?php // Disable auto-scale in iOS 10 Mail entirely ?>
	<title></title>

	<?php 
	/**
	<!-- The title tag shows in email notifications, like Android 4.4. -->
	
	<!-- Web Font / @font-face : BEGIN -->
	<!-- NOTE: If web fonts are not required, lines 10 - 27 can be safely removed. -->
	
	<!-- Desktop Outlook chokes on web font references and defaults to Times New Roman, so we force a safe fallback font. -->
	*/
	?>
	<!--[if mso]>
		<style>
			* {
				font-family: sans-serif !important;
			}
		</style>
	<![endif]-->
	<?php // All other clients get the webfont reference; some will render the font and others will silently fail to the fallbacks. More on that here: http://stylecampaign.com/blog/2015/02/webfont-support-in-email/ ?>

	<!--[if !mso]><!-->
	<?php // insert web font reference, eg: <link href='https://fonts.googleapis.com/css?family=Roboto:400,700' rel='stylesheet' type='text/css'> ?>
	<!--<![endif]-->
	
	<!-- Web Font / @font-face : END -->
	
	<!-- CSS Reset : BEGIN -->
	<style>
		
		<?php //What it does: Remove spaces around the email design added by some email clients. ?>
		<?php // Beware: It can remove the padding / margin and add a background color to the compose a reply window. ?>
		html, body {margin: 0 auto !important; padding: 0 !important; height: 100% !important; width: 100% !important;}
		body, p, div { font-family: arial; font-size: 14px; }
		body { color: #000000; }

		<?php //What it does: Stops email clients resizing small text. ?>
		* {-ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; }
		
		<?php //What it does: Centers email on Android 4.4 ?>
		div[style*="margin: 16px 0"] { margin: 0 !important; }
		
		<?php //What it does: Stops Outlook from adding extra spacing to tables. ?>
		table, td { mso-table-lspace: 0pt !important; mso-table-rspace: 0pt !important; }
		
		<?php //What it does: Fixes webkit padding issue. Fix for Yahoo mail table alignment bug. Applies table-layout to the first 2 tables then removes for anything nested deeper. ?>
		table { border-spacing: 0 !important; border-collapse: collapse !important; table-layout: fixed !important; margin: 0 auto !important; }
		table table table { table-layout: auto; }
		
		<?php //What it does: Uses a better rendering method when resizing images in IE. ?>
		img { -ms-interpolation-mode:bicubic; }
		
		<?php //What it does: A work-around for email clients meddling in triggered links. ?>
		*[x-apple-data-detectors], .x-gmail-data-detectors, .x-gmail-data-detectors *,
		.aBn {
			border-bottom: 0 !important;
			cursor: default !important;
			color: inherit !important;
			text-decoration: none !important;
			font-size: inherit !important;
			font-family: inherit !important;
			font-weight: inherit !important;
			line-height: inherit !important;
		}
		
		<?php //What it does: Prevents Gmail from displaying an download button on large, non-linked images. ?>
		.a6S { display: none !important; opacity: 0.01 !important; }

		<?php //If the above doesn't work, add a .g-img class to any image in question. ?>
		img.g-img + div { display: none !important; }
		
		<?php //What it does: Prevents underlining the button text in Windows 10 ?>
		.button-link { text-decoration: none !important; }
		
		<?php
			//What it does: Removes right gutter in Gmail iOS app: https://github.com/TedGoas/Cerberus/issues/89
			// Create one of these media queries for each additional viewport size you'd like to fix
			// Thanks to Eric Lepetit @ericlepetitsf) for help troubleshooting
		?>
		@media only screen and (min-device-width: 375px) and (max-device-width: 413px) {
			<?php // iPhone 6 and 6+ ?>
			.email-container { min-width: 375px !important; }
		}
		
		<?php // What it does: Forces Gmail app to display email full width ?>
		u ~ div .email-container { min-width: 100vw; }
	
	</style>
	<!-- CSS Reset : END -->
	
	<!-- Progressive Enhancements : BEGIN -->
	<style>
		
		<?php // What it does: Hover styles for buttons ?>
		.button-td, .button-a { transition: all 100ms ease-in; }
		.button-td:hover, .button-a:hover { background: #555555 !important; border-color: #555555 !important; }
		
		<?php // Media Queries ?>
		@media screen and (max-width: 480px) {
			<?php // What it does: Forces elements to resize to the full width of their container. Useful for resizing images beyond their max-width. ?>
			.fluid { width: 100% !important; max-width: 100% !important; height: auto !important; margin-left: auto !important; margin-right: auto !important; }
			
			<?php // What it does: Forces table cells into full-width rows. ?>
			.stack-column, .stack-column-center { display: block !important; width: 100% !important; max-width: 100% !important; direction: ltr !important; }

			<?php // And center justify these ones. ?>
			.stack-column-center { text-align: center !important; }
			
			<?php // What it does: Generic utility class for centering. Useful for images, buttons, and nested tables. ?>
			.center-on-narrow { text-align: center !important; display: block !important; margin-left: auto !important; margin-right: auto !important; float: none !important; }
			table.center-on-narrow { display: inline-block !important; }
			
			<?php // What it does: Adjust typography on small screens to improve readability ?>
			.email-container p { font-size: 17px !important; }
		}
	
	</style>
	<!-- Progressive Enhancements : END -->
	
	<?php // What it does: Makes background images in 72ppi Outlook render at correct size. ?>
	<!--[if gte mso 9]>
	<xml>
		<o:OfficeDocumentSettings>
			<o:AllowPNG/>
			<o:PixelsPerInch>96</o:PixelsPerInch>
		</o:OfficeDocumentSettings>
	</xml>
	<![endif]-->

</head>
<body width="100%" style="margin: 0; mso-line-height-rule: exactly;" bgcolor="<?php echo $this->getStyle('backgroundColor', '#ebebeb') ?>">
    <center style="width: 100%; background: <?php echo $this->getStyle('backgroundColor', '#ebebeb') ?>; text-align: left;">
    <!--[if mso | IE]>
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" bgcolor="#222222">
    <tr>
    <td>
    <![endif]-->
	
    <?php if($this->excerpt): ?>
	<!-- Visually Hidden Preheader Text : BEGIN -->
	<div style="display: none; font-size: 1px; line-height: 1px; max-height: 0px; max-width: 0px; opacity: 0; overflow: hidden; mso-hide: all; font-family: sans-serif;">
		<?php echo $this->excerpt ?>
	</div>
	<!-- Visually Hidden Preheader Text : END -->
    <?php endif; ?>
	
	<?php if($this->logo): ?>
	<?php
		/**
			Set the email width. Defined in two places:
			1. max-width for all clients except Desktop Windows Outlook, allowing the email to squish on narrow but never go wider than 680px.
			2. MSO tags for Desktop Windows Outlook enforce a 680px width.
			Note: The Fluid and Responsive templates have a different width (600px). The hybrid grid is more "fragile", and I've found that 680px is a good width. Change with caution.
		*/
	?>
	<div style="max-width: 680px; margin: auto;" class="email-container">
		<!--[if mso]>
		<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="680" align="center">
		<tr>
		<td>
		<![endif]-->
		
        
		<!-- Email Header : BEGIN -->
		<table role="presentation" cellspacing="0" cellpadding="0" border="0" align="center" width="100%" style="max-width: 680px;">
			<tr>
				<td style="padding: 20px 0; text-align: center">
					<img src="<?php echo $this->logo ?>" width="200" height="50" alt="alt_text" border="0" style="height: auto; background: #dddddd; font-family: sans-serif; font-size: 15px; line-height: 140%; color: #555555;">
				</td>
			</tr>
		</table>
		<!-- Email Header : END -->
        
		
		<!--[if mso]>
		</td>
		</tr>
		</table>
		<![endif]-->
	</div>
	<?php endif; ?>

    
    <!-- Full Bleed Background Section : BEGIN -->
    <div style="width: 100%; text-align: left; ">
        <div style="max-width: 680px; margin: auto;" class="email-container">
        	<!--[if mso]>
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="680" align="center">
            <tr>
            <td>
            <![endif]-->
            <?php foreach($this->sections as $section): ?>
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" align="center" width="100%">
                <tr>
                    <td valign="top" align="center">
                        <?php $this->parse($section) ?>
                    </td>
                </tr>
            </table>
            <?php endforeach; ?>
            <!--[if mso]>
            </td>
            </tr>
            </table>
            <![endif]-->
        </div>
    </div>
    <!-- Full Bleed Background Section : END -->
    

    <?php if($this->footer || $this->pixelPath): ?>
    <div style="width: 100%; background: <?php echo '#e4e4e4' ?: '#222222' ?>; text-align: left; text-align: center;">
    	<?php
        /**
			Set the email width. Defined in two places:
			1. max-width for all clients except Desktop Windows Outlook, allowing the email to squish on narrow but never go wider than 680px.
			2. MSO tags for Desktop Windows Outlook enforce a 680px width.
			Note: The Fluid and Responsive templates have a different width (600px). The hybrid grid is more "fragile", and I've found that 680px is a good width. Change with caution.
		*/
		?>
        <div style="max-width: 680px; margin: auto; background: #333" class="email-container">
            <!--[if mso]>
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="680" align="center">
                <tr>
                    <td>
            <![endif]-->

            <!-- Email Footer : BEGIN -->
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" align="center" width="100%" style="max-width: 680px; font-family: sans-serif; color: #eeeeee; font-size: 12px; line-height: 140%;">
                <tr>
                    <td style="padding: 20px 10px 40px; width: 100%; font-family: sans-serif; font-size: 12px; line-height: 140%; text-align: center; color: #eeeeee;" class="x-gmail-data-detectors">
                        <?php
                        if($this->footer) {
                            echo '<br><br>' . $this->footer;
                        }
                        ?>
						<?php
						if($this->unsubscribe) {
							echo '<br><br>';
							echo '<unsubscribe style="color: #eeeeee; text-decoration: underline;">unsubscribe</unsubscribe>';
						}

						if($this->pixelPath) {
							echo '<img width="1" height="1" src="'. $this->pixelPath . '" style="display: inline-block; width: 1px; height: 1px;" />';
						}
						?>
                    </td>
                </tr>
            </table>
            <!-- Email Footer : END -->

            <!--[if mso]>
            </td>
            </tr>
            </table>
            <![endif]-->
        </div>
    </div>

	<?php
	endif;
    //include 'components/full-bleed-background.php';
    ?>

 <!--[if mso | IE]>
    </td>
    </tr>
    </table>
    <![endif]-->
    </center>
</body>
</html>