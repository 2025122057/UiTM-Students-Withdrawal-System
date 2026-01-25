<?php
/**
 * Unified Form Handler
 * This file replaces individual wrapper files (dekan.php, bendahari.php, etc.)
 * mapping the 'type' GET parameter to the appropriate form configuration.
 */

$type = $_GET['type'] ?? 'dekan';

switch ($type) {
    case 'bendahari':
        $form_title = "BORANG BENDAHARI";
        $form_subtitle = "Pelepasan Bayaran & Yuran Pengajian";
        $form_icon = "fa-money-check-alt";
        $theme_color_text = "text-success";
        $theme_color_btn = "btn-success";
        $form_name_db = "Borang Bendahari (Pelepasan Hutang)";
        break;

    case 'pustakawan':
        $form_title = "BORANG KETUA PUSTAKAWAN";
        $form_subtitle = "Pemulangan Buku & Pelepasan Perpustakaan";
        $form_icon = "fa-book-reader";
        $theme_color_text = "text-info";
        $theme_color_btn = "btn-info";
        $theme_color_btn_text = "text-white";
        $form_name_db = "Borang Ketua Pustakawan (Pemulangan Buku)";
        break;

    case 'pengetua':
        $form_title = "BORANG PENGETUA KOLEJ";
        $form_subtitle = "Pemulangan Kunci Kolej & Pelepasan Kolej";
        $form_icon = "fa-key";
        $theme_color_text = "text-warning";
        $theme_color_btn = "btn-warning";
        $theme_color_btn_text = "text-dark";
        $form_name_db = "Borang Pengetua Kolej (Pelepasan Kolej)";
        $extra_note = "Borang ini hanya untuk pelajar sekiranya tinggal di kolej sahaja.";
        $extra_note_class = "alert-warning text-dark";
        break;

    case 'hep':
        $form_title = "BORANG HEP";
        $form_subtitle = "Pembatalan Pinjaman & Bantuan Kewangan";
        $form_icon = "fa-hand-holding-usd";
        $theme_color_text = "text-primary";
        $theme_color_btn = "btn-primary";
        $form_name_db = "Borang HEP (Pengesahan Pinjaman/Bantuan)";
        $extra_note = "Borang ini hanya untuk pelajar sekiranya pernah menjadi Komander Kesatria atau Ko Kurikulum lain yang berpakaian seragam sahaja.";
        $extra_note_class = "alert-info";
        break;

    case 'dekan':
    default:
        $form_title = "BORANG DEKAN";
        $form_subtitle = "Permohonan Menarik Diri Dari Pengajian";
        $form_icon = "fa-file-signature";
        $theme_color_text = "text-primary";
        $theme_color_btn = "btn-primary";
        $form_name_db = "Borang Dekan (Permohonan Menarik Diri)";
        break;
}

include 'includes/withdrawal_form_template.php';
?>