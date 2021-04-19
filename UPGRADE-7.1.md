UPGRADE FROM 7.0 to 7.1
=======================

# Changed Features
## Cronjobs
All Cron Jobs should now call the superclass constructor.

Before:
```php
 public function __construct()
    {
        parent::TCMSCronJob();
    }
```
Should Be:
```php
 public function __construct()
    {
        parent::__construct();
    }
```
