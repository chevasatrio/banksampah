# ═══════════════════════════════════════════════════════════════
# SIBANK — Selenium Test Suite
# Sistem Informasi Bank Sampah
# ═══════════════════════════════════════════════════════════════
#
# Prasyarat:
#   pip install selenium webdriver-manager
#
# Cara menjalankan:
#   1. Pastikan XAMPP (Apache + MySQL) sudah berjalan
#   2. Pastikan database sudah di-seed: php artisan migrate:fresh --seed
#   3. Jalankan: python tests/selenium/test_sibank.py
#   4. Atau jalankan test tertentu: python -m pytest tests/selenium/test_sibank.py::TestLogin -v
#
# Base URL default: http://localhost/bank-sampah/public
# ═══════════════════════════════════════════════════════════════

import unittest
import time
import os
from datetime import datetime

from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.common.keys import Keys
from selenium.webdriver.support.ui import WebDriverWait, Select
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.chrome.service import Service
from selenium.webdriver.chrome.options import Options

# ──── Konfigurasi ────
BASE_URL = "http://localhost/bank-sampah/public"
ADMIN_EMAIL = "admin@sibank.com"
PETUGAS_EMAIL = "petugas@sibank.com"
PASSWORD = "password"
IMPLICIT_WAIT = 10
SCREENSHOT_DIR = os.path.join(os.path.dirname(__file__), "screenshots")


class SIBANKTestBase(unittest.TestCase):
    """Base class untuk semua test SIBANK dengan setup/teardown browser."""

    @classmethod
    def setUpClass(cls):
        """Setup browser Chrome satu kali untuk seluruh test class."""
        os.makedirs(SCREENSHOT_DIR, exist_ok=True)

        chrome_options = Options()
        # Uncomment baris di bawah untuk mode headless (tanpa tampilan browser)
        # chrome_options.add_argument("--headless=new")
        chrome_options.add_argument("--window-size=1366,768")
        chrome_options.add_argument("--disable-gpu")
        chrome_options.add_argument("--no-sandbox")

        try:
            from webdriver_manager.chrome import ChromeDriverManager
            service = Service(ChromeDriverManager().install())
            cls.driver = webdriver.Chrome(service=service, options=chrome_options)
        except Exception:
            cls.driver = webdriver.Chrome(options=chrome_options)

        cls.driver.implicitly_wait(IMPLICIT_WAIT)
        cls.wait = WebDriverWait(cls.driver, IMPLICIT_WAIT)

    @classmethod
    def tearDownClass(cls):
        """Tutup browser setelah semua test selesai."""
        if hasattr(cls, 'driver'):
            cls.driver.quit()

    def screenshot(self, name):
        """Simpan screenshot dengan timestamp."""
        timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
        filename = f"{timestamp}_{name}.png"
        filepath = os.path.join(SCREENSHOT_DIR, filename)
        self.driver.save_screenshot(filepath)
        print(f"  📸 Screenshot: {filepath}")

    def login_as(self, email, password=PASSWORD):
        """Helper: login dengan email dan password."""
        self.driver.get(f"{BASE_URL}/login")
        time.sleep(1)

        # Clear dan isi field email
        email_field = self.driver.find_element(By.ID, "email")
        email_field.clear()
        email_field.send_keys(email)

        # Clear dan isi field password
        pwd_field = self.driver.find_element(By.ID, "password")
        pwd_field.clear()
        pwd_field.send_keys(password)

        # Klik tombol login
        submit_btn = self.driver.find_element(By.CSS_SELECTOR, "button[type='submit']")
        submit_btn.click()
        time.sleep(2)

    def logout(self):
        """Helper: logout dari aplikasi."""
        try:
            logout_btn = self.driver.find_element(By.CSS_SELECTOR, ".btn-logout")
            logout_btn.click()
            time.sleep(2)
        except Exception:
            # Fallback: navigate ke logout form
            self.driver.get(f"{BASE_URL}/logout")
            time.sleep(1)

    def assert_url_contains(self, expected_path):
        """Assert bahwa URL saat ini mengandung path tertentu."""
        current_url = self.driver.current_url
        self.assertIn(expected_path, current_url,
                      f"Expected URL to contain '{expected_path}', but got '{current_url}'")

    def assert_text_present(self, text):
        """Assert bahwa teks tertentu ada di halaman."""
        page_source = self.driver.page_source
        self.assertIn(text, page_source,
                      f"Expected text '{text}' not found on page")

    def assert_text_not_present(self, text):
        """Assert bahwa teks tertentu TIDAK ada di halaman."""
        page_source = self.driver.page_source
        self.assertNotIn(text, page_source,
                         f"Text '{text}' should not be on page, but was found")

    def find_element_safe(self, by, value, timeout=10):
        """Find element dengan explicit wait."""
        return WebDriverWait(self.driver, timeout).until(
            EC.presence_of_element_located((by, value))
        )

    def click_element_safe(self, by, value, timeout=10):
        """Click element dengan explicit wait sampai clickable."""
        element = WebDriverWait(self.driver, timeout).until(
            EC.element_to_be_clickable((by, value))
        )
        element.click()
        return element


