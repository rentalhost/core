<?php

	// Gerencia operações de coverage
	class test_coverage_controller extends core_controller {
		const   VALID_MD5 = '/^[a-fA-F0-9]{32}$/';

		// Ignora uma linha
		public function ignore_line() {
			$this->set_return_type('json');

			// É necessário um MD5 válido
			if(preg_match(self::VALID_MD5, $_POST['file']) === 0)
				return false;

			// Carrega o arquivo, se existir
			$file = core::get_current_path() . "/files/{$_POST['file']}.lines";
			$data = is_file($file)
				  ? json_decode(file_get_contents($file), true)
				  : array();

			// Adiciona a informação no arquivo
			$data["{$_POST['line']}.{$_POST['content']}"] = true;

			// Salva o arquivo
			file_put_contents($file, json_encode($data));
			return true;
		}

		// Recupera uma linha
		public function recovery_line() {
			$this->set_return_type('json');

			// É necessário um MD5 válido
			if(preg_match(self::VALID_MD5, $_POST['file']) === 0)
				return false;

			// Carrega o arquivo, se existir
			$file = core::get_current_path() . "/files/{$_POST['file']}.lines";
			$data = is_file($file)
				  ? json_decode(file_get_contents($file), true)
				  : array();

			// Adiciona a informação no arquivo
			unset($data["{$_POST['line']}.{$_POST['content']}"]);

			// Salva o arquivo
			file_put_contents($file, json_encode($data));
			return true;
		}
	}
