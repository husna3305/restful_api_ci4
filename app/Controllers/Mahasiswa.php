<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\MahasiswaModel;
use CodeIgniter\Files\File;

class Mahasiswa extends ResourceController
{
	use ResponseTrait;
	/**
	 * Return an array of resource objects, themselves in array format
	 *
	 * @return mixed
	 */

	public function index()
	{
		$model = new MahasiswaModel();
		$data = $model->findAll();
		return $this->respond($data, 200);
	}

	public function show($id = null)
	{
		$model = new MahasiswaModel();
		$data = $model->getWhere(['nim' => $id])->getRow();
		if ($data) {
			return $this->respond($data);
		} else {
			return $this->failNotFound('Data tidak ditemukan. ID : ' . $id);
		}
	}

	public function create()
	{
		$model = new MahasiswaModel();

		// Cek apakah nim sudah ada
		$nim = $this->request->getPost('nim');
		$cek = $model->getWhere(['nim' => $nim])->getRow();
		if ($cek) {
			return $this->fail('NIM : ' . $nim . ' sudah ada dengan Nama : ' . $cek->nama_mahasiswa);
		}

		$data = [
			'nim' => $this->request->getPost('nim'),
			'nama_mahasiswa' => $this->request->getPost('nama_mahasiswa'),
			'jenis_kelamin' => $this->request->getPost('jenis_kelamin'),
			'program_studi' => $this->request->getPost('program_studi'),
			'alamat' => $this->request->getPost('alamat'),
		];
		$model->insert($data);
		$response = [
			'status'   => 200,
			'error'    => null,
			'messages' => [
				'success' => 'Data Berhasil Disimpan'
			]
		];

		return $this->respondCreated($response, 201);
	}

	public function update($id = null)
	{
		$model = new MahasiswaModel();
		$input = $this->request->getRawInput();

		if (isset($input['nim'])) {
			$data = [
				'nim'               => $input['nim'],
				'nama_mahasiswa'    => $input['nama_mahasiswa'],
				'jenis_kelamin'     => $input['jenis_kelamin'],
				'program_studi'     => $input['program_studi'],
				'alamat'            => $input['alamat'],
				'updated_at'        => date("Y-m-d H:i:s")
			];
			$model->update($id, $data);
			$response = [
				'status'   => 200,
				'error'    => null,
				'messages' => [
					'success' => 'Data mahasiswa berhasil diubah.'
				]
			];
		} else {
			$validationRule = [
				'foto' => [
					'label' => 'Foto',
					'rules' => 'uploaded[foto]'
						. '|is_image[foto]'
						. '|mime_in[foto,image/jpg,image/jpeg,image/gif,image/png,image/webp]'
						. '|max_size[foto,2048]'
						. '|max_dims[foto,1024,768]',
					'errors' => [
						'uploaded'    => 'Silahkan pilih file yang akan di upload terlebih dahulu',
						'mime_in'     => 'File Extention Harus Berupa jpg,jpeg,gif,png,webp',
						'max_size'    => 'Ukuran File Maksimal 2 MB'
					]
				],
			];
			if (!$this->validate($validationRule)) {
				$error = $this->validator->getErrors()['foto'];
				return $this->failNotFound($error);
			} else {
				$foto = $this->request->getFile('foto');
				$fileName = $foto->getRandomName();
				$foto->move('uploads/foto/', $fileName); //move file upload
				if ($foto->hasMoved()) {
					$data = ['foto' => "uploads/foto/$fileName"];
					$model->update($id, $data);
					$response = [
						'status'   => 200,
						'error'    => null,
						'messages' => [
							'success' => 'Foto mahasiswa berhasil diunggah.'
						]
					];
				}
			}
		}
		return $this->respond($response);
	}

	public function delete($id = null)
	{
		$model = new MahasiswaModel();
		$data = $model->find($id);
		if ($data) {
			$model->delete($id);
			$response = [
				'status'   => 200,
				'error'    => null,
				'messages' => [
					'success' => 'Data Mahasiswa Berhasil Dihapus'
				]
			];

			return $this->respondDeleted($response);
		} else {
			return $this->failNotFound('Data tidak ditemukan. ID : ' . $id);
		}
	}
}
