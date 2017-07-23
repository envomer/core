<!DOCTYPE html>
<html>
<head>
	<title><?php echo config('app.name') ?></title>

	<style type="text/css">
		*{ box-sizing: border-box }
		html{font-size:100%;-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%}
		html,button,input,select,textarea{font-family:sans-serif}
		body, html { margin: 0; padding: 0; background: #fff; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #23262B; text-align: center;}
		body { font-weight:100; font-size:13px; }
		h1 { color: #23262B; padding: 0 50px; width: 400px; margin: 50pt auto 0; text-align: center; font-size: 60pt; }
		h1 b { color: #E04157; }
		p { color: rgba(0,0,0,0.8); font-size: 14pt; margin: 0; }
		p span { color: #2ABB9B; }

		#back-home{ color: green; text-decoration: none; font-size: 15pt; margin-top: 20px; display: block; }

		/*#img { width: 200px; margin: 6% auto 0; display: block; height: 120px }*/
		/*#img img{ width: auto; height: inherit; }*/

		.not-found { font-size: 20pt; margin-top: 10%; }
		.not-found b { display: block; font-size: 100pt  }

		.right { text-align: right; }
	</style>
</head>
<body>
	<div style="display: none" id="error-page"></div>

	<div class="not-found">
		<?php
			$messages = [
				'400' => 'badRequest',
				'403' => 'notallowed',
				'404' => 'notfound',
				'500' => 'internalServerError'
			];

			$code = $error->getCode();

			if( array_key_exists($code, $messages) === false ) {
				$code = 500;
			}

			$message = $messages[$code];
		?>
	    <b><?php echo $code ?></b>
	    <?php echo \_t('app.' . $message); ?>
	</div>

	<a href="/" class="center" id="back-home">
		<?php echo \_t('app.backToHomepage'); ?>
	</a>

    <?php if( env('APP_DEBUG') && isset($error) ): ?>
	<div style="text-align:left; max-width: 900px; margin: 50px auto 50px; border-left: 4px solid #E75A5C; padding: 20px; font-size: 8pt; background: #f5f5f5">
		<?php

 			$message = "Runtime: " . (microtime(true) - APP_START) . " msec\n";
 			$message .= "Memory peak usage: " . (memory_get_peak_usage(true)/1024/1024) . " MiB\n";
 			$message .= "Memory usage: " . (memory_get_usage(true)/1024/1024) . " MiB\n";

			$message .= "\n";
			$message .= $error->getMessage(). "\n"
			 . " Class: " . get_class($error) . "\n"
	         . " File:  ". $error->getFile(). "\n"
	         . " Line:  ". $error->getLine(). "\n";

			echo '<h2>'. $error->getMessage() .'</h2>';
	        echo '<pre style="word-wrap:break-word">' .  $message . "\n";

            echo $error->getTraceAsString();

			var_dump($_SERVER);

            //  foreach($error->getTrace() as $trace) {
            //      echo isset($trace['class']) ? $trace['class'] : '';
            //      echo $trace['function'];
            //      if( isset($trace['file']) ) {
            //          var_dump(file_get_contents($trace['file']));
            //      }
            //  }
             
             echo '</pre>';
	    ?>
	</div>
	<?php endif; ?>
</body>
</html>