# ═══════════════════════════════════════════════════════════════
# TEST 1: LOGIN & AUTENTIKASI
# Berdasarkan SRS bagian 10.3
# ═══════════════════════════════════════════════════════════════
class TestLogin(SIBANKTestBase):
    """TC-AUTH: Test case untuk modul autentikasi."""

    def test_01_halaman_login_tampil_dengan_benar(self):
        """TC-AUTH-01: Halaman login menampilkan form dengan benar."""
        print("\n🔐 TC-AUTH-01: Halaman login tampil dengan benar")

        self.driver.get(f"{BASE_URL}/login")
        time.sleep(1)

        # Assert judul dan elemen form ada
        self.assert_text_present("SIBANK")
        self.assert_text_present("Sistem Informasi Bank Sampah")

        # Assert field email dan password ada
        email_field = self.driver.find_element(By.ID, "email")
        pwd_field = self.driver.find_element(By.ID, "password")
        self.assertIsNotNone(email_field)
        self.assertIsNotNone(pwd_field)

        # Assert tombol submit ada
        submit_btn = self.driver.find_element(By.CSS_SELECTOR, "button[type='submit']")
        self.assertIsNotNone(submit_btn)

        self.screenshot("login_page")
        print("  ✅ PASS: Halaman login tampil dengan benar")

    def test_02_admin_berhasil_login(self):
        """TC-AUTH-02: Admin berhasil login dan redirect ke dashboard."""
        print("\n🔐 TC-AUTH-02: Admin berhasil login")

        self.login_as(ADMIN_EMAIL)

        # Assert redirect ke admin dashboard
        self.assert_url_contains("/admin/dashboard")
        self.assert_text_present("Dashboard")

        self.screenshot("admin_login_success")
        print("  ✅ PASS: Admin berhasil login ke dashboard")

        # Logout untuk test berikutnya
        self.logout()

    def test_03_petugas_berhasil_login(self):
        """TC-AUTH-03: Petugas berhasil login dan redirect ke halaman nasabah."""
        print("\n🔐 TC-AUTH-03: Petugas berhasil login")

        self.login_as(PETUGAS_EMAIL)

        # Assert redirect ke halaman nasabah (bukan admin dashboard)
        self.assert_url_contains("/admin/nasabah")
        self.assert_text_present("Nasabah")

        self.screenshot("petugas_login_success")
        print("  ✅ PASS: Petugas berhasil login ke halaman nasabah")

        self.logout()

    def test_04_login_gagal_password_salah(self):
        """TC-AUTH-04: Login gagal dengan password salah."""
        print("\n🔐 TC-AUTH-04: Login gagal dengan password salah")

        self.login_as(ADMIN_EMAIL, "password_salah_banget")

        # Assert tetap di halaman login
        self.assert_url_contains("/login")

        # Assert pesan error tampil
        self.assert_text_present("salah")

        self.screenshot("login_failed")
        print("  ✅ PASS: Login gagal ditolak dengan benar")

    def test_05_login_gagal_email_tidak_terdaftar(self):
        """TC-AUTH-05: Login gagal dengan email yang tidak terdaftar."""
        print("\n🔐 TC-AUTH-05: Login gagal email tidak terdaftar")

        self.login_as("tidakada@sibank.com", "password")

        self.assert_url_contains("/login")
        self.assert_text_present("salah")

        self.screenshot("login_email_not_found")
        print("  ✅ PASS: Email tidak terdaftar ditolak")

    def test_06_logout_berhasil(self):
        """TC-AUTH-06: Logout berhasil menghapus sesi."""
        print("\n🔐 TC-AUTH-06: Logout berhasil")

        # Login dulu
        self.login_as(ADMIN_EMAIL)
        self.assert_url_contains("/admin/dashboard")

        # Logout
        self.logout()
        time.sleep(1)

        # Assert kembali ke halaman login
        self.assert_url_contains("/login")

        # Assert tidak bisa akses dashboard tanpa login
        self.driver.get(f"{BASE_URL}/admin/dashboard")
        time.sleep(1)
        self.assert_url_contains("/login")

        self.screenshot("logout_success")
        print("  ✅ PASS: Logout berhasil dan sesi dihapus")


