Untuk Testing Selenium : 
Pastikan urutan 3 terminal ini semua aktif bersamaan

Terminal 1: ./vendor/laravel/dusk/bin/chromedriver-win.exe --port=9515 (biarkan jalan)
Terminal 2: php artisan serve (biarkan jalan)
Terminal 3: php artisan dusk tests/Browser/LoginTest.php