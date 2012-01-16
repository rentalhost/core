<?php

	// Testes para core_language
	class unit_core__language_library extends test_class_library {
		public function test_language() {
			$this->test(1, language('useful/default'));
			$this->test(2, language('/test/useful/default'));

			$this->set_prefix('properties');
			$lang = language('useful/simple');
			$this->test(1, $lang->get_dir_format());
			$this->test(2, $lang->get_real_value('just'));
			$this->test(3, $lang->get_real_value('fake'));
			$this->test(4, $lang->get_real_value('it'));
			$this->test(5, $lang->get_value('it'));
			$this->test(6, $lang->it);
			$this->test(7, $lang->get_value('fake'));
			$this->test(8, $lang->other('is', 'test'));
			$this->test(9, $lang->float(1.23));
			$this->test(10, $lang->only_ptbr);
			$this->test(11, $lang->only_en);
			$this->test(12, $lang->deep_language_ptbr);

			$this->set_prefix('order');
			$lang = language('useful/simple', 'fa-ke');
			$this->test(1, $lang->fake);
			$this->test(2, $lang->fake);
			$this->test(3, $lang->get_language_order());

			$this->set_prefix('reorder');
			$lang = language('useful/simple', 'pt-br, en');
			$this->test(1, $lang->text);
			$this->test(2, $lang->set_language_order('en, pt-br'));
			$this->test(3, $lang->text);
		}
	}
