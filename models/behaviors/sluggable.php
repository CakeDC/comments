<?php
App::import('Core', 'Multibyte');
/**
 * Sluggable Behavior
 */
class SluggableBehavior extends ModelBehavior {
/**
 * Settings to configure the behavior
 *
 * @var array
 * @access public
 */
	public $settings = array();

/**
 * Default settings
 *
 * @var array
 * @access protected
 */
	protected $_defaults = array(
		'label' => 'title',
		'slug' => 'slug',
		'scope' => array(),
		'separator' => '_',
		'length' => 255,
		'unique' => true,
		'translation' => null,
		'update' => false);

/**
 * Initiate behaviour
 *
 * @param object $Model
 * @param array $settings
 * @access public
 */
	public function setup(&$Model, $settings = array()) {
		$this->settings[$Model->alias] = array_merge($this->_defaults, $settings);
	}

/**
 * beforeSave callback
 *
 * @param object $Model
 * @access public
 */
	public function beforeSave(&$Model) {
		if (empty($Model->data[$Model->alias])) {
			return;
		} else if (empty($Model->data[$Model->alias][$this->settings[$Model->alias]['label']])) {
			return;
		} else if (!$this->settings[$Model->alias]['update'] && !empty($Model->id)) {
			return;
		}

		$settings = $this->settings[$Model->alias];
		$slug = $this->multibyteSlug($Model, $Model->data[$Model->alias][$settings['label']], $settings['separator']);

		if ($settings['unique'] === true || is_array($settings['unique'])) {
			$conditions = array();
			if ($settings['unique'] === true) {
				$conditions[$Model->alias . '.' . $settings['slug'] . ' LIKE'] = $slug . '%';
			} else if (is_array($settings['unique'])) {
				foreach ($settings['unique'] as $field) {
					$conditions[$Model->alias . '.' . $field] = $Model->data[$Model->alias][$field];
				}
				$conditions[$Model->alias . '.' . $settings['slug'] . ' LIKE'] = $slug . '%';
			}

			if (!empty($Model->id)) {
				$conditions[$Model->alias . '.' . $Model->primaryKey . ' !='] = $Model->id;
			}

			$conditions = array_merge($conditions, $settings['scope']);

			$duplicates = $Model->find('all', array(
				'recursive' => -1,
				'conditions' => $conditions,
				'fields' => array($settings['slug'])));

			if (!empty($duplicates)) {
				$duplicates = Set::extract($duplicates, '{n}.' . $Model->alias . '.' . $settings['slug']);
				$startSlug = $slug;
				$index = 1;

				while ($index > 0) {
					if (!in_array($startSlug . $settings['separator'] . $index, $duplicates)) {
						$slug = $startSlug . $settings['separator'] . $index;
						$index = -1;
					}
					$index++;
				}
			}
		}

		if (!empty($Model->whitelist) && !in_array($settings['slug'], $Model->whitelist)) {
			$Model->whitelist[] = $settings['slug'];
		}

		$Model->data[$Model->alias][$settings['slug']] = $slug;
	}

/**
 * @param string
 * @return string
 * @access public
 */
	public function multibyteSlug(&$Model, $string = null) {
		$str = mb_strtolower($string);
		$str = preg_replace('/\xE3\x80\x80/', ' ', $str);
		$str = str_replace($this->settings[$Model->alias]['separator'], ' ', $str);
		$str = preg_replace( '#[:\#\*"()~$^{}`@+=;,<>!&%\.\]\/\'\\\\|\[]#', "\x20", $str );
		$str = str_replace('?', '', $str);
		$str = trim($str);
		$str = preg_replace('#\x20+#', $this->settings[$Model->alias]['separator'], $str);
		return $str;
	}

}
?>