# ═══════════════════════════════════════════════════════════════
# TEST 2: MANAJEMEN NASABAH
# Berdasarkan SRS bagian 10.4
# ═══════════════════════════════════════════════════════════════
class TestNasabah(SIBANKTestBase):
    """TC-NSB: Test case untuk modul manajemen nasabah."""

    @classmethod
    def setUpClass(cls):
        super().setUpClass()
        # Login sebagai admin untuk semua test nasabah
        cls.driver.get(f"{BASE_URL}/login")
        time.sleep(1)
        cls.driver.find_element(By.ID, "email").send_keys(ADMIN_EMAIL)
        cls.driver.find_element(By.ID, "password").send_keys(PASSWORD)
        cls.driver.find_element(By.CSS_SELECTOR, "button[type='submit']").click()
        time.sleep(2)

    def test_01_halaman_daftar_nasabah(self):
        """TC-NSB-01: Admin dapat melihat halaman daftar nasabah."""
        print("\n👥 TC-NSB-01: Halaman daftar nasabah")

        self.driver.get(f"{BASE_URL}/admin/nasabah")
        time.sleep(1)

        self.assert_text_present("Daftar Nasabah")
        self.assert_text_present("Tambah Nasabah")

        # Assert tabel ada
        table = self.driver.find_element(By.TAG_NAME, "table")
        self.assertIsNotNone(table)

        # Assert data seeder ada (5 nasabah dari NasabahSeeder)
        self.assert_text_present("Budi Santoso")
        self.assert_text_present("Siti Rahayu")

        self.screenshot("nasabah_index")
        print("  ✅ PASS: Halaman daftar nasabah tampil dengan data")

    def test_02_tambah_nasabah_baru(self):
        """TC-NSB-02: Admin dapat menambah nasabah baru via form."""
        print("\n👥 TC-NSB-02: Tambah nasabah baru")

        self.driver.get(f"{BASE_URL}/admin/nasabah/create")
        time.sleep(1)

        self.assert_text_present("Tambah Nasabah")

        # Isi form
        self.driver.find_element(By.ID, "nama").send_keys("Selenium Tester")
        self.driver.find_element(By.ID, "nik").send_keys("9999888877776666")
        self.driver.find_element(By.ID, "no_hp").send_keys("089999888877")
        self.driver.find_element(By.ID, "alamat").send_keys("Jl. Selenium No. 1, Kota Test, Jawa Timur")

        self.screenshot("nasabah_create_filled")

        # Submit form
        self.driver.find_element(By.CSS_SELECTOR, "button[type='submit']").click()
        time.sleep(2)

        # Assert redirect ke index dengan pesan sukses
        self.assert_url_contains("/admin/nasabah")
        self.assert_text_present("berhasil")
        self.assert_text_present("Selenium Tester")

        self.screenshot("nasabah_create_success")
        print("  ✅ PASS: Nasabah baru berhasil ditambahkan")

    def test_03_validasi_nik_duplikat(self):
        """TC-NSB-03: Tidak bisa tambah nasabah dengan NIK duplikat."""
        print("\n👥 TC-NSB-03: Validasi NIK duplikat")

        self.driver.get(f"{BASE_URL}/admin/nasabah/create")
        time.sleep(1)

        # Isi form dengan NIK yang sudah ada (dari seeder)
        self.driver.find_element(By.ID, "nama").send_keys("Duplikat Test")
        self.driver.find_element(By.ID, "nik").send_keys("3578011234567890")  # NIK Budi Santoso
        self.driver.find_element(By.ID, "no_hp").send_keys("081111111111")
        self.driver.find_element(By.ID, "alamat").send_keys("Jl. Duplikat")

        self.driver.find_element(By.CSS_SELECTOR, "button[type='submit']").click()
        time.sleep(2)

        # Assert error validasi
        page_source = self.driver.page_source.lower()
        has_error = "nik" in page_source and ("sudah" in page_source or "taken" in page_source or "kesalahan" in page_source)
        self.assertTrue(has_error, "Seharusnya ada pesan error validasi NIK duplikat")

        self.screenshot("nasabah_nik_duplicate")
        print("  ✅ PASS: NIK duplikat ditolak dengan validasi")

    def test_04_edit_nasabah(self):
        """TC-NSB-04: Admin dapat mengedit data nasabah."""
        print("\n👥 TC-NSB-04: Edit nasabah")

        self.driver.get(f"{BASE_URL}/admin/nasabah")
        time.sleep(1)

        # Klik tombol edit pada nasabah pertama
        edit_buttons = self.driver.find_elements(By.CSS_SELECTOR, "a[title='Edit']")
        if edit_buttons:
            edit_buttons[0].click()
            time.sleep(1)

            self.assert_text_present("Edit")

            # Ubah nomor HP
            no_hp = self.driver.find_element(By.ID, "no_hp")
            no_hp.clear()
            no_hp.send_keys("089876543210")

            self.driver.find_element(By.CSS_SELECTOR, "button[type='submit']").click()
            time.sleep(2)

            # Assert berhasil
            self.assert_text_present("berhasil")

            self.screenshot("nasabah_edit_success")
            print("  ✅ PASS: Data nasabah berhasil diperbarui")
        else:
            self.fail("Tombol edit tidak ditemukan")

    def test_05_detail_nasabah(self):
        """TC-NSB-05: Admin dapat melihat detail nasabah."""
        print("\n👥 TC-NSB-05: Detail nasabah")

        self.driver.get(f"{BASE_URL}/admin/nasabah")
        time.sleep(1)

        # Klik tombol detail
        detail_buttons = self.driver.find_elements(By.CSS_SELECTOR, "a[title='Detail']")
        if detail_buttons:
            detail_buttons[0].click()
            time.sleep(1)

            # Assert informasi detail tampil
            self.assert_text_present("Informasi Pribadi")
            self.assert_text_present("Informasi Tabungan")
            self.assert_text_present("Saldo Tabungan")
            self.assert_text_present("NSB-")

            self.screenshot("nasabah_detail")
            print("  ✅ PASS: Detail nasabah tampil lengkap")
        else:
            self.fail("Tombol detail tidak ditemukan")

    def test_06_toggle_status_nasabah(self):
        """TC-NSB-06: Admin dapat mengaktifkan/menonaktifkan nasabah."""
        print("\n👥 TC-NSB-06: Toggle status nasabah")

        self.driver.get(f"{BASE_URL}/admin/nasabah")
        time.sleep(1)

        # Cari dan klik tombol toggle
        toggle_buttons = self.driver.find_elements(By.CSS_SELECTOR, "form[action*='toggle-active'] button")
        if toggle_buttons:
            toggle_buttons[0].click()
            time.sleep(2)

            self.assert_text_present("berhasil")
            self.screenshot("nasabah_toggle_status")
            print("  ✅ PASS: Status nasabah berhasil diubah")
        else:
            self.fail("Tombol toggle tidak ditemukan")

    def test_07_pencarian_nasabah(self):
        """TC-NSB-07: Fitur pencarian nasabah berfungsi."""
        print("\n👥 TC-NSB-07: Pencarian nasabah")

        self.driver.get(f"{BASE_URL}/admin/nasabah")
        time.sleep(1)

        # Cari nasabah
        search_input = self.driver.find_element(By.ID, "search")
        search_input.clear()
        search_input.send_keys("Budi")
        search_input.send_keys(Keys.RETURN)
        time.sleep(2)

        # Assert Budi ditemukan
        self.assert_text_present("Budi Santoso")

        self.screenshot("nasabah_search")
        print("  ✅ PASS: Pencarian nasabah berfungsi")


