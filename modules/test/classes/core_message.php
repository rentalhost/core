<?php

	// Teste para core_message
	class unit_core__message_library extends test_class_library {
		public function test_message() {
			$this->test(1, message());
			$this->test(2, message('Just a test!'));
			$this->test(3, message('Just a test!', core_message::TYPE_ERROR));
			$this->test(4, message('Just a test!', null, 1234));

			$this->set_prefix('append');
			$msg1 = message();
			$msg1->push('Hello', null, 1234);
			$msg1->push('World', core_message::TYPE_ERROR, null);
			$this->test(1, $msg1);

			$msg2 = message(null, core_message::TYPE_OK);
			$msg2->push('Hello', core_message::TYPE_OK | core_message::TYPE_WARNING, 1234);
			$msg2->push('World');
			$this->test(2, $msg2);

			$msg3 = message();
			$msg3->push('Hello', core_message::TYPE_OK, 1234);
			$msg3->push('World', core_message::TYPE_ERROR, 1234);
			$msg3->push('Again', core_message::TYPE_INFO, 1234);
			$this->test(3, $msg3);

			$this->test(4, $msg1->append($msg2));

			$this->set_prefix('count');
			$this->test(1, $msg1->count());
			$this->test(2, $msg2->count());
			$this->test(3, $msg3->count());
			$this->test(4, $msg3->count(core_message::TYPE_OK));
			$this->test(5, $msg3->count(core_message::TYPE_OK | core_message::TYPE_ERROR));
			$this->test(6, $msg3->count(core_message::TYPE_ERROR));
			$this->test(7, $msg3->count(core_message::TYPE_OK | core_message::TYPE_ERROR | core_message::TYPE_INFO));
			$this->test(8, $msg2->count(core_message::TYPE_WARNING));
			$this->test(9, $msg2->count(core_message::TYPE_OK));
			$this->test(10, $msg2->count(core_message::TYPE_OK | core_message::TYPE_WARNING));
			$this->test(11, $msg2->has(core_message::TYPE_OK));
			$this->test(12, $msg2->has(core_message::TYPE_OK | core_message::TYPE_WARNING));
			$this->test(13, $msg2->has(core_message::TYPE_OK | core_message::TYPE_ERROR));

			$this->set_prefix('copy');
			$this->test(1, $msg3->get_messages(core_message::TYPE_OK));
			$this->test(2, $msg3->get_messages(core_message::TYPE_OK | core_message::TYPE_ERROR));
			$this->test(4, $msg3->get_messages(core_message::TYPE_ERROR));
			$this->test(5, $msg3->get_messages(core_message::TYPE_OK | core_message::TYPE_ERROR | core_message::TYPE_INFO));
			$this->test(6, $msg2->get_messages(core_message::TYPE_WARNING));
			$this->test(7, $msg2->get_messages(core_message::TYPE_OK));
			$this->test(8, $msg2->get_messages(core_message::TYPE_OK | core_message::TYPE_WARNING));
			$this->test(9, $msg2->get_messages());

			$this->set_prefix('foreach');
			$count = 0;
			foreach($msg3 as $key => $value) {
				$this->test(++$count, $key);
				$this->test(++$count, $value);
			}
		}
	}
