
## About this project

this project is a backend take home challenge the main goal is to fetch data from some external apis and show them to users

## Requirements

** PHP: 8.2 or higher

** Composer: Latest version

** Database: MySQL 8.0+

** Web Server: Apache or Nginx


## How to install

clone project ```git clone github.com```

make you own .env file ```cp .env.example .env```

modify .env file and set your variables

run ```composer install```

then migrate database ```php artisan migrate```

to run fetcher manually run ```php artisan app:fetch-article newsapi,guardian,nyt```

You can add the following cron entry to your server's crontab by running crontab -e (on Linux/Unix-based systems):

```* * * * * cd /path-to-your-laravel-project && php artisan schedule:run >> /dev/null 2>&1```

to start project run ```php artisan serve``` or if using Apache or Nginx configure it
