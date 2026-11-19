<?php require __DIR__.'/koneksi.php'; wajib_metode(['GET']);
sukses([
 'nama'=>'Toko Mungil Marketplace REST API','versi'=>'1.0',
 'endpoint'=>[
  ['metode'=>'GET','url'=>'/api/produk.php','fungsi'=>'Daftar semua produk (filter: ?kategori_id=, ?cari=)'],
  ['metode'=>'GET','url'=>'/api/produk.php?id=1','fungsi'=>'Detail satu produk'],
  ['metode'=>'GET','url'=>'/api/kategori.php','fungsi'=>'Daftar kategori'],
  ['metode'=>'POST','url'=>'/api/pesanan.php','fungsi'=>'Membuat pesanan baru (body JSON)'],
  ['metode'=>'GET','url'=>'/api/pesanan.php?kode=...&no_hp=...','fungsi'=>'Cek status pesanan'],
 ]],'API berjalan');