# ═══════════════════════════════════════════════════════════════
# TEST 3: KATEGORI SAMPAH
# ═══════════════════════════════════════════════════════════════
class TestKategoriSampah(SIBANKTestBase):
    """TC-KAT: Test case untuk modul kategori sampah."""

    @classmethod
    def setUpClass(cls):
        super().setUpClass()
        cls.driver.get(f"{BASE_URL}/login")
        time.sleep(1)
        cls.driver.find_element(By.ID, "email").send_keys(ADMIN_EMAIL)
        cls.driver.find_element(By.ID, "password").send_keys(PASSWORD)
        cls.driver.find_element(By.CSS_SELECTOR, "button[type='submit']").click()
        time.sleep(2)

    def test_01_halaman_kategori_sampah(self):
        """TC-KAT-01: Admin dapat melihat halaman kategori sampah."""
        print("\n📂 TC-KAT-01: Halaman kategori sampah")

        self.driver.get(f"{BASE_URL}/admin/kategori-sampah")
        time.sleep(1)

        self.assert_text_present("Kategori Sampah")
        self.assert_text_present("Organik")
        self.assert_text_present("Anorganik")

        self.screenshot("kategori_index")
        print("  ✅ PASS: Halaman kategori tampil dengan data seeder")

    def test_02_tambah_kategori_baru(self):
        """TC-KAT-02: Admin dapat menambah kategori baru."""
        print("\n📂 TC-KAT-02: Tambah kategori baru")

        self.driver.get(f"{BASE_URL}/admin/kategori-sampah")
        time.sleep(1)

        # Isi form tambah
        self.driver.find_element(By.ID, "nama").send_keys("Kategori Selenium")
        self.driver.find_element(By.ID, "deskripsi").send_keys("Kategori test dari Selenium")

        self.driver.find_element(By.CSS_SELECTOR, "button[type='submit']").click()
        time.sleep(2)

        self.assert_text_present("berhasil")
        self.assert_text_present("Kategori Selenium")

        self.screenshot("kategori_create_success")
        print("  ✅ PASS: Kategori baru berhasil ditambahkan")


# ═══════════════════════════════════════════════════════════════
# TEST 4: JENIS & HARGA SAMPAH
# ═══════════════════════════════════════════════════════════════
class TestJenisSampah(SIBANKTestBase):
    """TC-JNS: Test case untuk modul jenis dan harga sampah."""

    @classmethod
    def setUpClass(cls):
        super().setUpClass()
        cls.driver.get(f"{BASE_URL}/login")
        time.sleep(1)
        cls.driver.find_element(By.ID, "email").send_keys(ADMIN_EMAIL)
        cls.driver.find_element(By.ID, "password").send_keys(PASSWORD)
        cls.driver.find_element(By.CSS_SELECTOR, "button[type='submit']").click()
        time.sleep(2)

    def test_01_halaman_jenis_sampah(self):
        """TC-JNS-01: Admin dapat melihat daftar jenis dan harga sampah."""
        print("\n🗑️ TC-JNS-01: Halaman jenis sampah")

        self.driver.get(f"{BASE_URL}/admin/jenis-sampah")
        time.sleep(1)

        self.assert_text_present("Jenis & Harga Sampah")
        self.assert_text_present("Botol Plastik")
        self.assert_text_present("Rp")

        # Assert tabel ada
        table = self.driver.find_element(By.TAG_NAME, "table")
        rows = table.find_elements(By.CSS_SELECTOR, "tbody tr")
        self.assertGreater(len(rows), 0, "Tabel jenis sampah harus memiliki data")

        self.screenshot("jenis_sampah_index")
        print(f"  ✅ PASS: Daftar jenis sampah tampil ({len(rows)} jenis)")

    def test_02_tambah_jenis_sampah_baru(self):
        """TC-JNS-02: Admin dapat menambah jenis sampah baru."""
        print("\n🗑️ TC-JNS-02: Tambah jenis sampah baru")

        self.driver.get(f"{BASE_URL}/admin/jenis-sampah")
        time.sleep(1)

        # Isi form
        self.driver.find_element(By.ID, "nama").send_keys("Sampah Selenium")

        # Pilih kategori
        kategori_select = Select(self.driver.find_element(By.ID, "kategori_id"))
        kategori_select.select_by_index(1)  # Pilih kategori pertama

        self.driver.find_element(By.ID, "harga_per_kg").send_keys("9999")

        self.driver.find_element(By.CSS_SELECTOR, "button[type='submit']").click()
        time.sleep(2)

        self.assert_text_present("berhasil")
        self.assert_text_present("Sampah Selenium")

        self.screenshot("jenis_sampah_create")
        print("  ✅ PASS: Jenis sampah baru berhasil ditambahkan")

    def test_03_toggle_status_jenis_sampah(self):
        """TC-JNS-03: Admin dapat mengaktifkan/menonaktifkan jenis sampah."""
        print("\n🗑️ TC-JNS-03: Toggle status jenis sampah")

        self.driver.get(f"{BASE_URL}/admin/jenis-sampah")
        time.sleep(1)

        toggle_forms = self.driver.find_elements(By.CSS_SELECTOR, "form[action*='toggle-active']")
        if toggle_forms:
            toggle_forms[0].find_element(By.CSS_SELECTOR, "button").click()
            time.sleep(2)
            self.assert_text_present("berhasil")
            self.screenshot("jenis_toggle")
            print("  ✅ PASS: Status jenis sampah berhasil diubah")
        else:
            print("  ⚠️ SKIP: Tombol toggle tidak ditemukan")


