<?php namespace ProcessWire;

/**
 * ProcessWire 3.x Textformatter: Emoji 
 *
 * Copyright 2021 by Ryan Cramer | MPL 2.0
 * 
 * @property string $emojiTag
 * @property bool $wrapEmoji
 * @property string $wrapMarkup
 *
 */
class TextformatterEmoji extends Textformatter implements Module, ConfigurableModule {

	public static function getModuleInfo() {
		return array(
			'title' => 'Emoji',
			'version' => 1,
			'icon' => 'smile-o',
			'summary' => 'Converts 800+ emojis shortcodes in text to native browser UTF-8 emoji.',
			'requires' => 'ProcessWire>=3.0.164',
		);
	}

	/**
	 * Emojis loaded from emojis.json (upon request)
	 *
	 * @var array
	 *
	 */
	static protected $emojis = array();

	/**
	 * Construct
	 * 
	 */
	public function __construct() {
		$this->set('emojiTag', ':name:');
		$this->set('wrapEmoji', false);
		$this->set('wrapMarkup', '<span class="pw-emoji pw-emoji-{name}">{emoji}</span>');
		parent::__construct();
	}
	
	/**
	 * Get emoji by name or blank string if not found
	 *
	 * @param string $name
	 * @return string
	 *
	 */
	public function getEmoji($name) {
		if(empty(self::$emojis)) {
			self::$emojis = json_decode(file_get_contents(__DIR__ . '/emojis.json'), true);
		}
		return isset(self::$emojis[$name]) ? self::$emojis[$name] : '';
	}

	/**
	 * Format the given $value
	 *
	 * @param string $value
	 *
	 */
	public function format(&$value) {
		
		if(strpos($value, ':') === false) return;
		
		if($this->emojiTag === ':name:') {
			if(!preg_match_all('!:([a-z18][-_a-z0-9]*|\+1|\-1):!', $value, $matches)) return;
		} else {
			if(!preg_match_all('!\bemoji:([a-z18][-_a-z0-9]*|\+1|\-1)\b!', $value, $matches)) return;
		}
		
		$wrapMarkup = (int) $this->wrapEmoji ? $this->wrapMarkup : ''; 
		if(empty($wrapMarkup) || strpos($wrapMarkup, '{emoji}') === false) $wrapMarkup = '';
	
		$a = array();
		
		foreach($matches[0] as $key => $fullMatch) {
			$name = str_replace('-', '_', $matches[1][$key]);
			$emoji = $this->getEmoji($name);
			if($emoji === '') continue;
			if($wrapMarkup) $emoji = str_replace(array('{name}', '{emoji}'), array($name, $emoji), $wrapMarkup);
			$a[$fullMatch] = $emoji;
		}
		
		if(count($a)) {
			$value = str_replace(array_keys($a), array_values($a), $value);
		}
	}

	/**
	 * Module config
	 * 
	 * @param InputfieldWrapper $inputfields
	 * 
	 */
	public function getModuleConfigInputfields(InputfieldWrapper $inputfields) {
		$modules = $this->wire()->modules;
		$smile = $this->getEmoji('smile');
	
		/** @var InputfieldRadios $f */
		$f = $modules->get('InputfieldRadios');
		$f->label = $this->_('Emoji tag style');
		$f->description = $this->_('This determines what you must type in order to have it replaced with an emoji.'); 
		$f->attr('name', 'emojiTag');
		$f->addOption(':name:', sprintf($this->_('Emoji name wrapped with colons, for example `:%1$s:` converts to %2$s'), 'smile', $smile));
		$f->addOption('emoji:name', sprintf($this->_('Emoji name prefixed with word “emoji:”, for example `emoji:%1$s` converts to %2$s'), 'smile', $smile));
		$f->val($this->emojiTag);
		$inputfields->add($f);

		/** @var InputfieldToggle $f */
		$f = $modules->get('InputfieldToggle');
		$f->attr('name', 'wrapEmoji');
		$f->label = $this->_('Wrap emojis with span?'); 
		$f->description = 
			sprintf(
				$this->_('When enabled, emojis will be wrapped with markup like this `%s`.'), 
				str_replace(array('{name}', '{emoji}'), array('smile', $smile), $this->wrapMarkup)
			);
		$f->val($this->wrapEmoji ? 1 : 0); 
		$inputfields->add($f);

		/** @var MarkupAdminDataTable $table */
		$table = $modules->get('MarkupAdminDataTable');
		$table->setEncodeEntities(false);
		$table->headerRow(array(
			$this->_('Emoji'), 
			$this->_('Text to type')
		));
		/** @var InputfieldMarkup $f */
		$f = $modules->get('InputfieldMarkup');
		$f->attr('name', '_emojis_ref'); 
		$f->label = $this->_('Emoji reference (A-Z)'); 
		$f->collapsed = Inputfield::collapsedYes;
		$f->icon = 'smile-o';
		$tag = $this->emojiTag;
		foreach(self::$emojis as $name => $emoji) {
			$name = str_replace('name', $name, $tag);
			$table->row(array("<span style='font-size:larger'>$emoji</span>", "<code>$name</code>"));
		}
		$f->val($table->render());
		$inputfields->add($f);
	}
}