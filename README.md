# Framework

## Features:
- IP checker/blocker/throttle (done?)
- Maintenance mode (done)
- Migration status

## TODO
- Give the user more access to notification providers (Pushover for example has some params such url that can be of use at some point)
- Add api to services defined in core package
- Add these methods to repo (https://github.com/micheleangioni/phalcon-repositories) (https://laravel.com/docs/5.5/eloquent#retrieving-models)
- Add backup config file with the option to encrypt compression
- Add docker @CM
- Add api docs (extract documention from api class) @CM
- Add debugbar (https://github.com/snowair/phalcon-debugbar)

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

## Model\Repository

Folder structure
```
- Console
- Model
- - Repository
- Service
```

Call repo method
```php
$result = Model::repo()->where('name', $name)->get();

$result = Model::repo()->getIn(string $whereInKey, array $whereIn = [])->orderBy(['description' => 'asc', 'name' => 'desc'])->limit(20)->get();
$result = Model::repo()->getIn(string $whereInKey, array $whereIn = [])->orderBy('name', 'desc')->limit(20)->get();
$result = Model::repo()->getByPage(int 7)->limit(20)->with(['team.members', 'events''])->get();
```