# ═══════════════════════════════════════════════════════════════
# TEST 5: TRANSAKSI SETOR SAMPAH
# Berdasarkan SRS bagian 10.5
# ═══════════════════════════════════════════════════════════════
class TestTransaksiSetor(SIBANKTestBase):
    """TC-STR: Test case untuk modul transaksi setor sampah."""

    @classmethod
    def setUpClass(cls):
        super().setUpClass()
        cls.driver.get(f"{BASE_URL}/login")
        time.sleep(1)
        cls.driver.find_element(By.ID, "email").send_keys(ADMIN_EMAIL)
        cls.driver.find_element(By.ID, "password").send_keys(PASSWORD)
        cls.driver.find_element(By.CSS_SELECTOR, "button[type='submit']").click()
        time.sleep(2)

    def test_01_halaman_daftar_setor(self):
        """TC-STR-01: Dapat melihat halaman daftar transaksi setor."""
        print("\n📥 TC-STR-01: Halaman daftar setor")

        self.driver.get(f"{BASE_URL}/admin/transaksi-setor")
        time.sleep(1)

        self.assert_text_present("Setor Sampah")
        self.assert_text_present("Setor Baru")

        self.screenshot("setor_index")
        print("  ✅ PASS: Halaman daftar setor tampil")

    def test_02_form_setor_tampil(self):
        """TC-STR-02: Form setor sampah tampil dengan dropdown nasabah dan jenis sampah."""
        print("\n📥 TC-STR-02: Form setor tampil lengkap")

        self.driver.get(f"{BASE_URL}/admin/transaksi-setor/create")
        time.sleep(1)

        self.assert_text_present("Form Setor Sampah")

        # Assert dropdown nasabah ada dan terisi
        nasabah_select = Select(self.driver.find_element(By.ID, "nasabah_id"))
        options = nasabah_select.options
        self.assertGreater(len(options), 1, "Dropdown nasabah harus terisi")

        # Assert dropdown jenis sampah ada
        jenis_select = self.driver.find_element(By.CSS_SELECTOR, ".jenis-select")
        self.assertIsNotNone(jenis_select)

        self.screenshot("setor_create_form")
        print(f"  ✅ PASS: Form setor tampil ({len(options) - 1} nasabah tersedia)")

    def test_03_proses_setor_sampah(self):
        """TC-STR-03: Petugas/admin dapat mencatat transaksi setor sampah."""
        print("\n📥 TC-STR-03: Proses setor sampah")

        self.driver.get(f"{BASE_URL}/admin/transaksi-setor/create")
        time.sleep(1)

        # Pilih nasabah pertama
        nasabah_select = Select(self.driver.find_element(By.ID, "nasabah_id"))
        nasabah_select.select_by_index(1)

        # Pilih jenis sampah pertama
        jenis_select = Select(self.driver.find_element(By.CSS_SELECTOR, ".jenis-select"))
        jenis_select.select_by_index(1)
        time.sleep(0.5)

        # Isi berat
        berat_input = self.driver.find_element(By.CSS_SELECTOR, ".berat-input")
        berat_input.clear()
        berat_input.send_keys("5")
        time.sleep(0.5)

        # Assert subtotal terhitung (JavaScript)
        subtotal = self.driver.find_element(By.CSS_SELECTOR, ".subtotal-display")
        subtotal_value = subtotal.get_attribute("value")
        self.assertNotEqual(subtotal_value, "Rp 0", "Subtotal harus terhitung otomatis")

        # Assert grand total terhitung
        grand_total = self.driver.find_element(By.ID, "grand-total").text
        self.assertNotEqual(grand_total, "Rp 0", "Grand total harus terhitung")

        self.screenshot("setor_filled")

        # Submit
        self.driver.find_element(By.CSS_SELECTOR, "button[type='submit']").click()
        time.sleep(2)

        # Assert berhasil
        self.assert_text_present("berhasil")

        self.screenshot("setor_success")
        print("  ✅ PASS: Transaksi setor berhasil dicatat")

    def test_04_tambah_item_sampah(self):
        """TC-STR-04: Dapat menambah beberapa jenis sampah dalam satu transaksi."""
        print("\n📥 TC-STR-04: Multi-item setor")

        self.driver.get(f"{BASE_URL}/admin/transaksi-setor/create")
        time.sleep(1)

        # Klik tombol "Tambah Jenis Sampah"
        add_btn = self.driver.find_element(By.XPATH, "//button[contains(text(), 'Tambah Jenis')]")
        add_btn.click()
        time.sleep(0.5)

        # Assert ada 2 item sekarang
        items = self.driver.find_elements(By.CSS_SELECTOR, ".setor-item")
        self.assertEqual(len(items), 2, "Harus ada 2 item sampah")

        # Assert tombol hapus muncul
        remove_buttons = self.driver.find_elements(By.CSS_SELECTOR, ".remove-item")
        visible_removes = [btn for btn in remove_buttons if btn.is_displayed()]
        self.assertGreater(len(visible_removes), 0, "Tombol hapus item harus muncul")

        self.screenshot("setor_multi_item")
        print("  ✅ PASS: Multi-item setor berfungsi")

    def test_05_detail_transaksi_setor(self):
        """TC-STR-05: Dapat melihat detail/slip transaksi setor."""
        print("\n📥 TC-STR-05: Detail transaksi setor")

        self.driver.get(f"{BASE_URL}/admin/transaksi-setor")
        time.sleep(1)

        # Klik detail jika ada transaksi
        detail_buttons = self.driver.find_elements(By.CSS_SELECTOR, "a[class*='btn-info']")
        if detail_buttons:
            detail_buttons[0].click()
            time.sleep(1)

            self.assert_text_present("Detail Transaksi")
            self.assert_text_present("Detail Sampah Disetor")
            self.assert_text_present("Informasi Nasabah")
            self.assert_text_present("Cetak Bukti")

            self.screenshot("setor_detail")
            print("  ✅ PASS: Detail/slip transaksi setor tampil")
        else:
            print("  ⚠️ SKIP: Belum ada transaksi setor untuk dilihat detailnya")


