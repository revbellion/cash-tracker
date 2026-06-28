<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('logo:remove-bg')]
#[Description('Hapus background putih dari logo.png')]
class RemoveLogoBackground extends Command
{
    public function handle()
    {
        $path = public_path('logo.png');

        if (!file_exists($path)) {
            $this->error("File logo.png tidak ditemukan di public/");
            return 1;
        }

        $img = imagecreatefrompng($path);
        if (!$img) {
            $this->error("Gagal membaca logo.png");
            return 1;
        }

        $w = imagesx($img);
        $h = imagesy($img);

        $out = imagecreatetruecolor($w, $h);
        imagealphablending($out, false);
        imagesavealpha($out, true);
        imagefill($out, 0, 0, imagecolorallocatealpha($out, 0, 0, 0, 127));

        $threshold = 230;

        for ($x = 0; $x < $w; $x++) {
            for ($y = 0; $y < $h; $y++) {
                $rgb = imagecolorat($img, $x, $y);
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;
                $alpha = ($rgb >> 24) & 0x7F;

                if ($r >= $threshold && $g >= $threshold && $b >= $threshold) {
                    $color = imagecolorallocatealpha($out, $r, $g, $b, 127);
                } else {
                    $color = imagecolorallocatealpha($out, $r, $g, $b, $alpha);
                }
                imagesetpixel($out, $x, $y, $color);
            }
        }

        imagedestroy($img);

        $backup = public_path('logo_original.png');
        if (!file_exists($backup)) {
            copy($path, $backup);
            $this->info("Backup dibuat: logo_original.png");
        }

        imagepng($out, $path);
        imagedestroy($out);

        $this->info("✅ Background putih berhasil dihapus dari logo.png");
        $this->warn("Backup disimpan sebagai logo_original.png");
        return 0;
    }
}
