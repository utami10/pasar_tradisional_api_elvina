-- Database untuk XAMPP (localhost). Import lewat phpMyAdmin -> tab Import.
CREATE DATABASE IF NOT EXISTS pasar_tradisional CHARACTER SET utf8mb4;
USE pasar_tradisional;
SET FOREIGN_KEY_CHECKS=0;DROP TABLE IF EXISTS detail_pesanan,pesanan,produk,kategori,penjual,admin;SET FOREIGN_KEY_CHECKS=1;
SET NAMES utf8mb4;
CREATE TABLE admin(id_admin INT AUTO_INCREMENT PRIMARY KEY,username VARCHAR(30) UNIQUE NOT NULL,password CHAR(64) NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE penjual(id_penjual INT AUTO_INCREMENT PRIMARY KEY,nama_toko VARCHAR(60) NOT NULL,kontak VARCHAR(20)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE kategori(id_kategori INT AUTO_INCREMENT PRIMARY KEY,nama_kategori VARCHAR(40) NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE produk(id_produk INT AUTO_INCREMENT PRIMARY KEY,id_penjual INT NOT NULL,id_kategori INT NOT NULL,nama VARCHAR(80) NOT NULL,deskripsi TEXT,harga INT NOT NULL,stok INT NOT NULL DEFAULT 0,emoji VARCHAR(8) DEFAULT '🎀',gambar VARCHAR(100),
 FOREIGN KEY(id_penjual) REFERENCES penjual(id_penjual),FOREIGN KEY(id_kategori) REFERENCES kategori(id_kategori)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE pesanan(id_pesanan INT AUTO_INCREMENT PRIMARY KEY,kode VARCHAR(20) UNIQUE NOT NULL,nama_pembeli VARCHAR(60) NOT NULL,no_hp VARCHAR(20) NOT NULL,alamat TEXT NOT NULL,total INT NOT NULL,
 status ENUM('Menunggu Konfirmasi','Dikonfirmasi','Dikemas','Dikirim','Selesai','Dibatalkan') DEFAULT 'Menunggu Konfirmasi',tanggal TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE detail_pesanan(id_detail INT AUTO_INCREMENT PRIMARY KEY,id_pesanan INT NOT NULL,id_produk INT NOT NULL,jumlah INT NOT NULL,harga INT NOT NULL,
 FOREIGN KEY(id_pesanan) REFERENCES pesanan(id_pesanan) ON DELETE CASCADE,FOREIGN KEY(id_produk) REFERENCES produk(id_produk));
INSERT INTO admin(username,password) VALUES('admin',SHA2('admin123',256));
INSERT INTO penjual(nama_toko,kontak) VALUES('Toko Bunga Melati','0812000111'),('Dapur Nenek','0812000222');
INSERT INTO kategori(nama_kategori) VALUES('Makanan'),('Kerajinan'),('Fashion');
INSERT INTO produk(id_penjual,id_kategori,nama,deskripsi,harga,stok,emoji) VALUES
(2,1,'Kue Putu Ayu','Kue tradisional lembut dengan kelapa parut. Isi 10 pcs.',15000,40,'🍰'),
(2,1,'Klepon Manis','Bola ketan isi gula merah, meletus di mulut!',12000,50,'🍡'),
(2,1,'Keripik Singkong','Renyah gurih, cocok teman ngemil.',10000,60,'🥔'),
(1,2,'Tas Anyaman Mini','Tas anyaman tangan warna pastel.',45000,15,'👜'),
(1,2,'Boneka Rajut','Boneka rajut kecil lucu buatan tangan.',35000,20,'🧸'),
(1,3,'Batik Scrunchie','Ikat rambut kain batik motif imut.',8000,80,'🎀'),
(1,3,'Kain Tenun Mini','Tenun tradisional ukuran syal kecil.',60000,10,'🧣'),
(2,1,'Teh Melati Kemasan','Teh melati wangi, 25 kantong.',18000,35,'🍵');
