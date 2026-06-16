<?php

namespace Tests\Browser;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class LoginTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * Helper: pastikan browser dalam kondisi guest lalu buka halaman login.
     * Dusk berbagi satu browser window antar test — session test sebelumnya
     * bisa masih aktif, sehingga guest middleware redirect dari /login.
     */
    private function visitLoginAsGuest(Browser $browser): Browser
    {
        $browser->logout();

        return $browser->visit('/login')
            ->waitFor('#email', 10)
            ->pause(500);
    }

    /**
     * Helper: isi form login via JavaScript (lebih reliable daripada type()).
     * Dusk type() kadang gagal mengirim keystrokes ke field setelah
     * logout+visit cycle, menyebabkan field tetap kosong.
     */
    private function fillLoginForm(Browser $browser, string $email, string $password): Browser
    {
        $browser->script([
            "document.getElementById('email').value = " . json_encode($email) . ";",
            "document.getElementById('password').value = " . json_encode($password) . ";",
        ]);

        return $browser;
    }

    /**
     * Pastikan halaman login dapat diakses dan menampilkan form.
     */
    public function test_halaman_login_dapat_diakses()
    {
        $this->browse(function (Browser $browser) {
            $this->visitLoginAsGuest($browser)
                ->assertSee('SIBANK')
                ->assertSee('Sistem Informasi Bank Sampah')
                ->assertPresent('#email')
                ->assertPresent('#password')
                ->assertPresent('button[type=submit]')
                ->assertSee('Masuk');
        });
    }

    /**
     * State: Guest -> Authenticated (Admin)
     * Admin login dengan kredensial valid, redirect ke dashboard admin.
     */
    public function test_admin_dapat_login_dengan_kredensial_valid()
    {
        $admin = User::factory()->create([
            'name' => 'Admin SIBANK',
            'email' => 'admin@sibank.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $this->browse(function (Browser $browser) use ($admin) {
            $this->visitLoginAsGuest($browser);
            $this->fillLoginForm($browser, 'admin@sibank.com', 'password');

            $browser->press('Masuk')
                ->waitForLocation('/admin/dashboard', 10)
                ->assertPathIs('/admin/dashboard')
                ->assertSee('Dashboard')
                ->assertSee($admin->name);
        });
    }

    /**
     * State: Guest -> Authenticated (Petugas)
     * Petugas login dengan kredensial valid, redirect ke /admin/nasabah.
     */
    public function test_petugas_dapat_login_dengan_kredensial_valid()
    {
        User::factory()->create([
            'name' => 'Petugas SIBANK',
            'email' => 'petugas@sibank.com',
            'password' => bcrypt('password'),
            'role' => 'petugas',
        ]);

        $this->browse(function (Browser $browser) {
            $this->visitLoginAsGuest($browser);
            $this->fillLoginForm($browser, 'petugas@sibank.com', 'password');

            $browser->press('Masuk')
                ->waitForLocation('/admin/nasabah', 10)
                ->assertPathIs('/admin/nasabah');
        });
    }

    /**
     * State: tetap Guest jika password salah.
     */
    public function test_login_gagal_dengan_password_salah()
    {
        User::factory()->create([
            'email' => 'admin@sibank.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $this->browse(function (Browser $browser) {
            $this->visitLoginAsGuest($browser);
            $this->fillLoginForm($browser, 'admin@sibank.com', 'password_salah');

            // Submit form via JS (bypass HTML5 required validation jika field kosong)
            $browser->script("document.getElementById('login-form').submit();");

            $browser->waitFor('.alert-error', 10)
                ->assertPathIs('/login')
                ->assertSee('Email atau password salah');
        });
    }

    /**
     * State: tetap Guest jika email tidak terdaftar.
     */
    public function test_login_gagal_dengan_email_tidak_terdaftar()
    {
        $this->browse(function (Browser $browser) {
            $this->visitLoginAsGuest($browser);
            $this->fillLoginForm($browser, 'tidakada@sibank.com', 'password');

            $browser->script("document.getElementById('login-form').submit();");

            $browser->waitFor('.alert-error', 10)
                ->assertPathIs('/login')
                ->assertSee('Email atau password salah');
        });
    }

    /**
     * Pengguna yang belum login tidak dapat mengakses dashboard secara langsung
     * dan akan diarahkan kembali ke halaman login.
     */
    public function test_guest_tidak_dapat_mengakses_dashboard_secara_langsung()
    {
        $this->browse(function (Browser $browser) {
            $browser->logout()
                ->visit('/admin/dashboard')
                ->waitForLocation('/login', 10)
                ->assertPathIs('/login')
                ->assertSee('SIBANK');
        });
    }

    /**
     * State: Authenticated -> Guest
     * User yang sudah login dapat melakukan logout dan kembali ke halaman login.
     */
    public function test_user_dapat_logout()
    {
        $admin = User::factory()->create([
            'email' => 'admin@sibank.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $this->browse(function (Browser $browser) use ($admin) {
            $browser->loginAs($admin)
                ->visit('/admin/dashboard')
                ->waitForText('Dashboard', 10)
                ->assertSee('Dashboard');

            $browser->waitFor('.btn-logout', 10)
                ->script("document.querySelector('.btn-logout').closest('form').submit();");

            $browser->waitForLocation('/login', 10)
                ->assertPathIs('/login');

            $browser->visit('/admin/dashboard')
                ->waitForLocation('/login', 10)
                ->assertPathIs('/login');
        });
    }
}