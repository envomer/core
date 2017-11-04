<!DOCTYPE html>
<html>
<head>
	<title>In maintenance</title>

	<style type="text/css">
		*{ box-sizing: border-box }
		html{
            -webkit-text-size-adjust:100%;
            font-family: -apple-system, BlinkMacSystemFont, "myriad-pro", sans-serif;
            font-weight: 400;
            background-color: #ffffff;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
		body, html { margin: 0; padding: 0; background: #fff; color: #23262B; text-align: center; height: 100%; }
		body {  font-size:13px; color: #ddd }
		h1 { color: #19B5FE; padding: 0 50px; width: 400px; margin: 50pt auto 50px; text-align: center; font-size: 60pt; }
		h1 b, h3 span { color: #E04157; }
        h3 { max-width: 700px; width: 90%; margin: 20% auto auto; z-index: 1; position: relative; padding: 60px 40px; font-weight: normal }
		p { color: rgba(0,0,0,0.8); font-size: 14pt; margin: 0; }
		p span { color: #2ABB9B; }

		.not-found { font-size: 20pt; padding: 10px; }
		.not-found b { display: block; font-size: 100pt  }

		.right { text-align: right; }

		.footer { position: absolute; bottom: 20px; margin: auto; left: 0; right: 0;; font-size: 11pt; background: #000; padding: 15px; }
		.footer div { display: inline-block; width: 32%; vertical-align: top; color: #ccc; }
		.footer div:first-child b { color: #19B5FE }
		.footer div:nth-child(2) b { color: #7b22aa }
		.footer div:last-child b { color: #b223a1 }
		.footer div:first-child { text-align: left; }
		.footer div:last-child { text-align: right; }
        .sub { position: absolute; bottom: 70px; height: 5px; left: 0; right: 0; }

		#copy { position: fixed; top: 50px; right: 60px; height: 50px; width: 100px }
        #copy img { max-height: 100px; max-width: 100px; opacity: 0.8; }
        .purple {
            background: #b223a1;
            background: -moz-linear-gradient(-45deg, #b223a1 0%, #7b22aa 27%, #271f9b 55%, #322cd6 96%);
            background: -webkit-linear-gradient(-45deg, #b223a1 0%,#7b22aa 27%,#271f9b 55%,#322cd6 96%);
            background: linear-gradient(135deg, #b223a1 0%,#7b22aa 27%,#271f9b 55%,#322cd6 96%);
            filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#b223a1', endColorstr='#322cd6',GradientType=1 );
        }
        .purple2 { background: #22074D; }
	</style>
</head>
<body class="purple2">
    <div class="not-found">
        <i class="ex ex-sad ex-7x"></i>
        <h3>
            We are performing an <span>update</span> right now.<br>
            This could take a while. Come back later.
        </h3>

        <br><br>
    </div>

	<!--<div id="copy"><img src="/img/logo.png" alt="logo"></div>-->

	<div class="footer">
		<div class="part">Duration: <b> ~<?php echo number_format($maintenance->retry/60, 2) ?> Hour</b></div>
		<div class="part">Progress: <b><?php echo $maintenance->progress  ?>%</b></div>
		<div class="part"><b><?php echo env('APP_NAME', 'envo') ?></b></div>
	</div>
    <div class="sub purple"></div>
</body>
</html>
