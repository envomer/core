<!DOCTYPE html>
<html>
<head>
	<title><?php echo $notification->subject ?></title>
</head>
<body>
<?php echo $notification->body ?>

<div style="center"><a href="<?php echo env('APP_URL') ?>/comm/newsletter/<?php echo 'TODO...' ?>/email/%recipient.email%/unsubscribe?u=%recipient.id%">Unsubscribe</a></div>
<img width="1" height="1" src="<?php echo env('APP_URL') ?>/comm/newsletter/mail/%recipient.id%/pixel.png" style="display: inline-block; width: 1px; height: 1px;" />
</body>
</html>