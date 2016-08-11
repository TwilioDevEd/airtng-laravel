# Airtng App: Part 2 - Masked Numbers With Twilio - Laravel

[![Build Status](https://travis-ci.org/TwilioDevEd/airtng-laravel.svg?branch=airtng-masked-numbers)](https://travis-ci.org/TwilioDevEd/airtng-laravel)

Protect your customers' privacy, and create a seamless interaction by provisioning Twilio numbers on the fly, and routing all voice calls, and messages through your very own 3rd party. This allows you to control the interaction between your customers, while putting your customer's privacy first.

[Read the full tutorial here](https://www.twilio.com/docs/tutorials/walkthrough/masked-numbers/php/laravel)!

### Create a TwiML App

This project is configured to use a **TwiML App**, which allows us to easily set the voice URLs for all Twilio phone numbers we purchase in this app.

Create a new TwiML app at https://www.twilio.com/user/account/apps/add and use its `Sid` as the `TWIML_APPLICATION_SID` environment variable wherever you run this app.

![Creating a TwiML App](http://howtodocs.s3.amazonaws.com/call-tracking-twiml-app.gif)

Once you have created your TwiML app, configure your Twilio phone number to use it ([instructions here](https://www.twilio.com/help/faq/twilio-client/how-do-i-create-a-twiml-app)).

If you don't have a Twilio phone number yet, you can purchase a new number in your [Twilio Account Dashboard](https://www.twilio.com/user/account/phone-numbers/incoming).

### Run the application

1. Clone the repository and `cd` into it.

1. Install the application's dependencies with [Composer](https://getcomposer.org/)

   ```bash
   $ composer install
   ```

1. The application uses PostgreSQL as the persistence layer. If you
  don't have it already, you should install it. The easiest way is by
  using [Postgres.app](http://postgresapp.com/).

1. Create a database.

  ```bash
  $ createdb airtng
  ```

1. Copy the sample configuration file and edit it to match your configuration.

   ```bash
   $ cp .env.example .env
   ```

  You can find your `TWILIO_ACCOUNT_SID` and `TWILIO_AUTH_TOKEN` under
  your
  [Twilio Account Settings](https://www.twilio.com/user/account/settings).
  Set `TWILIO_APPLICATION_SID` to the app SID you created before.
  You can buy Twilio phone numbers at [Twilio numbers](https://www.twilio.com/user/account/phone-numbers/search)
  `TWILIO_NUMBER` should be set to the phone number you purchased above.

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

   Once you have started ngrok, update your TwiML app's voice and SMS URL
   setting to use your ngrok hostname. It will look something like
   this:

   ```
   http://<your-ngrok-subdomain>.ngrok.io/reservation/connect_voice
   http://<your-ngrok-subdomain>.ngrok.io/reservation/connect_sms
   ```

1. Configure Twilio to call your webhooks.

   You will also need to configure Twilio to send requests to your application
   when SMSs are received.

   You will need to provision at least one Twilio number with SMS capabilities
   so the application's users can make property reservations. You can buy a number [right
   here](https://www.twilio.com/user/account/phone-numbers/search). Once you have
   a number you need to configure it to work with your application. Open
   [the number management page](https://www.twilio.com/user/account/phone-numbers/incoming)
   and open a number's configuration by clicking on it.

   Remember that the number where you change the SMS webhooks must be the same one you set on
   the `TWILIO_NUMBER` environment variable.

   ![Configure Voice](http://howtodocs.s3.amazonaws.com/twilio-number-config-all-med.gif)

   For this application you must set the SMS webhook of your number to
   something like this:

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

  The most common scenario, is that your app will be reachable through address
  `http://127.0.0.1:8000`. This is important because ngrok creates the
  tunnel using only that address. So, if `http://127.0.0.1:8000` is not reachable
  in your local machine when you run the app, you must tell artisan to use this
  address, like this:

  ```bash
  $ php artisan serve --host=127.0.0.1
  ```

### Dependencies

This application uses this Twilio helper library:
* [twilio-php](https://github.com/twilio/twilio-php)

### Run the tests

1. Run at the top-level directory.

   ```bash
   $ phpunit
   ```

   If you don't have phpunit installed on your system, you can follow [this
   instructions](https://phpunit.de/manual/current/en/installation.html) to
   install it.
