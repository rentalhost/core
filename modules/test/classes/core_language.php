<?php

	// Testes para core_language
	class unit_core__language_library extends test_class_library {
		public function test_language() {
			$this->test(1, lang('useful/default'));
			$this->test(2, lang('/test/useful/default'));

			$this->set_prefix('properties');
			$lang = lang('useful/simple');
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
			$lang = lang('useful/simple', 'fa-ke');
			$this->test(1, $lang->fake);
			$this->test(2, $lang->fake);
			$this->test(3, $lang->get_language_order());

			$this->set_prefix('reorder');
			$lang = lang('useful/simple', 'pt-br, en');
			$this->test(1, $lang->text);
			$this->test(2, $lang->set_language_order('en, pt-br'));
			$this->test(3, $lang->text);

			$this->set_prefix('static');
			$this->test(1, array_keys(core_language::get_available()));
			$this->test(2, array_keys(core_language::get_available('/core')));
			$this->test(3, core_language::get_available(null, 'en'));
			$this->test(4, core_language::get_available(null, 'pt-br'));
			$this->test(5, core_language::get_available(null, true));

			$this->set_prefix('request');
			$original_request = isset($_REQUEST['language-id']) ? $_REQUEST['language-id'] :
				(isset($_SESSION['language-id']) ? $_SESSION['language-id'] : null);
			$_REQUEST['language-id'] = 'en';
			$lang = lang('useful/simple');
			$this->test(1, $lang->text);
			$_REQUEST['language-id'] = $original_request;
			$_SESSION['language-id'] = $original_request;
		}
	}
