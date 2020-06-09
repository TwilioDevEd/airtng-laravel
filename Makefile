install:
	touch database/database.sqlite
	touch database/database-test.sqlite
	composer install
	php artisan key:generate --force
	php artisan migrate --force

serve-setup:
	php artisan serve --host=127.0.0.1
open-browser:
	python3 -m webbrowser "http://127.0.0.1:8000"; 
serve: open-browser serve-setup
