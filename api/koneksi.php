<?php
// Dipakai semua endpoint: koneksi MySQLi + fungsi bantu JSON.
mysqli_report(MYSQLI_REPORT_OFF);
ini_set('display_errors','0');
date_default_timezone_set('Asia/Jakarta');
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if(($_SERVER['REQUEST_METHOD']??'')==='OPTIONS'){http_response_code(204);exit;}

function kirim($kode,$arr){http_response_code($kode);echo json_encode($arr,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);exit;}
function sukses($data,$pesan='OK',$kode=200){kirim($kode,['status'=>'success','message'=>$pesan,'data'=>$data]);}
function gagal($kode,$pesan,$extra=[]){kirim($kode,['status'=>'error','message'=>$pesan]+$extra);}
function wajib_metode(array $ok){
  if(!in_array($_SERVER['REQUEST_METHOD'],$ok,true)){header('Allow: '.implode(', ',$ok));
    gagal(405,'Metode '.$_SERVER['REQUEST_METHOD'].' tidak diizinkan. Gunakan: '.implode(', ',$ok));}}

$f=__DIR__.'/../koneksi.local.php';
if(!file_exists($f))gagal(500,'File koneksi.local.php tidak ditemukan di folder utama project.');
$cfg=require $f;
$db=@mysqli_connect($cfg['host'],$cfg['user'],$cfg['pass'],$cfg['name']);
if(!$db)gagal(500,'Koneksi database gagal. Pastikan MySQL di XAMPP aktif, database sudah di-import, dan koneksi.local.php benar.');
mysqli_set_charset($db,'utf8mb4');

function q($sql,$t='',$p=[]){global $db;
  $st=mysqli_prepare($db,$sql);if(!$st)gagal(500,'Query gagal. Pastikan database marketplace sudah di-import.');
  if($p)mysqli_stmt_bind_param($st,$t,...$p);
  if(!mysqli_stmt_execute($st))gagal(500,'Query gagal dijalankan.');
  return mysqli_stmt_get_result($st);}
function ex($sql,$t,$p){global $db;
  $st=mysqli_prepare($db,$sql);if(!$st)return false;
  mysqli_stmt_bind_param($st,$t,...$p);
  return mysqli_stmt_execute($st)?(mysqli_stmt_insert_id($st)?:true):false;}
function all($r){return $r?mysqli_fetch_all($r,MYSQLI_ASSOC):[];}
function one($r){return $r?mysqli_fetch_assoc($r):null;}
function id_valid($v){return is_scalar($v)&&ctype_digit((string)$v)&&(int)$v>0;}

function url_gambar($f){
  if(!$f)return null;
  $skema=(!empty($_SERVER['HTTPS'])&&$_SERVER['HTTPS']!=='off')?'https':'http';
  $dir=rtrim(str_replace('\\','/',dirname(dirname($_SERVER['SCRIPT_NAME']))),'/');
  return "$skema://{$_SERVER['HTTP_HOST']}$dir/uploads/$f";}
function fmt_produk($p){return [
  'id_produk'=>(int)$p['id_produk'],'nama'=>$p['nama'],'deskripsi'=>$p['deskripsi'],
  'harga'=>(int)$p['harga'],'stok'=>(int)$p['stok'],'emoji'=>$p['emoji'],'gambar_url'=>url_gambar($p['gambar']),
  'kategori'=>['id_kategori'=>(int)$p['id_kategori'],'nama_kategori'=>$p['nama_kategori']],
  'penjual'=>['id_penjual'=>(int)$p['id_penjual'],'nama_toko'=>$p['nama_toko']]];}
