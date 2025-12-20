$files = @(
  'public\reset_password.php',
  'public\views\dosen\profile.php',
  'public\views\dosen\lihat_progres.php',
  'public\views\dosen\lihat_mahasiswa.php',
  'public\views\dosen\dosen-connect-google.php',
  'public\views\dosen\edit_tugas.php',
  'public\views\mahasiswa\view_attachment.php',
  'public\views\mahasiswa\dashboard_mahasiswa.php',
  'public\views\mahasiswa\daftar_tugas.php',
  'public\views\mahasiswa\profile.php',
  'public\views\dosen\daftar_tugas.php',
  'public\views\layouts\sidebar_admin.php',
  'public\views\layouts\sidebar_dosen.php',
  'public\views\dosen\buat_tugas.php',
  'public\views\admin\user\user_edit.php',
  'public\views\admin\user\index.php',
  'public\views\admin\prodi\prodi_edit.php',
  'public\views\admin\user\user_add.php',
  'public\views\admin\kelas\kelas_edit.php',
  'public\cron\send_h1_reminders.php',
  'public\api\sync_calendar.php'
)

foreach ($file in $files) {
    $path = "C:\laragon\www\FinalProject\$file"
    if (Test-Path $path) {
        $content = Get-Content $path -Raw
        # Remove conflict markers, keeping HEAD version
        $content = $content -replace '(?s)<<<<<<< HEAD\r?\n(.*?)\r?\n=======\r?\n.*?\r?\n>>>>>>> [a-f0-9]+\r?\n', '$1'
        Set-Content $path $content -NoNewline
        Write-Host "Fixed: $file"
    } else {
        Write-Host "Not found: $file"
    }
}

Write-Host "`nAll conflicts resolved!"
