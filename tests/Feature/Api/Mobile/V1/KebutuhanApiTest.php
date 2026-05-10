<?php

namespace Tests\Feature\Api\Mobile\V1;

use App\Models\JenisKebutuhan;
use App\Models\JenisPenanganan;
use App\Models\Opd;
use App\Models\Pasien;
use App\Models\PasienKebutuhan;
use App\Models\Faskes;
use App\Models\Puskesmas;
use App\Models\Kecamatan;
use App\Models\Kelurahan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class KebutuhanApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_show_kebutuhan_returns_detail_for_allowed_user(): void
    {
        $this->seed([\Database\Seeders\PermissionSeeder::class, \Database\Seeders\RoleSeeder::class]);

        [$user, $kebutuhan] = $this->makeUserAndKebutuhan();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/mobile/v1/kebutuhans/' . $kebutuhan->id);

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $kebutuhan->id)
            ->assertJsonPath('data.opd', $kebutuhan->opd->name)
            ->assertJsonPath('data.verification_status', 'pending');
    }

    public function test_verify_updates_status_for_allowed_user(): void
    {
        $this->seed([\Database\Seeders\PermissionSeeder::class, \Database\Seeders\RoleSeeder::class]);

        [$user, $kebutuhan] = $this->makeUserAndKebutuhan();

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson('/api/mobile/v1/kebutuhans/' . $kebutuhan->id . '/verify', [
                'status' => 'proses',
            ]);

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.verification_status', 'proses');

        $this->assertDatabaseHas('pasien_kebutuhans', [
            'id' => $kebutuhan->id,
            'verification_status' => 'proses',
        ]);
    }

    public function test_add_follow_up_creates_history_and_photos(): void
    {
        Storage::fake('public');
        $this->seed([\Database\Seeders\PermissionSeeder::class, \Database\Seeders\RoleSeeder::class]);

        [$user, $kebutuhan] = $this->makeUserAndKebutuhan('proses');

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/mobile/v1/kebutuhans/' . $kebutuhan->id . '/follow-ups', [
                'keterangan' => 'Update penanganan mobile',
                'fotos' => [
                    UploadedFile::fake()->image('foto-1.jpg'),
                    UploadedFile::fake()->image('foto-2.png'),
                ],
            ]);

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(2, 'data.fotos');

        $this->assertDatabaseHas('pasien_kebutuhan_tindak_lanjuts', [
            'pasien_kebutuhan_id' => $kebutuhan->id,
            'user_id' => $user->id,
            'keterangan' => 'Update penanganan mobile',
        ]);

        $this->assertDatabaseCount('tindak_lanjut_fotos', 2);
    }

    public function test_mark_done_requires_existing_follow_up(): void
    {
        $this->seed([\Database\Seeders\PermissionSeeder::class, \Database\Seeders\RoleSeeder::class]);

        [$user, $kebutuhan] = $this->makeUserAndKebutuhan('proses');

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson('/api/mobile/v1/kebutuhans/' . $kebutuhan->id . '/mark-done');

        $response
            ->assertStatus(422)
            ->assertJsonPath('success', false);

        $this->assertDatabaseHas('pasien_kebutuhans', [
            'id' => $kebutuhan->id,
            'verification_status' => 'proses',
        ]);
    }

    public function test_mark_done_success_when_follow_up_exists(): void
    {
        Storage::fake('public');
        $this->seed([\Database\Seeders\PermissionSeeder::class, \Database\Seeders\RoleSeeder::class]);

        [$user, $kebutuhan] = $this->makeUserAndKebutuhan('proses');

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/mobile/v1/kebutuhans/' . $kebutuhan->id . '/follow-ups', [
                'keterangan' => 'Follow up awal',
                'fotos' => [UploadedFile::fake()->image('foto.jpg')],
            ])
            ->assertOk();

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson('/api/mobile/v1/kebutuhans/' . $kebutuhan->id . '/mark-done');

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.verification_status', 'selesai');

        $this->assertDatabaseHas('pasien_kebutuhans', [
            'id' => $kebutuhan->id,
            'verification_status' => 'selesai',
        ]);
    }

    public function test_kebutuhan_forbidden_for_other_opd_user(): void
    {
        $this->seed([\Database\Seeders\PermissionSeeder::class, \Database\Seeders\RoleSeeder::class]);

        [$ownerUser, $kebutuhan] = $this->makeUserAndKebutuhan();

        $otherOpd = Opd::query()->create(['name' => 'OPD Lain']);
        $otherUser = User::factory()->create([
            'opd_id' => $otherOpd->id,
        ]);
        $otherUser->assignRole('OPD');

        $this->actingAs($otherUser, 'sanctum')
            ->getJson('/api/mobile/v1/kebutuhans/' . $kebutuhan->id)
            ->assertStatus(403)
            ->assertJsonPath('success', false);
    }

    private function makeUserAndKebutuhan(string $status = 'pending'): array
    {
        $opd = Opd::query()->create(['name' => 'OPD Tes']);
        $jenisKebutuhan = JenisKebutuhan::query()->create(['name' => 'Kebutuhan Tes']);
        $jenisPenanganan = JenisPenanganan::query()->create(['name' => 'Penanganan Tes']);
        $faskes = Faskes::query()->create(['name' => 'Faskes Tes']);
        $puskesmas = Puskesmas::query()->create(['name' => 'Puskesmas Tes']);
        $kecamatan = Kecamatan::query()->create(['name' => 'Kecamatan Tes']);
        $kelurahan = Kelurahan::query()->create([
            'kecamatan_id' => $kecamatan->id,
            'name' => 'Kelurahan Tes',
        ]);

        $user = User::factory()->create([
            'opd_id' => $opd->id,
        ]);
        $user->assignRole('OPD');

        $pasien = Pasien::query()->create([
            'name' => 'Pasien Uji',
            'nik' => '3501010101010001',
            'birth_date' => '2000-01-01',
            'gender' => 'laki-laki',
            'faskes_id' => $faskes->id,
            'puskesmas_id' => $puskesmas->id,
            'kecamatan_id' => $kecamatan->id,
            'kelurahan_id' => $kelurahan->id,
            'address' => 'Alamat Uji',
            'coordinates' => '-7.801389,110.364722',
            'economic_status' => 'sederhana',
            'family_head_name' => 'Kepala Keluarga Uji',
            'family_head_nik' => '3501010101010002',
            'family_income_range' => '>1000000',
            'patient_relationship' => 'pasien-sendiri',
            'tb_so_ro' => 1,
            'treatment_start_date' => '2026-01-10',
            'visit_date' => '2026-01-12',
            'information_date' => '2026-01-14',
            'treatment_status' => 'terlaksana',
            'nutritional_status' => 'normal',
        ]);

        $kebutuhan = PasienKebutuhan::query()->create([
            'pasien_id' => $pasien->id,
            'jenis_kebutuhan_id' => $jenisKebutuhan->id,
            'opd_id' => $opd->id,
            'jenis_penanganan_id' => $jenisPenanganan->id,
            'need_detail' => 'Detail kebutuhan',
            'verification_status' => $status,
        ]);

        return [$user, $kebutuhan];
    }
}
