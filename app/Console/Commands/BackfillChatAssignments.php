<?php

namespace App\Console\Commands;

use App\Models\ChatSession;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackfillChatAssignments extends Command
{
    protected $signature = 'chat:backfill-assignments {--dry-run : Cuma tampilkan yang akan diubah, tanpa nyimpen apapun}';

    protected $description = 'Isi ulang chat_session_agents untuk sesi lama yang punya balasan agent tapi belum ke-assign (primary) karena bug lama';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        // Ambil semua session_id yang SUDAH punya primary agent — buat di-exclude
        $alreadyAssigned = DB::table('chat_session_agents')
            ->where('role', 'primary')
            ->pluck('session_id')
            ->unique();

        // Cari sesi yang: belum dihapus, gak ada di daftar yang udah assigned
        $candidates = ChatSession::whereNull('deleted_at')
            ->whereNotIn('id', $alreadyAssigned)
            ->get(['id', 'guest_name', 'status', 'inquiry_type']);

        if ($candidates->isEmpty()) {
            $this->info('Gak ada sesi yang perlu di-backfill. Semua udah punya primary agent.');
            return self::SUCCESS;
        }

        $this->info("Ditemukan {$candidates->count()} sesi kandidat (belum punya primary agent). Mengecek isi pesan...");

        $fixed  = 0;
        $skipped = 0;

        $rows = $this->withProgressBar($candidates, function ($session) use ($dryRun, &$fixed, &$skipped) {
            // Cari pesan pertama dari agent di sesi ini
            $firstAgentMsg = DB::table('messages')
                ->where('session_id', $session->id)
                ->where('sender_type', 'agent')
                ->whereNotNull('sender_id')
                ->orderBy('sent_at')
                ->first(['sender_id', 'sent_at']);

            if (!$firstAgentMsg) {
                // Sesi ini emang beneran gak pernah disentuh agent — bukan bug, skip aja
                $skipped++;
                return;
            }

            if ($dryRun) {
                $this->newLine();
                $this->line("[DRY RUN] Session #{$session->id} ({$session->guest_name}) → agent_id {$firstAgentMsg->sender_id} @ {$firstAgentMsg->sent_at}");
                $fixed++;
                return;
            }

            DB::table('chat_session_agents')->insert([
                'session_id'  => $session->id,
                'agent_id'    => $firstAgentMsg->sender_id,
                'role'        => 'primary',
                'is_active'   => false,   // sesi lama, anggap udah gak aktif (kebanyakan closed)
                'assigned_at' => $firstAgentMsg->sent_at,
                'left_at'     => $firstAgentMsg->sent_at, // opsional, sesuaikan kalau kolom ini nullable & gak wajib
            ]);

            $fixed++;
        });

        $this->newLine(2);

        if ($dryRun) {
            $this->info("[DRY RUN] {$fixed} sesi AKAN di-backfill, {$skipped} sesi dilewati (gak ada balasan agent sama sekali).");
            $this->comment('Jalankan tanpa --dry-run untuk benar-benar menyimpan perubahan.');
        } else {
            $this->info("Selesai! {$fixed} sesi berhasil di-backfill, {$skipped} sesi dilewati (gak ada balasan agent sama sekali).");
        }

        return self::SUCCESS;
    }
}