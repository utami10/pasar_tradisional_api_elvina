<?php require __DIR__.'/koneksi.php'; wajib_metode(['GET']);
$sql="SELECT p.*,k.nama_kategori,t.nama_toko FROM produk p JOIN kategori k USING(id_kategori) JOIN penjual t USING(id_penjual)";

if(isset($_GET['id'])){                                   // GET /api/produk.php?id=1
  if(!id_valid($_GET['id']))gagal(400,'Parameter id harus berupa angka bulat positif.');
  $p=one(q($sql.' WHERE p.id_produk=?','i',[(int)$_GET['id']]));
  if(!$p)gagal(404,'Produk dengan id '.(int)$_GET['id'].' tidak ditemukan.');
  sukses(fmt_produk($p),'Detail produk ditemukan');
}

$w=[];$t='';$par=[];                                      // GET /api/produk.php
if(isset($_GET['kategori_id'])){
  if(!id_valid($_GET['kategori_id']))gagal(400,'Parameter kategori_id harus berupa angka bulat positif.');
  $w[]='p.id_kategori=?';$t.='i';$par[]=(int)$_GET['kategori_id'];}
if(isset($_GET['cari'])&&trim($_GET['cari'])!==''){
  $w[]='p.nama LIKE ?';$t.='s';$par[]='%'.trim($_GET['cari']).'%';}
$rows=all(q($sql.($w?' WHERE '.implode(' AND ',$w):'').' ORDER BY p.id_produk',$t,$par));
sukses(['jumlah'=>count($rows),'produk'=>array_map('fmt_produk',$rows)],'Daftar produk berhasil diambil');