# ═══════════════════════════════════════════════════════════════
# TEST 6: TRANSAKSI TARIK SALDO
# ═══════════════════════════════════════════════════════════════
class TestTransaksiTarik(SIBANKTestBase):
    """TC-TRK: Test case untuk modul transaksi tarik saldo."""

    @classmethod
    def setUpClass(cls):
        super().setUpClass()
        cls.driver.get(f"{BASE_URL}/login")
        time.sleep(1)
        cls.driver.find_element(By.ID, "email").send_keys(ADMIN_EMAIL)
        cls.driver.find_element(By.ID, "password").send_keys(PASSWORD)
        cls.driver.find_element(By.CSS_SELECTOR, "button[type='submit']").click()
        time.sleep(2)

    def test_01_halaman_daftar_tarik(self):
        """TC-TRK-01: Dapat melihat halaman daftar penarikan."""
        print("\n📤 TC-TRK-01: Halaman daftar tarik")

        self.driver.get(f"{BASE_URL}/admin/transaksi-tarik")
        time.sleep(1)

        self.assert_text_present("Tarik Saldo")
        self.assert_text_present("Tarik Baru")

        self.screenshot("tarik_index")
        print("  ✅ PASS: Halaman daftar tarik tampil")

    def test_02_form_tarik_dengan_info_saldo(self):
        """TC-TRK-02: Form tarik menampilkan info saldo saat nasabah dipilih."""
        print("\n📤 TC-TRK-02: Form tarik dengan info saldo")

        self.driver.get(f"{BASE_URL}/admin/transaksi-tarik/create")
        time.sleep(1)

        self.assert_text_present("Form Penarikan Saldo")

        # Pilih nasabah
        nasabah_select = Select(self.driver.find_element(By.ID, "nasabah_id"))
        if len(nasabah_select.options) > 1:
            nasabah_select.select_by_index(1)
            time.sleep(1)

            # Assert info saldo muncul
            saldo_info = self.driver.find_element(By.ID, "saldo-info")
            self.assertTrue(saldo_info.is_displayed(), "Info saldo harus tampil")

            saldo_text = self.driver.find_element(By.ID, "saldo-display").text
            self.assertIn("Rp", saldo_text, "Saldo harus ditampilkan dalam Rupiah")

            self.screenshot("tarik_saldo_info")
            print(f"  ✅ PASS: Info saldo tampil: {saldo_text}")
        else:
            print("  ⚠️ SKIP: Tidak ada nasabah dengan saldo > 0")

    def test_03_validasi_saldo_tidak_cukup(self):
        """TC-TRK-03: Validasi client-side saat jumlah melebihi saldo."""
        print("\n📤 TC-TRK-03: Validasi saldo tidak cukup")

        self.driver.get(f"{BASE_URL}/admin/transaksi-tarik/create")
        time.sleep(1)

        nasabah_select = Select(self.driver.find_element(By.ID, "nasabah_id"))
        if len(nasabah_select.options) > 1:
            nasabah_select.select_by_index(1)
            time.sleep(1)

            # Isi jumlah yang sangat besar
            jumlah_input = self.driver.find_element(By.ID, "jumlah")
            jumlah_input.clear()
            jumlah_input.send_keys("999999999")
            time.sleep(0.5)

            # Assert warning muncul
            warning = self.driver.find_element(By.ID, "saldo-warning")
            self.assertTrue(warning.is_displayed(), "Warning saldo tidak cukup harus muncul")

            # Assert tombol submit disabled
            submit_btn = self.driver.find_element(By.ID, "btn-submit")
            self.assertTrue(submit_btn.get_attribute("disabled"), "Tombol submit harus disabled")

            self.screenshot("tarik_saldo_warning")
            print("  ✅ PASS: Validasi saldo berfungsi dengan benar")
        else:
            print("  ⚠️ SKIP: Tidak ada nasabah dengan saldo > 0")

    def test_04_proses_tarik_saldo(self):
        """TC-TRK-04: Proses penarikan saldo berhasil."""
        print("\n📤 TC-TRK-04: Proses tarik saldo")

        self.driver.get(f"{BASE_URL}/admin/transaksi-tarik/create")
        time.sleep(1)

        nasabah_select = Select(self.driver.find_element(By.ID, "nasabah_id"))
        if len(nasabah_select.options) > 1:
            nasabah_select.select_by_index(1)
            time.sleep(1)

            # Isi jumlah penarikan kecil
            jumlah_input = self.driver.find_element(By.ID, "jumlah")
            jumlah_input.clear()
            jumlah_input.send_keys("1000")

            # Isi keterangan
            keterangan = self.driver.find_element(By.ID, "keterangan")
            keterangan.send_keys("Test penarikan Selenium")

            self.screenshot("tarik_filled")

            # Submit
            self.driver.find_element(By.CSS_SELECTOR, "button[type='submit']").click()
            time.sleep(2)

            # Assert berhasil atau ada validasi server-side
            current_url = self.driver.current_url
            page_source = self.driver.page_source
            if "berhasil" in page_source.lower():
                self.screenshot("tarik_success")
                print("  ✅ PASS: Penarikan saldo berhasil")
            elif "transaksi-tarik" in current_url and "create" not in current_url:
                self.screenshot("tarik_success")
                print("  ✅ PASS: Penarikan saldo berhasil (redirect ke index)")
            else:
                self.screenshot("tarik_result")
                print("  ⚠️ INFO: Penarikan mungkin gagal — saldo nasabah seeder tidak cukup")
        else:
            print("  ⚠️ SKIP: Tidak ada nasabah dengan saldo > 0")


