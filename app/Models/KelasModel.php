<?php
class KelasModel
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function getAll()
    {
        $sql = "SELECT k.id_kelas, k.nama_kelas, p.nama_prodi, a.tahun
            FROM class k
            JOIN prodi p ON k.prodi_id = p.id_prodi
            JOIN angkatan a ON k.angkatan_id = a.id_angkatan
            ORDER BY p.nama_prodi, a.tahun, k.nama_kelas";
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }


    public function create($nama_kelas, $prodi_id, $angkatan_id)
    {
        $stmt = $this->pdo->prepare("INSERT INTO class (nama_kelas, prodi_id, angkatan_id) VALUES (?, ?, ?)");
        return $stmt->execute([$nama_kelas, $prodi_id, $angkatan_id]);
    }

    public function find($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM class WHERE id_kelas = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function update($id, $nama_kelas, $prodi_id, $angkatan_id)
    {
        $stmt = $this->pdo->prepare("UPDATE class SET nama_kelas=?, prodi_id=?, angkatan_id=? WHERE id_kelas=?");
        return $stmt->execute([$nama_kelas, $prodi_id, $angkatan_id, $id]);
    }

    public function delete($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM class WHERE id_kelas=?");
        return $stmt->execute([$id]);
    }
}
