<?php

require "../lib/auth.php";
global $tempdir;

/* based on jQuery File Upload Plugin PHP Example 4.2.4
 *           https://github.com/blueimp/jQuery-File-Upload
 *           Copyright 2010, Sebastian Tschan
 *           https://blueimp.net
 *
 * Upload für Wahl2011
 * (C) 2011, Michael Braun <michael-dev@fami-braun.de>
 *
 * Licensed under the MIT license:
 * http://creativecommons.org/licenses/MIT/
 */

error_reporting(E_ALL | E_STRICT);

$options = array(
    'upload_dir' => $tempdir,
    'field_name' => 'file'
);

class UploadHandler
{
    private $upload_dir;
    private $field_name;

    function __construct($options) {
        $this->upload_dir = $options['upload_dir'];
        $this->field_name = $options['field_name'];
    }

    private function processUploadedFile($file_name) {
        $file_path = $this->upload_dir.$file_name;
        $content = file_get_contents($file_path);
        $newcontent = iconv("ISO-8859-15", "UTF-8//TRANSLIT", $content);
        file_put_contents($file_path, $newcontent);

 	execStm(newStm("BEGIN;"), Array());

        dblog("importfile");
        $sth = newStm("INSERT INTO stimmberechtigt (mtknr,  namenszusatz, vorname, nachname,  geburtsdatum,  fakultaet,  studiengang,  freigegeben) VALUES (:mtknr, :namenszusatz, :vorname, :nachname, :geburtsdatum, :fakultaet, :studiengang, 1);");

        $fn = fopen($file_path,"r");
        $row=0;
        while (($data = fgetcsv($fn, 1000, ";")) !== FALSE) {
          $num = count($data);
          $row++;
          if ($row == 1) continue;  // skip header
          execStm($sth,Array(
               ":mtknr" => trim($data[0]),
               ":namenszusatz" => trim($data[1]),
               ":vorname" => trim($data[3]),
               ":nachname" => trim($data[2]),
               ":geburtsdatum" => trim($data[4]),
               ":fakultaet" => trim($data[5]),
               ":studiengang" => trim($data[6])),join(",", $data));
        }

	execStm(newStm("COMMIT;"), Array());

        fclose($fn);
        @unlink($file_path);
    }

    private function handle_file_upload($uploaded_file, $name, $size, $type, $error) {
        $file = new stdClass();
        $file->name = basename(stripslashes($name));
        $file->size = intval($size);
        $file->type = $type;
        if (!$error && $file->name) {
            if ($file->name[0] === '.') {
                $file->name = substr($file->name, 1);
            }
            $file_path = $this->upload_dir.$file->name;
            $append_file = is_file($file_path) && $file->size > filesize($file_path);
            clearstatcache();
            if ($uploaded_file && is_uploaded_file($uploaded_file)) {
                // multipart/formdata uploads (POST method uploads)
                if ($append_file) {
                    file_put_contents(
                        $file_path,
                        fopen($uploaded_file, 'r'),
                        FILE_APPEND
                    );
                } else {
                    move_uploaded_file($uploaded_file, $file_path);
                }
            } else {
                // Non-multipart uploads (PUT method support)
                file_put_contents(
                    $file_path,
                    fopen('php://input', 'r'),
                    $append_file ? FILE_APPEND : 0
                );
            }
            $file_size = filesize($file_path);
            if ($file_size === $file->size) {
                $this->processUploadedFile($file->name);
            }
            $file->size = $file_size;
        } else {
            $file->error = $error;
        }
        return $file;
    }

    public function post() {
        $upload = isset($_FILES[$this->field_name]) ?
            $_FILES[$this->field_name] : array(
                'tmp_name' => null,
                'name' => null,
                'size' => null,
                'type' => null,
                'error' => null
            );
        if (is_array($upload['tmp_name']) && count($upload['tmp_name']) > 1) {
            $info = array();
            foreach ($upload['tmp_name'] as $index => $value) {
                $info[] = $this->handle_file_upload(
                    $upload['tmp_name'][$index],
                    $upload['name'][$index],
                    $upload['size'][$index],
                    $upload['type'][$index],
                    $upload['error'][$index]
                );
            }
        } else {
            if (is_array($upload['tmp_name'])) {
                $upload = array(
                    'tmp_name' => $upload['tmp_name'][0],
                    'name' => $upload['name'][0],
                    'size' => $upload['size'][0],
                    'type' => $upload['type'][0],
                    'error' => $upload['error'][0]
                );
            }
            $info = $this->handle_file_upload(
                $upload['tmp_name'],
                isset($_SERVER['HTTP_X_FILE_NAME']) ?
                    $_SERVER['HTTP_X_FILE_NAME'] : $upload['name'],
                isset($_SERVER['HTTP_X_FILE_SIZE']) ?
                    $_SERVER['HTTP_X_FILE_SIZE'] : $upload['size'],
                isset($_SERVER['HTTP_X_FILE_TYPE']) ?
                    $_SERVER['HTTP_X_FILE_TYPE'] : $upload['type'],
                $upload['error']
            );
        }
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
            header('Content-type: application/json');
        } else {
            header('Content-type: text/plain');
        }
        echo json_encode($info);
    }

}

$upload_handler = new UploadHandler($options);

switch ($_SERVER['REQUEST_METHOD']) {
    case 'POST':
        $upload_handler->post();
        break;
    default:
        header('HTTP/1.0 405 Method Not Allowed');
}
?>
