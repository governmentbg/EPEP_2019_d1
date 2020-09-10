<?php

namespace site\data;

use vakata\database\DBInterface;
use vakata\files\FileStorageInterface;
use site\ErrorException;
use Zend\Diactoros\UploadedFile;

class ContactsService
{
    protected $db;
    protected $files;

    public function __construct(DBInterface $db, FileStorageInterface $files)
    {
        $this->db = $db;
        $this->files = $files;
    }
    public function departments(array $depts): array
    {
        $departments = $this->db->departments()->filter('site', SITE);
        if (count($depts)) {
            $departments->filter('department', $depts);
            $order = array_map(function ($item) {
                return 'department = ' . $item . ' DESC';
            }, $depts);
            $departments->order(implode(', ', $order));
        } else {
            return [];
        }
        $departments = $departments->select();

        foreach ($departments as $key => $department) {
            $rows = explode("\n", trim($department['hours']));
            $department['hours'] = [];
            foreach ($rows as $row) {
                if (strlen(trim($row))) {
                    $department['hours'][] = array_map('trim', explode('|', $row, 2));
                }
            }
            $department['ord'] = json_decode($department['ord'], true) ?? [];
            $departments[$key] = (object) $department;
        }

        return $departments;
    }
    protected function normalize(array $item)
    {
        $item = (object) $item;
        if ((int) $item->image) {
            try {
                $image = $this->files->get((int) $item->image);
                $item->image = $image['id'] . '/' . $image['name'];
            } catch (\Exception $e) {
                $item->image = null;
            }
        } else {
            $item->image = null;
        }
        return $item;
    }
    public function employees(array $departments): array
    {
        $depts = array_map(function ($item) {
            return $item->department;
        }, $departments);
        $query = $this->db->employees()->filter('site', SITE)->order('name');
        if (count($depts)) {
            $query->filter('department', $depts);
        } else {
            return [];
        }
        $temp = array_map(function ($item) {
            return $this->normalize($item);
        }, $query->select());
        $data = [];
        $removed = [];

        foreach ($temp as $employee) {
            if (!isset($data[$employee->department])) {
                $data[$employee->department] = [];
            }
            $data[$employee->department][$employee->employee] = $employee;
        }
        foreach ($departments as $department) {
            $real = [];
            if (isset($data[$department->department])) {
                foreach ($department->ord as $order) {
                    if (isset($data[$department->department][$order['employee']])) {
                        $real[] = $data[$department->department][$order['employee']];
                        unset($data[$department->department][$order['employee']]);
                    }
                }
                $data[$department->department] = array_merge($real, $data[$department->department]);
            }
        }
        $employees = [];

        foreach ($data as $department => $emps) {
            for ($i = 0; $i < count($emps); $i += 2) {
                $employees[$department][] = array_slice($emps, $i, 2);
            }
        }

        return $employees;
    }
    public function signal(array $data, UploadedFile $file)
    {
        $fields = [ 'name' => 255, 'phone' => 20, 'mail' => 255, 'consent' => null, 'message' => 65535, 'lang' => null ];
        $temp = [];
        $errors = [];

        foreach ($fields as $field => $length) {
            if (!isset($data[$field]) || !strlen(trim($data[$field]))) {
                $errors[] = 'contactform.errors.' . $field;
                continue;
            }
            if ($field == 'mail' && !filter_var($data[$field], FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'contactform.errors.mail';
            }
            if ($field == 'consent') {
                continue;
            }
            if ($field == 'message' && strlen($data[$field]) < 50) {
                $errors[] = 'contactform.errors.message.tooshort';
            }
            if ($field === 'name' && !preg_match('/^[а-яa-z .\-]+$/iu', $data[$field])) {
                $errors[] = 'contactform.errors.name.onlyletters';
            }
            if ($field === 'phone' && !preg_match('/^\+?[0-9]+$/', $data[$field])) {
                $errors[] = 'contactform.errors.phone.invalid';
            }
            if ($length && strlen($data[$field]) > $length) {
                $errors[] = 'contactform.errors.' . $field . '.toolong';
            }
            $temp[$field] = $data[$field];
        }
        if (isset($data['addr']) && (!isset($data['address']) || !strlen($data['address']))) {
            $errors[] = 'contactform.errors.address';
        }
        $fileError = $file->getError();
        if ($fileError !== UPLOAD_ERR_NO_FILE) {
            if ($fileError !== UPLOAD_ERR_OK) {
                $errors[] = 'contactform.errors.file.error';
            }
            if ($file->getSize() > MAX_FILE_SIZE) {
                $errors[] = 'contactform.errors.file.size';
            }
            $name = $file->getClientFilename();
            $ext = explode('.', $name);
            $ext = end($ext);
            if (!in_array(strtolower($ext), ['txt','png','jpg','gif','jpeg','doc','docx','xls','xlsx','csv','pdf'])) {
                $errors[] = 'contactform.errors.file.extention';
            }
            if (!count($errors)) {
                do {
                    $newName = time() . '_' . md5($name);
                } while (is_file(STORAGE_PUBLIC . '/' . $newName));

                $file->moveTo(STORAGE_PUBLIC . '/' . $newName);
                $temp = array_merge($temp, [ 'file' => $newName, 'filename' => $name ]);
            }
        }

        if (count($errors)) {
            throw (new ErrorException())->setErrors($errors);
        }
        $temp['_created'] = date('Y-m-d H:i:s');
        $temp['site'] = SITE;

        return $this->db
            ->query('INSERT INTO signals (' . implode(', ', array_keys($temp)) . ') VALUES (??)', $temp)
            ->insertID();
    }
    public function validateSignal(array $data)
    {
        if (!isset($data['signal']) || !(int) $data['signal']) {
            throw new ErrorException('Invalid signal');
        }
        if (!isset($data['mail']) || !strlen($data['mail'])) {
            throw new ErrorException('Invalid mail');
        }
        if ($this->db->one(
            'SELECT validated FROM signals WHERE sgnl = ? AND mail = ?',
            [ (int) $data['signal'], $data['mail'] ]
        )) {
            throw new ErrorException('Already validated');
        }
        $affected = $this->db->query('UPDATE signals SET validated = ? WHERE sgnl = ? AND mail = ?', [
            1,
            (int) $data['signal'],
            $data['mail']
        ])->affected();

        if (!$affected) {
            throw new ErrorException('Invalid data');
        }
    }
}
