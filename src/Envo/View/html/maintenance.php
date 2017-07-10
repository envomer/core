<!DOCTYPE html>
<html>
<head>
	<title>In maintenance</title>

	<style type="text/css">
		*{ box-sizing: border-box }
		html{font-size:100%;-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%}
		html,button,input,select,textarea{font-family:sans-serif}
		body, html { margin: 0; padding: 0; background: #fff; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #23262B; text-align: center; height: 100%; }
		body { font-weight:100; font-size:13px; }
		body { background: radial-gradient(ellipse at center, #222 0%,#111 68%,#0f0f15 100%); color: #ddd }
		h1 { color: #19B5FE; padding: 0 50px; width: 400px; margin: 50pt auto 50px; text-align: center; font-size: 60pt; }
		h1 b, h3 span { color: #E04157; }
        h3 { max-width: 600px; width: 90%; margin: auto; margin-top: 20%; z-index: 1; position: relative; }
		p { color: rgba(0,0,0,0.8); font-size: 14pt; margin: 0; }
		p span { color: #2ABB9B; }

		.not-found { font-size: 20pt; padding: 10px; }
		.not-found b { display: block; font-size: 100pt  }

		.right { text-align: right; }

		.footer { position: absolute; bottom: 40px; border-top: 1px solid rgba(255,255,255,0.1); width: 90%; max-width: 600px; margin: auto; left: 0; right: 0; padding-top: 10px; font-size: 13pt }
		.footer div { display: inline-block; width: 32%; vertical-align: top; color: #ccc; font-weight: bold }
		.footer b { color: #19B5FE }
		.footer div:first-child { text-align: left; }
		.footer div:last-child { text-align: right; }

		#copy { position: fixed; top: 50px; right: 60px; height: 50px; width: 100px }
        #copy img { max-height: 100px; max-width: 100px; opacity: 0.8; }
	</style>
</head>
<body>
    <div class="not-found">
        <i class="ex ex-sad ex-7x"></i>
        <h3>
            Wir führen gerade ein <span>Update</span> durch.<br>
            Dies kann einige Zeit dauern. Versuchen Sie es später nochmal.
        </h3>

        <br><br>
    </div>

	<!--<div id="copy"><img src="/img/logo.png" alt="logo"></div>-->

	<div class="footer">
		<div class="part">Dauer: <b> ~<?php echo number_format($maintenance->retry/60, 2) ?> Std</b></div>
		<div class="part">Fortschritt: <b><?php echo $maintenance->progress  ?>%</b></div>
		<div class="part">- <b><?php echo getenv('APP_NAME') ?: 'envo' ?></b></div>
	</div>
</body>
</html>
