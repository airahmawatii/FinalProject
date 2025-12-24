<?php
require_once __DIR__ . "/../models/UserModel.php";

class UserController
{
    private $model;

    public function __construct($pdo)
    {
        $this->model = new UserModel($pdo);
    }


    // 1. LIST USER

    public function index()
    {
        $users = $this->model->getAll();

        include __DIR__ . "/../../public/views/admin/user/index.php";
    }



    // 2. ADD USER

    public function create()
    {

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nama = $_POST['nama'];
            $email = $_POST['email'];
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $role = $_POST['role'];

            $this->model->create($nama, $email, $password, $role);
            header("Location: users.php");
            exit;
        }

        include __DIR__ . "/../../public/views/admin/user/user_add.php";
    }

    // -------------------------
    // 3. EDIT USER

    public function edit($id)
    {
        $user = $this->model->findById($id);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nama = $_POST['nama'];
            $email = $_POST['email'];
            $role = $_POST['role'];

            $this->model->update($id, $nama, $email, $role);

            header("Location: users.php");
            exit;
        }

        include __DIR__ . "/../../public/views/admin/user/user_edit.php";
    }

    // 4. DELETE USER

    public function delete($id)
    {
        $this->model->delete($id);
        header("Location: users.php");
        exit;
    }
}
