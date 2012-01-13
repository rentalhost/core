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

		// Conversor de tipagem
		private function _type_transform($type_data, $default_type = null) {
			if($type_data === null)
				return $default_type;

			if(is_string($type_data)
			|| is_array($type_data)) {
				$list = core::parse_setlist($type_data);
				$code = 0;

				foreach($list as $item) {
					switch($item) {
						case 'ok':			$code|= self::TYPE_OK;			break;
						case 'info':		$code|= self::TYPE_INFO;		break;
						case 'error':		$code|= self::TYPE_ERROR;		break;
						case 'warning':		$code|= self::TYPE_WARNING;		break;
						case 'exception':	$code|= self::TYPE_EXCEPTION;	break;
						case 'all':			$code = self::TYPE_ALL;			break;
						default:
							throw new core_exception("Message type \"{$item}\" unknow in \"{$type_data}\".");
					}
				}

				return $code;
			}

			return $type_data;
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
			$container->type = $this->_type_transform($type, $this->_default_type);
			$container->code = $code;

			$this->_messages[] = $container;
		}

  		// Conta quantas mensagens existem de um tipo específico
  		public function count($type_filter = self::TYPE_ALL){
  			$type_filter = $this->_type_transform($type_filter);

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
  			$type_filter = $this->_type_transform($type_filter);

			foreach($this->_messages as $message)
			if(($message->type & $type_filter) === $type_filter)
				return true;

			return false;
  		}

  		// Obtém uma cópia das mensagens diretamente como array
  		public function get_messages($type_filter = null) {
  			$type_filter = $this->_type_transform($type_filter);

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
