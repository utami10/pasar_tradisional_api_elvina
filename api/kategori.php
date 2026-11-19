<?php require __DIR__.'/koneksi.php'; wajib_metode(['GET']);
$sql="SELECT k.*,(SELECT COUNT(*) FROM produk p WHERE p.id_kategori=k.id_kategori) jumlah_produk FROM kategori k";
$fmt=fn($k)=>['id_kategori'=>(int)$k['id_kategori'],'nama_kategori'=>$k['nama_kategori'],'jumlah_produk'=>(int)$k['jumlah_produk']];
if(isset($_GET['id'])){
  if(!id_valid($_GET['id']))gagal(400,'Parameter id harus berupa angka bulat positif.');
  $k=one(q($sql.' WHERE k.id_kategori=?','i',[(int)$_GET['id']]));
  if(!$k)gagal(404,'Kategori dengan id '.(int)$_GET['id'].' tidak ditemukan.');
  sukses($fmt($k),'Detail kategori ditemukan');
}
$rows=all(q($sql.' ORDER BY k.id_kategori'));
sukses(['jumlah'=>count($rows),'kategori'=>array_map($fmt,$rows)],'Daftar kategori berhasil diambil');