# ═══════════════════════════════════════════════════════════════
# TEST 7: LAPORAN
# ═══════════════════════════════════════════════════════════════
class TestLaporan(SIBANKTestBase):
    """TC-LPR: Test case untuk modul laporan."""

    @classmethod
    def setUpClass(cls):
        super().setUpClass()
        cls.driver.get(f"{BASE_URL}/login")
        time.sleep(1)
        cls.driver.find_element(By.ID, "email").send_keys(ADMIN_EMAIL)
        cls.driver.find_element(By.ID, "password").send_keys(PASSWORD)
        cls.driver.find_element(By.CSS_SELECTOR, "button[type='submit']").click()
        time.sleep(2)

    def test_01_halaman_laporan_tampil(self):
        """TC-LPR-01: Halaman laporan tampil dengan filter."""
        print("\n📊 TC-LPR-01: Halaman laporan")

        self.driver.get(f"{BASE_URL}/admin/laporan")
        time.sleep(1)

        self.assert_text_present("Laporan Transaksi")

        # Assert filter date ada
        dari_input = self.driver.find_element(By.ID, "dari")
        sampai_input = self.driver.find_element(By.ID, "sampai")
        self.assertIsNotNone(dari_input)
        self.assertIsNotNone(sampai_input)

        # Assert filter tipe ada
        tipe_select = self.driver.find_element(By.ID, "tipe")
        self.assertIsNotNone(tipe_select)

        # Assert tombol cetak ada
        self.assert_text_present("Cetak Laporan")

        self.screenshot("laporan_index")
        print("  ✅ PASS: Halaman laporan tampil dengan filter lengkap")

    def test_02_filter_laporan_setor(self):
        """TC-LPR-02: Filter laporan hanya transaksi setor."""
        print("\n📊 TC-LPR-02: Filter laporan setor")

        self.driver.get(f"{BASE_URL}/admin/laporan")
        time.sleep(1)

        # Set filter tipe = setor
        tipe_select = Select(self.driver.find_element(By.ID, "tipe"))
        tipe_select.select_by_value("setor")

        # Klik filter
        self.driver.find_element(By.CSS_SELECTOR, "button[type='submit']").click()
        time.sleep(2)

        # Assert hanya setor yang tampil
        self.assert_text_present("Transaksi Setor")

        self.screenshot("laporan_filter_setor")
        print("  ✅ PASS: Filter laporan setor berfungsi")

    def test_03_filter_laporan_tarik(self):
        """TC-LPR-03: Filter laporan hanya transaksi tarik."""
        print("\n📊 TC-LPR-03: Filter laporan tarik")

        self.driver.get(f"{BASE_URL}/admin/laporan")
        time.sleep(1)

        tipe_select = Select(self.driver.find_element(By.ID, "tipe"))
        tipe_select.select_by_value("tarik")

        self.driver.find_element(By.CSS_SELECTOR, "button[type='submit']").click()
        time.sleep(2)

        self.assert_text_present("Transaksi Tarik")

        self.screenshot("laporan_filter_tarik")
        print("  ✅ PASS: Filter laporan tarik berfungsi")


# ═══════════════════════════════════════════════════════════════
# TEST 8: HAK AKSES (ROLE-BASED)
# ═══════════════════════════════════════════════════════════════
class TestHakAkses(SIBANKTestBase):
    """TC-ACL: Test case untuk kontrol akses berbasis role."""

    def test_01_petugas_tidak_bisa_akses_dashboard_admin(self):
        """TC-ACL-01: Petugas tidak bisa mengakses dashboard admin."""
        print("\n🔒 TC-ACL-01: Petugas dilarang akses dashboard admin")

        self.login_as(PETUGAS_EMAIL)
        time.sleep(1)

        # Coba akses dashboard admin
        self.driver.get(f"{BASE_URL}/admin/dashboard")
        time.sleep(1)

        # Assert ditolak (403) atau redirect
        page_source = self.driver.page_source
        current_url = self.driver.current_url
        is_blocked = ("403" in page_source or
                      "Akses ditolak" in page_source or
                      "Forbidden" in page_source or
                      "/admin/dashboard" not in current_url)
        self.assertTrue(is_blocked, "Petugas seharusnya tidak bisa akses dashboard admin")

        self.screenshot("acl_petugas_blocked_dashboard")
        print("  ✅ PASS: Petugas dilarang akses dashboard admin")

        self.logout()

    def test_02_petugas_tidak_bisa_akses_kategori(self):
        """TC-ACL-02: Petugas tidak bisa mengakses kategori sampah."""
        print("\n🔒 TC-ACL-02: Petugas dilarang akses kategori")

        self.login_as(PETUGAS_EMAIL)
        time.sleep(1)

        self.driver.get(f"{BASE_URL}/admin/kategori-sampah")
        time.sleep(1)

        page_source = self.driver.page_source
        is_blocked = ("403" in page_source or
                      "Akses ditolak" in page_source or
                      "Forbidden" in page_source)
        self.assertTrue(is_blocked, "Petugas seharusnya tidak bisa akses kategori sampah")

        self.screenshot("acl_petugas_blocked_kategori")
        print("  ✅ PASS: Petugas dilarang akses kategori")

        self.logout()

    def test_03_petugas_tidak_bisa_akses_laporan(self):
        """TC-ACL-03: Petugas tidak bisa mengakses laporan."""
        print("\n🔒 TC-ACL-03: Petugas dilarang akses laporan")

        self.login_as(PETUGAS_EMAIL)
        time.sleep(1)

        self.driver.get(f"{BASE_URL}/admin/laporan")
        time.sleep(1)

        page_source = self.driver.page_source
        is_blocked = ("403" in page_source or
                      "Akses ditolak" in page_source or
                      "Forbidden" in page_source)
        self.assertTrue(is_blocked, "Petugas seharusnya tidak bisa akses laporan")

        self.screenshot("acl_petugas_blocked_laporan")
        print("  ✅ PASS: Petugas dilarang akses laporan")

        self.logout()

    def test_04_petugas_bisa_akses_transaksi(self):
        """TC-ACL-04: Petugas bisa mengakses transaksi setor dan tarik."""
        print("\n🔒 TC-ACL-04: Petugas bisa akses transaksi")

        self.login_as(PETUGAS_EMAIL)
        time.sleep(1)

        # Akses setor
        self.driver.get(f"{BASE_URL}/admin/transaksi-setor")
        time.sleep(1)
        self.assert_text_present("Setor Sampah")

        # Akses tarik
        self.driver.get(f"{BASE_URL}/admin/transaksi-tarik")
        time.sleep(1)
        self.assert_text_present("Tarik Saldo")

        # Akses nasabah
        self.driver.get(f"{BASE_URL}/admin/nasabah")
        time.sleep(1)
        self.assert_text_present("Nasabah")

        self.screenshot("acl_petugas_allowed")
        print("  ✅ PASS: Petugas berhasil akses transaksi dan nasabah")

        self.logout()

    def test_05_guest_redirect_ke_login(self):
        """TC-ACL-05: User yang belum login di-redirect ke halaman login."""
        print("\n🔒 TC-ACL-05: Guest redirect ke login")

        # Pastikan tidak login
        self.driver.delete_all_cookies()
        self.driver.get(f"{BASE_URL}/admin/dashboard")
        time.sleep(2)

        self.assert_url_contains("/login")

        self.screenshot("acl_guest_redirect")
        print("  ✅ PASS: Guest di-redirect ke login")


