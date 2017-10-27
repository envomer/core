# Framework

## Features:
- IP checker/blocker/throttle
- maintenance mode

## TODO
- Give the user more access to notification providers (Pushover for example has some params such url that can be of use at some point)
- Add api to services defined in core package
- Add these methods to repo (https://github.com/micheleangioni/phalcon-repositories) (https://laravel.com/docs/5.5/eloquent#retrieving-models)


## API

### Notification/Mail
```php
$provider = Notification::SMS; // sms,pushover,mail,

$notification = new Notification($provider);
$notification->setProvider($provider);
$notification->setBody($body);
$notification->setSubject($subject);
$notification->setRecipients(['+4367712345678', 'om@me.com', '@me']);
$notification->setFrom($name, $email);

$notification->setBCC($bcc);
$notification->setCC($cc);

$notification->send($afterSeconds = 60);
$notification->queue($afterSeconds = 60);
```

OR use this method to send notifications

```php
class WelcomeMail extends Notification {
    /**
     * Either fill these class attributes or use the methods for better control
     */
    public $body;
    public $subject;
    public $recipients;
    public $from;
    public $cc;
    public $bcc;
    
    public function getBody() {}
    public function getSubject() {}
    public function getRecipients() {}
    public function getFrom() {}
    public function getCc() {}
    public function getBcc() {}
}
```

```php
notify(WelcomeMail::class, Notification::SMS);
notify(new WelcomeMail(), [Notification::SMS, Notification::EMAIL, NOTIFICATION:SLACK]);
```


## Console
```bash
php envo
```