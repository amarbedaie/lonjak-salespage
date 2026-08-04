<?php

namespace App\Console\Commands;

use App\Models\InfraSalespage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

/**
 * Penyambung INFRA → Mendap. Tarik arahan salespage.cipta dari otak pemasaran
 * BeDaie, simpan sebagai draf InfraSalespage, dan lapor hasil balik.
 * NOTA: pemetaan draf → Salespage sebenar Mendap ialah kerja fasa seterusnya.
 * Self-gate: tidak berbuat apa-apa selagi INFRA_PULL_ENABLED tidak diset.
 */
class InfraTarikArahan extends Command
{
    protected $signature = 'infra:tarik-arahan';

    protected $description = 'Tarik & simpan arahan salespage dari INFRA (BeDaie OS)';

    public function handle(): int
    {
        if (! config('infra.aktif') || config('infra.url') === '' || config('infra.token') === '') {
            $this->line('Penyambung INFRA tidak aktif (set INFRA_URL, INFRA_TOKEN, INFRA_PULL_ENABLED=true).');

            return self::SUCCESS;
        }

        try {
            $respons = Http::withToken(config('infra.token'))
                ->acceptJson()->timeout(15)
                ->get(config('infra.url').'/api/v1/commands');
        } catch (\Throwable $e) {
            $this->warn('INFRA tidak dapat dihubungi: '.$e->getMessage());

            return self::SUCCESS;
        }

        if (! $respons->successful()) {
            $this->warn('INFRA balas HTTP '.$respons->status());

            return self::SUCCESS;
        }

        $senarai = $respons->json() ?? [];
        $siap = 0;

        foreach ($senarai as $arahan) {
            $uuid = $arahan['command_uuid'] ?? null;
            if (! $uuid) {
                continue;
            }

            if (($arahan['command_type'] ?? '') === 'salespage.cipta') {
                $draf = InfraSalespage::updateOrCreate(['command_uuid' => $uuid], [
                    'produk' => $arahan['payload']['produk'] ?? null,
                    'salespage' => $arahan['payload']['salespage'] ?? null,
                    'utm' => $arahan['payload']['utm'] ?? null,
                    'status' => 'draf',
                ]);
                $this->laporkan($uuid, 'selesai', [
                    'infra_salespage_id' => $draf->id,
                    'nota' => 'Disimpan sebagai draf di Mendap — penerbitan automatik menyusul',
                ]);
                $siap++;
            } else {
                $this->laporkan($uuid, 'gagal', ['mesej' => 'Jenis arahan belum disokong oleh Mendap: '.($arahan['command_type'] ?? '?')]);
            }
        }

        $this->info('Arahan diproses: '.count($senarai).' (selesai: '.$siap.')');

        return self::SUCCESS;
    }

    private function laporkan(string $uuid, string $status, array $hasil): void
    {
        try {
            Http::withToken(config('infra.token'))
                ->acceptJson()->timeout(15)
                ->post(config('infra.url').'/api/v1/commands/'.$uuid.'/result', [
                    'status' => $status,
                    'hasil' => $hasil,
                ]);
        } catch (\Throwable $e) {
            $this->warn('Gagal lapor '.$uuid.': '.$e->getMessage());
        }
    }
}
