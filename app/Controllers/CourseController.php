<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    die("Akses ditolak.");
}

require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../Models/CourseModel.php";

class CourseController
{

    private $model;
    private $pdo;

    public function __construct()
    {
        $db = new Database();
        $this->pdo = $db->connect();
        $this->model = new CourseModel($this->pdo);
    }

    // ================================
    // 1. INDEX (Daftar Mata Kuliah)
    // ================================
    public function index()
    {
        $courses = $this->model->getAll();
        include __DIR__ . "/../../views/courses/index.php";
    }

    // ================================
    // 2. TAMBAH MATA KULIAH
    // ================================
    public function add()
    {
        // Ambil daftar dosen
        $users = $this->pdo->query("SELECT id, nama FROM users WHERE role='dosen'")
            ->fetchAll(PDO::FETCH_ASSOC);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            // Simpan course
            $this->model->create($_POST['name'], $_POST['semester']);

            // Ambil ID matkul terakhir
            $matkul_id = $this->pdo->lastInsertId();

            // Simpan dosen pengajar ke pivot
            $stmt = $this->pdo->prepare("INSERT INTO dosen_courses (dosen_id, matkul_id) VALUES (?, ?)");
            $stmt->execute([$_POST['dosen_id'], $matkul_id]);

            header("Location: index.php");
            exit;
        }

        include __DIR__ . "/../../views/courses/add.php";
    }

    // ================================
    // 3. EDIT MATA KULIAH
    // ================================
    public function edit()
    {
        $id = $_GET['id'];
        $data = $this->model->find($id);

        // daftar dosen
        $users = $this->pdo->query("SELECT id, nama FROM users WHERE role='dosen'")
            ->fetchAll(PDO::FETCH_ASSOC);

        // dosen saat ini
        $stmt = $this->pdo->prepare("SELECT dosen_id FROM dosen_courses WHERE matkul_id=?");
        $stmt->execute([$id]);
        $current_dosen = $stmt->fetchColumn();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            // update MK
            $this->model->update($id, $_POST['name'], $_POST['semester']);

            // update dosen
            $upd = $this->pdo->prepare("
                UPDATE dosen_courses
                SET dosen_id=?
                WHERE matkul_id=?
            ");
            $upd->execute([$_POST['dosen_id'], $id]);

            header("Location: index.php");
            exit;
        }

        include __DIR__ . "/../../views/courses/edit.php";
    }

    // ================================
    // 4. HAPUS MATA KULIAH
    // ================================
    public function delete()
    {
        $id = $_GET['id'];

        // hapus pivot dulu
        $this->pdo->prepare("DELETE FROM dosen_courses WHERE matkul_id=?")->execute([$id]);

        // hapus course
        $this->model->delete($id);

        header("Location: index.php");
        exit;
    }
}
