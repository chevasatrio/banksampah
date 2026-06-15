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
     * Pastikan halaman login dapat diakses dan menampilkan form.
     *
     * @test
     */
    public function halaman_login_dapat_diakses()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->assertSee('Login')
                ->assertPresent('input[name=email]')
                ->assertPresent('input[name=password]')
                ->assertPresent('button[type=submit]');
        });
    }

    /**
     * State: Guest -> Authenticated (Admin)
     * Admin login dengan kredensial valid, redirect ke dashboard admin.
     *
     * @test
     */
    public function admin_dapat_login_dengan_kredensial_valid()
    {
        $admin = User::factory()->create([
            'name' => 'Admin SIBANK',
            'email' => 'admin@sibank.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $this->browse(function (Browser $browser) use ($admin) {
            $browser->visit('/login')
                ->type('email', 'admin@sibank.com')
                ->type('password', 'password')
                ->press('Login')
                ->waitForLocation('/admin/dashboard')
                ->assertPathIs('/admin/dashboard')
                ->assertSee('Dashboard')
                ->assertSee($admin->name);
        });
    }

    /**
     * State: Guest -> Authenticated (Petugas)
     * Petugas login dengan kredensial valid, redirect ke dashboard sesuai role.
     *
     * @test
     */
    public function petugas_dapat_login_dengan_kredensial_valid()
    {
        User::factory()->create([
            'name' => 'Petugas SIBANK',
            'email' => 'petugas@sibank.com',
            'password' => bcrypt('password'),
            'role' => 'petugas',
        ]);

        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->type('email', 'petugas@sibank.com')
                ->type('password', 'password')
                ->press('Login')
                ->waitForLocation('/admin/dashboard')
                ->assertPathIs('/admin/dashboard');
        });
    }

    /**
     * State: tetap Guest jika password salah.
     *
     * @test
     */
    public function login_gagal_dengan_password_salah()
    {
        User::factory()->create([
            'email' => 'admin@sibank.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->type('email', 'admin@sibank.com')
                ->type('password', 'password_salah')
                ->press('Login')
                ->assertPathIs('/login')
                ->assertSee('These credentials do not match our records.');
        });
    }

    /**
     * State: tetap Guest jika email tidak terdaftar.
     *
     * @test
     */
    public function login_gagal_dengan_email_tidak_terdaftar()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->type('email', 'tidakada@sibank.com')
                ->type('password', 'password')
                ->press('Login')
                ->assertPathIs('/login')
                ->assertSee('These credentials do not match our records.');
        });
    }

    /**
     * Validasi: field email wajib diisi.
     *
     * @test
     */
    public function login_gagal_jika_email_kosong()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->type('password', 'password')
                ->press('Login')
                ->assertPathIs('/login')
                ->assertSee('The email field is required.');
        });
    }

    /**
     * Pengguna yang belum login tidak dapat mengakses dashboard secara langsung
     * dan akan diarahkan kembali ke halaman login.
     *
     * @test
     */
    public function guest_tidak_dapat_mengakses_dashboard_secara_langsung()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/admin/dashboard')
                ->assertPathIs('/login')
                ->assertSee('Login');
        });
    }

    /**
     * State: Authenticated -> Guest
     * User yang sudah login dapat melakukan logout dan kembali ke halaman login.
     *
     * @test
     */
    public function user_dapat_logout()
    {
        $admin = User::factory()->create([
            'email' => 'admin@sibank.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $this->browse(function (Browser $browser) use ($admin) {
            $browser->loginAs($admin)
                ->visit('/admin/dashboard')
                ->assertSee('Dashboard')
                ->press('Logout')
                ->assertPathIs('/login');

            $browser->visit('/admin/dashboard')
                ->assertPathIs('/login');
        });
    }
}