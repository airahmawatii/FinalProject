<?php
class AngkatanModel {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getAll() {
        return $this->pdo->query("SELECT * FROM angkatan ORDER BY tahun DESC")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($tahun) {
        $stmt = $this->pdo->prepare("INSERT INTO angkatan (tahun) VALUES (?)");
        return $stmt->execute([$tahun]);
    }

    public function find($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM angkatan WHERE id_angkatan=?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function update($id, $tahun) {
        $stmt = $this->pdo->prepare("UPDATE angkatan SET tahun=? WHERE id_angkatan=?");
        return $stmt->execute([$tahun, $id]);
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM angkatan WHERE id_angkatan=?");
        return $stmt->execute([$id]);
    }
}
?>