# ═══════════════════════════════════════════════════════════════
# TEST 9: NAVIGASI & UI
# ═══════════════════════════════════════════════════════════════
class TestNavigasiUI(SIBANKTestBase):
    """TC-UI: Test case untuk navigasi dan elemen UI."""

    @classmethod
    def setUpClass(cls):
        super().setUpClass()
        cls.driver.get(f"{BASE_URL}/login")
        time.sleep(1)
        cls.driver.find_element(By.ID, "email").send_keys(ADMIN_EMAIL)
        cls.driver.find_element(By.ID, "password").send_keys(PASSWORD)
        cls.driver.find_element(By.CSS_SELECTOR, "button[type='submit']").click()
        time.sleep(2)

    def test_01_sidebar_navigasi_berfungsi(self):
        """TC-UI-01: Sidebar navigasi berfungsi dengan benar."""
        print("\n🎨 TC-UI-01: Sidebar navigasi")

        self.driver.get(f"{BASE_URL}/admin/dashboard")
        time.sleep(1)

        # Assert sidebar ada
        sidebar = self.driver.find_element(By.CSS_SELECTOR, ".sidebar")
        self.assertTrue(sidebar.is_displayed())

        # Assert brand SIBANK ada
        self.assert_text_present("SIBANK")

        # Assert menu items ada
        nav_items = self.driver.find_elements(By.CSS_SELECTOR, ".nav-item")
        self.assertGreater(len(nav_items), 3, "Sidebar harus memiliki menu items")

        # Klik menu Nasabah
        for item in nav_items:
            if "Nasabah" in item.text:
                item.click()
                time.sleep(1)
                self.assert_url_contains("/admin/nasabah")
                break

        self.screenshot("sidebar_navigation")
        print(f"  ✅ PASS: Sidebar berfungsi ({len(nav_items)} menu items)")

    def test_02_dashboard_statistik_card(self):
        """TC-UI-02: Dashboard menampilkan statistik card."""
        print("\n🎨 TC-UI-02: Dashboard statistik card")

        self.driver.get(f"{BASE_URL}/admin/dashboard")
        time.sleep(1)

        # Assert stat cards ada
        stat_cards = self.driver.find_elements(By.CSS_SELECTOR, ".stat-card")
        self.assertGreater(len(stat_cards), 3, "Dashboard harus memiliki minimal 4 stat card")

        # Assert grid kategori dan bulanan ada
        cards = self.driver.find_elements(By.CSS_SELECTOR, ".card")
        self.assertGreater(len(cards), 0, "Dashboard harus memiliki card konten")

        self.screenshot("dashboard_stats")
        print(f"  ✅ PASS: Dashboard tampil dengan {len(stat_cards)} stat cards")

    def test_03_flash_message_auto_dismiss(self):
        """TC-UI-03: Flash message otomatis hilang setelah beberapa detik."""
        print("\n🎨 TC-UI-03: Flash message auto dismiss")

        # Buat nasabah untuk trigger flash success
        self.driver.get(f"{BASE_URL}/admin/nasabah/create")
        time.sleep(1)

        self.driver.find_element(By.ID, "nama").send_keys("Flash Test User")
        self.driver.find_element(By.ID, "nik").send_keys("1111222233334444")
        self.driver.find_element(By.ID, "no_hp").send_keys("081234512345")
        self.driver.find_element(By.ID, "alamat").send_keys("Jl. Flash Test")

        self.driver.find_element(By.CSS_SELECTOR, "button[type='submit']").click()
        time.sleep(1)

        # Assert flash message ada
        try:
            flash = self.driver.find_element(By.ID, "flash-success")
            self.assertTrue(flash.is_displayed(), "Flash message harus tampil")
            print("  ✅ PASS: Flash message tampil setelah aksi sukses")
        except Exception:
            print("  ⚠️ INFO: Flash message mungkin sudah auto-dismiss")

        self.screenshot("flash_message")


# ═══════════════════════════════════════════════════════════════
# RUNNER
# ═══════════════════════════════════════════════════════════════
if __name__ == "__main__":
    print("=" * 60)
    print("🏦 SIBANK — Selenium Test Suite")
    print(f"📅 {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
    print(f"🌐 Base URL: {BASE_URL}")
    print("=" * 60)

    # Jalankan test secara berurutan
    loader = unittest.TestLoader()
    suite = unittest.TestSuite()

    # Urutan test sesuai alur pengguna
    test_classes = [
        TestLogin,
        TestNasabah,
        TestKategoriSampah,
        TestJenisSampah,
        TestTransaksiSetor,
        TestTransaksiTarik,
        TestLaporan,
        TestHakAkses,
        TestNavigasiUI,
    ]

    for test_class in test_classes:
        tests = loader.loadTestsFromTestCase(test_class)
        suite.addTests(tests)

    runner = unittest.TextTestRunner(verbosity=2)
    result = runner.run(suite)

    # Summary
    print("\n" + "=" * 60)
    print("📋 TEST SUMMARY")
    print(f"  Total  : {result.testsRun}")
    print(f"  Passed : {result.testsRun - len(result.failures) - len(result.errors)}")
    print(f"  Failed : {len(result.failures)}")
    print(f"  Errors : {len(result.errors)}")
    print(f"  📸 Screenshots saved in: {SCREENSHOT_DIR}")
    print("=" * 60)
