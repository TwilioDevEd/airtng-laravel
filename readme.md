# Airtng App: Part 1 - Workflow Automation with Twilio - Laravel

![](https://github.com/TwilioDevEd/airtng-laravel/workflows/Laravel/badge.svg)


> We are currently in the process of updating this sample template. If you are encountering any issues with the sample, please open an issue at [github.com/twilio-labs/code-exchange/issues](https://github.com/twilio-labs/code-exchange/issues) and we'll try to help you.

Learn how to automate your workflow using Twilio's REST API and Twilio SMS. This example app is a vacation rental site where the host can confirm a reservation via SMS.

[Read the full tutorial here](https://www.twilio.com/docs/tutorials/walkthrough/workflow-automation/php/laravel)!

## Run the application

1. Clone the repository and `cd` into it.

1. Install the application's dependencies with [Composer](https://getcomposer.org/)

   ```bash
   $ composer install
   ```
1. The application uses [sqlite3](https://www.sqlite.org/) as the persistence layer. If you
  don't have it already, you should install it.

1. Create an empty database file.

  ```bash
  $ touch database/database.sqlite
  ```

1. Copy the sample configuration file and edit it to match your configuration.

   ```bash
   $ cp .env.example .env
   ```

  You can find your `TWILIO_ACCOUNT_SID` and `TWILIO_AUTH_TOKEN` under
  your
  [Twilio Account Settings](https://www.twilio.com/user/account/settings).
  You can buy a Twilio phone number here [Twilio numbers](https://www.twilio.com/user/account/phone-numbers/search)
  `TWILIO_NUMBER` should be set according to the phone number you purchased above.

1. Generate an `APP_KEY`.

   ```bash
   $ php artisan key:generate
   ```

1. Run the migrations.

  ```bash
  $ php artisan migrate
  ```

1. Load the seed data.

  ```bash
  $ php artisan db:seed
  ```

1. Expose the application to the wider Internet using [ngrok](https://ngrok.com/)

   ```bash
   $ ngrok http 8000
   ```
   Once you have started ngrok, update your Twilio number sms URL
   settings to use your ngrok hostname. It will look something like
   this:

   ```
   http://<your-ngrok-subdomain>.ngrok.io/reservation/incoming
   ```

1. Configure Twilio to call your webhooks.

 You will also need to configure Twilio to send requests to your application
 when sms are received.

 You will need to provision at least one Twilio number with sms capabilities
 so the application's users can make property reservations. You can buy a number [right
 here](https://www.twilio.com/user/account/phone-numbers/search). Once you have
 a number you need to configure it to work with your application. Open
 [the number management page](https://www.twilio.com/user/account/phone-numbers/incoming)
 and open a number's configuration by clicking on it.

 Remember that the number where you change the sms webhooks must be the same one you set on
 the `TWILIO_NUMBER` environment variable.

 ![Configure Messaging](webhook.png)

 For this application, you must set the voice webhook of your number so that it
 looks something like this:

 ```
 http://<your-ngrok-subdomain>.ngrok.io/reservation/incoming
 ```

 And in this case set the `POST` method on the configuration for this webhook.

1. Run the application using Artisan.

  ```bash
  $ php artisan serve
  ```

  It is `artisan serve` default behaviour to use `http://localhost:8000` when
  the application is run. This means that the ip addresses where your app will be
  reachable on you local machine will vary depending on the operating system.

  The most common scenario is that your app will be reachable through address
  `http://127.0.0.1:8000`. This is important because ngrok creates the
  tunnel using only that address. So, if `http://127.0.0.1:8000` is not reachable
  in your local machine when you run the app, you must tell artisan to use this
  address. Here's how to set that up:

  ```bash
  $ php artisan serve --host=127.0.0.1
  ```

## Dependencies

This application uses this Twilio helper library:
* [twilio-php](https://github.com/twilio/twilio-php)

## Run the tests

1. Create an empty database file.

  ```bash
  $ touch database/database-test.sqlite
  ```

1. Run the migrations.

  ```bash
  $ php artisan migrate --database=testing
  ```

1. Run at the top-level directory.

   ```bash
   $ phpunit
   ```

   or

   ```bash
   $ vendor/bin/phpunit
   ```

   If you don't have phpunit installed on your system, you can follow [these
   instructions](https://phpunit.de/manual/current/en/installation.html) to
   install it.

## Meta

* No warranty expressed or implied. Software is as is. Diggity.
* The CodeExchange repository can be found [here](https://github.com/twilio-labs/code-exchange/).
* [MIT License](http://www.opensource.org/licenses/mit-license.html)
* Lovingly crafted by Twilio Developer Education.
