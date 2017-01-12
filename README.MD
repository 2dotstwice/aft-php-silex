# AFT Silex workshop code

**For demonstration purposes only.**
Do not use this code in a production environment for security and performance issues.

More info: [http://www.aftleuven.be/event/web-development-track-workshop-1/](http://www.aftleuven.be/event/web-development-track-workshop-1/)

Joind.in link: [https://joind.in/event/php-leuven---event-1-introduction-to-php--fast-frameworks/introduction-to-silex](https://joind.in/event/php-leuven---event-1-introduction-to-php--fast-frameworks/introduction-to-silex)

## Continuing on the workshop servers

Sign up at http://phpbelgium.be/ and contact metalarend to keep using the server you were working on during the workshop.

## Quick setup @ Home

### Requirements

* Mac OS X or Linux recommended.
* **PHP 5.4+** (**PHP 5.5+** recommended)
	* **Mac OS X**: PHP comes pre-installed. Yay!
	* **Linux**: [Setup guide](http://php.net/manual/en/install.unix.debian.php) (advanced) or use [XAMPP](https://www.apachefriends.org/index.html) (easy).
	* **Windows**: [Setup guide](http://www.sitepoint.com/how-to-install-php-on-windows/) (advanced) or use [XAMPP](https://www.apachefriends.org/index.html) (easy).

* **Composer** ([https://getcomposer.org](https://getcomposer.org))

### Download

[https://github.com/2dotstwice/aft-php-silex/archive/master.zip](https://github.com/2dotstwice/aft-php-silex/archive/master.zip)

### Starting PHP's built-in webserver

* Unzip the downloaded file.
* Open the Terminal / Console / ...
* Run `composer install`
* `cd` to the `web` folder of the unzipped download.
* Run `php -S localhost:8080`
* Open `http://localhost:8080/hello` in your browser.
* You should see `Hello world!`

### Troubleshooting

#### When having problems writing data to the filesystem

Make the `web/files` folder writable by PHP.

* **Mac OS X / Linux**: `chmod 777 web/files`.
* **Windows**: Try [http://stackoverflow.com/questions/8402415/xampp-on-windows-7-set-chmod](http://stackoverflow.com/questions/8402415/xampp-on-windows-7-set-chmod), otherwise Google is your friend.

#### When getting errors about timezones

Set `date.timezone` in `php.ini` to `Europe/Brussels`. (If you know where the `php.ini` file is located.)
Otherwise put the following code at the top of your `index.php`:

	ini_set('date.timezone', 'Europe/Brussels');
