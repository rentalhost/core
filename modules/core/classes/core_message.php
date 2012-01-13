<?php

	// Classe para gerenciamento de mensagens
	class core_message implements Iterator {
		// Tipos de mensagens
		const	TYPE_OK			= 1;
		const	TYPE_INFO		= 2;
		const	TYPE_ERROR		= 4;
		const	TYPE_WARNING	= 8;
		const	TYPE_EXCEPTION	= 16;
		const	TYPE_ALL		= 31;

		// Armazena as mensagens
		private $_messages = array();
		// Armazena o tipo padrão das mensagens
		private $_default_type = null;

		// Cria um objeto de mensagem
		public function __construct($default_type = self::TYPE_INFO) {
			$this->_default_type = $default_type === null ? self::TYPE_INFO : $default_type;
		}

  		// Anexa todo conteúdo de um core_message dentro desta
  		public function append(core_message $messages){
			$this->_messages = array_merge($this->_messages, $messages->_messages);
			return $this;
  		}

		// Adiciona uma nova mensagem
		public function push($message, $type = null, $code = null) {
			$container = new stdclass;
			$container->message = $message;
			$container->type = $type === null ? $this->_default_type : $type;
			$container->code = $code;

			$this->_messages[] = $container;
		}

  		// Conta quantas mensagens existem de um tipo específico
  		public function count($type_filter = self::TYPE_ALL){
			if(($type_filter & self::TYPE_ALL) === self::TYPE_ALL)
				return count($this->_messages);

			$count = 0;

			foreach($this->_messages as $message)
			if(($message->type & $type_filter) === $type_filter)
				$count++;

			return $count;
  		}

  		// Retorna true se um erro específico for encontrado
  		public function has($type_filter){
			foreach($this->_messages as $message)
			if(($message->type & $type_filter) === $type_filter)
				return true;

			return false;
  		}

  		// Obtém uma cópia das mensagens diretamente como array
  		public function get_messages($type_filter = null) {
  			if($type_filter === null)
  				return $this->_messages;

			$messages_copy = array();
			foreach($this->_messages as $message)
			if(($message->type & $type_filter) === $type_filter)
				$messages_copy[] = $message;

			return $messages_copy;
  		}

  		/** ITERATOR */
  		public function current() { return current($this->_messages); }
  		public function key() { return key($this->_messages); }
  		public function next() { return next($this->_messages); }
  		public function rewind() { return reset($this->_messages); }
  		public function valid() { return key($this->_messages) !== null; }
	}
