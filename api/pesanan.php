<?php require __DIR__.'/koneksi.php'; wajib_metode(['GET','POST']);

function fmt_pesanan($o){
  $items=all(q("SELECT d.id_produk,p.nama,d.jumlah,d.harga FROM detail_pesanan d JOIN produk p USING(id_produk) WHERE d.id_pesanan=? ORDER BY d.id_detail",'i',[$o['id_pesanan']]));
  return ['id_pesanan'=>(int)$o['id_pesanan'],'kode'=>$o['kode'],'nama_pembeli'=>$o['nama_pembeli'],'no_hp'=>$o['no_hp'],
    'alamat'=>$o['alamat'],'total'=>(int)$o['total'],'status'=>$o['status'],'tanggal'=>$o['tanggal'],
    'items'=>array_map(fn($i)=>['id_produk'=>(int)$i['id_produk'],'nama'=>$i['nama'],'jumlah'=>(int)$i['jumlah'],
       'harga'=>(int)$i['harga'],'subtotal'=>(int)$i['harga']*(int)$i['jumlah']],$items)];}

/* ---------- GET /api/pesanan.php?kode=MG...&no_hp=08... : cek status ---------- */
if($_SERVER['REQUEST_METHOD']==='GET'){
  $kode=trim($_GET['kode']??'');$hp=trim($_GET['no_hp']??'');
  if($kode===''||$hp==='')gagal(400,'Parameter kode dan no_hp wajib diisi.');
  $o=one(q("SELECT * FROM pesanan WHERE kode=? AND no_hp=?",'ss',[$kode,$hp]));
  if(!$o)gagal(404,'Pesanan tidak ditemukan. Periksa kode pesanan dan no. HP.');
  sukses(fmt_pesanan($o),'Pesanan ditemukan');
}

/* ---------- POST /api/pesanan.php : buat pesanan baru ---------- */
$raw=file_get_contents('php://input');
if(trim($raw)!==''){$in=json_decode($raw,true);
  if(!is_array($in))gagal(400,'Body bukan JSON yang valid. Kirim dengan header Content-Type: application/json.');
}else $in=$_POST;
if(!$in)gagal(400,'Body request kosong. Kirim data pesanan dalam format JSON.');

$nama=trim((string)($in['nama_pembeli']??''));$hp=trim((string)($in['no_hp']??''));$alamat=trim((string)($in['alamat']??''));
$err=[];
if($nama===''||strlen($nama)>60)$err['nama_pembeli']='Wajib diisi, maksimal 60 karakter.';
if(!preg_match('/^[0-9+]{8,15}$/',$hp))$err['no_hp']='Wajib diisi, 8-15 digit angka (boleh diawali +).';
if($alamat==='')$err['alamat']='Wajib diisi.';
$items=[];
if(!isset($in['items'])||!is_array($in['items'])||!$in['items'])$err['items']='Wajib berisi minimal satu produk: [{"id_produk":1,"jumlah":2}].';
else foreach($in['items'] as $i=>$it){
  $id=$it['id_produk']??null;$jml=$it['jumlah']??null;
  if(!id_valid($id)||!id_valid($jml)||(int)$jml>999){$err["items[$i]"]='id_produk dan jumlah harus angka bulat positif (jumlah maks 999).';continue;}
  $items[(int)$id]=($items[(int)$id]??0)+(int)$jml;}      // gabungkan produk yang sama
if($err)gagal(422,'Data pesanan tidak valid.',['errors'=>$err]);

$detail=[];$total=0;
foreach($items as $id=>$jml){
  $p=one(q("SELECT id_produk,nama,harga,stok FROM produk WHERE id_produk=?",'i',[$id]));
  if(!$p)gagal(404,"Produk dengan id $id tidak ditemukan.");
  if($p['stok']<$jml)gagal(409,"Stok produk \"{$p['nama']}\" tidak mencukupi.",['stok_tersedia'=>(int)$p['stok'],'jumlah_diminta'=>$jml]);
  $detail[]=[$id,$jml,(int)$p['harga']];$total+=$jml*(int)$p['harga'];}   // harga selalu dari database

do{$kode='MG'.date('ymd').rand(100,999);}while(one(q("SELECT 1 x FROM pesanan WHERE kode=?",'s',[$kode])));
mysqli_begin_transaction($db);
$idp=ex("INSERT INTO pesanan(kode,nama_pembeli,no_hp,alamat,total) VALUES(?,?,?,?,?)",'ssssi',[$kode,$nama,$hp,$alamat,$total]);
$ok=(bool)$idp;
if($ok)foreach($detail as [$id,$jml,$h])if(!ex("INSERT INTO detail_pesanan(id_pesanan,id_produk,jumlah,harga) VALUES(?,?,?,?)",'iiii',[$idp,$id,$jml,$h])){$ok=false;break;}
if(!$ok){mysqli_rollback($db);gagal(500,'Pesanan gagal disimpan ke database.');}
mysqli_commit($db);
$o=one(q("SELECT * FROM pesanan WHERE id_pesanan=?",'i',[$idp]));
header('Location: pesanan.php?kode='.urlencode($kode).'&no_hp='.urlencode($hp));
sukses(fmt_pesanan($o),'Pesanan berhasil dibuat dan menunggu konfirmasi admin. Simpan kode pesanan.',201);
