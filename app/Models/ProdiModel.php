<?php
class ProdiModel {

    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Ambil semua prodi
    public function getAll() {
        return $this->pdo->query("SELECT * FROM prodi ORDER BY nama_prodi")->fetchAll(PDO::FETCH_ASSOC);
    }

    // Tambah prodi baru
    public function create($kode_prodi, $nama_prodi) {
        $stmt = $this->pdo->prepare("INSERT INTO prodi (kode_prodi, nama_prodi) VALUES (?, ?)");
        return $stmt->execute([$kode_prodi, $nama_prodi]);
    }

    // Cari prodi berdasarkan id_prodi
    public function find($id_prodi) {
        $stmt = $this->pdo->prepare("SELECT * FROM prodi WHERE id_prodi=?");
        $stmt->execute([$id_prodi]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Update prodi berdasarkan id_prodi
    public function update($id_prodi, $kode_prodi, $nama_prodi) {
        $stmt = $this->pdo->prepare("UPDATE prodi SET kode_prodi=?, nama_prodi=? WHERE id_prodi=?");
        return $stmt->execute([$kode_prodi, $nama_prodi, $id_prodi]);
    }

    // Hapus prodi berdasarkan id_prodi
    public function delete($id_prodi) {
        $stmt = $this->pdo->prepare("DELETE FROM prodi WHERE id_prodi=?");
        return $stmt->execute([$id_prodi]);
    }
}
?>
