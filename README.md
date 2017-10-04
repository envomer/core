# Framework

## Features:
- IP checker/blocker/throttle
- maintenance mode


## API

### Notification/Mail
```php
$provider = Notification::SMS; // sms,pushover,mail,

$notification = new Notification($provider);
$notification->setProvider($provider);
$notification->setBody($body);
$notification->setSubject($subject);
$notification->setRecipients([]);
$notification->setFrom($name, $email);

$notification->setBCC($bcc);
$notification->setCC($cc);

$notification->send($afterSeconds = 60);
$notification->queue($afterSeconds = 60);
```


## Console
```bash
php envo
```