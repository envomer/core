# Core layr

## Features:
- IP checker/blocker/throttle (done?)
- Maintenance mode (done)
- Migration status

## TODO

- [ ] Give the user more access to notification providers (Pushover for example has some params such url that can be of use at some point)
- [ ] Add api to services defined in core package
- [x] Add these methods to repo (https://github.com/micheleangioni/phalcon-repositories) (https://laravel.com/docs/5.5/eloquent#retrieving-models)
- [ ] Add backup config file with the option to encrypt compression
- [ ] Add docker file @micaso
- [ ] Add api docs (extract documentation from api class) @micaso
- [ ] Rewrite database migration commands
- [x] Add debugbar (https://github.com/snowair/phalcon-debugbar)
- [ ] Implement middleWares (https://docs.phalconphp.com/en/3.2/application-micro) @envomer 

https://gist.github.com/alex-LE/6138209
https://gist.github.com/claviska/3536458
https://gist.github.com/zofe/8bcd6a3d6d5832e8256bf6cb45cc0fad

https://gist.github.com/search?utf8=✓&q=rest+client+php&ref=searchresults

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

$notification->send();
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

$result = Model::repo()->in(string $whereInKey, array $whereIn = [])->orderBy(['description' => 'asc', 'name' => 'desc'])->limit(20)->get();
$result = Model::repo()->in(string $whereInKey, array $whereIn = [])->orderBy('name', 'desc')->limit(20)->get();
$result = Model::repo()->page(int 7)->limit(20)->with(['team.members', 'events''])->get();
```

## Permissions
We almost have a Role class implemented which you can use 
or you can extend your class from AbstractRole
Our user and team class are extending the AbstractRole class. 

### What will do the AbstractRole class for me?
Every class which extends it get following methods.

- canI(permissionName, moduleName)
```php
$result = $role->canI('VIEW', 'core');
```
- grant(permissionName, moduleName)

```php
$role->grant('VIEW', 'core');
```

The role system is a hierarchy so you can set a parent on roles
```php
$teamWithView = new Team();
$teamWithView->name = 'team with view permission';
$teamWithView->save();

$teamWithView->grant('VIEW', 'core');

$teamWithEdit = new Team();
$teamWithEdit->name = 'team with edit permission';
$teamWithEdit->parent = $teamWithView;
$teamWithEdit->save();

$teamWithEdit->grant('EDIT', 'core');

$user = new User();
$user->parent = $teamWithEdit;
$user->save();

$teamWithDelete = new Team();
$teamWithDelete->name = 'team with delete permission';
$teamWithDelete->save();

$teamWithDelete->grant('DELETE', 'core');

$user2 = new User();
$user2->parent = $user;
$user2->save();

$user2->addRole($teamWithDelete);

$teamWithView->canI('VIEW', 'core'); //true
$teamWithView->canI('EDIT', 'core'); //false
$teamWithView->canI('DELETE', 'core'); //false

$teamWithEdit->canI('VIEW', 'core'); //true
$teamWithEdit->canI('EDIT', 'core'); //true
$teamWithEdit->canI('DELETE', 'core'); //false

$user->canI('VIEW', 'core'); //true
$user->canI('EDIT', 'core'); //true
$user->canI('DELETE', 'core'); //false

$user2->canI('VIEW', 'core'); //true
$user2->canI('EDIT', 'core'); //true
$user2->canI('DELETE', 'core'); //true
```