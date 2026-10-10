<?php
/**
 * @package    mod_sobipro_latest_entries
 * @author     F.Yousefi - https://www.asiasun.ir
 * @copyright  (C) 2026 AsiaSun.ir, Pvt. Ltd. All rights reserved
 * @license    GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @version    2.0.0
 */

defined('_JEXEC') or die;

class ModSobiproLatestEntriesHelper
{
	public static function decodeSobiProString($var)
	{
		if (!is_string($var) || strlen($var) < 3) {
			return null;
		}

		$decoded = base64_decode($var, true);

		if ($decoded === false) {
			$unserialized = @unserialize($var);
			return $unserialized !== false ? $unserialized : null;
		}

		if (function_exists('gzinflate')) {
			$inflated = @gzinflate($decoded);
			if ($inflated !== false) {
				$unserialized = @unserialize($inflated);
				return $unserialized !== false ? $unserialized : null;
			}
		}

		$unserialized = @unserialize($decoded);
		return $unserialized !== false ? $unserialized : null;
	}

	public static function getLatestEntries($params)
	{
		$db = JFactory::getDbo();

		$limit = max(1, (int) $params->get('items_count', 10));
		$sectionId = max(1, (int) $params->get('section_id', 1));
		$sortFieldId = max(1, (int) $params->get('sort_field_id', 49));
		$titleFieldId = max(1, (int) $params->get('title_field_id', 1));
		$coverFieldId = max(1, (int) $params->get('cover_field_id', 48));
		$categoryFieldId = max(1, (int) $params->get('category_field_id', 77));

		$allowedRaw = trim((string) $params->get('allowed_categories', ''));
		$allowedCategories = array();
		if ($allowedRaw !== '') {
			foreach (explode(',', $allowedRaw) as $cid) {
				$cid = (int) trim($cid);
				if ($cid > 0) {
					$allowedCategories[] = $cid;
				}
			}
		}

		$query = $db->getQuery(true)
			->select(array(
				'a.sid',
				'a.baseData AS sort_value',
			))
			->from($db->quoteName('#__sobipro_field_data', 'a'))
			->join('INNER', $db->quoteName('#__sobipro_object', 'b') . ' ON a.sid = b.id')
			->where('a.fid = ' . (int) $sortFieldId)
			->where('a.section = ' . (int) $sectionId)
			->where("b.oType = " . $db->quote('entry'))
			->where('b.approved = 1')
			->order('a.baseData DESC');

		$db->setQuery($query, 0, $limit * 5);
		$rows = $db->loadObjectList();

		if (!is_array($rows)) {
			return array();
		}

		$entries = array();
		$seen = array();

		foreach ($rows as $row) {
			if (count($entries) >= $limit) {
				break;
			}

			$sid = (int) $row->sid;
			if (isset($seen[$sid])) {
				continue;
			}
			$seen[$sid] = true;

			$catQuery = $db->getQuery(true)
				->select('baseData')
				->from($db->quoteName('#__sobipro_field_data'))
				->where('fid = ' . (int) $categoryFieldId)
				->where('sid = ' . $sid);
			$db->setQuery($catQuery);
			$catRaw = $db->loadResult();

			$categories = self::decodeSobiProString($catRaw);
			if (!is_array($categories)) {
				$categories = array();
			}

			if ($allowedCategories) {
				$ok = false;
				foreach ($categories as $cid) {
					if (in_array((int) $cid, $allowedCategories, true)) {
						$ok = true;
						break;
					}
				}
				if (!$ok) {
					continue;
				}
			}

			$titleQuery = $db->getQuery(true)
				->select('baseData')
				->from($db->quoteName('#__sobipro_field_data'))
				->where('fid = ' . (int) $titleFieldId)
				->where('sid = ' . $sid);
			$db->setQuery($titleQuery);
			$title = (string) $db->loadResult();

			$coverQuery = $db->getQuery(true)
				->select('baseData')
				->from($db->quoteName('#__sobipro_field_data'))
				->where('fid = ' . (int) $coverFieldId)
				->where('sid = ' . $sid);
			$db->setQuery($coverQuery);
			$coverRaw = $db->loadResult();
			$cover = self::decodeSobiProString($coverRaw);
			$image = '';
			if (is_array($cover)) {
				$image = !empty($cover['thumb']) ? $cover['thumb'] : (!empty($cover['original']) ? $cover['original'] : '');
			}

			$objQuery = $db->getQuery(true)
				->select(array('id', 'nid'))
				->from($db->quoteName('#__sobipro_object'))
				->where('id = ' . $sid);
			$db->setQuery($objQuery);
			$obj = $db->loadObject();

			$catNid = '';
			if (!empty($categories[0])) {
				$catObjQuery = $db->getQuery(true)
					->select('nid')
					->from($db->quoteName('#__sobipro_object'))
					->where('id = ' . (int) $categories[0]);
				$db->setQuery($catObjQuery);
				$catNid = (string) $db->loadResult();
			}

			$entries[] = (object) array(
				'sid' => $sid,
				'title' => $title,
				'nid' => $obj ? (string) $obj->nid : '',
				'image' => $image,
				'category_nid' => $catNid,
			);
		}

		return $entries;
	}

	public static function buildUrl($entry, $basePath)
	{
		$base = trim((string) $basePath, '/');
		$parts = array();

		if ($base !== '') {
			$parts[] = $base;
		}

		if ($entry->category_nid !== '') {
			$parts[] = $entry->category_nid;
		}

		$parts[] = $entry->sid . '-' . $entry->nid;

		return JUri::root() . implode('/', $parts);
	}
}
